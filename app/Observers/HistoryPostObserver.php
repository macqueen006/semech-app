<?php

namespace App\Observers;

use App\Models\HistoryPost;
use App\Services\ImageServices;

class HistoryPostObserver
{
    public function __construct(
        private ImageServices $imageService
    ) {}

    /**
     * Handle the HistoryPost "deleting" event.
     */
    public function deleting(HistoryPost $historyPost): void
    {
        // Delete thumbnail image
        if ($historyPost->image_path) {
            $this->imageService->deleteImageByPath($historyPost->image_path);
        }

        // Delete images from body content
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
                $this->imageService->deleteImageByPath($oldPath);
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
     * Same as deleting for force delete
     */
    public function forceDeleted(HistoryPost $historyPost): void
    {
        // Delete thumbnail image
        if ($historyPost->image_path) {
            $this->imageService->deleteImageByPath($historyPost->image_path);
        }

        // Delete images from body content
        if ($historyPost->body) {
            $this->deleteBodyImages($historyPost->body);
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
            $this->imageService->deleteImageByPath($imagePath);
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

        // Delete removed images
        foreach ($removedImages as $imagePath) {
            $this->imageService->deleteImageByPath($imagePath);
        }
    }
}
