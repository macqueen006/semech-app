@props([
    'posts' => []
])

<section class="hero-section">
    <div class="hero-container">
        <div class="hero-top">
            <div class="tag-block uppercase">trending</div>
            <div class="w-full">
                @if($posts->isEmpty())
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Trending Posts Yet</h3>
                        <p class="text-gray-500">Check back soon for the latest trending content!</p>
                    </div>
                @else
                    <div role="list" class="hero-content">
                        @foreach($posts->take(4) as $post)
                            <div role="listitem" class="news-list">
                                <a href="{{ route('post.show', $post->slug) }}" class="hero-list inline-block">
                                    @if($post->category)
                                        <div class="category-text">{{ $post->category->name }}</div>
                                    @endif
                                    <h6 class="text-black text-base leading-[150%] font-normal">
                                        {{ $post->title }}
                                    </h6>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($posts->count() >= 3)
        <!-- Featured Posts Grid - Next 3 posts (5th, 6th, 7th) -->
        <div>
            <div role="list" class="hero-info">
                @foreach($posts->skip(4)->take(3) as $post)
                    <div class="hero-data" role="listitem">
                        @if($post->image_path)
                            <img src="{{ $post->image_path }}"
                                 class="absolute inset-0 size-full object-cover object-center"
                                 alt="{{ $post->image_alt ? $post->image_alt : $post->title }}">
                        @else
                            <div class="absolute inset-0 size-full bg-gradient-to-br from-gray-200 to-gray-300"></div>
                        @endif

                        <a href="{{ route('post.show', $post->slug) }}" class="hero-img z-10">
                            <div class="hero-title">
                                @if($post->category)
                                    <div class="body-x-small">{{ $post->category->name }}</div>
                                @endif
                                <div class="hero-text">
                                    {{ $post->title }}
                                </div>
                            </div>
                            <div class="line"></div>
                            <div class="news-date">
                                <div class="body-x-small">{{ $post->created_at->format('M d, Y') }}</div>
                                <div class="small-line"></div>
                                <div class="body-x-small">{{ $post->user->firstname ?? 'Anonymous'  }} {{ $post->user->lastname ?? 'Anonymous'  }}</div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
