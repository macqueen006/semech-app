<div id="editorImageModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Insert Image</h3>
                <button type="button" id="closeEditorModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="flex gap-2 mb-4">
                <button type="button" id="editorUploadModeBtn" class="px-4 py-2 rounded bg-blue-500 text-white">
                    📤 Upload File
                </button>
                <button type="button" id="editorUrlModeBtn" class="px-4 py-2 rounded bg-gray-200 text-gray-700">
                    🔗 Use URL
                </button>
                <button type="button" id="editorBrowseModeBtn" class="px-4 py-2 rounded bg-gray-200 text-gray-700">
                    🖼️ Browse Storage
                </button>
            </div>

            <!-- Upload Mode -->
            <div id="editorUploadMode" class="space-y-3">
                <div id="editorFileInputContainer">
                    <input
                        type="file"
                        id="editorImageFile"
                        accept="image/*"
                        class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100"
                    >
                </div>

                <div id="editorFileUploadProgress" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <!-- Progress will be inserted here -->
                </div>

                <p id="editorUploadHint" class="text-xs text-gray-500">
                    Supported formats: JPG, PNG, GIF, WebP (Max 5MB)
                </p>
            </div>

            <!-- URL Mode -->
            <div id="editorUrlMode" class="hidden space-y-3">
                <div>
                    <input
                        type="text"
                        id="editorImageUrl"
                        class="form-input w-full rounded-lg px-4 py-2"
                        placeholder="Enter image URL (e.g., /images/posts/example.jpg or https://...)"
                    >
                    <p class="text-xs text-gray-500 mt-2">
                        You can use a full URL or a path to an existing image in your storage
                    </p>
                </div>

                <div id="editorUrlPreview" class="hidden">
                    <p class="text-sm text-gray-600 mb-2">Preview:</p>
                    <img id="editorUrlPreviewImg" src=""
                         class="max-w-full h-64 object-contain rounded border mx-auto"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div class="text-center text-red-500 text-sm mt-2" style="display:none;">
                        Unable to load image from this URL
                    </div>
                </div>

                <div class="flex gap-2 justify-end mt-4">
                    <button type="button" id="cancelEditorUrl" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="button" id="insertEditorUrl" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed">
                        Insert Image
                    </button>
                </div>
            </div>

            <!-- Browse Mode -->
            <div id="editorBrowseMode" class="hidden space-y-3">
                <div id="editorBrowseLoading" class="flex justify-center items-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="ml-2 text-gray-600">Loading images...</span>
                </div>

                <div id="editorBrowseGrid" class="hidden grid grid-cols-2 md:grid-cols-3 gap-3 max-h-96 overflow-y-auto">
                    <!-- Images will be inserted here -->
                </div>

                <div id="editorBrowseEmpty" class="hidden col-span-full text-center py-8 text-gray-500">
                    No images found in storage.
                </div>
            </div>
        </div>
    </div>
</div>
