<?php

namespace App\Observers;

use App\Models\HistoryPost;
use App\Services\ImageStorageService;
use App\Services\ImageUsageService;
use Illuminate\Support\Facades\Log;

class HistoryPostObserver
{
    public function __construct(
        private ImageStorageService $imageStorageService,
        private ImageUsageService $usageService
    ) {}

    /**
     * Handle the HistoryPost "deleting" event.
     * Only runs safeDelete — never destroys images still referenced by the live post.
     */
    public function deleting(HistoryPost $historyPost): void
    {
        if ($historyPost->image_path) {
            $this->usageService->clearUsageCache($historyPost->image_path);
            $this->imageStorageService->safeDelete($historyPost->image_path);
        }

        if ($historyPost->body) {
            $this->deleteBodyImages($historyPost->body);
        }
    }

    /**
     * Handle the HistoryPost "updating" event.
     * Only clean up images that were actually removed from this snapshot.
     */
    public function updating(HistoryPost $historyPost): void
    {
        if ($historyPost->isDirty('image_path') && $historyPost->getOriginal('image_path')) {
            $oldPath = $historyPost->getOriginal('image_path');
            if (!str_contains($oldPath, 'default')) {
                $this->usageService->clearUsageCache($oldPath);
                $this->imageStorageService->safeDelete($oldPath);
            }
        }

        if ($historyPost->isDirty('body')) {
            $oldBody = $historyPost->getOriginal('body') ?? '';
            $newBody = $historyPost->body ?? '';
            $this->deleteRemovedImages($oldBody, $newBody);
        }
    }

    /**
     * Handle the HistoryPost "forceDeleted" event.
     */
    public function forceDeleted(HistoryPost $historyPost): void
    {
        if ($historyPost->image_path) {
            $this->usageService->clearUsageCache($historyPost->image_path);
            $this->imageStorageService->safeDelete($historyPost->image_path);
        }

        if ($historyPost->body) {
            $this->deleteBodyImages($historyPost->body);
        }
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function deleteBodyImages(string $body): void
    {
        preg_match_all('/<img[^>]+src="([^"]+)"/i', $body, $matches);

        foreach ($matches[1] as $imagePath) {
            $imagePath = parse_url($imagePath, PHP_URL_PATH);
            if (!$imagePath) {
                continue;
            }
            $this->usageService->clearUsageCache($imagePath);
            $this->imageStorageService->safeDelete($imagePath);
        }
    }

    private function deleteRemovedImages(string $oldBody, string $newBody): void
    {
        preg_match_all('/<img[^>]+src="([^"]+)"/i', $oldBody, $oldMatches);
        $oldImages = array_filter(array_map(
            fn($url) => parse_url($url, PHP_URL_PATH),
            $oldMatches[1]
        ));

        preg_match_all('/<img[^>]+src="([^"]+)"/i', $newBody, $newMatches);
        $newImages = array_filter(array_map(
            fn($url) => parse_url($url, PHP_URL_PATH),
            $newMatches[1]
        ));

        foreach (array_diff($oldImages, $newImages) as $imagePath) {
            $this->usageService->clearUsageCache($imagePath);
            $this->imageStorageService->safeDelete($imagePath);
        }
    }
}
