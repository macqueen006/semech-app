<?php

namespace App\Services;

use App\Models\Post;
use App\Models\SavedPost;
use App\Models\User;
use App\Models\Advertisement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ImageUsageService
{
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get usage count for a single image path
     */
    public function getUsageCount(string $imagePath): int
    {
        $cacheKey = $this->getCacheKey($imagePath);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($imagePath) {
            return $this->calculateUsageCount($imagePath);
        });
    }

    /**
     * Get usage counts for multiple directories
     */
    public function getUsageCounts(array $directories): array
    {
        $cacheKey = 'image_usage_counts_' . md5(implode(',', $directories));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($directories) {
            return $this->calculateBatchUsageCounts($directories);
        });
    }

    /**
     * Get detailed usage information for an image
     */
    public function getUsageDetails(string $imagePath): array
    {
        $usages = [];

        // Search in posts body
        $postsInBody = Post::where('body', 'like', '%' . $imagePath . '%')->get();
        foreach ($postsInBody as $post) {
            $usages[] = [
                'type' => 'Post',
                'location' => 'Body',
                'title' => $post->title,
                'image' => $post->image_path ?? '/images/default-post.jpg',
                'url' => route('post.show', $post->slug),
            ];
        }

        // Search in posts featured image
        $postsAsImage = Post::where('image_path', $imagePath)->get();
        foreach ($postsAsImage as $post) {
            $usages[] = [
                'type' => 'Post',
                'location' => 'Featured Image',
                'title' => $post->title,
                'image' => $post->image_path,
                'url' => route('post.show', $post->slug),
            ];
        }

        // Search in user avatars
        $users = User::where('image_path', $imagePath)->get();
        foreach ($users as $user) {
            $usages[] = [
                'type' => 'User',
                'location' => 'Avatar',
                'title' => $user->firstname . ' ' . $user->lastname,
                'image' => $user->image_path,
                'url' => route('admin.users.edit', $user->id),
            ];
        }

        // Search in advertisements
        $advertisements = Advertisement::where('image_path', $imagePath)->get();
        foreach ($advertisements as $ad) {
            $usages[] = [
                'type' => 'Advertisement',
                'location' => ucfirst($ad->position),
                'title' => $ad->title,
                'image' => $ad->image_path,
                'url' => route('admin.advertisements.edit', $ad->id),
                'status' => $ad->is_active ? 'Active' : 'Inactive',
            ];
        }

        return $usages;
    }

    /**
     * Check if an image is safe to delete (not used anywhere)
     */
    public function isSafeToDelete(string $imagePath): bool
    {
        return $this->getUsageCount($imagePath) === 0;
    }

    /**
     * Get all images that are not used anywhere
     */
    public function getUnusedImages(array $imagePaths): array
    {
        $unused = [];

        foreach ($imagePaths as $path) {
            if ($this->isSafeToDelete($path)) {
                $unused[] = $path;
            }
        }

        return $unused;
    }

    /**
     * Clear usage cache for a specific image
     */
    public function clearUsageCache(string $imagePath): void
    {
        Cache::forget($this->getCacheKey($imagePath));
    }

    /**
     * Clear all usage caches
     */
    public function clearAllUsageCaches(): void
    {
        Cache::tags(['image_usage'])->flush();
    }

    /**
     * Calculate usage count for a single image
     */
    /*private function calculateUsageCount(string $imagePath): int
    {
        $count = 0;

        // Count posts with image in body
        $count += Post::where('body', 'like', '%' . $imagePath . '%')->count();

        // Count posts with image as featured image
        $count += Post::where('image_path', $imagePath)->count();

        // Count users with image as avatar
        $count += User::where('image_path', $imagePath)->count();

        // Count advertisements with image
        $count += Advertisement::where('image_path', $imagePath)->count();

        return $count;
    }*/

    private function calculateUsageCount(string $imagePath): int
    {
        $count = 0;

        // Count posts with image in body
        $count += Post::where('body', 'like', '%' . $imagePath . '%')->count();

        // Count posts with image as featured image
        $count += Post::where('image_path', $imagePath)->count();

        // Count saved posts with image in body
        $count += SavedPost::where('body', 'like', '%' . $imagePath . '%')->count();

        // Count saved posts with image as featured image
        $count += SavedPost::where('image_path', $imagePath)->count();

        // Count users with image as avatar
        $count += User::where('image_path', $imagePath)->count();

        // Count advertisements with image
        $count += Advertisement::where('image_path', $imagePath)->count();

        return $count;
    }

    /**
     * Calculate usage counts for all images in directories (batch optimization)
     */
    private function calculateBatchUsageCounts(array $directories): array
    {
        $usageCounts = [];

        // Build path patterns for each directory
        $pathPatterns = array_map(function ($dir) {
            return "/images/{$dir}/%";
        }, $directories);

        // Get all image paths from posts (featured images)
        $postImagePaths = Post::whereNotNull('image_path')
            ->where(function ($query) use ($pathPatterns) {
                foreach ($pathPatterns as $pattern) {
                    $query->orWhere('image_path', 'like', $pattern);
                }
            })
            ->pluck('image_path')
            ->toArray();

        foreach ($postImagePaths as $path) {
            $usageCounts[$path] = ($usageCounts[$path] ?? 0) + 1;
        }

        // Get all image paths from posts (in body) - this is more complex
        $posts = Post::where(function ($query) use ($pathPatterns) {
            foreach ($pathPatterns as $pattern) {
                $query->orWhere('body', 'like', $pattern);
            }
        })->get();

        foreach ($posts as $post) {
            // Extract image paths from post body
            preg_match_all('/\/images\/[a-zA-Z0-9_\-\/]+\.(jpg|jpeg|png|gif|webp|svg)/i', $post->body, $matches);
            if (!empty($matches[0])) {
                foreach ($matches[0] as $path) {
                    $usageCounts[$path] = ($usageCounts[$path] ?? 0) + 1;
                }
            }
        }

        // Get all image paths from users
        $userImagePaths = User::whereNotNull('image_path')
            ->where(function ($query) use ($pathPatterns) {
                foreach ($pathPatterns as $pattern) {
                    $query->orWhere('image_path', 'like', $pattern);
                }
            })
            ->pluck('image_path')
            ->toArray();

        foreach ($userImagePaths as $path) {
            $usageCounts[$path] = ($usageCounts[$path] ?? 0) + 1;
        }

        // Get all image paths from advertisements
        $adImagePaths = Advertisement::whereNotNull('image_path')
            ->where(function ($query) use ($pathPatterns) {
                foreach ($pathPatterns as $pattern) {
                    $query->orWhere('image_path', 'like', $pattern);
                }
            })
            ->pluck('image_path')
            ->toArray();

        foreach ($adImagePaths as $path) {
            $usageCounts[$path] = ($usageCounts[$path] ?? 0) + 1;
        }

        return $usageCounts;
    }

    /**
     * Get usage statistics by model type
     */
    public function getUsageStatsByType(string $imagePath): array
    {
        return [
            'posts_body' => Post::where('body', 'like', '%' . $imagePath . '%')->count(),
            'posts_featured' => Post::where('image_path', $imagePath)->count(),
            'users_avatar' => User::where('image_path', $imagePath)->count(),
            'advertisements' => Advertisement::where('image_path', $imagePath)->count(),
            'total' => $this->getUsageCount($imagePath),
        ];
    }

    /**
     * Get all models using a specific image
     */
    public function getModelsUsingImage(string $imagePath): array
    {
        return [
            'posts_body' => Post::where('body', 'like', '%' . $imagePath . '%')
                ->select('id', 'title', 'slug')
                ->get(),
            'posts_featured' => Post::where('image_path', $imagePath)
                ->select('id', 'title', 'slug')
                ->get(),
            'users' => User::where('image_path', $imagePath)
                ->select('id', 'firstname', 'lastname', 'email')
                ->get(),
            'advertisements' => Advertisement::where('image_path', $imagePath)
                ->select('id', 'title', 'position', 'is_active')
                ->get(),
        ];
    }

    /**
     * Find orphaned images (images that exist but aren't referenced anywhere)
     */
    public function findOrphanedImages(array $allImagePaths): array
    {
        $orphaned = [];

        foreach ($allImagePaths as $path) {
            if ($this->isSafeToDelete($path)) {
                $orphaned[] = $path;
            }
        }

        return $orphaned;
    }

    /**
     * Get cache key for image path
     */
    private function getCacheKey(string $imagePath): string
    {
        return 'image_usage_' . md5($imagePath);
    }

    /**
     * Rebuild usage cache for all images in directories
     */
    public function rebuildCache(array $directories): void
    {
        $this->clearAllUsageCaches();
        $this->getUsageCounts($directories);
    }
}
