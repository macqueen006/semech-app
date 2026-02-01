<x-guest-layout>
    @section('meta')
        @php
            $ogImage = $post->og_image ?: $post->image_path;
            $twitterImage = $post->twitter_image ?: $post->og_image ?: $post->image_path;
            $ogImageUrl = $ogImage ? (str_starts_with($ogImage, 'http') ? $ogImage : url($ogImage)) : url('/images/default-og-image.jpg');
            $twitterImageUrl = $twitterImage ? (str_starts_with($twitterImage, 'http') ? $twitterImage : url($twitterImage)) : url('/images/default-og-image.jpg');
            $description = strip_tags($post->meta_description ?: $post->excerpt);
            $ogDescription = strip_tags($post->og_description ?: $post->meta_description ?: $post->excerpt);
            $twitterDescription = strip_tags($post->twitter_description ?: $post->og_description ?: $post->meta_description ?: $post->excerpt);
        @endphp

        <title>{{ $post->meta_title ?: $post->title }} - {{ config('app.name') }}</title>
        <meta name="description" content="{{ $description }}">
        @if($post->focus_keyword)
            <meta name="keywords" content="{{ $post->focus_keyword }}">
        @endif

        <meta property="og:type" content="article">
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:title" content="{{ $post->og_title ?: $post->meta_title ?: $post->title }}">
        <meta property="og:description" content="{{ $ogDescription }}">
        <meta property="og:image" content="{{ $ogImageUrl }}">
        <meta property="og:image:secure_url" content="{{ $ogImageUrl }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="article:published_time" content="{{ $post->created_at->toIso8601String() }}">
        <meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}">
        <meta property="article:author" content="{{ $post->user->firstname }} {{ $post->user->lastname }}">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title"
              content="{{ $post->twitter_title ?: $post->og_title ?: $post->meta_title ?: $post->title }}">
        <meta name="twitter:description" content="{{ $twitterDescription }}">
        <meta name="twitter:image" content="{{ $twitterImageUrl }}">
        <meta name="twitter:url" content="{{ url()->current() }}">

        <link rel="canonical" href="{{ url()->current() }}">
    @endsection

    <div>
        <!-- Reading Progress Bar -->
        <div id="reading-progress" class="fixed top-0 left-0 right-0 z-50">
            <div class="h-1 bg-gray-200">
                <div id="progress-bar" class="h-full bg-secondary transition-all duration-150 ease-out"
                     style="width: 0%"></div>
            </div>
        </div>

        <!-- Post Header -->
        <section class="block pt-[60px] pb-[40px]">
            <div class="hero-container">
                <div class="news-inner flex flex-col mx-auto max-w-[900px] gap-[25px]">
                    @if(!$post->is_published)
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                            <p class="font-bold">Draft Preview</p>
                            <p>This post is not published yet.</p>
                        </div>
                    @endif

                    <div class="news-info flex flex-col gap-[20px]">
                        <div class="hero-title flex flex-col gap-1 items-start justify-start">
                            @if($post->category)
                                <a href="{{ route('category.show', $post->category->slug) }}"
                                   class="subtitle hover:opacity-80 transition-opacity inline-block px-3 py-1 rounded-sm"
                                   style="background-color: {{ $post->category->backgroundColor }}; color: {{ $post->category->textColor }}">
                                    {{ $post->category->name }}
                                </a>
                            @endif
                            <h1 class="news-heading font-light leading-[120%] text-[30px] xs:text-[32px] xlg:text-[38px]">
                                {{ $post->title }}
                            </h1>
                        </div>
                        <div class="news-date">
                            <div
                                class="subtitle font-medium">{{ $post->user->firstname }} {{ $post->user->lastname }}</div>
                            <div class="black-line"></div>
                            <div class="subtitle">Publisher</div>
                        </div>
                    </div>

                    <div class="latest-divider w-full h-[1px] bg-[rgba(0,0,0,.2)]"></div>

                    <div class="data-block flex justify-start items-center gap-4">
                        <div class="subtitle">{{ $post->updated_at->format('M d, Y g:i A') }}</div>
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

                    @if($post->image_path)
                        <div class="news-main aspect-[3/1.8] relative overflow-hidden rounded-[6px] bg-background">
                            <img src="{{ asset($post->image_path) }}"
                                 loading="eager"
                                 alt="{{ $post->image_alt ? $post->image_alt : $post->title }}"
                                 class="cover-image"/>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <!-- Social Share Buttons -->
        <div id="social-share" class="z-40 max-w-[900px] mx-auto px-4 mb-8">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                <div class="flex flex-col xs:flex-row items-start xs:items-center xs:justify-between justify-center">
                    <span class="text-sm font-normal text-start text-gray-700">Share this post:</span>

                    <div class="flex flex-col xs:flex-row xs:items-center items-start gap-2">
                        <div>
                            <button data-share="twitter" class="p-2 hover:bg-blue-50 rounded-lg transition-colors group"
                                    title="Share on Twitter">
                                <svg class="w-5 h-5 text-gray-600 group-hover:text-blue-400" fill="currentColor"
                                     viewBox="0 0 24 24">
                                    <path
                                        d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </button>

                            <button data-share="facebook"
                                    class="p-2 hover:bg-blue-50 rounded-lg transition-colors group"
                                    title="Share on Facebook">
                                <svg class="w-5 h-5 text-gray-600 group-hover:text-blue-600" fill="currentColor"
                                     viewBox="0 0 24 24">
                                    <path
                                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </button>

                            <button data-share="linkedin"
                                    class="p-2 hover:bg-blue-50 rounded-lg transition-colors group"
                                    title="Share on LinkedIn">
                                <svg class="w-5 h-5 text-gray-600 group-hover:text-blue-700" fill="currentColor"
                                     viewBox="0 0 24 24">
                                    <path
                                        d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </button>

                            <button data-share="whatsapp"
                                    class="p-2 hover:bg-green-50 rounded-lg transition-colors group"
                                    title="Share on WhatsApp">
                                <svg class="w-5 h-5 text-gray-600 group-hover:text-green-500" fill="currentColor"
                                     viewBox="0 0 24 24">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                            </button>

                            <button data-share="email" class="p-2 hover:bg-gray-100 rounded-lg transition-colors group"
                                    title="Share via Email">
                                <svg class="w-5 h-5 text-gray-600 group-hover:text-gray-800" fill="none"
                                     stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center">
                            <button id="copy-link"
                                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors group relative"
                                    title="Copy Link">
                                <svg id="copy-icon" class="w-5 h-5 text-gray-600 group-hover:text-gray-800" fill="none"
                                     stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <svg id="copied-icon" class="w-5 h-5 text-green-600 hidden" fill="none"
                                     stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>

                            <div class="border-l border-gray-200 pl-2 ml-2">
                                @auth
                                   <x-bookmark-button :post="$post" />
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>

                <div id="copy-message" class="mt-2 text-xs text-green-600 text-right hidden">
                    Link copied to clipboard!
                </div>
            </div>
        </div>
        <!-- Post Content -->
        <section class="section to-top">
            <div class="hero-container">
                <div class="flex flex-col max-w-[900px] w-full gap-[25px] mx-auto">
                    <article class="w-full -mb-[20px] prose prose-lg max-w-none">
                        {!! $post->body !!}
                    </article>
                </div>
            </div>
        </section>

        <!-- Author Bio Section -->
        <section class="section">
            <div class="hero-container">
                <div class="max-w-[900px] mx-auto">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-8">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                @if($post->user->image_path)
                                    <img src="{{ $post->user->image_path }}"
                                         alt="{{ $post->user->firstname }} {{ $post->user->lastname }}"
                                         class="h-10 w-10 xs:w-20 xs:h-20 rounded-full object-cover">
                                @else
                                    <div
                                        class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white xs:text-2xl font-bold">
                                        {{ substr($post->user->firstname, 0, 1) }}{{ substr($post->user->lastname, 0, 1) }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <h3 class="text-sm xs:text-xl font-bold text-gray-900">
                                            {{ $post->user->firstname }} {{ $post->user->lastname }}
                                        </h3>
                                        <p class="text-sm text-gray-500">Author</p>
                                    </div>

                                    @if($post->user->website || $post->user->twitter || $post->user->linkedin || $post->user->github)
                                        <div class="flex items-center gap-2">
                                            @if($post->user->website)
                                                <a href="{{ $post->user->website }}" target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                   title="Website">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                                    </svg>
                                                </a>
                                            @endif

                                            @if($post->user->twitter)
                                                <a href="https://twitter.com/{{ $post->user->twitter }}" target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="p-2 text-gray-600 hover:text-blue-400 hover:bg-blue-50 rounded-lg transition-colors"
                                                   title="Twitter">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                                    </svg>
                                                </a>
                                            @endif

                                            @if($post->user->linkedin)
                                                <a href="https://linkedin.com/in/{{ $post->user->linkedin }}"
                                                   target="_blank" rel="noopener noreferrer"
                                                   class="p-2 text-gray-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors"
                                                   title="LinkedIn">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                                    </svg>
                                                </a>
                                            @endif

                                            @if($post->user->github)
                                                <a href="https://github.com/{{ $post->user->github }}" target="_blank"
                                                   rel="noopener noreferrer"
                                                   class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                                                   title="GitHub">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                @if($post->user->bio)
                                    <p class="text-gray-700 leading-relaxed">{{ $post->user->bio }}</p>
                                @else
                                    <p class="text-gray-500 italic">This author hasn't added a bio yet.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Post Footer Actions -->
                    <div class="flex items-center gap-4 justify-between mb-6 p-4 border-t">
                        <div class="text-sm text-gray-600">
                            Last updated: {{ $post->updated_at->format('F d, Y') }}
                        </div>

                        <div class="flex items-center gap-3">
                            <button onclick="window.print()" class="print-button flex gap-2 items-center text-sm"
                                    title="Print this article">
                                <svg fill="none" class="h-8 w-8" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Print Article
                            </button>

                            @if(auth()->check() && (auth()->id() === $post->user_id || auth()->user()->can('post-edit')))
                                <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-sm">
                                    Edit Post
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Newsletter CTA -->
        <x-sections.newsletter-banner/>

        <!-- Related Posts -->
        @if($relatedPosts->isNotEmpty())
            <section class="title-section pb-[25px]">
                <div class="hero-container">
                    <div class="title-wrap">
                        <h2 class="main-title uppercase">RELATED ARTICLES</h2>
                    </div>
                </div>
            </section>

            <section class="section pt-0">
                <div class="hero-container">
                    <div>
                        <div role="list" class="news-wrap">
                            @foreach($relatedPosts as $relatedPost)
                                <div role="listitem">
                                    <a href="{{ route('post.show', $relatedPost->slug) }}" class="news-right">
                                        <div class="news-image">
                                            <img src="{{ $relatedPost->image_path }}"
                                                 loading="eager"
                                                 alt="{{ $relatedPost->image_alt ? $relatedPost->image_alt : $relatedPost->title }}"
                                                 class="cover-image"/>
                                        </div>
                                        <div class="new-info">
                                            <h5>{{ $relatedPost->title }}</h5>
                                            <div class="news-date">
                                                <div
                                                    class="home-subtitle">{{ $relatedPost->user->firstname }} {{ $relatedPost->user->lastname }}</div>
                                                <div class="black-line"></div>
                                                <div
                                                    class="home-subtitle">{{ $relatedPost->created_at->format('M d, Y') }}</div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const postUrl = '{{ route('post.show', $post->slug) }}';
                const postTitle = {{ Js::from($post->title) }};

                // Reading Progress Bar
                function updateProgress() {
                    const article = document.querySelector('article');
                    if (!article) return;

                    const articleTop = article.offsetTop;
                    const articleHeight = article.offsetHeight;
                    const windowHeight = window.innerHeight;
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                    const scrollStart = articleTop;
                    const scrollEnd = articleTop + articleHeight - windowHeight;
                    const scrollDistance = scrollEnd - scrollStart;

                    let scrollProgress = 0;
                    if (scrollTop < scrollStart) {
                        scrollProgress = 0;
                    } else if (scrollTop > scrollEnd) {
                        scrollProgress = 100;
                    } else {
                        scrollProgress = ((scrollTop - scrollStart) / scrollDistance) * 100;
                    }

                    document.getElementById('progress-bar').style.width = scrollProgress + '%';
                }

                window.addEventListener('scroll', updateProgress);
                window.addEventListener('resize', updateProgress);
                updateProgress();

                // Social Share Functions
                const shareButtons = document.querySelectorAll('[data-share]');
                shareButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        const platform = this.dataset.share;
                        let url = '';

                        switch (platform) {
                            case 'twitter':
                                url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(postTitle)}&url=${encodeURIComponent(postUrl)}`;
                                break;
                            case 'facebook':
                                url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(postUrl)}`;
                                break;
                            case 'linkedin':
                                url = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(postUrl)}`;
                                break;
                            case 'whatsapp':
                                url = `https://wa.me/?text=${encodeURIComponent(postTitle + ' ' + postUrl)}`;
                                window.open(url, '_blank');
                                return;
                            case 'email':
                                const subject = encodeURIComponent(postTitle);
                                const body = encodeURIComponent(`Check out this article: ${postUrl}`);
                                window.location.href = `mailto:?subject=${subject}&body=${body}`;
                                return;
                        }

                        if (url) {
                            window.open(url, '_blank', 'width=550,height=420');
                        }
                    });
                });

                // Copy Link
                const copyBtn = document.getElementById('copy-link');
                const copyIcon = document.getElementById('copy-icon');
                const copiedIcon = document.getElementById('copied-icon');
                const copyMessage = document.getElementById('copy-message');

                copyBtn.addEventListener('click', async function () {
                    try {
                        await navigator.clipboard.writeText(postUrl);
                        copyIcon.classList.add('hidden');
                        copiedIcon.classList.remove('hidden');
                        copyMessage.classList.remove('hidden');

                        setTimeout(() => {
                            copyIcon.classList.remove('hidden');
                            copiedIcon.classList.add('hidden');
                            copyMessage.classList.add('hidden');
                        }, 2000);
                    } catch (err) {
                        console.error('Failed to copy:', err);
                    }
                });
            });
        </script>
    @endpush
</x-guest-layout>
