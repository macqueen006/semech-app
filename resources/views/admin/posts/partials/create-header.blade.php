<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold">
            @if($savedPost)
                Edit Draft
            @else
                Create Post
            @endif
        </h1>
        @if($savedPost)
            <p class="text-sm text-gray-600 mt-1">Editing saved draft #{{ $savedPost->id }}</p>
        @endif
    </div>

    <div class="flex items-center gap-2">
        <span id="autoSaveMessage" class="text-sm text-green-600"></span>
        <a href="{{ route('admin.posts.index') }}" class="btn">
            Cancel
        </a>
    </div>
</div>
