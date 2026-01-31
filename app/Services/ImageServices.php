<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImageServices
{
    public function getFilesFromDirectories(array $directories): array
    {
        $fileList = [];
        $extensions = [];
        $duplicateNames = [];
        $uniqueNames = [];

        foreach ($directories as $directory) {
            $directoryPath = public_path('images' . DIRECTORY_SEPARATOR . $directory);

            if (!File::exists($directoryPath)) {
                continue; // Skip non-existent directories
            }

            $files = File::allFiles($directoryPath);

            $fileNameCounts = $this->getUniqueFileNames($files);
            $imageUsageCounts = $this->getImageUsageCounts([$directory]);

            foreach ($files as $file) {
                $filePath = $file->getPathname();
                $fileName = $file->getFilename();
                $fileSize = File::size($filePath);
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (!empty($extension)) {
                    $extensions[$extension] = ($extensions[$extension] ?? 0) + 1;
                }

                $fileNameWithoutUniqid = preg_replace('/^([a-z0-9]{13})-/', '', $fileName);
                preg_match('/^([a-z0-9]{13})/', $fileName, $matches);
                $uniqid = $matches[0] ?? null;

                $fileList[] = [
                    'fullname' => $fileName,
                    'path' => "/images/$directory/$fileName",
                    'directory' => $directory,
                    'name' => $fileNameWithoutUniqid,
                    'extension' => $extension,
                    'uniqid' => $uniqid,
                    'size' => $fileSize,
                    'usage_count' => $imageUsageCounts["/images/$directory/$fileName"] ?? 0,
                ];
            }
            $nameCounts = array_count_values($fileNameCounts);

            foreach ($fileNameCounts as $fileName => $fileNameWithoutUniqid) {
                $count = $nameCounts[$fileNameWithoutUniqid];
                if ($count > 1) {
                    $duplicateNames[] = $fileName;
                } else {
                    $uniqueNames[] = $fileName;
                }
            }
            /*foreach ($fileNameCounts as $fileName => $fileNameWithoutUniqid) {
                $count = array_count_values($fileNameCounts)[$fileNameWithoutUniqid];
                if ($count > 1) {
                    $duplicateNames[] = $fileName;
                } else {
                    $uniqueNames[] = $fileName;
                }
            }*/
        }

        return [$fileList, $extensions, $duplicateNames, $uniqueNames];
    }

    public function getFileInfo(string $directory, string $name): ?array
    {
        $basePath = public_path('images' . DIRECTORY_SEPARATOR . $directory);
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $fileName = pathinfo($name, PATHINFO_FILENAME);
        $filePath = $basePath . DIRECTORY_SEPARATOR . $name;

        if (!File::exists($filePath)) {
            return null;
        }

        return [
            'name' => $fileName,
            'extension' => $extension,
            'directory' => $directory,
            'size' => File::size($filePath),
            'path' => "/images/$directory/$name",
            'used' => $this->searchImageUsage($directory, $fileName . "." . $extension)
        ];
    }

    public function deleteFile(string $directory, string $name): bool
    {
        try {
            Storage::disk('public')->path("images/$directory/$name");

            if (!Storage::disk('public')->exists("images/$directory/$name")) {
                return false;
            }

            return Storage::disk('public')->delete("images/$directory/$name");
        } catch (\Exception $e) {
            \Log::error("Failed to delete file: " . $e->getMessage());
            return false;
        }
        /*$basePath = public_path('images' . DIRECTORY_SEPARATOR . $directory);
        $filePath = $basePath . DIRECTORY_SEPARATOR . $name;

        if (!File::exists($filePath)) {
            return false;
        }

        return File::delete($filePath);*/
    }

    public function storeImage($image): string
    {
        $imageName = str_replace(' ', '-', $image->getClientOriginalName());
        $newImageName = uniqid() . '-' . $imageName;

        // Livewire handles the file moving internally
        $image->storeAs('images/posts', $newImageName, 'public');
        return '/images/posts/' . $newImageName;
    }
    public function storeAvatar($image): string
    {
        $imageName = str_replace(' ', '-', $image->getClientOriginalName());
        $newImageName = uniqid() . '-' . $imageName;

        // Use Laravel Storage
        $image->storeAs('images/avatars', $newImageName, 'public');
        return '/images/avatars/' . $newImageName;
    }

    public function updateAvatar($image, ?string $oldAvatarPath = null): string
    {
        // Delete old avatar if exists and not default
        if ($oldAvatarPath && !str_contains($oldAvatarPath, 'default-avatar')) {
            $this->deleteImageByPath($oldAvatarPath);
        }

        // Store new avatar
        return $this->storeAvatar($image);
    }

    public function deleteImageByPath(string $path): bool
    {
        // Remove leading slash and convert to storage path
        $path = ltrim($path, '/');

        // Ensure path starts with / for database comparison
        $dbPath = str_starts_with($path, '/') ? $path : '/' . $path;

        // Check if image is still used elsewhere
        $usageCount = $this->getImageUsageCount($dbPath);

        if ($usageCount > 1) {
            // Image is still used, don't delete
            \Log::info("Image still in use ({$usageCount} times), skipping delete: {$path}");
            return false;
        }

        if ($usageCount === 1) {
            // Last reference being deleted, safe to remove file
            \Log::info("Deleting last reference to image: {$path}");
        }

        // Check if file exists in storage
        if (\Storage::disk('public')->exists($path)) {
            return \Storage::disk('public')->delete($path);
        }

        return false;
    }

    /*public function getImageUsageCounts(array $directories): array
    {
        $imageUsageCounts = [];

        foreach ($directories as $directory) {
            if ($directory === "posts") {
                $postTypes = ['posts', 'saved_posts', 'history_posts'];

                foreach ($postTypes as $postType) {
                    $contents = DB::table($postType)->pluck('body');
                    $imagePaths = DB::table($postType)->pluck('image_path');

                    foreach ($contents as $content) {
                        preg_match_all('/<img[^>]+src="([^"]+)"/', $content, $matches);

                        foreach ($matches[1] as $imagePath) {
                            $imagePath = parse_url($imagePath, PHP_URL_PATH);
                            $imageUsageCounts[$imagePath] = isset($imageUsageCounts[$imagePath])
                                ? $imageUsageCounts[$imagePath] + 1
                                : 1;
                        }
                    }

                    foreach ($imagePaths as $imagePath) {
                        $imageUsageCounts[$imagePath] = isset($imageUsageCounts[$imagePath])
                            ? $imageUsageCounts[$imagePath] + 1
                            : 1;
                    }
                }
            } elseif ($directory === "avatars") {
                $imagePaths = DB::table('users')->pluck('image_path');

                foreach ($imagePaths as $imagePath) {
                    $imageUsageCounts[$imagePath] = isset($imageUsageCounts[$imagePath])
                        ? $imageUsageCounts[$imagePath] + 1
                        : 1;
                }
            }
        }

        return $imageUsageCounts;
    }*/
    public function getImageUsageCounts(array $directories): array
    {
        $imageUsageCounts = [];

        foreach ($directories as $directory) {
            if ($directory === "posts") {
                $postTypes = ['posts', 'saved_posts', 'history_posts'];

                // Build union query dynamically from postTypes array
                $query = DB::table($postTypes[0])->select('body', 'image_path');

                for ($i = 1; $i < count($postTypes); $i++) {
                    $query->union(DB::table($postTypes[$i])->select('body', 'image_path'));
                }

                $allPosts = $query->get();

                // Process all posts together
                foreach ($allPosts as $post) {
                    // Count images in body content
                    if ($post->body) {
                        preg_match_all('/<img[^>]+src="([^"]+)"/', $post->body, $matches);

                        foreach ($matches[1] as $imagePath) {
                            $imagePath = parse_url($imagePath, PHP_URL_PATH);
                            $imageUsageCounts[$imagePath] = ($imageUsageCounts[$imagePath] ?? 0) + 1;
                        }
                    }

                    // Count thumbnail images
                    if ($post->image_path) {
                        $imageUsageCounts[$post->image_path] = ($imageUsageCounts[$post->image_path] ?? 0) + 1;
                    }
                }
            } elseif ($directory === "avatars") {
                // Fetch all user avatars in one query
                $imagePaths = DB::table('users')
                    ->whereNotNull('image_path')
                    ->pluck('image_path');

                foreach ($imagePaths as $imagePath) {
                    $imageUsageCounts[$imagePath] = ($imageUsageCounts[$imagePath] ?? 0) + 1;
                }
            }
        }

        return $imageUsageCounts;
    }

    public function getImageUsageCount(string $imagePath): int
    {
        $count = 0;

        // Normalize path (ensure it starts with /)
        if (!str_starts_with($imagePath, '/')) {
            $imagePath = '/' . $imagePath;
        }

        // Check in all post tables
        $postTypes = ['posts', 'saved_posts', 'history_posts'];

        foreach ($postTypes as $table) {
            // Count as thumbnail
            $count += DB::table($table)
                ->where('image_path', $imagePath)
                ->count();

            // Count in body content
            $count += DB::table($table)
                ->where('body', 'like', "%{$imagePath}%")
                ->count();
        }

        // Check in users table (avatars)
        $count += DB::table('users')
            ->where('image_path', $imagePath)
            ->count();

        return $count;
    }
    public function searchImageUsage(string $directory, string $imageName): array
    {
        $imageUsageInfo = [];
        $imageName = "/images/$directory/$imageName";

        if ($directory === "avatars") {
            $users = DB::table('users')->get(['id', 'firstname', 'lastname', 'image_path']);

            foreach ($users as $user) {
                if (str_contains($user->image_path, $imageName)) {
                    $imageUsageInfo[] = [
                        'type' => 'User',
                        'id' => $user->id,
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                        'thumbnail' => $user->image_path,
                        'location' => 'Avatar'
                    ];
                }
            }

            return $imageUsageInfo;
        }

        $postTypes = [
            'posts' => 'Post',
            'saved_posts' => 'Saved post',
            'history_posts' => 'History'
        ];

        foreach ($postTypes as $table => $type) {
            $posts = DB::table($table)->get(['id', 'title', 'body', 'image_path']);

            foreach ($posts as $post) {
                if (stripos($post->image_path, $imageName) !== false) {
                    $imageUsageInfo[] = [
                        'type' => $type,
                        'id' => $post->id,
                        'title' => $post->title,
                        'thumbnail' => $post->image_path,
                        'location' => 'Thumbnail'
                    ];
                }

                if (preg_match_all('/<img[^>]+src="([^"]+)"/', $post->body, $matches)) {
                    foreach ($matches[1] as $match) {
                        if (stripos($match, $imageName) !== false) {
                            $imageUsageInfo[] = [
                                'type' => $type,
                                'id' => $post->id,
                                'title' => $post->title,
                                'thumbnail' => $post->image_path,
                                'location' => 'Content'
                            ];
                        }
                    }
                }
            }
        }

        return $imageUsageInfo;
    }

    private function getUniqueFileNames($files): array
    {
        $fileNameCounts = [];

        foreach ($files as $file) {
            $fileName = $file->getFilename();
            $fileNameWithoutUniqid = preg_replace('/^([a-z0-9]{13})-/', '', $fileName);
            $fileNameCounts[$fileName] = $fileNameWithoutUniqid;
        }

        return $fileNameCounts;
    }
}
