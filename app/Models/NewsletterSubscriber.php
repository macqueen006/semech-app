<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{

    protected $fillable = [
        'email',
        'token',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    /**
     * Check if subscriber is active
     */
    public function isSubscribed()
    {
        return $this->subscribed_at !== null && $this->unsubscribed_at === null;
    }

    /**
     * Scope to get only active subscribers
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('subscribed_at')
            ->whereNull('unsubscribed_at');
    }
}
