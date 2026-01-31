@props([
    'posts' => []
])

<section class="section">
    <div class="section-container">
        <div class="section-title">
            <h2 class="uppercase">EDITOR'S PICK</h2>
            <a href="{{ route('articles') }}" class="secondary-button">VIEW ALL</a>
        </div>
        @if($posts->isEmpty())
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Featured Posts Yet</h3>
                <p class="text-gray-500">Check back soon for editor's picks!</p>
            </div>
        @else
            <div class="pick-wrap">
                <!-- Left side - 2 posts -->
                <div class="pick-left">
                    <div role="list" class="pick-collections">
                        @foreach($posts->take(2) as $highlight)
                            @if($highlight->post && $highlight->post->is_published)
                                <div role="listitem">
                                    <a href="{{ route('post.show', $highlight->post->slug) }}" class="pick-block">
                                        <div class="pick-img">
                                            <img src="{{ $highlight->post->image_path }}"
                                                 loading="lazy"
                                                 alt="{{ $highlight->post->image_alt ? $highlight->post->image_alt : $highlight->post->title }}"
                                                 class="cover-image" />
                                        </div>
                                        <h5>{{ $highlight->post->title }}</h5>
                                        <div class="news-date">
                                            <div class="body-small">{{ $highlight->post->created_at->format('M d, Y') }}</div>
                                            <div class="black-line"></div>
                                            <div class="body-small">{{ $highlight->post->read_time }} Min</div>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Right side - 1 large post -->
                @if($posts->count() >= 3)
                    @php
                        $featuredPost = $posts->skip(2)->first();
                    @endphp
                    @if($featuredPost->post && $featuredPost->post->is_published)
                        <div class="news-right">
                            <div role="list">
                                <div role="listitem">
                                    <a href="{{ route('post.show', $featuredPost->post->slug) }}" class="news-right">
                                        <div class="pick-right">
                                            <img src="{{ $featuredPost->post->image_path }}"
                                                 loading="lazy"
                                                 alt="{{ $featuredPost->post->image_alt ? $featuredPost->post->image_alt : $featuredPost->post->title }}"
                                                 class="cover-image" />
                                        </div>
                                        <div class="pick-content">
                                            <div class="pick-data">
                                                <h3>{{ $featuredPost->post->title }}</h3>
                                                @if($featuredPost->post->excerpt)
                                                    <p>{{ Str::limit($featuredPost->post->excerpt, 200) }}</p>
                                                @endif
                                            </div>
                                            <div class="news-date">
                                                <div class="body-small">{{ $featuredPost->post->created_at->format('M d, Y') }}</div>
                                                <div class="black-line"></div>
                                                <div class="body-small">{{ $featuredPost->post->read_time }} Min</div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>
</section>
