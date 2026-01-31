<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ImageServices;

class UserObserver
{
    public function __construct(
        private ImageServices $imageService
    ) {}

    /**
     * Handle the User "deleting" event.
     */
    public function deleting(User $user): void
    {
        // Delete avatar image (skip default avatars)
        if ($user->image_path && !str_contains($user->image_path, 'default-avatar')) {
            $this->imageService->deleteImageByPath($user->image_path);
        }
    }

    /**
     * Handle the User "updating" event.
     * Clean up old avatar when changed
     */
    public function updating(User $user): void
    {
        // Check if avatar changed
        if ($user->isDirty('image_path')) {
            $oldPath = $user->getOriginal('image_path');

            // Only delete if it's not a default avatar
            if ($oldPath && !str_contains($oldPath, 'default-avatar')) {
                $this->imageService->deleteImageByPath($oldPath);
            }
        }
    }
}
