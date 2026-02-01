<div class="max-w-4xl mx-auto p-6">
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">You have saved drafts</h2>
        <p class="mb-4">Would you like to continue working on a saved draft or create a new post?</p>

        <div class="space-y-2 mb-4">
            @foreach($savedPosts as $saved)
                <div class="bg-white p-3 rounded border hover:border-blue-500 transition" id="draft-{{ $saved->id }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-medium">{{ $saved->title ?: 'Untitled' }}</h3>
                            <p class="text-sm text-gray-600">Last updated: {{ $saved->updated_at->diffForHumans() }}</p>
                            <a href="{{ route('admin.posts.create', ['edit' => $saved->id]) }}"
                               class="text-blue-600 hover:underline text-sm inline-block mt-2">
                                Continue editing →
                            </a>
                        </div>
                        <button
                            type="button"
                            onclick="deleteDraft({{ $saved->id }})"
                            class="ml-4 text-red-600 hover:text-red-800 transition"
                            title="Delete draft">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.posts.create', ['new' => 1]) }}" class="btn btn-primary">
                Create New Post
            </a>
            <a href="{{ route('admin.posts-saved.index') }}" class="btn btn-secondary">
                View All Saved Posts
            </a>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function deleteDraft(draftId) {
            if (!confirm('Are you sure you want to delete this draft? This action cannot be undone.')) {
                return;
            }

            fetch(`/admin/posts/drafts/${draftId}`, {
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
                        // Remove the draft card from the DOM
                        const draftElement = document.getElementById(`draft-${draftId}`);
                        if (draftElement) {
                            draftElement.style.opacity = '0';
                            draftElement.style.transform = 'scale(0.95)';
                            draftElement.style.transition = 'all 0.3s ease';

                            setTimeout(() => {
                                draftElement.remove();

                                // Check if there are any drafts left
                                const remainingDrafts = document.querySelectorAll('[id^="draft-"]');
                                if (remainingDrafts.length === 0) {
                                    // Redirect to create page if no drafts left
                                    window.location.href = '{{ route("admin.posts.create", ["new" => 1]) }}';
                                }
                            }, 300);
                        }

                        // Show success message
                        showFlashMessage(data.message, 'success');
                    } else {
                        showFlashMessage(data.message || 'Failed to delete draft', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showFlashMessage('An error occurred while deleting the draft', 'error');
                });
        }

        function showFlashMessage(message, type) {
            const flashContainer = document.createElement('div');
            flashContainer.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${
                type === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'
            }`;
            flashContainer.innerHTML = `
        <div class="flex items-center gap-2">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-gray-500 hover:text-gray-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    `;
            document.body.appendChild(flashContainer);

            setTimeout(() => {
                flashContainer.style.opacity = '0';
                flashContainer.style.transition = 'opacity 0.3s ease';
                setTimeout(() => flashContainer.remove(), 300);
            }, 3000);
        }
    </script>
@endpush
