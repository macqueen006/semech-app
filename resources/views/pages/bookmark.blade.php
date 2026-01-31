<x-guest-layout>
    @section('meta')
        <title>Bookmarks</title>
    @endsection
    <div>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <h1 class="text-4xl font-bold text-gray-900">My Bookmarks</h1>
                </div>
                <p class="text-gray-600">Articles you've saved for later reading</p>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 p-4 rounded-lg">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-green-800 font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if($bookmarks->isEmpty())
                <!-- Empty State -->
                <div class="text-center py-16 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                    <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No bookmarks yet</h3>
                    <p class="text-gray-500 mb-6">Start saving articles you want to read later!</p>
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        Browse Articles
                    </a>
                </div>
            @else
                <!-- Bookmarks Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($bookmarks as $bookmark)
                        @php $post = $bookmark->post; @endphp

                        <article
                            class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow group relative">
                            <!-- Remove Bookmark Button (Top Right) -->
                            <div class="absolute top-2 right-2 z-10">
                                <x-bookmark-button :post="$post" />
                            </div>

                            @if($post->image_path)
                                <a href="{{ route('post.show', $post->slug) }}" class="block overflow-hidden">
                                    <img src="{{ $post->image_path }}"
                                         alt="{{ $post->title }}"
                                         class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                                </a>
                            @endif

                            <div class="p-6">
                                @if($post->category)
                                    <a href="{{ route('category.show', $post->category->slug) }}"
                                       class="inline-block px-2 py-1 text-xs rounded hover:opacity-80 transition-opacity mb-3"
                                       style="background-color: {{ $post->category->backgroundColor }};
                                          color: {{ $post->category->textColor }}">
                                        {{ $post->category->name }}
                                    </a>
                                @endif

                                <h3 class="text-lg font-bold mb-2 line-clamp-2">
                                    <a href="{{ route('post.show', $post->slug) }}"
                                       class="hover:text-blue-600 transition-colors">
                                        {{ $post->title }}
                                    </a>
                                </h3>

                                @if($post->excerpt)
                                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $post->excerpt }}</p>
                                @endif

                                <div class="flex items-center justify-between text-xs text-gray-500 border-t pt-4">
                                    <div class="flex items-center gap-2">
                                        @if($post->user->image_path)
                                            <img src="{{ $post->user->image_path }}"
                                                 alt="{{ $post->user->firstname }}"
                                                 class="w-6 h-6 rounded-full">
                                        @endif
                                        <span>{{ $post->user->firstname }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span>{{ $post->read_time }} min</span>
                                        <div class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span>{{ number_format($post->view_count) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-xs text-gray-400 mt-2">
                                    Saved {{ $bookmark->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $bookmarks->links() }}
                </div>
            @endif
        </div>
    </div>
</x-guest-layout>
