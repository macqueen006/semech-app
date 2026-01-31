<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'link_url',
        'position',
        'size',
        'is_active',
        'open_new_tab',
        'display_order',
        'start_date',
        'end_date',
        'clicks',
        'impressions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'open_new_tab' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Check if ad is currently active
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    // Get active ads by position
    public static function getActiveByPosition(string $position)
    {
        return self::where('is_active', true)
            ->where('position', $position)
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
    }

    // Increment impressions
    public function recordImpression(): void
    {
        $this->increment('impressions');
    }

    // Increment clicks
    public function recordClick(): void
    {
        $this->increment('clicks');
    }

    // Get CTR (Click Through Rate)
    public function getCtrAttribute(): float
    {
        if ($this->impressions === 0) {
            return 0;
        }

        return round(($this->clicks / $this->impressions) * 100, 2);
    }
}
