<?php

namespace App\Services;

use App\Repositories\ImageRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ImageStorageService
{
    private ImageRepository $repository;
    private ImageUsageService $usageService;

    public function __construct(
        ImageRepository $repository,
        ImageUsageService $usageService
    ) {
        $this->repository = $repository;
        $this->usageService = $usageService;
    }

    /**
     * Store a post image
     */
    public function storePostImage(UploadedFile $image): string
    {
        return $this->storeImage($image, 'posts');
    }

    /**
     * Store an avatar image
     */
    public function storeAvatar(UploadedFile $image): string
    {
        return $this->storeImage($image, 'avatars');
    }

    /**
     * Store an image in a specified directory
     */
    public function storeImage(UploadedFile $image, string $directory): string
    {
        $sanitizedName = $this->sanitizeFilename($image->getClientOriginalName());
        $uniqueName = uniqid() . '-' . $sanitizedName;

        try {
            return $this->repository->store($image, $directory, $uniqueName);
        } catch (\Exception $e) {
            Log::error("Failed to store image in {$directory}", [
                'filename' => $sanitizedName,
                'error' => $e->getMessage()
            ]);
            throw new \RuntimeException("Failed to store image: " . $e->getMessage());
        }
    }

    /**
     * Store an advertisement image
     */
    public function storeAdvertisement(UploadedFile $image): string
    {
        return $this->storeImage($image, 'advertisements');
    }

    /**
     * Update an avatar and delete the old one if safe
     */
    public function updateAvatar(UploadedFile $newImage, ?string $oldAvatarPath = null): string
    {
        // Store new avatar first
        $newPath = $this->storeAvatar($newImage);

        // Delete old avatar if exists and not default
        if ($oldAvatarPath && !$this->isDefaultAvatar($oldAvatarPath)) {
            $this->safeDelete($oldAvatarPath);
        }

        return $newPath;
    }

    /**
     * Delete an image file
     */
    public function delete(string $directory, string $filename): bool
    {
        try {
            $deleted = $this->repository->delete($directory, $filename);

            if ($deleted) {
                Log::info("Deleted image: {$directory}/{$filename}");
            }

            return $deleted;
        } catch (\Exception $e) {
            Log::error("Failed to delete image", [
                'directory' => $directory,
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete an image by its full path
     */
    public function deleteByPath(string $path): bool
    {
        try {
            $deleted = $this->repository->deleteByPath($path);

            if ($deleted) {
                $this->usageService->clearUsageCache($path);
                Log::info("Deleted image by path: {$path}");
            }

            return $deleted;
        } catch (\Exception $e) {
            Log::error("Failed to delete image by path", [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Safely delete an image (only if not used elsewhere)
     */
    public function safeDelete(string $path): bool
    {
        $usageCount = $this->usageService->getUsageCount($path);

        if ($usageCount > 1) {
            Log::info("Image still in use, skipping delete", [
                'path' => $path,
                'usage_count' => $usageCount
            ]);
            return false;
        }

        if ($usageCount === 1) {
            Log::info("Deleting last reference to image", [
                'path' => $path
            ]);
        }

        if ($usageCount === 0) {
            Log::info("Deleting unused image", [
                'path' => $path
            ]);
        }

        return $this->deleteByPath($path);
    }

    /**
     * Batch delete images that are not used anywhere
     */
    public function deleteUnusedImages(array $imagePaths): array
    {
        $results = [
            'deleted' => [],
            'skipped' => [],
            'failed' => []
        ];

        foreach ($imagePaths as $path) {
            if (!$this->usageService->isSafeToDelete($path)) {
                $results['skipped'][] = $path;
                continue;
            }

            if ($this->deleteByPath($path)) {
                $results['deleted'][] = $path;
            } else {
                $results['failed'][] = $path;
            }
        }

        Log::info("Batch delete completed", [
            'deleted_count' => count($results['deleted']),
            'skipped_count' => count($results['skipped']),
            'failed_count' => count($results['failed'])
        ]);

        return $results;
    }

    /**
     * Get storage statistics for directories
     */
    public function getStorageStats(array $directories): array
    {
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'by_directory' => []
        ];

        $directoryCounts = $this->repository->getDirectoriesWithCounts();

        foreach ($directories as $directory) {
            $count = $directoryCounts[$directory] ?? 0;
            $stats['total_files'] += $count;
            $stats['by_directory'][$directory] = [
                'file_count' => $count
            ];
        }

        return $stats;
    }

    /**
     * Sanitize filename to remove dangerous characters
     */
    private function sanitizeFilename(string $filename): string
    {
        // Replace spaces with hyphens
        $filename = str_replace(' ', '-', $filename);

        // Remove any characters that aren't alphanumeric, dash, underscore, or dot
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);

        // Remove multiple consecutive hyphens
        $filename = preg_replace('/-+/', '-', $filename);

        return $filename;
    }

    /**
     * Check if path points to a default avatar
     */
    private function isDefaultAvatar(string $path): bool
    {
        return str_contains($path, 'default-avatar');
    }
}
