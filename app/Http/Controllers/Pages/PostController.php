<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 7;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        // OPTIMIZED: Use pagination instead of manual offset
        $posts = Post::query()
            ->with([
                'category:id,name,backgroundColor,textColor',
                'user:id,firstname,lastname,image_path',
            ])
            ->leftJoin('highlight_posts', 'posts.id', '=', 'highlight_posts.post_id')
            ->isLive()
            ->notExpired()
            ->select('posts.*', \DB::raw('highlight_posts.id IS NOT NULL as is_highlighted'))
            ->orderBy('posts.id', 'desc')
            ->paginate($perPage);

        // OPTIMIZED: Cache active ads for 15 minutes
        // Ads don't change frequently
        $betweenPostsAds = Cache::remember('ads.between_posts', 900, function () {
            return Advertisement::where('position', 'between-posts')
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
        });

        return view('pages.articles', [
            'posts' => $posts,
            'betweenPostsAds' => $betweenPostsAds,
        ]);
    }

    public function trackAdClick(Request $request)
    {
        $request->validate([
            'advertisement_id' => 'required|exists:advertisements,id'
        ]);

        try {
            $advertisement = Advertisement::find($request->advertisement_id);

            if ($advertisement && $advertisement->isCurrentlyActive()) {
                // OPTIMIZED: Use increment instead of full model save
                $advertisement->increment('clicks');

                // Clear ad cache when click is tracked
                Cache::forget('ads.between_posts');

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
        // OPTIMIZED: Don't load ALL comments at once - that's insane for popular posts
        // Load only top-level comments, paginate them
        $post = Post::with([
            'category:id,name,slug,backgroundColor,textColor',
            'user:id,firstname,lastname,image_path'
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        // Check if post is visible
        if (!$post->isVisible()) {
            if (auth()->check()) {
                // Allow viewing if user is the author or has permission
                if (auth()->id() != $post->user_id && !auth()->user()->hasPermissionTo('post-super-list')) {
                    abort(404);
                }
            } else {
                abort(404);
            }
        }

        // OPTIMIZED: Paginate comments instead of loading all 4 levels
        // Load first 2 levels only, lazy-load deeper nesting via AJAX
        $comments = $post->comments()
            ->with([
                'user:id,firstname,lastname,image_path',
                'replies' => function ($q) {
                    $q->with('user:id,firstname,lastname,image_path')
                        ->where('is_approved', true)
                        ->orderBy('created_at', 'desc');
                }
            ])
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // OPTIMIZED: Cache related posts per category for 30 minutes
        $cacheKey = "related_posts.category_{$post->category_id}.exclude_{$post->id}";

        $relatedPosts = Cache::remember($cacheKey, 1800, function () use ($post) {
            $categoryPosts = Post::query()
                ->where('category_id', $post->category_id)
                ->where('id', '!=', $post->id)
                ->isLive()
                ->notExpired()
                ->select('id', 'title', 'slug', 'excerpt', 'image_path', 'category_id', 'user_id', 'view_count', 'created_at')
                ->with([
                    'category:id,name,slug,backgroundColor,textColor',
                    'user:id,firstname,lastname,image_path'
                ])
                ->orderBy('view_count', 'desc')
                ->limit(3)
                ->get();

            // OPTIMIZED: Only run second query if needed
            if ($categoryPosts->count() < 3) {
                $additionalCount = 3 - $categoryPosts->count();

                $additionalPosts = Post::query()
                    ->where('id', '!=', $post->id)
                    ->whereNotIn('id', $categoryPosts->pluck('id'))
                    ->isLive()
                    ->notExpired()
                    ->select('id', 'title', 'slug', 'excerpt', 'image_path', 'category_id', 'user_id', 'view_count', 'created_at')
                    ->with([
                        'category:id,name,slug,backgroundColor,textColor',
                        'user:id,firstname,lastname,image_path'
                    ])
                    ->orderBy('view_count', 'desc')
                    ->limit($additionalCount)
                    ->get();

                return $categoryPosts->merge($additionalPosts);
            }

            return $categoryPosts;
        });

        // OPTIMIZED: Queue view tracking instead of blocking response
        // Move to observer or queue job
        dispatch(function () use ($post) {
            $post->recordView();
        })->afterResponse();

        return view('pages.article', compact('post', 'relatedPosts', 'comments'));
    }

    /**
     * Load more comments via AJAX (implement this for nested comments)
     */
    public function loadMoreComments(Request $request, $postId)
    {
        $page = $request->get('page', 1);

        $comments = Comment::where('post_id', $postId)
            ->whereNull('parent_id')
            ->where('is_approved', true)
            ->with([
                'user:id,firstname,lastname,image_path',
                'replies' => function ($q) {
                    $q->with('user:id,firstname,lastname,image_path')
                        ->where('is_approved', true)
                        ->limit(5); // Limit replies shown initially
                }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($comments);
    }
}
