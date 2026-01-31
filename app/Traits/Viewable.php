<?php

namespace App\Traits;

use App\Models\PostView;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Record a view
     */
    public function recordView($request = null): void
    {
        PostView::recordView($this, $request);
    }

    /**
     * Get views in date range
     */
    public function getViewsInRange($startDate, $endDate): int
    {
        return $this->views()
            ->whereBetween('viewed_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Get unique viewers count
     */
    public function getUniqueViewersCount(): int
    {
        return $this->views()
            ->distinct()
            ->count('ip_address');
    }
}
