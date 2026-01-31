<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Comment extends Model
{
//    use HasFactory;
    use LogsActivity;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($comment) {
            $comment->body = strip_tags($comment->body);
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['content', 'is_approved'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'created',
                'updated' => 'updated',
                'deleted' => 'deleted',
                default => $eventName,
            })
            ->useLogName('comments');
    }

    protected $fillable = [
        'post_id', 'user_id', 'parent_id', 'name', 'body', 'is_approved',
    ];

    protected $with = ['user']; // Eager load user relationship

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Parent comment (for replies)
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // Child comments (replies to this comment)
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    // Get approved replies only
    public function approvedReplies()
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->where('is_approved', true)
            ->orderBy('created_at', 'asc');
    }

    // Get all replies recursively
    public function allReplies()
    {
        return $this->replies()->with('allReplies');
    }

    // Check if this is a reply
    public function isReply()
    {
        return $this->parent_id !== null;
    }

    // Get nesting depth
    public function getDepth()
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    // Check if comment is by authenticated user
    public function isOwnComment()
    {
        return $this->user_id && auth()->check() && $this->user_id === auth()->id();
    }

    // Check if user can see this comment
    public function canView()
    {
        // Approved comments are visible to everyone
        if ($this->is_approved) {
            return true;
        }

        // Not approved - only visible to author and post owner
        if (auth()->check()) {
            // Comment author can see their own comment
            if ($this->isOwnComment()) {
                return true;
            }

            // Post owner can see all comments on their post
            if ($this->post->user_id === auth()->id()) {
                return true;
            }

            // Users with permission can see all
            if (auth()->user()->hasPermissionTo('comment-super-list')) {
                return true;
            }
        }

        return false;
    }

    // Get commenter name (from user or name field)
    public function getCommenterName()
    {
        return $this->user
            ? $this->user->firstname . ' ' . $this->user->lastname
            : $this->name;
    }

    // Get commenter avatar
    public function getCommenterAvatar()
    {
        if ($this->user && $this->user->image_path) {
            return $this->user->image_path;
        }
        return null;
    }

    // Get commenter initials
    public function getCommenterInitials()
    {
        if ($this->user) {
            return substr($this->user->firstname, 0, 1) . substr($this->user->lastname, 0, 1);
        }
        return substr($this->name, 0, 2);
    }

    // Scope for approved comments
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    // Scope for pending comments
    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }
}
