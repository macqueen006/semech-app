<div class="max-w-4xl mx-auto p-6">
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">You have saved drafts</h2>
        <p class="mb-4">Would you like to continue working on a saved draft or create a new post?</p>

        <div class="space-y-2 mb-4">
            @foreach($savedPosts as $saved)
                <div class="bg-white p-3 rounded border hover:border-blue-500 transition">
                    <h3 class="font-medium">{{ $saved->title ?: 'Untitled' }}</h3>
                    <p class="text-sm text-gray-600">Last updated: {{ $saved->updated_at->diffForHumans() }}</p>
                    <a href="{{ route('admin.posts.create', ['edit' => $saved->id]) }}"
                       class="text-blue-600 hover:underline text-sm inline-block mt-2">
                        Continue editing →
                    </a>
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
