@props([
    'posts' => []
])

<section>
    <div class="section-container">
        <div class="section-title">
            <h2 class="uppercase">popular post</h2>
            <a href="{{route('articles')}}" class="secondary-button">VIEW ALL</a>
        </div>

        @if($posts->isEmpty())
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Popular Posts Yet</h3>
                <p class="text-gray-500">Check back soon for trending content!</p>
            </div>
        @else
            <div class="latest-news">
                <!-- Top Featured Post -->
                @if($posts->isNotEmpty())
                    @php $featuredPost = $posts->first(); @endphp
                    <div>
                        <div role="list">
                            <div role="listitem">
                                <a href="{{ route('post.show', $featuredPost->slug) }}" class="latest-top">
                                    <div class="latest-img">
                                        <img src="{{ $featuredPost->image_path }}"
                                             loading="lazy"
                                             alt="{{ $featuredPost->image_alt ? $featuredPost->image_alt : $featuredPost->title }}"
                                             class="cover-image" />
                                    </div>
                                    <div class="latest-right">
                                        <div class="latest-content">
                                            <div class="hero-title">
                                                @if($featuredPost->category)
                                                    <div class="body-small">{{ $featuredPost->category->name }}</div>
                                                @endif
                                                <div class="latest-title">
                                                    {{ $featuredPost->title }}
                                                </div>
                                            </div>
                                            @if($featuredPost->excerpt)
                                                <p>{{ Str::limit($featuredPost->excerpt, 200) }}</p>
                                            @endif
                                        </div>
                                        <div class="news-date">
                                            <div class="body-small">{{ $featuredPost->created_at->format('M d, Y') }}</div>
                                            <div class="black-line"></div>
                                            <div class="body-small">{{ $featuredPost->read_time }} Min</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="latest-divider"></div>

                <!-- Bottom Grid Posts -->
                @if($posts->count() > 1)
                    <div>
                        <div role="list" class="latest-bottom">
                            @foreach($posts->skip(1) as $post)
                                <div role="listitem" class="news-outer">
                                    <a href="{{ route('post.show', $post->slug) }}" class="latest-info">
                                        <div class="latest-inner">
                                            <div class="hero-title space-large">
                                                @if($post->category)
                                                    <div class="body-small small">{{ $post->category->name }}</div>
                                                @endif
                                                <h5>{{ $post->title }}</h5>
                                            </div>
                                            <div class="news-date">
                                                <div class="body-small small">{{ $post->user->firstname ?? 'Anonymous' }}</div>
                                                <div class="black-line"></div>
                                                <div class="body-small small">{{ $post->created_at->format('M d, Y') }}</div>
                                            </div>
                                        </div>
                                        <div class="latest-thumb">
                                            <img src="{{ $post->image_path }}"
                                                 loading="lazy"
                                                 alt="{{ $post->image_alt ? $post->image_alt : $post->title }}"
                                                 class="cover-image" />
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
