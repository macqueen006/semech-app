<div>
    <label for="category_id" class="block font-medium mb-2">Category *</label>
    <select
        id="category_id"
        name="category_id"
        class="py-3 px-4 pe-9 block w-full bg-layer border-layer-line rounded-lg text-sm text-foreground focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none"
        required
    >
        <option value="">Select Category</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" {{ $savedPost && $savedPost->category_id == $category->id ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>

    <span class="text-red-600 text-sm error-message" data-field="category_id"></span>
</div>
