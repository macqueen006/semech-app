<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\HighlightPost;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Display the homepage with optimized queries
     */
    public function index()
    {
        // OPTIMIZED: Single query with LEFT JOIN instead of correlated subquery
        // Eliminates subquery that ran for every post row
        $posts = Post::query()
            ->with([
                'category:id,slug,name,backgroundColor,textColor',
                'user:id,firstname,lastname,image_path',
            ])
            ->leftJoin('highlight_posts', 'posts.id', '=', 'highlight_posts.post_id')
            ->isLive()
            ->notExpired()
            ->select('posts.*', DB::raw('highlight_posts.id IS NOT NULL as is_highlighted'))
            ->orderBy('posts.id', 'desc')
            ->limit(5)
            ->get();

        // OPTIMIZED: Cache highlighted posts for 5 minutes
        // Fixed N+1: Added ->with('post') to eager load relationship
        $highlightedPosts = Cache::remember('homepage.highlighted_posts', 300, function () {
            return HighlightPost::with([
                'post' => function ($q) {
                    $q->select('id', 'title', 'slug', 'excerpt', 'image_path', 'category_id', 'user_id', 'created_at', 'is_published', 'image_alt', 'read_time')
                        ->with([
                            'category:id,slug,name,backgroundColor,textColor',
                            'user:id,firstname,lastname,image_path'
                        ]);
                }
            ])
                ->whereHas('post', fn($q) => $q->where('is_published', true))
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        });

        // OPTIMIZED: Cache trending topics for 10 minutes
        // Trending calculation is expensive (30-day aggregation)
        $trendingTopics = Cache::remember('homepage.trending_topics', 600, function () {
            return Post::query()
                ->with('category:id,name,slug,backgroundColor,textColor')
                ->isLive()
                ->notExpired()
                ->where('created_at', '>=', now()->subDays(30))
                ->orderBy('view_count', 'desc')
                ->limit(7)
                ->get(['id', 'title', 'slug', 'view_count', 'category_id', 'created_at']);
        });

        // OPTIMIZED: Cache popular posts for 10 minutes
        $popularPosts = Cache::remember('homepage.popular_posts', 600, function () {
            return Post::query()
                ->with([
                    'category:id,name,slug,backgroundColor,textColor',
                    'user:id,firstname,lastname,image_path'
                ])
                ->isLive()
                ->notExpired()
                ->orderBy('view_count', 'desc')
                ->limit(3)
                ->get(['id', 'title', 'slug', 'excerpt', 'image_path', 'category_id', 'user_id', 'view_count', 'created_at']);
        });

        return view('pages.index', [
            'posts' => $posts,
            'highlightedPosts' => $highlightedPosts,
            'trendingTopics' => $trendingTopics,
            'popularPosts' => $popularPosts
        ]);
    }

    /**
     * Load more posts for infinite scroll
     */
    public function loadMore(Request $request)
    {
        $offset = $request->input('offset', 0);

        // Same optimization as main query
        $posts = Post::query()
            ->with([
                'category:id,name,slug,backgroundColor,textColor',
                'user:id,firstname,lastname,image_path',
            ])
            ->leftJoin('highlight_posts', 'posts.id', '=', 'highlight_posts.post_id')
            ->isLive()
            ->notExpired()
            ->select('posts.*', DB::raw('highlight_posts.id IS NOT NULL as is_highlighted'))
            ->offset($offset)
            ->limit(5)
            ->orderBy('posts.id', 'desc')
            ->get();

        return response()->json([
            'posts' => $posts,
            'hasMore' => $posts->count() === 5
        ]);
    }

    /**
     * Clear homepage caches
     * Call this when posts are published/updated (via PostObserver)
     */
    public static function clearCache(): void
    {
        Cache::forget('homepage.highlighted_posts');
        Cache::forget('homepage.trending_topics');
        Cache::forget('homepage.popular_posts');
    }
}
