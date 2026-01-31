<x-app-layout>
        <div class="p-4" id="postCreateApp">
            @if($showSavedPosts === true)
                @include('admin.posts.partials.saved-posts-prompt', ['savedPosts' => $savedPosts])
            @elseif($showSavedPosts === false)
                @include('admin.posts.partials.create-header', ['savedPost' => $savedPost])

                @if(session('success'))
                    <div class="max-w-4xl mb-4 p-3 bg-green-50 border border-green-200 rounded text-green-700 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="max-w-4xl mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <div id="flashMessage" class="hidden max-w-4xl mb-4"></div>

                <form id="postForm" class="max-w-4xl">
                    @csrf
                    <input type="hidden" id="savedPostId" name="saved_post_id" value="{{ $savedPost->id ?? '' }}">

                    <div class="bg-white rounded-lg shadow p-6 space-y-6">
                        @include('admin.posts.partials.form-title', ['savedPost' => $savedPost])
                        @include('admin.posts.partials.form-excerpt', ['savedPost' => $savedPost])
                        @include('admin.posts.partials.form-body', ['savedPost' => $savedPost])
                        @include('admin.posts.partials.form-featured-image', ['savedPost' => $savedPost])
                        @include('admin.posts.partials.form-category', ['savedPost' => $savedPost, 'categories' => $categories])
                        @include('admin.posts.partials.form-seo', ['savedPost' => $savedPost])
                        @include('admin.posts.partials.form-scheduling', ['savedPost' => $savedPost])
                        @include('admin.posts.partials.form-actions')
                    </div>
                </form>
            @endif
        </div>

        @push('styles')
            <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet"/>
        @endpush

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
            @include('admin.posts.scripts.create-post-script')
        @endpush
</x-app-layout>
