@props(['post', 'showText' => false])

@php
    $bookmarked = false;
    if (auth()->check()) {
        $bookmarked = auth()->user()
            ->bookmarks()
            ->where('post_id', $post->id)
            ->exists();
    }
@endphp

<div>
    @auth
        <button
            onclick="toggleBookmark({{ $post->id }}, this)"
            data-bookmarked="{{ $bookmarked ? 'true' : 'false' }}"
            class="group relative inline-flex items-center gap-2 px-4 py-2 rounded-lg transition-all duration-200 {{ $bookmarked ? 'bg-blue-50 text-blue-600 hover:bg-blue-100' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
            title="{{ $bookmarked ? 'Remove from bookmarks' : 'Add to bookmarks' }}"
        >
            <!-- Bookmark Icon -->
            <svg
                class="bookmark-icon w-5 h-5 transition-all duration-200"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
                    fill="{{ $bookmarked ? 'currentColor' : 'none' }}"
                />
            </svg>
            @if($showText)
                <span class="bookmark-text font-medium text-sm">{{ $bookmarked ? 'Saved' : 'Save' }}</span>
            @endif
            <!-- Loading Spinner -->
            <svg
                class="loading-spinner absolute inset-0 m-auto w-5 h-5 animate-spin text-current hidden"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </button>
    @else
        <a
            href="{{ route('login') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-lg transition-colors"
            title="Sign in to save this post"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
            </svg>
            @if($showText)
                <span class="font-medium text-sm">Save</span>
            @endif
        </a>
    @endauth
</div>

@push('scripts')
    <script>
        function toggleBookmark(postId, button) {
            const isBookmarked = button.dataset.bookmarked === 'true';
            const icon = button.querySelector('.bookmark-icon path');
            const text = button.querySelector('.bookmark-text');
            const spinner = button.querySelector('.loading-spinner');

            // Show loading state
            button.disabled = true;
            spinner.classList.remove('hidden');

            fetch('/bookmarks/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({post_id: postId})
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update button state
                        button.dataset.bookmarked = data.bookmarked ? 'true' : 'false';

                        // Update styles
                        if (data.bookmarked) {
                            button.className = 'group relative inline-flex items-center gap-2 px-4 py-2 rounded-lg transition-all duration-200 bg-blue-50 text-blue-600 hover:bg-blue-100';
                            icon.setAttribute('fill', 'currentColor');
                            if (text) text.textContent = 'Saved';
                            button.title = 'Remove from bookmarks';
                        } else {
                            button.className = 'group relative inline-flex items-center gap-2 px-4 py-2 rounded-lg transition-all duration-200 bg-gray-100 text-gray-600 hover:bg-gray-200';
                            icon.setAttribute('fill', 'none');
                            if (text) text.textContent = 'Save';
                            button.title = 'Add to bookmarks';
                        }

                        // Show success message
                        showBookmarkMessage(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                })
                .finally(() => {
                    // Hide loading state
                    button.disabled = false;
                    spinner.classList.add('hidden');
                });
        }

        function showBookmarkMessage(message) {
            // Create toast notification
            const toast = document.createElement('div');
            toast.className = 'fixed top-4 right-4 bg-green-50 border border-green-200 p-4 rounded-lg shadow-lg z-50 transition-opacity duration-300';
            toast.innerHTML = `
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-green-800 font-medium">${message}</span>
        </div>
    `;

            document.body.appendChild(toast);

            // Remove after 3 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
@endpush
