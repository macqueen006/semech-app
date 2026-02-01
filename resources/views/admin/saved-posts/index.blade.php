<x-app-layout>
    <div class="p-4">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Saved Posts</h1>

            <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
                Create New Post
            </a>
        </div>

        <div id="flashMessage" class="hidden mb-4"></div>

        @if($posts->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No saved posts</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new post.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="savedPostsGrid">
                @foreach($posts as $post)
                    <div id="saved-post-{{ $post->id }}" class="bg-white rounded-lg shadow overflow-hidden">
                        @if($post->image_path)
                            <img src="{{ $post->image_path }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-400">No image</span>
                            </div>
                        @endif

                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2 line-clamp-2">{{ $post->title }}</h3>

                            @if($post->excerpt)
                                <p class="text-gray-600 text-sm mb-3 line-clamp-3">{{ $post->excerpt }}</p>
                            @endif

                            <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                                <span>{{ $post->read_time }} min read</span>
                                <span>{{ $post->created_at->diffForHumans() }}</span>
                            </div>

                            @if($post->category_id && $post->category)
                                <div class="mb-4">
                                    <span class="inline-block px-2 py-1 text-xs rounded"
                                          style="background-color: {{ $post->category->backgroundColor }}; color: {{ $post->category->textColor }}">
                                        {{ $post->category->name }}
                                    </span>
                                </div>
                            @endif

                            @if($post->focus_keyword)
                                <div class="mb-2">
                                    <span class="inline-block px-2 py-1 text-xs rounded bg-purple-100 text-purple-700">
                                        <i class="fa-solid fa-bullseye"></i>
                                        {{ $post->focus_keyword }}
                                    </span>
                                </div>
                            @endif

                            @if($post->meta_description)
                                <p class="text-xs text-gray-500 mb-2 line-clamp-2">
                                    <i class="fa-solid fa-tags"></i> {{ $post->meta_description }}
                                </p>
                            @endif

                            @if($post->scheduled_at)
                                <div class="mb-2">
                                    <span class="inline-block px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                        <i class="fa-solid fa-clock"></i>
                                        Scheduled: {{ \Carbon\Carbon::parse($post->scheduled_at)->format('M d, H:i') }}
                                    </span>
                                </div>
                            @endif

                            @if($post->expires_at)
                                <div class="mb-2">
                                    <span class="inline-block px-2 py-1 text-xs rounded bg-orange-100 text-orange-700">
                                        <i class="fa-solid fa-hourglass-end"></i>
                                        Expires: {{ \Carbon\Carbon::parse($post->expires_at)->format('M d, H:i') }}
                                    </span>
                                </div>
                            @endif

                            <div class="flex items-center gap-2">
                                <span class="text-xs {{ $post->is_published ? 'text-green-600' : 'text-yellow-600' }}">
                                    {{ $post->is_published ? '● Published' : '● Draft' }}
                                </span>
                            </div>

                            <div class="flex gap-2 mt-4">
                                <a href="{{ route('admin.posts.create', ['edit' => $post->id]) }}"
                                   class="flex-1 btn btn-sm btn-primary">
                                    Edit
                                </a>
                                <button
                                    onclick="deleteSavedPost({{ $post->id }})"
                                    class="btn btn-sm btn-danger">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function deleteSavedPost(postId) {
                if (!confirm('Are you sure you want to delete this saved post?')) {
                    return;
                }

                fetch(`/admin/posts-saved/${postId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the card with animation
                            const postElement = document.getElementById(`saved-post-${postId}`);
                            if (postElement) {
                                postElement.style.opacity = '0';
                                postElement.style.transform = 'scale(0.95)';
                                postElement.style.transition = 'all 0.3s ease';

                                setTimeout(() => {
                                    postElement.remove();

                                    // Check if there are any posts left
                                    const grid = document.getElementById('savedPostsGrid');
                                    const remainingPosts = grid?.querySelectorAll('[id^="saved-post-"]');

                                    if (!remainingPosts || remainingPosts.length === 0) {
                                        // Show empty state
                                        location.reload();
                                    }
                                }, 300);
                            }

                            // Show success message
                            showFlashMessage(data.message, 'success');
                        } else {
                            showFlashMessage(data.message || 'Failed to delete saved post', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showFlashMessage('An error occurred while deleting the saved post', 'error');
                    });
            }

            function showFlashMessage(message, type) {
                const flashContainer = document.getElementById('flashMessage');
                flashContainer.className = `p-4 rounded-lg mb-4 ${
                    type === 'success'
                        ? 'bg-green-50 border border-green-200 text-green-700'
                        : 'bg-red-50 border border-red-200 text-red-700'
                }`;
                flashContainer.innerHTML = `
                <div class="flex items-center justify-between">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            `;
                flashContainer.classList.remove('hidden');

                // Auto-hide after 3 seconds
                setTimeout(() => {
                    flashContainer.classList.add('hidden');
                }, 3000);
            }
        </script>
    @endpush
</x-app-layout>
