<div>
    <label for="title" class="block font-medium mb-2">Title *</label>
    <x-text-input
        type="text"
        id="title"
        name="title"
        value="{{ $savedPost->title ?? '' }}"
        maxlength="255"
        placeholder="Enter post title..."
        required
    />
    <div class="flex flex-col">
        <p id="titleCounter" class="text-sm text-gray-500 mt-1">
            <span id="titleCount">{{ $savedPost ? strlen($savedPost->title ?? '') : 0 }}</span>/255 characters
        </p>
        <span class="text-red-600 text-sm block mt-1 error-message" data-field="title"></span>
    </div>
</div>
