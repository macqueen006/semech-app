<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\ImageStorageService;
use App\Services\ImageUsageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PostObserver
{
    public function __construct(
        private ImageStorageService $imageStorageService,
        private ImageUsageService $usageService
    )
    {
    }

    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        // Clear homepage caches when new post is published
        if ($post->is_published) {
            $this->clearHomepageCaches();
            $this->clearCategoryCaches($post->category_id);
        }

        Log::info("Post created: {$post->title} (ID: {$post->id})");
    }

    /**
     * Handle the Post "updating" event.
     * Clean up removed images when post is updated
     */
  /*  public function updating(Post $post): void
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
    }*/

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {
        // CACHE INVALIDATION (performance optimization)

        // Clear caches if publish status changed
        if ($post->wasChanged('is_published')) {
            $this->clearHomepageCaches();
            $this->clearCategoryCaches($post->category_id);

            Log::info("Post publish status changed: {$post->title} (ID: {$post->id})");
        }

      /*  // Clear caches if category changed
        if ($post->wasChanged('category_id')) {
            // Clear both old and new category caches
            $this->clearCategoryCaches($post->getOriginal('category_id'));
            $this->clearCategoryCaches($post->category_id);

            Log::info("Post category changed: {$post->title} (ID: {$post->id})");
        }

        // Clear related posts cache if display fields changed
        if ($post->wasChanged(['title', 'excerpt', 'image_path'])) {
            $this->clearRelatedPostsCache($post);
        }

        // Clear trending/popular if view count changed significantly (every 10 views)
        if ($post->wasChanged('view_count') && $post->view_count % 10 === 0) {
            Cache::forget('homepage.trending_topics');
            Cache::forget('homepage.popular_posts');
        }*/
        if ($post->wasChanged('image_path')) {
            $oldPath = $post->getOriginal('image_path');

            if ($oldPath
                && !str_contains($oldPath, 'default')
                && $oldPath !== $post->image_path
            ) {
                try {
                    $this->usageService->clearUsageCache($oldPath);
                    $this->imageStorageService->safeDelete($oldPath);
                } catch (\Exception $e) {
                    Log::warning('PostObserver: failed to delete old featured image', [
                        'post_id'  => $post->id,
                        'old_path' => $oldPath,
                        'error'    => $e->getMessage(),
                    ]);
                }
            }
        }

        if ($post->wasChanged('body')) {
            $oldBody = $post->getOriginal('body') ?? '';
            $newBody = $post->body ?? '';
            $this->deleteRemovedBodyImages($oldBody, $newBody);
        }
    }

    /**
     * Delete images that were in the old body but are no longer in the new body.
     * Uses set difference so we never touch images still referenced.
     */
    private function deleteRemovedBodyImages(string $oldBody, string $newBody): void
    {
        $oldImages = $this->extractImagePaths($oldBody);
        $newImages = $this->extractImagePaths($newBody);

        foreach (array_diff($oldImages, $newImages) as $imagePath) {
            try {
                $this->usageService->clearUsageCache($imagePath);
                $this->imageStorageService->safeDelete($imagePath);
            } catch (\Exception $e) {
                Log::warning('PostObserver: failed to delete removed body image', [
                    'path'  => $imagePath,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Extract all /images/... paths from an HTML body string.
     * Returns normalised internal paths only — ignores external http(s) URLs.
     */
    private function extractImagePaths(string $html): array
    {
        preg_match_all('/<img[^>]+src="([^"]+)"/i', $html, $matches);

        return array_values(array_filter(
            array_map(function (string $url) {
                $path = parse_url($url, PHP_URL_PATH);
                // Only manage images stored in our own storage
                return ($path && str_starts_with($path, '/images/')) ? $path : null;
            }, $matches[1])
        ));
    }

    /**
     * Handle the Post "deleting" event.
     */
    public function deleting(Post $post): void
    {
        // IMAGE CLEANUP (your original logic)
        // Clear cache first so usage count is accurate
       /* if ($post->image_path) {
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
        }*/
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        // Clear all relevant caches
        $this->clearHomepageCaches();
        $this->clearCategoryCaches($post->category_id);
        $this->clearRelatedPostsCache($post);
        $this->forceDeleted($post);

        Log::info("Post deleted: {$post->title} (ID: {$post->id})");
    }

    /**
     * Handle the Post "restored" event.
     */
    public function restored(Post $post): void
    {
        $this->clearHomepageCaches();
        $this->clearCategoryCaches($post->category_id);

        Log::info("Post restored: {$post->title} (ID: {$post->id})");
    }

    private function deleteAllBodyImages(string $body): void
    {
        foreach ($this->extractImagePaths($body) as $imagePath) {
            try {
                $this->usageService->clearUsageCache($imagePath);
                $this->imageStorageService->safeDelete($imagePath);
            } catch (\Exception $e) {
                Log::warning('PostObserver: failed to delete body image', [
                    'path'  => $imagePath,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function forceDeleted(Post $post): void
    {
        $this->clearHomepageCaches();
        $this->clearCategoryCaches($post->category_id);

        Log::info("Post force deleted: {$post->title} (ID: {$post->id})");

        if ($post->image_path && !str_contains($post->image_path, 'default')) {
            try {
                $this->usageService->clearUsageCache($post->image_path);
                $this->imageStorageService->safeDelete($post->image_path);
            } catch (\Exception $e) {
                Log::warning('PostObserver: failed to delete featured image on force-delete', [
                    'post_id' => $post->id,
                    'path'    => $post->image_path,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        if ($post->body) {
            $this->deleteAllBodyImages($post->body);
        }
    }

    // ========================================
    // IMAGE CLEANUP METHODS (Your Original)
    // ========================================

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

    // ========================================
    // CACHE INVALIDATION METHODS (Performance)
    // ========================================

    /**
     * Clear homepage-related caches
     */
    private function clearHomepageCaches(): void
    {
        Cache::forget('homepage.highlighted_posts');
        Cache::forget('homepage.trending_topics');
        Cache::forget('homepage.popular_posts');
    }

    /**
     * Clear category-specific caches
     */
    private function clearCategoryCaches(?int $categoryId): void
    {
        if (!$categoryId) {
            return;
        }

        // Clear category page cache
        Cache::forget("category.{$categoryId}.posts");
    }

    /**
     * Clear related posts cache for this specific post
     */
    private function clearRelatedPostsCache(Post $post): void
    {
        if ($post->category_id) {
            Cache::forget("related_posts.category_{$post->category_id}.exclude_{$post->id}");
        }
    }
}
