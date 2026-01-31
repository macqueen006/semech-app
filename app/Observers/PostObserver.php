<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\ImageStorageService;
use App\Services\ImageUsageService;

class PostObserver
{
    public function __construct(
        private ImageStorageService $imageStorageService,
        private ImageUsageService $usageService
    )
    {
    }

    /**
     * Handle the Post "deleting" event.
     */
    public function deleting(Post $post): void
    {
        // Clear cache first so usage count is accurate
        if ($post->image_path) {
            $this->usageService->clearUsageCache($post->image_path);
        }

        if ($post->body) {
            $this->clearBodyImagesCache($post->body);
        }

        // Now delete with accurate usage counts
        if ($post->image_path) {
            $this->imageStorageService->safeDelete($post->image_path);
        }

        if ($post->body) {
            $this->deleteBodyImages($post->body);
        }
    }

    /**
     * Handle the Post "updating" event.
     * Clean up removed images when post is updated
     */
    public function updating(Post $post): void
    {
        // Check if thumbnail changed
        if ($post->isDirty('image_path') && $post->getOriginal('image_path')) {
            $oldPath = $post->getOriginal('image_path');
            if (!str_contains($oldPath, 'default')) {
                $this->usageService->clearUsageCache($oldPath);
                $this->imageStorageService->safeDelete($oldPath);
            }
        }

        // Check if body content changed
        if ($post->isDirty('body')) {
            $oldBody = $post->getOriginal('body');
            $newBody = $post->body;

            // Find images that were removed
            $this->deleteRemovedImages($oldBody, $newBody);
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

    private function deleteBodyImages(string $body): void
    {
        preg_match_all('/<img[^>]+src="([^"]+)"/', $body, $matches);

        foreach ($matches[1] as $imagePath) {
            $imagePath = parse_url($imagePath, PHP_URL_PATH);
            $this->imageStorageService->safeDelete($imagePath);
        }
    }

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
