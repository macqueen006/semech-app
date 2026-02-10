<?php

namespace App\Observers;

use App\Models\HistoryPost;
use App\Services\ImageStorageService;
use App\Services\ImageUsageService;

class HistoryPostObserver
{
    public function __construct(
        private ImageStorageService $imageStorageService,
        private ImageUsageService $usageService
    ) {}

    /**
     * Handle the HistoryPost "deleting" event.
     */
    public function deleting(HistoryPost $historyPost): void
    {
        // Clear cache first so usage count is accurate
        if ($historyPost->image_path) {
            $this->usageService->clearUsageCache($historyPost->image_path);
        }

        if ($historyPost->body) {
            $this->clearBodyImagesCache($historyPost->body);
        }

        // Now delete with accurate usage counts
        if ($historyPost->image_path) {
            $this->imageStorageService->safeDelete($historyPost->image_path);
        }

        if ($historyPost->body) {
            $this->deleteBodyImages($historyPost->body);
        }
    }

    /**
     * Handle the HistoryPost "updating" event.
     * Clean up removed images when history post is updated
     */
    public function updating(HistoryPost $historyPost): void
    {
        // Check if thumbnail changed
        if ($historyPost->isDirty('image_path') && $historyPost->getOriginal('image_path')) {
            $oldPath = $historyPost->getOriginal('image_path');
            if (!str_contains($oldPath, 'default')) {
                $this->usageService->clearUsageCache($oldPath);
                $this->imageStorageService->safeDelete($oldPath);
            }
        }

        // Check if body content changed
        if ($historyPost->isDirty('body')) {
            $oldBody = $historyPost->getOriginal('body');
            $newBody = $historyPost->body;

            // Find and delete images that were removed
            $this->deleteRemovedImages($oldBody, $newBody);
        }
    }

    /**
     * Handle the HistoryPost "forceDeleted" event.
     */
    public function forceDeleted(HistoryPost $historyPost): void
    {
        // Clear cache first so usage count is accurate
        if ($historyPost->image_path) {
            $this->usageService->clearUsageCache($historyPost->image_path);
        }

        if ($historyPost->body) {
            $this->clearBodyImagesCache($historyPost->body);
        }

        // Now delete with accurate usage counts
        if ($historyPost->image_path) {
            $this->imageStorageService->safeDelete($historyPost->image_path);
        }

        if ($historyPost->body) {
            $this->deleteBodyImages($historyPost->body);
        }
    }

    /**
     * Clear usage cache for all images in body
     */
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
    private function deleteRemovedImages(string $oldBody, string $newBody): void
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
