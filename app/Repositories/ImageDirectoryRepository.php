<?php

namespace App\Repositories;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImageDirectoryRepository
{
    public function getDirectoriesWithCounts(): array
    {
        $publicPath = public_path('images');
        $directories = array_diff(scandir($publicPath), ['.', '..']);
        $filesCountByDirectory = [];

        foreach ($directories as $directory) {
            $path = $publicPath . DIRECTORY_SEPARATOR . $directory;

            if (is_dir($path)) {
                $files = array_diff(scandir($path), ['.', '..']);
                $filesCountByDirectory[$directory] = count($files);
            }
        }

        return $filesCountByDirectory;
    }

    public function getFilesFromDirectory(string $directory, int $offset = 0, int $limit = 20): array
    {
        if (!Storage::disk('public')->exists("images/$directory")) {
            return [];
        }

        $files = Storage::disk('public')->files("images/$directory");
        $fileList = [];

        // Apply offset and limit for pagination
        $files = array_slice($files, $offset, $limit);

        foreach ($files as $file) {
            $fileName = basename($file);
            $fileList[] = [
                'path' => "/images/$directory/$fileName",
            ];
        }

        return $fileList;
    }
}
