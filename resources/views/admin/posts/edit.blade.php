<x-app-layout>
    <div class="p-4">
        <div class="max-w-4xl mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Edit Post</h1>
            <a href="{{ route('admin.posts.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← Back to Posts</a>
        </div>

        <div id="flashMessage" class="hidden max-w-4xl mb-4"></div>
        <!-- Auto-save Warning banner -->
        @include('admin.posts.partials.autosave-warning', ['post' => $post])

        <form id="postForm" class="max-w-4xl">
            @csrf

            <div class="bg-white rounded-lg shadow p-6 space-y-6">
                @include('admin.posts.partials.form-title', ['savedPost' => $post])
                @include('admin.posts.partials.form-excerpt', ['savedPost' => $post])
                @include('admin.posts.partials.form-body', ['savedPost' => $post])
                @include('admin.posts.partials.form-featured-image', ['savedPost' => $post])
                @include('admin.posts.partials.form-category', ['savedPost' => $post, 'categories' => $categories])
                @include('admin.posts.partials.form-seo', ['savedPost' => $post])
                @include('admin.posts.partials.form-scheduling', ['savedPost' => $post])

                <div class="flex justify-between gap-3 pt-4 border-t">
                    <div class="flex gap-2">
                        <a href="{{ route('post.show', $post->slug ?? $post->id) }}"
                           target="_blank"
                           class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                            👁 Preview Post
                        </a>
                        <a href="{{ route('admin.posts.history.index', $post->id) }}"
                           class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                            📜 View History
                        </a>
                    </div>

                    <div class="flex gap-2">
                        <button type="button" id="saveDraftBtn"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                            💾 Save Draft
                        </button>
                        <button type="submit" id="updateBtn"
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-primary-hover">
                            🔄 Update Post
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet"/>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
        @include('admin.posts.scripts.edit-post-script', ['postId' => $post->id])
    @endpush
</x-app-layout>
