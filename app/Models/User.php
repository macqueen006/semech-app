<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
//use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'image_path',
        'bio',
        'website',
        'twitter',
        'is_admin',
        'linkedin',
        'github',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['firstname', 'lastname', 'email', 'image_path'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => $eventName)
            ->useLogName('users');
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function savedposts()
    {
        return $this->hasMany(SavedPost::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * Get all posts that this user has bookmarked
     */
    public function bookmarkedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'bookmarks')
            ->withTimestamps();
    }

    /**
     * Check if user has bookmarked a specific post
     */
    public function hasBookmarked($post): bool
    {
        $postId = $post instanceof Post ? $post->id : $post;

        return $this->bookmarks()
            ->where('post_id', $postId)
            ->exists();
    }

    /**
     * Assign role with logging
     */
    public function assignRoleWithLog($role, $causedBy = null)
    {
        $causedBy = $causedBy ?? auth()->user();

        $this->assignRole($role);

        activity('roles')
            ->performedOn($this)
            ->causedBy($causedBy)
            ->withProperties([
                'user' => $this->firstname . ' ' . $this->lastname,
                'role' => is_string($role) ? $role : $role->name,
                'action' => 'assigned',
            ])
            ->log('role assigned');

        return $this;
    }

    /**
     * Remove role with logging
     */
    public function removeRoleWithLog($role, $causedBy = null)
    {
        $causedBy = $causedBy ?? auth()->user();

        $this->removeRole($role);

        activity('roles')
            ->performedOn($this)
            ->causedBy($causedBy)
            ->withProperties([
                'user' => $this->firstname . ' ' . $this->lastname,
                'role' => is_string($role) ? $role : $role->name,
                'action' => 'removed',
            ])
            ->log('role removed');

        return $this;
    }

    /**
     * Sync roles with logging
     */
    public function syncRolesWithLog($roles, $causedBy = null)
    {
        $causedBy = $causedBy ?? auth()->user();
        $oldRoles = $this->roles->pluck('name')->toArray();

        $this->syncRoles($roles);

        $newRoles = $this->fresh()->roles->pluck('name')->toArray();

        activity('roles')
            ->performedOn($this)
            ->causedBy($causedBy)
            ->withProperties([
                'user' => $this->firstname . ' ' . $this->lastname,
                'old_roles' => $oldRoles,
                'new_roles' => $newRoles,
                'action' => 'synced',
            ])
            ->log('roles synced');

        return $this;
    }

    /**
     * Give permission with logging
     */
    public function givePermissionToWithLog($permission, $causedBy = null)
    {
        $causedBy = $causedBy ?? auth()->user();

        $this->givePermissionTo($permission);

        activity('permissions')
            ->performedOn($this)
            ->causedBy($causedBy)
            ->withProperties([
                'user' => $this->firstname . ' ' . $this->lastname,
                'permission' => is_string($permission) ? $permission : $permission->name,
                'action' => 'granted',
            ])
            ->log('permission granted');

        return $this;
    }

    /**
     * Revoke permission with logging
     */
    public function revokePermissionToWithLog($permission, $causedBy = null)
    {
        $causedBy = $causedBy ?? auth()->user();

        $this->revokePermissionTo($permission);

        activity('permissions')
            ->performedOn($this)
            ->causedBy($causedBy)
            ->withProperties([
                'user' => $this->firstname . ' ' . $this->lastname,
                'permission' => is_string($permission) ? $permission : $permission->name,
                'action' => 'revoked',
            ])
            ->log('permission revoked');

        return $this;
    }
}
