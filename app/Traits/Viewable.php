<?php

namespace App\Traits;

use App\Models\PostView;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

trait Viewable
{
    /**
     * Boot the trait
     */
    public static function bootViewable(): void
    {
        // Optional: Auto-record views when post is accessed
    }

    /**
     * Relationship to views
     */
    public function views(): HasMany
    {
        return $this->hasMany(PostView::class, 'post_id');
    }

    /**
     * OPTIMIZED: Record a view with deduplication
     *
     * Only counts as a view if:
     * - Same user hasn't viewed in last 24 hours (session-based)
     * - Same IP hasn't viewed in last 24 hours (for logged-out users)
     *
     * This prevents refresh spam and gives organic view counts
     */
    public function recordView($request = null): void
    {
        $request = $request ?? request();

        // Generate unique identifier for this user/post combination
        $identifier = $this->getViewIdentifier($request);
        $cacheKey = "post_view_{$this->id}_{$identifier}";

        // Check if this user/IP has already viewed this post in last 24 hours
        if (Cache::has($cacheKey)) {
            // Already counted this view, skip
            return;
        }

        // Mark this view as counted (expires in 24 hours)
        Cache::put($cacheKey, true, now()->addHours(24));

        // Increment the view counter
        $this->increment('view_count');

        // Invalidate related posts cache every 10 views
        if ($this->view_count % 10 === 0 && $this->category_id) {
            Cache::forget("related_posts.category_{$this->category_id}.exclude_{$this->id}");
        }

        // Sample 10% of views for detailed tracking (IP, user agent, etc.)
        if (rand(1, 10) === 1) {
            dispatch(function () use ($request) {
                PostView::recordView($this, $request);
            })->afterResponse();
        }
    }

    /**
     * Generate unique identifier for view deduplication
     * Priority: User ID > Session ID > IP Address
     */
    private function getViewIdentifier($request): string
    {
        // If user is logged in, use their user ID
        if (auth()->check()) {
            return 'user_' . auth()->id();
        }

        // If user has a session, use session ID
        if ($request->hasSession()) {
            return 'session_' . $request->session()->getId();
        }

        // Fallback to IP address (for users without sessions)
        return 'ip_' . md5($request->ip());
    }

    /**
     * Check if current user has viewed this post (useful for UI indicators)
     */
    public function hasBeenViewedByCurrentUser($request = null): bool
    {
        $request = $request ?? request();
        $identifier = $this->getViewIdentifier($request);
        $cacheKey = "post_view_{$this->id}_{$identifier}";

        return Cache::has($cacheKey);
    }

    /**
     * Get views in date range
     * OPTIMIZED: Uses index on viewed_at
     */
    public function getViewsInRange($startDate, $endDate): int
    {
        return $this->views()
            ->whereBetween('viewed_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Get unique viewers count
     * OPTIMIZED: Uses index on ip_address
     */
    public function getUniqueViewersCount(): int
    {
        return $this->views()
            ->distinct('ip_address')
            ->count('ip_address');
    }

    /**
     * Get cached view count with formatted display
     */
    public function getFormattedViewCountAttribute()
    {
        return Cache::remember("post_{$this->id}_formatted_views", 300, function () {
            $count = $this->view_count;

            if ($count >= 1000000) {
                return round($count / 1000000, 1) . 'M';
            } elseif ($count >= 1000) {
                return round($count / 1000, 1) . 'K';
            }

            return $count;
        });
    }
}
