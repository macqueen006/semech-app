<x-guest-layout>
    @section('meta')
        <title>Articles</title>
    @endsection

        <div>
            <x-ads.header />
            <!-- Title Section -->
            <section class="title-section">
                <div class="hero-container">
                    <div class="title-wrap">
                        <h1 class="main-title uppercase">recent articles</h1>
                    </div>
                </div>
            </section>

            <!-- Articles Section -->
            <section class="section pt-0">
                <div class="hero-container">
                    <div>
                        <div role="list" class="news-wrap">
                            @foreach($posts as $index => $post)
                                @php
                                    // Calculate global position across all pages (1-based)
                                    $globalPosition = $offset + $index + 1;
                                @endphp

                                {{-- Display the post --}}
                                <div role="listitem">
                                    <a href="{{ route('post.show', $post->slug) }}" class="news-right">
                                        <div class="news-image">
                                            <img
                                                src="{{ $post->image_path ? asset($post->image_path) : asset('images/default-post.jpg') }}"
                                                loading="lazy"
                                                alt="{{ $post->title }}"
                                                sizes="(max-width: 479px) 92vw, (max-width: 767px) 46vw, (max-width: 991px) 47vw, 29vw"
                                                class="cover-image"/>
                                        </div>
                                        <div class="new-info">
                                            <h5>{{ $post->title }}</h5>
                                            <div class="news-date">
                                                <div class="home-subtitle">{{ $post->user->firstname }} {{ $post->user->lastname }}</div>
                                                <div class="black-line"></div>
                                                <div class="home-subtitle">{{ $post->created_at->format('M d, Y') }}</div>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <div class="flex items-center gap-1 text-gray-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    <span class="subtitle">{{ number_format($post->view_count) }} views</span>
                                                </div>
                                                <div class="subtitle">{{ $post->read_time }} min read</div>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                {{-- Display ad after every 3rd post --}}
                                @if($betweenPostsAds->isNotEmpty() && $globalPosition % 3 === 0)
                                    @php
                                        // Calculate which ad to show
                                        $adSlotNumber = ($globalPosition / 3) - 1;

                                        // Rotate through available ads
                                        $adIndex = $adSlotNumber % $betweenPostsAds->count();
                                        $currentAd = $betweenPostsAds[$adIndex];
                                    @endphp

                                    <div role="listitem" class="w-full">
                                        <div class="my-6">
                                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border-l-4 border-secondary">
                                                <div class="flex items-center justify-between mb-4">
                                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase">Featured Ad</h4>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Sponsored</span>
                                                </div>

                                                <div class="w-full">
                                                    @if($currentAd->link_url)
                                                        <a
                                                            href="{{ $currentAd->link_url }}"
                                                            @if($currentAd->open_new_tab) target="_blank" rel="noopener noreferrer" @endif
                                                            onclick="trackAdClick({{ $currentAd->id }})"
                                                            class="block group"
                                                        >
                                                            @if($currentAd->image_path)
                                                                <img
                                                                    src="{{ asset($currentAd->image_path) }}"
                                                                    alt="{{ $currentAd->title }}"
                                                                    class="w-full h-auto rounded-sm transition-transform duration-300 group-hover:scale-105 object-cover"
                                                                >
                                                            @endif
                                                        </a>
                                                    @else
                                                        @if($currentAd->image_path)
                                                            <img
                                                                src="{{ $currentAd->image_path }}"
                                                                alt="{{ $currentAd->title }}"
                                                                class="w-full h-auto rounded-sm object-cover"
                                                            >
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($hasPrevious || $hasMore)
                            <div role="navigation" aria-label="List" class="pagination-wrapper">
                                @if($hasPrevious)
                                    <a href="{{ route('home.index', ['page' => $page - 1]) }}" aria-label="Prev Page" class="pagination-btn">
                                        <div class="inline-block">Previous</div>
                                    </a>
                                @endif

                                @if($hasMore)
                                    <a href="{{ route('home.index', ['page' => $page + 1]) }}" aria-label="Next Page" class="pagination-btn">
                                        <div class="inline-block">Next</div>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        </div>

        <script>
            function trackAdClick(advertisementId) {
                fetch('/track-ad-click', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        advertisement_id: advertisementId
                    })
                });
            }
        </script>
</x-guest-layout>
