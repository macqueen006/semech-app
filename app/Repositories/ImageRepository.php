<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class ImageRepository
{
    private string $disk = 'public';
    private string $baseImagePath = 'images';

    /**
     * Get all files from specified directories with metadata
     */
    public function getAllFromDirectories(array $directories): array
    {
        $files = [];

        foreach ($directories as $directory) {
            $this->validateDirectory($directory);
            $path = "{$this->baseImagePath}/{$directory}";

            if (!Storage::disk($this->disk)->exists($path)) {
                continue;
            }

            $directoryFiles = Storage::disk($this->disk)->files($path);

            foreach ($directoryFiles as $filePath) {
                $fileName = basename($filePath);
                $files[] = $this->buildFileMetadata($fileName, $directory);
            }
        }

        return $files;
    }

    /**
     * Get paginated files from a single directory
     */
    public function getFilesFromDirectory(string $directory, int $offset = 0, int $limit = 20): array
    {
        $this->validateDirectory($directory);
        $path = "{$this->baseImagePath}/{$directory}";

        if (!Storage::disk($this->disk)->exists($path)) {
            return [];
        }

        $allFiles = Storage::disk($this->disk)->files($path);
        $paginatedFiles = array_slice($allFiles, $offset, $limit);

        return array_map(function($filePath) use ($directory) {
            $fileName = basename($filePath);
            return [
                'path' => "/{$this->baseImagePath}/{$directory}/{$fileName}",
                'filename' => $fileName,
            ];
        }, $paginatedFiles);
    }

    /**
     * Get directories with file counts
     */
    public function getDirectoriesWithCounts(): array
    {
        $directories = Storage::disk($this->disk)->directories($this->baseImagePath);
        $counts = [];

        foreach ($directories as $directory) {
            $dirName = basename($directory);
            $files = Storage::disk($this->disk)->files($directory);
            $counts[$dirName] = count($files);
        }

        return $counts;
    }

    /**
     * Get detailed information about a specific file
     */
    public function getFileInfo(string $directory, string $name): ?array
    {
        $this->validateDirectory($directory);
        $this->validateFilename($name);

        $path = "{$this->baseImagePath}/{$directory}/{$name}";

        if (!Storage::disk($this->disk)->exists($path)) {
            return null;
        }

        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $fileName = pathinfo($name, PATHINFO_FILENAME);

        return [
            'name' => $fileName,
            'fullname' => $name,
            'extension' => strtolower($extension),
            'directory' => $directory,
            'size' => Storage::disk($this->disk)->size($path),
            'path' => "/{$path}",
            'last_modified' => Storage::disk($this->disk)->lastModified($path),
        ];
    }

    /**
     * Check if a file exists
     */
    public function exists(string $path): bool
    {
        // Remove leading slash if present
        $path = ltrim($path, '/');
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Delete a file
     */
    public function delete(string $directory, string $name): bool
    {
        $this->validateDirectory($directory);
        $this->validateFilename($name);

        $path = "{$this->baseImagePath}/{$directory}/{$name}";

        if (!Storage::disk($this->disk)->exists($path)) {
            return false;
        }

        try {
            return Storage::disk($this->disk)->delete($path);
        } catch (\Exception $e) {
            \Log::error("Failed to delete file: {$path}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Delete a file by its full path
     */
    public function deleteByPath(string $path): bool
    {
        // Normalize path
        $path = ltrim($path, '/');

        if (!Storage::disk($this->disk)->exists($path)) {
            return false;
        }

        try {
            return Storage::disk($this->disk)->delete($path);
        } catch (\Exception $e) {
            \Log::error("Failed to delete file by path: {$path}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Store an uploaded file
     */
    public function store($file, string $directory, ?string $customName = null): string
    {
        $this->validateDirectory($directory);

        $originalName = str_replace(' ', '-', $file->getClientOriginalName());
        $fileName = $customName ?? (uniqid() . '-' . $originalName);

        $path = $file->storeAs(
            "{$this->baseImagePath}/{$directory}",
            $fileName,
            $this->disk
        );

        return '/' . $path;
    }

    /**
     * Get all extension statistics from directories
     */
    public function getExtensionStatistics(array $directories): array
    {
        $extensions = [];

        foreach ($directories as $directory) {
            $path = "{$this->baseImagePath}/{$directory}";

            if (!Storage::disk($this->disk)->exists($path)) {
                continue;
            }

            $files = Storage::disk($this->disk)->files($path);

            foreach ($files as $file) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (!empty($extension)) {
                    $extensions[$extension] = ($extensions[$extension] ?? 0) + 1;
                }
            }
        }

        return $extensions;
    }

    /**
     * Build file metadata array
     */
    private function buildFileMetadata(string $fileName, string $directory): array
    {
        $path = "{$this->baseImagePath}/{$directory}/{$fileName}";
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Extract uniqid (13 character alphanumeric prefix)
        preg_match('/^([a-z0-9]{13})-/', $fileName, $matches);
        $uniqid = $matches[1] ?? null;

        // Remove uniqid prefix from name
        $nameWithoutUniqid = preg_replace('/^([a-z0-9]{13})-/', '', $fileName);

        return [
            'fullname' => $fileName,
            'path' => "/{$path}",
            'directory' => $directory,
            'name' => $nameWithoutUniqid,
            'extension' => $extension,
            'uniqid' => $uniqid,
            'size' => Storage::disk($this->disk)->size($path),
        ];
    }

    /**
     * Security: Validate directory name
     */
    private function validateDirectory(string $directory): void
    {
        if (str_contains($directory, '..') ||
            str_contains($directory, '/') ||
            str_contains($directory, '\\')) {
            throw new \InvalidArgumentException('Invalid directory name');
        }
    }

    /**
     * Security: Validate filename
     */
    private function validateFilename(string $filename): void
    {
        if (str_contains($filename, '..') ||
            str_contains($filename, '/') ||
            str_contains($filename, '\\')) {
            throw new \InvalidArgumentException('Invalid filename');
        }
    }
}
