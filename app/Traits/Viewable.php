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
     * OPTIMIZED: Record a view without blocking the response
     *
     * Strategy for small applications without Redis:
     * - Increment counter immediately (fast, no INSERT)
     * - Sample 10% of views for detailed tracking
     * - Queue detailed tracking to not block response
     */
    public function recordView($request = null): void
    {
        // CRITICAL: Increment counter immediately (fast, just an UPDATE)
        // This updates posts.view_count directly without INSERT overhead
        $this->increment('view_count');

        // Invalidate related posts cache every 10 views to avoid excessive cache churn
        // Only clear when it actually matters (multiples of 10)
        if ($this->view_count % 10 === 0 && $this->category_id) {
            Cache::forget("related_posts.category_{$this->category_id}.exclude_{$this->id}");
        }

        // OPTIMIZATION: Sample detailed tracking at 10% rate
        // For detailed analytics (IP, user agent, referrer)
        // This reduces DB writes by 90% while maintaining statistical accuracy
        if (rand(1, 10) === 1) {
            // Queue the detailed tracking to not block response
            dispatch(function () use ($request) {
                PostView::recordView($this, $request);
            })->afterResponse();
        }
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
     * Use this in views instead of direct attribute access
     *
     * Note: Formatted view count is already cached in Post model
     * via getFormattedViewCountAttribute() accessor
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
