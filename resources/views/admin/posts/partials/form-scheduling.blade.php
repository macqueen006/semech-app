<div class="border-t pt-6 space-y-4">
    <h3 class="text-lg font-semibold">Publishing Schedule</h3>

    <!-- Scheduled Publishing -->
    <div class="space-y-3">
        <label class="flex items-center gap-2">
            <input
                type="checkbox"
                id="useScheduling"
                name="use_scheduling"
                value="1"
                class="form-checkbox rounded"
                {{ $savedPost && $savedPost->scheduled_at ? 'checked' : '' }}
            >
            <span class="font-medium">Schedule for later</span>
        </label>

        <div id="schedulingInputs" class="{{ $savedPost && $savedPost->scheduled_at ? '' : 'hidden' }} ml-6 space-y-2">
            <label class="block text-sm font-medium text-gray-700">
                Publish Date & Time *
            </label>
            <input
                type="datetime-local"
                id="scheduledAt"
                name="scheduled_at"
                value="{{ $savedPost && $savedPost->scheduled_at ? $savedPost->scheduled_at->format('Y-m-d\TH:i') : '' }}"
                class="form-input w-full max-w-md rounded-lg px-4 py-2 border border-gray-200"
            >
            <span class="error-message text-red-600 text-sm" data-field="scheduled_at"></span>

            <p id="scheduledAtPreview" class="text-sm text-blue-600 hidden">
                <i class="fa-solid fa-clock"></i>
                <span></span>
            </p>
        </div>
    </div>

    <!-- Post Expiration -->
    <div class="space-y-3">
        <label class="flex items-center gap-2">
            <input
                type="checkbox"
                id="useExpiration"
                name="use_expiration"
                value="1"
                class="form-checkbox rounded"
                {{ $savedPost && $savedPost->expires_at ? 'checked' : '' }}
            >
            <span class="font-medium">Set expiration date</span>
        </label>

        <div id="expirationInputs" class="{{ $savedPost && $savedPost->expires_at ? '' : 'hidden' }} ml-6 space-y-2">
            <label class="block text-sm font-medium text-gray-700">
                Expiration Date & Time *
            </label>
            <input
                type="datetime-local"
                id="expiresAt"
                name="expires_at"
                value="{{ $savedPost && $savedPost->expires_at ? $savedPost->expires_at->format('Y-m-d\TH:i') : '' }}"
                class="form-input w-full max-w-md rounded-lg px-4 py-2 border border-gray-200"
            >
            <span class="error-message text-red-600 text-sm" data-field="expires_at"></span>

            <p id="expiresAtPreview" class="text-sm text-orange-600 hidden">
                <i class="fa-solid fa-hourglass-end"></i>
                <span></span>
            </p>
            <p class="text-xs text-gray-500">
                <i class="fa-solid fa-info-circle"></i>
                Post will automatically become unavailable after this date
            </p>
        </div>
    </div>

    <!-- Published Status -->
    <div>
        <label class="flex items-center gap-2">
            <input
                type="checkbox"
                id="is_published"
                name="is_published"
                value="1"
                class="form-checkbox rounded"
                {{ old('is_published', $savedPost->is_published ?? false) ? 'checked' : '' }}
            >
            <span id="publishedLabel">Published</span>
        </label>
        <p id="schedulingNote" class="text-xs text-gray-500 mt-1 ml-6 hidden">
            Posts are automatically published when scheduled
        </p>
    </div>
</div>
