<?php

//namespace App\Services;

//class ImageFilterService
//{
//    public function applyFilters(array $fileList, array $filters): array
//    {
//        // Search by terms
//        if (!empty($filters['terms'])) {
//            $fileList = $this->filterByTerms($fileList, $filters['terms']);
//        }
//
//        // Filter by extensions
//        if (!empty($filters['extensions'])) {
//            $fileList = $this->filterByExtensions($fileList, $filters['extensions']);
//        }
//
//        // Filter by duplicates
//        if (!empty($filters['duplicates'])) {
//            $fileList = $this->filterByDuplicates(
//                $fileList,
//                $filters['duplicates'],
//                $filters['duplicateNames'] ?? [],
//                $filters['uniqueNames'] ?? []
//            );
//        }
//
//        return $fileList;
//    }
//
//    public function sortFiles(array $fileList, string $order): array
//    {
//        switch ($order) {
//            case 'asc':
//                usort($fileList, fn($a, $b) => strcmp($a['name'], $b['name']));
//                break;
//            case 'desc':
//                usort($fileList, fn($a, $b) => strcmp($b['name'], $a['name']));
//                break;
//            case 'ascAlphabetical':
//                usort($fileList, fn($a, $b) => strcmp($a['uniqid'], $b['uniqid']));
//                break;
//            case 'descAlphabetical':
//                usort($fileList, fn($a, $b) => strcmp($b['uniqid'], $a['uniqid']));
//                break;
//            case 'ascSize':
//                usort($fileList, fn($a, $b) => $a['size'] - $b['size']);
//                break;
//            case 'descSize':
//                usort($fileList, fn($a, $b) => $b['size'] - $a['size']);
//                break;
//            case 'ascUsage':
//                usort($fileList, fn($a, $b) => $a['usage_count'] - $b['usage_count']);
//                break;
//            case 'descUsage':
//                usort($fileList, fn($a, $b) => $b['usage_count'] - $a['usage_count']);
//                break;
//            default:
//                usort($fileList, fn($a, $b) => strcmp($a['name'], $b['name']));
//                break;
//        }
//
//        return $fileList;
//    }
//
//    private function filterByTerms(array $fileList, string $terms): array
//    {
//        $keywords = explode(' ', $terms);
//
//        return array_filter($fileList, function($file) use ($keywords) {
//            foreach ($keywords as $keyword) {
//                if (stripos($file['name'], $keyword) !== false ||
//                    stripos($file['uniqid'], $keyword) !== false) {
//                    return true;
//                }
//            }
//            return false;
//        });
//    }
//
//    private function filterByExtensions(array $fileList, array $extensions): array
//    {
//        return array_filter($fileList, function($file) use ($extensions) {
//            return in_array($file['extension'], $extensions);
//        });
//    }
//
//    private function filterByDuplicates(
//        array $fileList,
//        array $duplicates,
//        array $duplicateNames,
//        array $uniqueNames
//    ): array {
//        if ($duplicates[0] && $duplicates[1]) {
//            return $fileList;
//        }
//
//        if ($duplicates[0]) {
//            return array_filter($fileList, fn($file) => in_array($file['fullname'], $duplicateNames));
//        }
//
//        if ($duplicates[1]) {
//            return array_filter($fileList, fn($file) => in_array($file['fullname'], $uniqueNames));
//        }
//
//        return $fileList;
//    }
//}


namespace App\Services;

class ImageFilterService
{
    /**
     * Apply multiple filters to file list
     */
    public function applyFilters(array $fileList, array $filters): array
    {
        if (empty($fileList)) {
            return [];
        }

        // Apply search terms filter
        if (!empty($filters['terms'])) {
            $fileList = $this->filterBySearchTerms($fileList, $filters['terms']);
        }

        // Apply extension filter
        if (!empty($filters['extensions']) && is_array($filters['extensions'])) {
            $fileList = $this->filterByExtensions($fileList, $filters['extensions']);
        }

        // Apply duplicate filter
        if (!empty($filters['duplicates']) && is_array($filters['duplicates'])) {
            $fileList = $this->filterByDuplicates(
                $fileList,
                $filters['duplicates'],
                $filters['duplicateNames'] ?? [],
                $filters['uniqueNames'] ?? []
            );
        }

        // Apply usage filter
        if (isset($filters['usage'])) {
            $fileList = $this->filterByUsage($fileList, $filters['usage']);
        }

        // Apply size filter
        if (!empty($filters['min_size']) || !empty($filters['max_size'])) {
            $fileList = $this->filterBySize(
                $fileList,
                $filters['min_size'] ?? 0,
                $filters['max_size'] ?? PHP_INT_MAX
            );
        }

        return $fileList;
    }

    /**
     * Sort files by specified order
     */
    public function sortFiles(array $fileList, string $order): array
    {
        if (empty($fileList)) {
            return [];
        }

        $sortMap = [
            'asc' => fn($a, $b) => strcmp($a['name'], $b['name']),
            'desc' => fn($a, $b) => strcmp($b['name'], $a['name']),
            'ascAlphabetical' => fn($a, $b) => strcmp($a['uniqid'] ?? '', $b['uniqid'] ?? ''),
            'descAlphabetical' => fn($a, $b) => strcmp($b['uniqid'] ?? '', $a['uniqid'] ?? ''),
            'ascSize' => fn($a, $b) => $a['size'] <=> $b['size'],
            'descSize' => fn($a, $b) => $b['size'] <=> $a['size'],
            'ascUsage' => fn($a, $b) => ($a['usage_count'] ?? 0) <=> ($b['usage_count'] ?? 0),
            'descUsage' => fn($a, $b) => ($b['usage_count'] ?? 0) <=> ($a['usage_count'] ?? 0),
        ];

        $sortFunction = $sortMap[$order] ?? $sortMap['asc'];
        usort($fileList, $sortFunction);

        return $fileList;
    }

    /**
     * Paginate file list
     */
    public function paginate(array $fileList, int $page = 1, int $perPage = 20): array
    {
        $total = count($fileList);
        $offset = ($page - 1) * $perPage;

        return [
            'data' => array_slice($fileList, $offset, $perPage),
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total),
            ]
        ];
    }

    /**
     * Filter by search terms (searches in name and uniqid)
     */
    private function filterBySearchTerms(array $fileList, string $terms): array
    {
        $keywords = array_filter(explode(' ', strtolower($terms)));

        if (empty($keywords)) {
            return $fileList;
        }

        return array_filter($fileList, function ($file) use ($keywords) {
            $searchableText = strtolower(
                ($file['name'] ?? '') . ' ' .
                ($file['uniqid'] ?? '') . ' ' .
                ($file['fullname'] ?? '')
            );

            foreach ($keywords as $keyword) {
                if (str_contains($searchableText, $keyword)) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Filter by file extensions
     */
    private function filterByExtensions(array $fileList, array $extensions): array
    {
        if (empty($extensions)) {
            return $fileList;
        }

        // Normalize extensions to lowercase
        $extensions = array_map('strtolower', $extensions);

        return array_filter($fileList, function ($file) use ($extensions) {
            $fileExtension = strtolower($file['extension'] ?? '');
            return in_array($fileExtension, $extensions, true);
        });
    }

    /**
     * Filter by duplicate status
     */
    private function filterByDuplicates(
        array $fileList,
        array $duplicates,
        array $duplicateNames,
        array $uniqueNames
    ): array
    {
        $showDuplicates = $duplicates[0] ?? false;
        $showUniques = $duplicates[1] ?? false;

        // Show all if both are selected or neither
        if (($showDuplicates && $showUniques) || (!$showDuplicates && !$showUniques)) {
            return $fileList;
        }

        // Show only duplicates
        if ($showDuplicates) {
            return array_filter($fileList, function ($file) use ($duplicateNames) {
                return in_array($file['fullname'], $duplicateNames, true);
            });
        }

        // Show only unique files
        if ($showUniques) {
            return array_filter($fileList, function ($file) use ($uniqueNames) {
                return in_array($file['fullname'], $uniqueNames, true);
            });
        }

        return $fileList;
    }

    /**
     * Filter by usage count
     */
    private function filterByUsage(array $fileList, string $usage): array
    {
        switch ($usage) {
            case 'used':
                return array_filter($fileList, fn($file) => ($file['usage_count'] ?? 0) > 0);

            case 'unused':
                return array_filter($fileList, fn($file) => ($file['usage_count'] ?? 0) === 0);

            default:
                return $fileList;
        }
    }

    /**
     * Filter by file size range
     */
    private function filterBySize(array $fileList, int $minSize, int $maxSize): array
    {
        return array_filter($fileList, function ($file) use ($minSize, $maxSize) {
            $size = $file['size'] ?? 0;
            return $size >= $minSize && $size <= $maxSize;
        });
    }

    /**
     * Get available filter options from file list
     */
    public function getFilterOptions(array $fileList): array
    {
        $extensions = [];
        $minSize = PHP_INT_MAX;
        $maxSize = 0;
        $usedCount = 0;
        $unusedCount = 0;

        foreach ($fileList as $file) {
            // Collect unique extensions
            if (!empty($file['extension'])) {
                $extensions[$file['extension']] = ($extensions[$file['extension']] ?? 0) + 1;
            }

            // Track size range
            $size = $file['size'] ?? 0;
            $minSize = min($minSize, $size);
            $maxSize = max($maxSize, $size);

            // Count usage
            if (($file['usage_count'] ?? 0) > 0) {
                $usedCount++;
            } else {
                $unusedCount++;
            }
        }

        return [
            'extensions' => $extensions,
            'size_range' => [
                'min' => $minSize === PHP_INT_MAX ? 0 : $minSize,
                'max' => $maxSize
            ],
            'usage' => [
                'used' => $usedCount,
                'unused' => $unusedCount
            ]
        ];
    }
}
