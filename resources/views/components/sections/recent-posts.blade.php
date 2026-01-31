@props([
    'posts' => []
])

<section class="section">
    <div class="section-container">
        <div class="section-title">
            <h2 class="uppercase">recent articles</h2>
            <div>
                <div role="list">
                    <div role="listitem">
                        <a href="{{ route('articles') }}" class="secondary-button">VIEW ALL</a>
                    </div>
                </div>
            </div>
        </div>

        @if($posts->isEmpty())
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Recent Posts Yet</h3>
                <p class="text-gray-500">New posts will appear here soon!</p>
            </div>
        @else
            <div class="news-block">
                <!-- Left Featured Post -->
                @if($posts->isNotEmpty())
                    @php $featuredPost = $posts->first(); @endphp
                    <div class="news-left">
                        <div role="list">
                            <div role="listitem">
                                <a href="{{ route('post.show', $featuredPost->slug) }}" class="news-right">
                                    <div class="news-thumb">
                                        <img src="{{ $featuredPost->image_path }}"
                                             loading="lazy"
                                             alt="{{ $featuredPost->image_alt ? $featuredPost->image_alt : $featuredPost->title }}"
                                             class="cover-image" />
                                    </div>
                                    <div class="pick-content">
                                        <div class="pick-data">
                                            <h3>{{ $featuredPost->title }}</h3>
                                            @if($featuredPost->excerpt)
                                                <p>{{ Str::limit($featuredPost->excerpt, 200) }}</p>
                                            @endif
                                        </div>
                                        <div class="news-date">
                                            <div class="body-small small">{{ $featuredPost->created_at->format('M d, Y') }}</div>
                                            <div class="black-line"></div>
                                            <div class="body-small small">{{ $featuredPost->read_time }} Min</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Right Grid Posts -->
                @if($posts->count() > 1)
                    <div class="news-body">
                        <div role="list" class="news-collection">
                            @foreach($posts->skip(1) as $post)
                                <div role="listitem">
                                    <a href="{{ route('post.show', $post->slug) }}" class="news-right">
                                        <div class="news-image">
                                            <img src="{{ $post->image_path }}"
                                                 loading="lazy"
                                                 alt="{{ $post->image_alt ? $post->image_alt : $post->title }}"
                                                 class="cover-image" />
                                        </div>
                                        <div class="new-info">
                                            <h5>{{ $post->title }}</h5>
                                            <div class="news-date">
                                                <div class="home-subtitle">{{ $post->user->firstname ?? 'Anonymous' }}</div>
                                                <div class="black-line"></div>
                                                <div class="home-subtitle">{{ $post->created_at->format('M d, Y') }}</div>
                                            </div>
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
