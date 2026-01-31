<div>
    <label class="block font-medium mb-2">Content *</label>
    <div id="editor" style="min-height: 300px;" class="[&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-none [&::-webkit-scrollbar-track]:bg-scrollbar-track [&::-webkit-scrollbar-thumb]:bg-scrollbar-thumb [&_.ql-editor]:min-h-[300px] [&_.ql-editor::-webkit-scrollbar]:w-2 [&_.ql-editor::-webkit-scrollbar-thumb]:rounded-none [&_.ql-editor::-webkit-scrollbar-track]:bg-scrollbar-track [&_.ql-editor::-webkit-scrollbar-thumb]:bg-scrollbar-thumb"></div>
    <input type="hidden" id="body" name="body" value="{{ $savedPost->body ?? '' }}">
    <input type="hidden" id="readTime" name="read_time" value="{{ $savedPost->read_time ?? 0 }}">

    <!-- Word Count and Read Time Display -->
    <div class="flex items-center gap-4 mt-2 text-sm text-gray-600">
        <div class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span id="wordCount">0</span>
            <span id="wordLabel">words</span>
        </div>

        <div class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span id="readTimeCount">0</span>
            <span id="readTimeLabel">minutes</span>
            <span class="text-gray-400">read</span>
        </div>
    </div>

    <span class="text-red-600 text-sm error-message" data-field="body"></span>
</div>
