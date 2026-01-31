<?php

namespace App\Helpers;

use Spatie\Activitylog\Facades\Activity;

class ActivityLogger
{
    /**
     * Log user login
     */
    public static function logLogin($user)
    {
        activity('authentication')
            ->causedBy($user)
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toDateTimeString(),
            ])
            ->log('logged in');
    }

    /**
     * Log user logout
     */
    public static function logLogout($user)
    {
        activity('authentication')
            ->causedBy($user)
            ->withProperties([
                'ip' => request()->ip(),
                'timestamp' => now()->toDateTimeString(),
            ])
            ->log('logged out');
    }

    /**
     * Log post publish
     */
    public static function logPostPublish($post, $user)
    {
        activity('posts')
            ->performedOn($post)
            ->causedBy($user)
            ->withProperties([
                'title' => $post->title,
                'scheduled_at' => $post->scheduled_at?->toDateTimeString(),
            ])
            ->log('published');
    }

    /**
     * Log post unpublish
     */
    public static function logPostUnpublish($post, $user)
    {
        activity('posts')
            ->performedOn($post)
            ->causedBy($user)
            ->withProperties([
                'title' => $post->title,
            ])
            ->log('unpublished');
    }

    /**
     * Log bulk delete
     */
    public static function logBulkDelete($modelType, $count, $user, $ids = [])
    {
        activity($modelType)
            ->causedBy($user)
            ->withProperties([
                'count' => $count,
                'ids' => $ids,
                'timestamp' => now()->toDateTimeString(),
            ])
            ->log('bulk deleted');
    }

    /**
     * Log bulk update
     */
    public static function logBulkUpdate($modelType, $count, $user, $changes = [])
    {
        activity($modelType)
            ->causedBy($user)
            ->withProperties([
                'count' => $count,
                'changes' => $changes,
                'timestamp' => now()->toDateTimeString(),
            ])
            ->log('bulk updated');
    }

    /**
     * Log comment approval
     */
    public static function logCommentApproval($comment, $user)
    {
        activity('comments')
            ->performedOn($comment)
            ->causedBy($user)
            ->withProperties([
                'post_id' => $comment->post_id,
                'comment_content' => substr($comment->content, 0, 100),
            ])
            ->log('approved');
    }

    /**
     * Log post highlight
     */
    public static function logPostHighlight($post, $user)
    {
        activity('posts')
            ->performedOn($post)
            ->causedBy($user)
            ->withProperties([
                'title' => $post->title,
            ])
            ->log('highlighted');
    }

    /**
     * Log post unhighlight
     */
    public static function logPostUnhighlight($post, $user)
    {
        activity('posts')
            ->performedOn($post)
            ->causedBy($user)
            ->withProperties([
                'title' => $post->title,
            ])
            ->log('removed highlight');
    }

    /**
     * Log category change
     */
    public static function logCategoryChange($post, $oldCategory, $newCategory, $user)
    {
        activity('posts')
            ->performedOn($post)
            ->causedBy($user)
            ->withProperties([
                'post_title' => $post->title,
                'old_category' => $oldCategory?->name,
                'new_category' => $newCategory?->name,
            ])
            ->log('category changed');
    }

    /**
     * Log permission change
     */
    public static function logPermissionChange($user, $action, $permissions, $causedBy)
    {
        activity('permissions')
            ->performedOn($user)
            ->causedBy($causedBy)
            ->withProperties([
                'user' => $user->firstname . ' ' . $user->lastname,
                'action' => $action, // 'granted' or 'revoked'
                'permissions' => $permissions,
            ])
            ->log('permission ' . $action);
    }

    /**
     * Log role assignment
     */
    public static function logRoleAssignment($user, $roles, $causedBy)
    {
        activity('roles')
            ->performedOn($user)
            ->causedBy($causedBy)
            ->withProperties([
                'user' => $user->firstname . ' ' . $user->lastname,
                'roles' => $roles,
            ])
            ->log('role assigned');
    }

    /**
     * Log file upload
     */
    public static function logFileUpload($fileName, $fileSize, $user, $context = 'images')
    {
        activity($context)
            ->causedBy($user)
            ->withProperties([
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'timestamp' => now()->toDateTimeString(),
            ])
            ->log('file uploaded');
    }

    /**
     * Log file deletion
     */
    public static function logFileDelete($fileName, $user, $context = 'images')
    {
        activity($context)
            ->causedBy($user)
            ->withProperties([
                'file_name' => $fileName,
                'timestamp' => now()->toDateTimeString(),
            ])
            ->log('file deleted');
    }

    /**
     * Log settings change
     */
    public static function logSettingsChange($settingName, $oldValue, $newValue, $user)
    {
        activity('settings')
            ->causedBy($user)
            ->withProperties([
                'setting' => $settingName,
                'old_value' => $oldValue,
                'new_value' => $newValue,
            ])
            ->log('settings changed');
    }

    /**
     * Log failed login attempt
     */
    public static function logFailedLogin($email, $ip)
    {
        activity('security')
            ->withProperties([
                'email' => $email,
                'ip' => $ip,
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toDateTimeString(),
            ])
            ->log('failed login attempt');
    }

    /**
     * Generic custom log
     */
    public static function log($logName, $description, $user = null, $subject = null, $properties = [])
    {
        $activity = activity($logName);

        if ($user) {
            $activity->causedBy($user);
        }

        if ($subject) {
            $activity->performedOn($subject);
        }

        if (!empty($properties)) {
            $activity->withProperties($properties);
        }

        $activity->log($description);
    }

    /**
     * Log role assignment to user
     */
    public static function logRoleAssigned($user, $role, $causedBy = null)
    {
        $causedBy = $causedBy ?? auth()->user();

        activity('roles')
            ->performedOn($user)
            ->causedBy($causedBy)
            ->withProperties([
                'user' => $user->firstname . ' ' . $user->lastname,
                'user_email' => $user->email,
                'role' => is_string($role) ? $role : $role->name,
            ])
            ->log('role assigned');
    }

    /**
     * Log role removal from user
     */
    public static function logRoleRemoved($user, $role, $causedBy = null)
    {
        $causedBy = $causedBy ?? auth()->user();

        activity('roles')
            ->performedOn($user)
            ->causedBy($causedBy)
            ->withProperties([
                'user' => $user->firstname . ' ' . $user->lastname,
                'user_email' => $user->email,
                'role' => is_string($role) ? $role : $role->name,
            ])
            ->log('role removed');
    }

    /**
     * Log permission granted to user
     */
    public static function logPermissionGranted($user, $permission, $causedBy = null)
    {
        $causedBy = $causedBy ?? auth()->user();

        activity('permissions')
            ->performedOn($user)
            ->causedBy($causedBy)
            ->withProperties([
                'user' => $user->firstname . ' ' . $user->lastname,
                'user_email' => $user->email,
                'permission' => is_string($permission) ? $permission : $permission->name,
            ])
            ->log('permission granted');
    }

    /**
     * Log permission revoked from user
     */
    public static function logPermissionRevoked($user, $permission, $causedBy = null)
    {
        $causedBy = $causedBy ?? auth()->user();

        activity('permissions')
            ->performedOn($user)
            ->causedBy($causedBy)
            ->withProperties([
                'user' => $user->firstname . ' ' . $user->lastname,
                'user_email' => $user->email,
                'permission' => is_string($permission) ? $permission : $permission->name,
            ])
            ->log('permission revoked');
    }

    /**
     * Log permission assigned to role
     */
    public static function logPermissionAssignedToRole($role, $permission, $causedBy = null)
    {
        $causedBy = $causedBy ?? auth()->user();

        activity('roles')
            ->performedOn($role)
            ->causedBy($causedBy)
            ->withProperties([
                'role' => $role->name,
                'permission' => is_string($permission) ? $permission : $permission->name,
            ])
            ->log('permission assigned to role');
    }

    /**
     * Log permission removed from role
     */
    public static function logPermissionRemovedFromRole($role, $permission, $causedBy = null)
    {
        $causedBy = $causedBy ?? auth()->user();

        activity('roles')
            ->performedOn($role)
            ->causedBy($causedBy)
            ->withProperties([
                'role' => $role->name,
                'permission' => is_string($permission) ? $permission : $permission->name,
            ])
            ->log('permission removed from role');
    }

    /**
     * Log roles synced
     */
    public static function logRolesSynced($user, $oldRoles, $newRoles, $causedBy = null)
    {
        $causedBy = $causedBy ?? auth()->user();

        activity('roles')
            ->performedOn($user)
            ->causedBy($causedBy)
            ->withProperties([
                'user' => $user->firstname . ' ' . $user->lastname,
                'user_email' => $user->email,
                'old_roles' => $oldRoles,
                'new_roles' => $newRoles,
                'added' => array_diff($newRoles, $oldRoles),
                'removed' => array_diff($oldRoles, $newRoles),
            ])
            ->log('roles synced');
    }

    /**
     * Log permissions synced to role
     */
    public static function logPermissionsSyncedToRole($role, $oldPermissions, $newPermissions, $causedBy = null)
    {
        $causedBy = $causedBy ?? auth()->user();

        activity('roles')
            ->performedOn($role)
            ->causedBy($causedBy)
            ->withProperties([
                'role' => $role->name,
                'old_permissions' => $oldPermissions,
                'new_permissions' => $newPermissions,
                'added' => array_diff($newPermissions, $oldPermissions),
                'removed' => array_diff($oldPermissions, $newPermissions),
            ])
            ->log('permissions synced to role');
    }
}
