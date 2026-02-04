@php
    use App\Models\Advertisement;

    $pages = [
        'home' => 0,
        'about' => 1,
        'contact' => 2,
        'blog' => 3,
        'services' => 4,
        'portfolio' => 5,
    ];

    $currentPage = Route::currentRouteName();
    $pageIndex = $pages[$currentPage] ?? 0;

    // Get total header ads
    $totalAdsCount = Advertisement::where('position', 'header')
        ->where('is_active', true)
        ->where(function ($query) {
            $query->whereNull('start_date')
                ->orWhere('start_date', '<=', now());
        })
        ->where(function ($query) {
            $query->whereNull('end_date')
                ->orWhere('end_date', '>=', now());
        })
        ->count();

    // Calculate offset with cycling
    $offset = $totalAdsCount > 0 ? ($pageIndex % $totalAdsCount) : 0;
@endphp

@if($totalAdsCount > 0)
    <!-- Header Banner Advertisement -->
    <div class="w-full bg-primary dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700" id="header-ad-banner">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="relative">
                <!-- Advertisement Content -->
                <div class="flex justify-center items-center min-h-[100px]">
                    <x-advertisement-display position="header" :limit="1" :offset="$offset" />
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Reset ad visibility when page is restored from cache (back/forward navigation)
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    const adBanner = document.getElementById('header-ad-banner');
                    if (adBanner) {
                        adBanner.style.display = 'block';
                    }
                }
            });
        </script>
    @endpush
@endif
