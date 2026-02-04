<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 7;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $posts = Post::with([
            'category' => fn($q) => $q->select('id', 'name', 'backgroundColor', 'textColor'),
            'user' => fn($q) => $q->select('id', 'firstname', 'lastname', 'image_path'),
        ])
            ->isLive()
            ->notExpired()
            ->select('posts.*', \DB::raw('(SELECT COUNT(*) FROM highlight_posts WHERE post_id = posts.id) > 0 AS is_highlighted'))
            ->offset($offset)
            ->limit($perPage)
            ->orderBy('id', 'desc')
            ->get();

        $totalPosts = Post::isLive()->notExpired()->count();
        $hasMore = $totalPosts > ($offset + $perPage);
        $hasPrevious = $page > 1;

        // Get all active between-posts ads for rotation
        $betweenPostsAds = Advertisement::where('position', 'between-posts')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->orderBy('display_order')
            ->get();

        return view('pages.articles', compact('posts', 'betweenPostsAds', 'offset', 'page', 'hasMore', 'hasPrevious'));
    }

    public function trackAdClick(Request $request)
    {
        $request->validate([
            'advertisement_id' => 'required|exists:advertisements,id'
        ]);

        try {
            $advertisement = Advertisement::find($request->advertisement_id);

            if ($advertisement && $advertisement->isCurrentlyActive()) {
                $advertisement->recordClick();

                return response()->json([
                    'success' => true,
                    'message' => 'Click tracked successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Advertisement not found or inactive'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error tracking ad click: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to track click'
            ], 500);
        }
    }

    public function show($slug)
    {
        $post = Post::with(['comments.replies.replies.replies.user', 'category', 'user'])
            ->where('slug', $slug)
            ->firstOrFail();

        $post->recordView();

        $user = User::find($post->user_id);

        // Check if post is published
        if (!$post->is_published) {
            if (auth()->check()) {
                // Allow viewing if user is the author or has permission
                if (auth()->id() != $user->id && !auth()->user()->hasPermissionTo('post-super-list')) {
                    abort(404);
                }
            } else {
                abort(404);
            }
        }

        // Get related posts
        $relatedPosts = Post::where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->where('is_published', true)
            ->with(['category', 'user'])
            ->orderBy('view_count', 'desc')
            ->limit(3)
            ->get();

        // If we don't have enough related posts, fill with recent popular posts
        if ($relatedPosts->count() < 3) {
            $additionalPosts = Post::where('id', '!=', $post->id)
                ->where('is_published', true)
                ->whereNotIn('id', $relatedPosts->pluck('id'))
                ->with(['category', 'user'])
                ->orderBy('view_count', 'desc')
                ->limit(3 - $relatedPosts->count())
                ->get();

            $relatedPosts = $relatedPosts->merge($additionalPosts);
        }
        return view('pages.article', compact('post', 'relatedPosts'));
    }
}
