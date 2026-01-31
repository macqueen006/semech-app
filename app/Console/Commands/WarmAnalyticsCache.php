<?php

namespace App\Console\Commands;

use App\Services\AnalyticsService;
use Illuminate\Console\Command;

class WarmAnalyticsCache extends Command
{
    protected $signature = 'analytics:warm-cache';
    protected $description = 'Warm up analytics cache with fresh data';

    public function handle(AnalyticsService $analyticsService)
    {
        $this->info('Warming analytics cache...');

        // Clear old cache
        $analyticsService->clearCache();
        $this->info('✓ Cleared old cache');

        // Warm cache for common date ranges
        $ranges = [
            ['start' => now()->startOfDay(), 'end' => now(), 'label' => 'Today'],
            ['start' => now()->subDay()->startOfDay(), 'end' => now()->subDay()->endOfDay(), 'label' => 'Yesterday'],
            ['start' => now()->subDays(7), 'end' => now(), 'label' => 'Last 7 days'],
            ['start' => now()->subDays(30), 'end' => now(), 'label' => 'Last 30 days'],
            ['start' => now()->startOfMonth(), 'end' => now(), 'label' => 'This month'],
        ];

        foreach ($ranges as $range) {
            $this->info("Processing: {$range['label']}");
            $analyticsService->getOverviewStats($range['start'], $range['end'], false);
        }

        // Warm other caches
        $this->info('Warming top posts cache...');
        $analyticsService->getTopPosts(10);

        $this->info('Warming categories cache...');
        $analyticsService->getPopularCategories(5);
        $analyticsService->getCategoryViews(5);

        $this->info('Warming authors cache...');
        $analyticsService->getTopAuthorsByPosts(5);
        $analyticsService->getTopAuthorsByViews(5);

        $this->info('Warming engagement cache...');
        $analyticsService->getMostBookmarkedPosts(5);
        $analyticsService->getMostCommentedPosts(5);

        $this->info('✓ Analytics cache warmed successfully!');
        return 0;
    }
}


