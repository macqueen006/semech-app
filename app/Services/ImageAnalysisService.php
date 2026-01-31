<?php

namespace App\Services;

use App\Repositories\ImageRepository;

class ImageAnalysisService
{
    private ImageRepository $repository;
    private ImageUsageService $usageService;

    public function __construct(
        ImageRepository $repository,
        ImageUsageService $usageService
    ) {
        $this->repository = $repository;
        $this->usageService = $usageService;
    }

    /**
     * Get comprehensive file list with usage statistics and duplicate detection
     */
    public function analyzeDirectories(array $directories): array
    {
        // Get all files from directories
        $fileList = $this->repository->getAllFromDirectories($directories);

        // Get usage counts in one batch query
        $usageCounts = $this->usageService->getUsageCounts($directories);

        // Add usage counts to file list
        foreach ($fileList as &$file) {
            $file['usage_count'] = $usageCounts[$file['path']] ?? 0;
        }
        unset($file);

        // Get extension statistics
        $extensions = $this->getExtensionCounts($fileList);

        // Detect duplicates
        [$duplicateNames, $uniqueNames] = $this->detectDuplicates($fileList);

        return [
            'files' => $fileList,
            'extensions' => $extensions,
            'duplicates' => $duplicateNames,
            'uniques' => $uniqueNames,
            'stats' => [
                'total_files' => count($fileList),
                'total_duplicates' => count($duplicateNames),
                'total_unique' => count($uniqueNames),
                'extension_count' => count($extensions)
            ]
        ];
    }

    /**
     * Get extension statistics from file list
     */
    private function getExtensionCounts(array $fileList): array
    {
        $extensions = [];

        foreach ($fileList as $file) {
            if (!empty($file['extension'])) {
                $extensions[$file['extension']] = ($extensions[$file['extension']] ?? 0) + 1;
            }
        }

        return $extensions;
    }

    /**
     * Detect duplicate files based on name (excluding uniqid prefix)
     */
    private function detectDuplicates(array $fileList): array
    {
        $nameFrequency = [];

        // Count occurrences of each clean name
        foreach ($fileList as $file) {
            $cleanName = $file['name'];
            $nameFrequency[$cleanName][] = $file['fullname'];
        }

        $duplicates = [];
        $uniques = [];

        // Categorize files
        foreach ($nameFrequency as $cleanName => $fullNames) {
            if (count($fullNames) > 1) {
                // Multiple files with same name = duplicates
                $duplicates = array_merge($duplicates, $fullNames);
            } else {
                // Single file with this name = unique
                $uniques = array_merge($uniques, $fullNames);
            }
        }

        return [$duplicates, $uniques];
    }

    /**
     * Find unused images in directories
     */
    public function findUnusedImages(array $directories): array
    {
        $fileList = $this->repository->getAllFromDirectories($directories);
        $usageCounts = $this->usageService->getUsageCounts($directories);

        $unusedImages = [];

        foreach ($fileList as $file) {
            $usageCount = $usageCounts[$file['path']] ?? 0;

            if ($usageCount === 0) {
                $unusedImages[] = [
                    'path' => $file['path'],
                    'name' => $file['name'],
                    'fullname' => $file['fullname'],
                    'directory' => $file['directory'],
                    'size' => $file['size'],
                    'extension' => $file['extension']
                ];
            }
        }

        return $unusedImages;
    }

    /**
     * Find duplicate images (same base name, different uniqid)
     */
    public function findDuplicateGroups(array $directories): array
    {
        $fileList = $this->repository->getAllFromDirectories($directories);
        $groups = [];

        foreach ($fileList as $file) {
            $cleanName = $file['name'];
            $groups[$cleanName][] = $file;
        }

        // Filter to only groups with multiple files
        return array_filter($groups, function($group) {
            return count($group) > 1;
        });
    }

    /**
     * Get storage size breakdown by directory and extension
     */
    public function getStorageBreakdown(array $directories): array
    {
        $fileList = $this->repository->getAllFromDirectories($directories);

        $breakdown = [
            'by_directory' => [],
            'by_extension' => [],
            'total_size' => 0
        ];

        foreach ($fileList as $file) {
            $size = $file['size'];
            $directory = $file['directory'];
            $extension = $file['extension'];

            // Aggregate by directory
            if (!isset($breakdown['by_directory'][$directory])) {
                $breakdown['by_directory'][$directory] = [
                    'size' => 0,
                    'count' => 0
                ];
            }
            $breakdown['by_directory'][$directory]['size'] += $size;
            $breakdown['by_directory'][$directory]['count']++;

            // Aggregate by extension
            if (!isset($breakdown['by_extension'][$extension])) {
                $breakdown['by_extension'][$extension] = [
                    'size' => 0,
                    'count' => 0
                ];
            }
            $breakdown['by_extension'][$extension]['size'] += $size;
            $breakdown['by_extension'][$extension]['count']++;

            // Total size
            $breakdown['total_size'] += $size;
        }

        // Convert bytes to human-readable format
        foreach ($breakdown['by_directory'] as $dir => &$data) {
            $data['size_formatted'] = $this->formatBytes($data['size']);
        }
        unset($data);

        foreach ($breakdown['by_extension'] as $ext => &$data) {
            $data['size_formatted'] = $this->formatBytes($data['size']);
        }
        unset($data);

        $breakdown['total_size_formatted'] = $this->formatBytes($breakdown['total_size']);

        return $breakdown;
    }

    /**
     * Format bytes to human-readable size
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
