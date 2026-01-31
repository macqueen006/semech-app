<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PostView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referer',
        'device_type',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a view for a post
     */
    public static function recordView(Post $post, $request = null): void
    {
        $request = $request ?? request();

        // Detect device type
        $userAgent = $request->userAgent();
        $deviceType = 'desktop';
        if (preg_match('/mobile/i', $userAgent)) {
            $deviceType = 'mobile';
        } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
            $deviceType = 'tablet';
        }

        // Check if this IP already viewed in last 24 hours (prevent spam)
        $recentView = static::where('post_id', $post->id)
            ->where('ip_address', $request->ip())
            ->where('viewed_at', '>', now()->subMinutes(1))
            ->exists();

        if (!$recentView) {
            static::create([
                'post_id' => $post->id,
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'device_type' => $deviceType,
                'viewed_at' => now(),
            ]);

            // Increment view count on post
            if (!session()->has('viewed_post_' . $post->id)) {
                $post->increment('view_count');
                session()->put('viewed_post_' . $post->id, true);
            }
        }
    }

    /**
     * Get views for a date range
     */
    public static function getViewsInRange($startDate, $endDate, $postId = null)
    {
        $query = static::whereBetween('viewed_at', [$startDate, $endDate]);

        if ($postId) {
            $query->where('post_id', $postId);
        }

        return $query->count();
    }

    /**
     * Get daily views breakdown
     */
    public static function getDailyViews($startDate, $endDate)
    {
        return static::selectRaw('DATE(viewed_at) as date, COUNT(*) as count')
            ->whereBetween('viewed_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(viewed_at)'))
            ->orderBy('date')
            ->get();
    }

    /**
     * Get top referrers
     */
    public static function getTopReferrers($limit = 10)
    {
        return static::selectRaw('referer, COUNT(*) as count')
            ->whereNotNull('referer')
            ->groupBy('referer')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get device breakdown
     */
    public static function getDeviceBreakdown($startDate = null, $endDate = null)
    {
        $query = static::selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type');

        if ($startDate && $endDate) {
            $query->whereBetween('viewed_at', [$startDate, $endDate]);
        }

        return $query->get();
    }
}
