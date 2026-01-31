<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display the dashboard analytics page
     */
    public function index(Request $request)
    {
        $dateRange = $request->get('dateRange', '30');
        $autoRefresh = $request->get('autoRefresh', true);

        // Set date ranges based on selection
        $dates = $this->setDateRange($dateRange);
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        try {
            // Get overview stats from service
            $overview = $this->analyticsService->getOverviewStats($startDate, $endDate);

            // Get comparison data
            $previousStart = $startDate->copy()->subDays($startDate->diffInDays($endDate));
            $previousEnd = $startDate->copy()->subSecond();

            $comparison = $this->analyticsService->getGrowthComparison(
                $startDate,
                $endDate,
                $previousStart,
                $previousEnd
            );

            // Today vs Yesterday
            $todayComparison = $this->analyticsService->getGrowthComparison(
                now()->startOfDay(),
                now()->endOfDay(),
                now()->subDay()->startOfDay(),
                now()->subDay()->endOfDay()
            );

            // This week vs Last week
            $weekComparison = $this->analyticsService->getGrowthComparison(
                now()->startOfWeek(),
                now()->endOfWeek(),
                now()->subWeek()->startOfWeek(),
                now()->subWeek()->endOfWeek()
            );

            // Get posts growth data (daily breakdown for last 30 days)
            $postsGrowth = \App\Models\Post::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->whereBetween('created_at', [now()->subDays(30), now()])
                ->groupBy(\DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->get();

            $data = [
                // Overview stats
                'totalPosts' => $overview['posts']['total'],
                'publishedPosts' => $overview['posts']['published'],
                'draftPosts' => $overview['posts']['drafts'],
                'scheduledPosts' => $overview['posts']['scheduled'],
                'postsInPeriod' => $overview['posts']['in_period'],

                // View stats
                'totalViews' => $overview['views']['total'],
                'viewsInPeriod' => $overview['views']['in_period'],
                'uniqueViewsInPeriod' => $overview['views']['unique_in_period'],
                'viewsGrowth' => $overview['views']['daily_breakdown'],
                'deviceBreakdown' => $overview['views']['device_breakdown'],

                // User stats
                'totalUsers' => $overview['users']['total'],
                'newUsersInPeriod' => $overview['users']['new_in_period'],
                'activeAuthors' => $overview['users']['active_authors'],

                // Subscriber stats
                'activeSubscribers' => $overview['subscribers']['total_active'],
                'newSubscribersInPeriod' => $overview['subscribers']['new_in_period'],
                'unsubscribedInPeriod' => $overview['subscribers']['unsubscribed_in_period'],

                // Engagement stats
                'totalBookmarks' => $overview['engagement']['total_bookmarks'],
                'bookmarksInPeriod' => $overview['engagement']['bookmarks_in_period'],
                'totalComments' => $overview['engagement']['total_comments'],
                'commentsInPeriod' => $overview['engagement']['comments_in_period'],
                'avgCommentsPerPost' => $overview['engagement']['avg_comments_per_post'],

                // Top performers
                'topPosts' => $this->analyticsService->getTopPosts(10),
                'popularCategories' => $this->analyticsService->getPopularCategories(5),
                'categoryViews' => $this->analyticsService->getCategoryViews(5),
                'topAuthors' => $this->analyticsService->getTopAuthorsByPosts(5),
                'topAuthorsByViews' => $this->analyticsService->getTopAuthorsByViews(5),
                'mostBookmarkedPosts' => $this->analyticsService->getMostBookmarkedPosts(5),
                'mostCommentedPosts' => $this->analyticsService->getMostCommentedPosts(5),

                // Comparisons
                'comparison' => $comparison,
                'todayComparison' => $todayComparison,
                'weekComparison' => $weekComparison,

                // For backward compatibility
                'postsToday' => $todayComparison['current']['posts'],
                'postsYesterday' => $todayComparison['previous']['posts'],
                'postsChange' => $todayComparison['changes']['posts'],
                'postsThisWeek' => $weekComparison['current']['posts'],
                'postsLastWeek' => $weekComparison['previous']['posts'],
                'weekChange' => $weekComparison['changes']['posts'],

                // Posts growth chart data
                'postsGrowth' => $postsGrowth,

                // UI state
                'dateRange' => $dateRange,
                'autoRefresh' => $autoRefresh,
                'lastUpdated' => now()->format('g:i A'),
            ];

            return view('admin.dashboard.index', $data);

        } catch (\Exception $e) {
            \Log::error('Analytics error: ' . $e->getMessage());

            return view('admin.dashboard.index', [
                'totalPosts' => 0,
                'publishedPosts' => 0,
                'draftPosts' => 0,
                'scheduledPosts' => 0,
                'totalViews' => 0,
                'totalUsers' => 0,
                'activeSubscribers' => 0,
                'dateRange' => $dateRange,
                'autoRefresh' => $autoRefresh,
                'lastUpdated' => now()->format('g:i A'),
                'error' => 'Failed to load analytics data',
            ]);
        }
    }

    /**
     * Refresh analytics data (AJAX endpoint)
     */
    public function refresh(Request $request)
    {
        $this->analyticsService->clearCache();

        return response()->json([
            'success' => true,
            'lastUpdated' => now()->format('g:i A'),
            'message' => 'Analytics data refreshed successfully'
        ]);
    }

    /**
     * Get analytics data (AJAX endpoint for auto-refresh)
     */
    public function getData(Request $request)
    {
        $dateRange = $request->get('dateRange', '30');

        $dates = $this->setDateRange($dateRange);
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        try {
            $overview = $this->analyticsService->getOverviewStats($startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => [
                    'totalPosts' => $overview['posts']['total'],
                    'totalViews' => $overview['views']['total'],
                    'totalUsers' => $overview['users']['total'],
                    'activeSubscribers' => $overview['subscribers']['total_active'],
                    'lastUpdated' => now()->format('g:i A'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load analytics data'
            ], 500);
        }
    }

    /**
     * Set date range based on selection
     */
    private function setDateRange($dateRange)
    {
        $endDate = now();
        $startDate = now();

        switch ($dateRange) {
            case 'today':
                $startDate = now()->startOfDay();
                break;
            case 'yesterday':
                $startDate = now()->subDay()->startOfDay();
                $endDate = now()->subDay()->endOfDay();
                break;
            case '7':
                $startDate = now()->subDays(7);
                break;
            case '30':
                $startDate = now()->subDays(30);
                break;
            case 'this_month':
                $startDate = now()->startOfMonth();
                break;
            case 'last_month':
                $startDate = now()->subMonth()->startOfMonth();
                $endDate = now()->subMonth()->endOfMonth();
                break;
            default:
                $startDate = now()->subDays(30);
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
        ];
    }
}
