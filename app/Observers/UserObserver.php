<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ImageStorageService;
use App\Services\ImageUsageService;

class UserObserver
{
    public function __construct(
        private ImageStorageService $imageStorageService,
        private ImageUsageService $usageService
    ) {}

    public function deleting(User $user): void
    {
        if ($user->image_path &&
            !str_contains($user->image_path, 'default-avatar') &&
            !str_contains($user->image_path, 'user.jpg')) {

            $this->usageService->clearUsageCache($user->image_path);
            $this->imageStorageService->safeDelete($user->image_path);
        }
    }

    public function updating(User $user): void
    {
        if ($user->isDirty('image_path')) {
            $oldPath = $user->getOriginal('image_path');
            $newPath = $user->image_path;

            if ($oldPath &&
                $oldPath !== $newPath &&
                !str_contains($oldPath, 'default-avatar') &&
                !str_contains($oldPath, 'user.jpg')) {

                $this->usageService->clearUsageCache($oldPath);
                $this->imageStorageService->safeDelete($oldPath);
            }
        }
    }
}
