<div>
    <label for="excerpt" class="block font-medium mb-2">Excerpt *</label>
    <textarea
        id="excerpt"
        name="excerpt"
        rows="3"
        maxlength="510"
        class="py-2 px-3 sm:py-3 sm:px-4 block w-full bg-layer border-layer-line rounded-lg sm:text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-none [&::-webkit-scrollbar-track]:bg-scrollbar-track [&::-webkit-scrollbar-thumb]:bg-scrollbar-thumb"
        placeholder="Brief summary of the post (max 510 characters)..."
        data-hs-textarea-auto-height
        required
    >{{ $savedPost->excerpt ?? '' }}</textarea>
    <p class="text-sm text-gray-500 mt-1">
        <span id="excerptCount">{{ $savedPost ? strlen($savedPost->excerpt ?? '') : 0 }}</span>/510 characters
    </p>
    <span class="text-red-600 text-sm error-message" data-field="excerpt"></span>
</div>
