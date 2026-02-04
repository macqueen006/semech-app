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

    // Get total footer ads
    $totalAdsCount = Advertisement::where('position', 'footer')
        ->where('is_active', true)
        ->count();

    // Calculate offset with cycling
    $offset = $totalAdsCount > 0 ? ($pageIndex % $totalAdsCount) : 0;
@endphp

{{-- Alternative: Minimal Footer Ad --}}
@if($totalAdsCount > 0)
    <div class="w-full py-6 bg-primary px-4">
        <div class="max-w-full mx-auto">
            <div class="text-center">
                <x-advertisement-display position="footer" :limit="1" :offset="$offset" />
            </div>
        </div>
    </div>
@endif
