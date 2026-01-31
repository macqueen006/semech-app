<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\PostView;
use App\Models\NewsletterSubscriber;
use App\Models\Category;
use App\Models\Bookmark;
use App\Models\Comment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Cache duration in seconds (1 hour)
     */
    protected $cacheDuration = 3600;

    /**
     * Remember with key tracking for clearing later
     */
    protected function rememberWithTracking($key, $callback)
    {
        // Track this key for later clearing
        $this->trackCacheKey($key);

        return Cache::remember($key, $this->cacheDuration, $callback);
    }

    /**
     * Track a cache key
     */
    protected function trackCacheKey($key)
    {
        $keys = Cache::get('analytics_cache_keys', []);

        if (!in_array($key, $keys)) {
            $keys[] = $key;
            Cache::forever('analytics_cache_keys', $keys);
        }
    }

    /**
     * Get overview statistics
     */
    public function getOverviewStats($startDate, $endDate, $useCache = true)
    {
        $cacheKey = "analytics.overview.{$startDate->format('Y-m-d')}.{$endDate->format('Y-m-d')}";

        if (!$useCache) {
            Cache::forget($cacheKey);
        }

        return $this->rememberWithTracking($cacheKey, function () use ($startDate, $endDate) {
            return [
                'posts' => $this->getPostStats($startDate, $endDate),
                'views' => $this->getViewStats($startDate, $endDate),
                'users' => $this->getUserStats($startDate, $endDate),
                'subscribers' => $this->getSubscriberStats($startDate, $endDate),
                'engagement' => $this->getEngagementStats($startDate, $endDate),
            ];
        });
    }

    /**
     * Get post statistics
     */
    protected function getPostStats($startDate, $endDate)
    {
        return [
            'total' => Post::count(),
            'published' => Post::where('is_published', true)->count(),
            'drafts' => Post::where('is_published', false)->count(),
            'scheduled' => Post::where('is_published', true)
                ->where('scheduled_at', '>', now())
                ->count(),
            'in_period' => Post::whereBetween('created_at', [$startDate, $endDate])->count(),
            'published_in_period' => Post::where('is_published', true)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
        ];
    }

    /**
     * Get view statistics (accurate with PostView table)
     */
    protected function getViewStats($startDate, $endDate)
    {
        return [
            'total' => Post::sum('view_count') ?? 0,
            'in_period' => PostView::whereBetween('viewed_at', [$startDate, $endDate])->count(),
            'unique_in_period' => PostView::whereBetween('viewed_at', [$startDate, $endDate])
                ->distinct()
                ->count('ip_address'),
            'daily_breakdown' => PostView::getDailyViews($startDate, $endDate),
            'device_breakdown' => PostView::getDeviceBreakdown($startDate, $endDate),
        ];
    }

    /**
     * Get user statistics
     */
    protected function getUserStats($startDate, $endDate)
    {
        return [
            'total' => User::count(),
            'new_in_period' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'active_authors' => User::whereHas('posts', function ($query) {
                $query->where('is_published', true);
            })->count(),
        ];
    }

    /**
     * Get subscriber statistics
     */
    protected function getSubscriberStats($startDate, $endDate)
    {
        return [
            'total_active' => NewsletterSubscriber::active()->count(),
            'new_in_period' => NewsletterSubscriber::active()
                ->whereBetween('subscribed_at', [$startDate, $endDate])
                ->count(),
            'unsubscribed_in_period' => NewsletterSubscriber::whereNotNull('unsubscribed_at')
                ->whereBetween('unsubscribed_at', [$startDate, $endDate])
                ->count(),
        ];
    }

    /**
     * Get engagement statistics
     */
    protected function getEngagementStats($startDate, $endDate)
    {
        $totalPosts = Post::where('is_published', true)->count();
        $totalComments = Comment::count();

        return [
            'total_bookmarks' => Bookmark::count(),
            'bookmarks_in_period' => Bookmark::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_comments' => $totalComments,
            'comments_in_period' => Comment::whereBetween('created_at', [$startDate, $endDate])->count(),
            'avg_comments_per_post' => $totalPosts > 0 ? round($totalComments / $totalPosts, 2) : 0,
        ];
    }

    /**
     * Get top performing posts
     */
    public function getTopPosts($limit = 10, $orderBy = 'view_count')
    {
        $cacheKey = "analytics.top_posts.{$orderBy}.{$limit}";

        return $this->rememberWithTracking($cacheKey, function () use ($limit, $orderBy) {
            return Post::where('is_published', true)
                ->orderByDesc($orderBy)
                ->with('user', 'category')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get popular categories
     */
    public function getPopularCategories($limit = 5)
    {
        $cacheKey = "analytics.popular_categories.{$limit}";

        return $this->rememberWithTracking($cacheKey, function () use ($limit) {
            return Category::withCount(['posts' => function ($query) {
                $query->where('is_published', true);
            }])
                ->whereHas('posts', function ($query) {
                    $query->where('is_published', true);
                })
                ->orderByDesc('posts_count')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get category views
     */
    public function getCategoryViews($limit = 5)
    {
        $cacheKey = "analytics.category_views.{$limit}";

        return $this->rememberWithTracking($cacheKey, function () use ($limit) {
            return Post::select('category_id', DB::raw('SUM(view_count) as total_views'))
                ->where('is_published', true)
                ->whereNotNull('category_id')
                ->groupBy('category_id')
                ->orderByDesc('total_views')
                ->with('category')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get top authors by post count
     */
    public function getTopAuthorsByPosts($limit = 5)
    {
        $cacheKey = "analytics.top_authors_posts.{$limit}";

        return $this->rememberWithTracking($cacheKey, function () use ($limit) {
            return User::withCount(['posts' => function ($query) {
                $query->where('is_published', true);
            }])
                ->whereHas('posts', function ($query) {
                    $query->where('is_published', true);
                })
                ->orderByDesc('posts_count')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get top authors by views
     */
    public function getTopAuthorsByViews($limit = 5)
    {
        $cacheKey = "analytics.top_authors_views.{$limit}";

        return $this->rememberWithTracking($cacheKey, function () use ($limit) {
            return User::select('users.*', DB::raw('SUM(posts.view_count) as total_views'))
                ->join('posts', 'users.id', '=', 'posts.user_id')
                ->where('posts.is_published', true)
                ->groupBy(
                    'users.id',
                    'users.firstname',
                    'users.lastname',
                    'users.email',
                    'users.email_verified_at',
                    'users.password',
                    'users.remember_token',
                    'users.created_at',
                    'users.updated_at'
                )
                ->orderByDesc('total_views')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get most bookmarked posts
     */
    public function getMostBookmarkedPosts($limit = 5)
    {
        $cacheKey = "analytics.most_bookmarked.{$limit}";

        return $this->rememberWithTracking($cacheKey, function () use ($limit) {
            return Post::withCount('bookmarks')
                ->where('is_published', true)
                ->whereHas('bookmarks')
                ->orderByDesc('bookmarks_count')
                ->with('user')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get most commented posts
     */
    public function getMostCommentedPosts($limit = 5)
    {
        $cacheKey = "analytics.most_commented.{$limit}";

        return $this->rememberWithTracking($cacheKey, function () use ($limit) {
            return Post::withCount('comments')
                ->where('is_published', true)
                ->whereHas('comments')
                ->orderByDesc('comments_count')
                ->with('user')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get growth comparison
     */
    public function getGrowthComparison($currentStart, $currentEnd, $previousStart, $previousEnd)
    {
        $current = [
            'posts' => Post::whereBetween('created_at', [$currentStart, $currentEnd])->count(),
            'views' => PostView::whereBetween('viewed_at', [$currentStart, $currentEnd])->count(),
            'bookmarks' => Bookmark::whereBetween('created_at', [$currentStart, $currentEnd])->count(),
            'comments' => Comment::whereBetween('created_at', [$currentStart, $currentEnd])->count(),
        ];

        $previous = [
            'posts' => Post::whereBetween('created_at', [$previousStart, $previousEnd])->count(),
            'views' => PostView::whereBetween('viewed_at', [$previousStart, $previousEnd])->count(),
            'bookmarks' => Bookmark::whereBetween('created_at', [$previousStart, $previousEnd])->count(),
            'comments' => Comment::whereBetween('created_at', [$previousStart, $previousEnd])->count(),
        ];

        return [
            'current' => $current,
            'previous' => $previous,
            'changes' => [
                'posts' => $this->calculatePercentageChange($current['posts'], $previous['posts']),
                'views' => $this->calculatePercentageChange($current['views'], $previous['views']),
                'bookmarks' => $this->calculatePercentageChange($current['bookmarks'], $previous['bookmarks']),
                'comments' => $this->calculatePercentageChange($current['comments'], $previous['comments']),
            ],
        ];
    }

    /**
     * Calculate percentage change
     */
    protected function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Clear all analytics cache
     */
    public function clearCache()
    {
        // Get all tracked cache keys
        $keys = Cache::get('analytics_cache_keys', []);

        // Clear each tracked key
        foreach ($keys as $key) {
            Cache::forget($key);
        }

        // Clear the tracking key itself
        Cache::forget('analytics_cache_keys');
    }
}
