<?php

namespace App\Observers;

use App\Models\SavedPost;
use App\Services\ImageStorageService;
use App\Services\ImageUsageService;

class SavedPostObserver
{
    public function __construct(
        private ImageStorageService $imageStorageService,
        private ImageUsageService $usageService
    ) {}

    /**
     * Handle the SavedPost "deleting" event.
     */
    public function deleting(SavedPost $savedPost): void
    {
        // Clear cache first so usage count is accurate
        if ($savedPost->image_path) {
            $this->usageService->clearUsageCache($savedPost->image_path);
        }

        if ($savedPost->body) {
            $this->clearBodyImagesCache($savedPost->body);
        }

        // Now delete with accurate usage counts
        if ($savedPost->image_path) {
            $this->imageStorageService->safeDelete($savedPost->image_path);
        }

        if ($savedPost->body) {
            $this->deleteBodyImages($savedPost->body);
        }
    }

    /**
     * Handle the SavedPost "updating" event.
     * Clean up removed images when saved post is updated
     */
    public function updating(SavedPost $savedPost): void
    {
        // Check if thumbnail changed
        if ($savedPost->isDirty('image_path') && $savedPost->getOriginal('image_path')) {
            $oldPath = $savedPost->getOriginal('image_path');
            if (!str_contains($oldPath, 'default')) {
                $this->usageService->clearUsageCache($oldPath);
                $this->imageStorageService->safeDelete($oldPath);
            }
        }

        // Check if body content changed
        if ($savedPost->isDirty('body')) {
            $oldBody = $savedPost->getOriginal('body');
            $newBody = $savedPost->body;

            // Find and delete images that were removed
            $this->deleteRemovedImages($oldBody, $newBody);
        }
    }

    /**
     * Handle the SavedPost "forceDeleted" event.
     * Same as deleting for force delete
     */
    public function forceDeleted(SavedPost $savedPost): void
    {
        // Clear cache first so usage count is accurate
        if ($savedPost->image_path) {
            $this->usageService->clearUsageCache($savedPost->image_path);
        }

        if ($savedPost->body) {
            $this->clearBodyImagesCache($savedPost->body);
        }

        // Now delete with accurate usage counts
        if ($savedPost->image_path) {
            $this->imageStorageService->safeDelete($savedPost->image_path);
        }

        if ($savedPost->body) {
            $this->deleteBodyImages($savedPost->body);
        }
    }

    private function clearBodyImagesCache(string $body): void
    {
        preg_match_all('/<img[^>]+src="([^"]+)"/', $body, $matches);

        foreach ($matches[1] as $imagePath) {
            $imagePath = parse_url($imagePath, PHP_URL_PATH);
            $this->usageService->clearUsageCache($imagePath);
        }
    }

    /**
     * Extract and delete all images from HTML body
     */
    private function deleteBodyImages(string $body): void
    {
        preg_match_all('/<img[^>]+src="([^"]+)"/', $body, $matches);

        foreach ($matches[1] as $imagePath) {
            $imagePath = parse_url($imagePath, PHP_URL_PATH);
            $this->imageStorageService->safeDelete($imagePath);
        }
    }

    /**
     * Compare old and new body content and delete removed images
     */
    private function deleteRemovedImages(?string $oldBody, string $newBody): void
    {
        // Extract old images
        preg_match_all('/<img[^>]+src="([^"]+)"/', $oldBody, $oldMatches);
        $oldImages = array_map(fn($url) => parse_url($url, PHP_URL_PATH), $oldMatches[1]);

        // Extract new images
        preg_match_all('/<img[^>]+src="([^"]+)"/', $newBody, $newMatches);
        $newImages = array_map(fn($url) => parse_url($url, PHP_URL_PATH), $newMatches[1]);

        // Find removed images
        $removedImages = array_diff($oldImages, $newImages);

        // Clear cache and delete removed images (safe delete - only if not used elsewhere)
        foreach ($removedImages as $imagePath) {
            $this->usageService->clearUsageCache($imagePath);
            $this->imageStorageService->safeDelete($imagePath);
        }
    }
}
