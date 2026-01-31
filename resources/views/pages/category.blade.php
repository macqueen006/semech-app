<x-guest-layout>
    @section('meta')
        <title>{{ $category->name }} - Articles</title>
    @endsection
    <div>
        <!-- Title Section with Breadcrumb -->
        <section class="title-section">
            <div class="hero-container">
                <div class="title-wrap">
                    <!-- Breadcrumb -->
                    <ol class="flex items-center whitespace-nowrap">
                        <li class="inline-flex items-center">
                            <a class="flex items-center text-sm text-gray-500 hover:text-blue-600 focus:outline-hidden focus:text-blue-600"
                               href="{{ route('home.index') }}">
                                Home
                            </a>
                            <svg class="shrink-0 size-5 text-gray-400 mx-2" width="16" height="16" viewBox="0 0 16 16"
                                 fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M6 13L10 3" stroke="currentColor" stroke-linecap="round"></path>
                            </svg>
                        </li>
                        <li class="inline-flex items-center text-sm font-semibold truncate"
                            aria-current="page">
                            {{ $category->name }}
                        </li>
                    </ol>

                    <!-- Category Title -->
                    <h1 class="main-title uppercase">{{ $category->name }}</h1>

                    <!-- Category Badge -->
                    <div class="mt-1">
                    <span class="px-4 py-2 text-sm font-semibold rounded-lg inline-block"
                          style="background-color: {{ $category->backgroundColor }}; color: {{ $category->textColor }}">
                        {{ $posts->count() }} {{ Str::plural('article', $posts->count()) }} available
                    </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Articles Section -->
        <section class="section pt-0">
            <div class="hero-container">
                <div>
                    @if($posts->isNotEmpty())
                        <div role="list" class="news-wrap">
                            @foreach($posts as $post)
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
                                                <div
                                                    class="home-subtitle">{{ $post->user->firstname }} {{ $post->user->lastname }}</div>
                                                <div class="black-line"></div>
                                                <div
                                                    class="home-subtitle">{{ $post->created_at->format('M d, Y') }}</div>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <div class="flex items-center gap-1 text-gray-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    <span
                                                        class="subtitle">{{ number_format($post->view_count) }} views</span>
                                                </div>
                                                <div class="subtitle">{{ $post->read_time }} min read</div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($hasPrevious || $hasMore)
                            <div role="navigation" aria-label="List" class="pagination-wrapper">
                                @if($hasPrevious)
                                    <a href="{{ route('category.show', ['slug' => $slug, 'page' => $page - 1]) }}"
                                       aria-label="Prev Page" class="pagination-btn">
                                        <div class="inline-block">Previous</div>
                                    </a>
                                @endif

                                @if($hasMore)
                                    <a href="{{ route('category.show', ['slug' => $slug, 'page' => $page + 1]) }}"
                                       aria-label="Next Page" class="pagination-btn">
                                        <div class="inline-block">Next</div>
                                    </a>
                                @endif
                            </div>
                        @endif
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-16">
                            <svg class="w-24 h-24 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="text-2xl font-bold text-gray-700 mb-2">No Posts Yet</h3>
                            <p class="text-gray-500 mb-6">There are no published posts in this category yet.</p>
                            <a href="{{ route('home') }}"
                               class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                                Back to Home
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</x-guest-layout>
