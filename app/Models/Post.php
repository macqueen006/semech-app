<?php

namespace App\Models;

use App\Traits\Viewable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Post extends Model
{
    use HasFactory;
    use Viewable;
    use LogsActivity;
    use Searchable;

    protected $fillable = [
        'user_id',
        'title',
        'excerpt',
        'body',
        'scheduled_at',
        'expires_at',
        'image_path',
        'slug',
        'is_published',
        'can_comment',
        'additional_info',
        'category_id',
        'read_time',
        'view_count',
        'change_user_id',
        'changelog',
        //SEO
        'meta_title',
        'meta_description',
        'focus_keyword',
        'image_alt',
        'og_title',
        'og_description',
        'og_image',
        'twitter_title',
        'twitter_description',
        'twitter_image',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_published' => 'boolean',
        'can_comment' => 'boolean',
    ];

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable()
    {
        return $this->is_published === true;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'slug', 'excerpt', 'is_published', 'category_id', 'scheduled_at'])
            ->logOnlyDirty() // Only log changed attributes
            ->dontSubmitEmptyLogs() // Don't log if nothing changed
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'created',
                'updated' => 'updated',
                'deleted' => 'deleted',
                default => $eventName,
            })
            ->useLogName('posts');
    }

    public function getPublishingStatusAttribute()
    {
        if (!$this->is_published) {
            return 'draft';
        }

        if ($this->scheduled_at && $this->scheduled_at->greaterThan(now())) {
            return 'scheduled';
        }

        return 'published';
    }

    public function isVisible()
    {
        if (!$this->is_published) {
            return false;
        }

        if ($this->scheduled_at && $this->scheduled_at->isFuture()) {
            return false;
        }

        return true;
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedViewCountAttribute()
    {
        if ($this->view_count >= 1000000) {
            return round($this->view_count / 1000000, 1) . 'M';
        } elseif ($this->view_count >= 1000) {
            return round($this->view_count / 1000, 1) . 'K';
        }
        return $this->view_count;
    }

    public function changeUser()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)
            ->whereNull('parent_id') // Only top-level comments
            ->with(['replies.replies.replies', 'user'])
            ->orderBy('created_at', 'desc');
    }

    public function historypost()
    {
        return $this->hasMany(HistoryPost::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function highlightPosts()
    {
        return $this->hasMany(HighlightPost::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function bookmarkedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bookmarks')
            ->withTimestamps();
    }

    public function bookmarksCount(): int
    {
        return $this->bookmarks()->count();
    }

    public function scopeIsLive($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            });
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }
}
