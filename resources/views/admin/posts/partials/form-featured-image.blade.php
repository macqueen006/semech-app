<div class="border-2 border-dashed border-layer-line rounded-lg p-4 sm:p-6">
    <label class="block font-medium mb-4 text-sm text-foreground">Featured Image *</label>

    <input type="hidden" id="imagePath" name="image_path" value="{{ $savedPost->image_path ?? '' }}">

    <!-- Image Preview -->
    <div id="imagePreview" class="{{ $savedPost && $savedPost->image_path ? 'inline-block' : 'hidden' }} mb-4 relative w-full sm:w-auto">
        <img id="imagePreviewImg"
             src="{{ $savedPost->image_path ?? '' }}"
             alt="Featured image"
             class="w-full sm:max-w-md h-48 object-cover rounded-lg border border-layer-line">
        <button
            type="button"
            id="removeImageBtn"
            class="absolute top-2 right-2 size-8 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-full border border-transparent bg-red-500 text-white hover:bg-red-600 focus:outline-hidden focus:bg-red-600 disabled:opacity-50 disabled:pointer-events-none">
            <svg class="shrink-0 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Error/Success Messages -->
    <div id="imageError" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm"></div>
    <div id="imageSuccess" class="hidden mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm"></div>
    <span class="text-red-600 text-sm error-message" data-field="image_path"></span>

    <!-- Mode Selection Buttons -->
    <div class="inline-flex flex-wrap gap-2 mb-4">
        <button
            type="button"
            id="uploadModeBtn"
            class="py-2 sm:py-2.5 px-3 sm:px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-blue-100 border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus disabled:opacity-50 disabled:pointer-events-none">
            <svg class="shrink-0 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <span class="hidden sm:inline">Upload File</span>
            <span class="sm:hidden">Upload</span>
        </button>
        <button
            type="button"
            id="urlModeBtn"
            class="py-2 sm:py-2.5 px-3 sm:px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-layer-line text-muted-foreground-1 hover:border-primary-hover hover:text-primary-hover focus:outline-hidden focus:border-primary-focus focus:text-primary-focus disabled:opacity-50 disabled:pointer-events-none">
            <svg class="shrink-0 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
            <span class="hidden sm:inline">Use URL</span>
            <span class="sm:hidden">URL</span>
        </button>
        <button
            type="button"
            id="browseModeBtn"
            class="py-2 sm:py-2.5 px-3 sm:px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-layer-line text-muted-foreground-1 hover:border-primary-hover hover:text-primary-hover focus:outline-hidden focus:border-primary-focus focus:text-primary-focus disabled:opacity-50 disabled:pointer-events-none">
            <svg class="shrink-0 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="hidden sm:inline">Browse Storage</span>
            <span class="sm:hidden">Browse</span>
        </button>
    </div>

    <!-- Upload Mode -->
    <div id="uploadMode" class="space-y-3">
        <div id="fileInputContainer">
            <label for="imageFile" class="sr-only">Choose file</label>
            <input
                type="file"
                id="imageFile"
                accept="image/*"
                class="block w-full border border-layer-line shadow-sm rounded-lg text-sm focus:z-10 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none
                    file:bg-layer file:border-0
                    file:me-4
                    file:py-2.5 file:px-4
                    file:sm:py-3
                    file:text-foreground"
            >
        </div>

        <div id="fileUploadProgress" class="hidden bg-layer border border-layer-line rounded-lg p-4">
            <!-- File preview and progress bar will be inserted here -->
        </div>

        <p id="uploadHint" class="text-xs text-muted-foreground-1">
            Supported formats: JPG, PNG, GIF, WebP (Max 1MB)
        </p>
    </div>

    <!-- URL Mode -->
    <div id="urlMode" class="hidden space-y-3">
        <div class="flex flex-col sm:flex-row gap-2">
            <input
                type="text"
                id="imageUrl"
                placeholder="Enter image URL (e.g., /images/posts/example.jpg or https://...)"
                class="py-2.5 sm:py-3 px-4 rounded-lg block w-full bg-layer border-layer-line sm:text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none"
            >
            <button
                type="button"
                id="setImageUrlBtn"
                class="py-2.5 sm:py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus disabled:opacity-50 disabled:pointer-events-none whitespace-nowrap">
                Set Image
            </button>
        </div>
        <p class="text-xs text-muted-foreground-1">
            You can use a full URL or a path to an existing image in your storage
        </p>
    </div>
    @push('modal')
        <!-- Browse Images Modal (Featured Image) -->
        <div id="browseImagesModal" class="hs-overlay hidden size-full fixed top-0 start-0 z-[80] overflow-x-hidden overflow-y-auto pointer-events-none" role="dialog" tabindex="-1" aria-labelledby="browseImagesModalLabel">
            <div class="hs-overlay-animation-target hs-overlay-open:scale-100 hs-overlay-open:opacity-100 scale-95 opacity-0 ease-in-out transition-all duration-200 sm:max-w-4xl sm:w-full m-3 sm:mx-auto min-h-[calc(100%-56px)] flex items-center">
                <div class="w-full flex flex-col bg-primary border border-overlay-line shadow-2xs rounded-xl pointer-events-auto">
                    <div class="flex justify-between items-center py-3 px-4 border-b border-overlay-header">
                        <h3 id="browseImagesModalLabel" class="font-semibold text-foreground">
                            Browse Images
                        </h3>
                        <button type="button" class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full bg-surface border border-surface-line text-surface-foreground hover:bg-surface-hover focus:outline-hidden focus:bg-surface-focus disabled:opacity-50 disabled:pointer-events-none" aria-label="Close" data-hs-overlay="#browseImagesModal">
                            <span class="sr-only">Close</span>
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18"></path>
                                <path d="m6 6 12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="p-4 overflow-y-auto max-h-[60vh]">
                        <!-- Loading State -->
                        <div id="browseLoading" class="hidden text-center py-8">
                            <div class="inline-block size-8 animate-spin rounded-full border-4 border-solid border-current border-e-transparent align-[-0.125em] text-primary motion-reduce:animate-[spin_1.5s_linear_infinite]" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-3 text-sm text-muted-foreground-1">Loading images...</p>
                        </div>

                        <!-- Empty State -->
                        <div id="browseEmpty" class="hidden text-center py-8">
                            <svg class="mx-auto size-16 text-muted-foreground-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="mt-3 text-sm text-muted-foreground-1">No images found in storage</p>
                        </div>

                        <!-- Images Grid -->
                        <div id="browseGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            <!-- Images will be inserted here -->
                        </div>
                    </div>
                    <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t border-overlay-footer">
                        <button type="button" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-layer border border-layer-line text-layer-foreground shadow-2xs hover:bg-layer-hover focus:outline-hidden focus:bg-layer-focus disabled:opacity-50 disabled:pointer-events-none" data-hs-overlay="#browseImagesModal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Editor Image Modal -->
        <div id="editorImageModal" class="hs-overlay hidden size-full fixed top-0 start-0 z-[80] overflow-x-hidden overflow-y-auto pointer-events-none" role="dialog" tabindex="-1" aria-labelledby="editorImageModalLabel">
            <div class="hs-overlay-animation-target hs-overlay-open:scale-100 hs-overlay-open:opacity-100 scale-95 opacity-0 ease-in-out transition-all duration-200 sm:max-w-3xl sm:w-full m-3 sm:mx-auto min-h-[calc(100%-56px)] flex items-center">
                <div class="w-full flex flex-col bg-primary border border-overlay-line shadow-2xs rounded-xl pointer-events-auto">
                    <div class="flex justify-between items-center py-3 px-4 border-b border-overlay-header">
                        <h3 id="editorImageModalLabel" class="font-semibold text-foreground">
                            Insert Image
                        </h3>
                        <button type="button" id="closeEditorModal" class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full bg-surface border border-surface-line text-surface-foreground hover:bg-surface-hover focus:outline-hidden focus:bg-surface-focus disabled:opacity-50 disabled:pointer-events-none" aria-label="Close" data-hs-overlay="#editorImageModal">
                            <span class="sr-only">Close</span>
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18"></path>
                                <path d="m6 6 12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="p-4 overflow-y-auto max-h-[70vh]">
                        <!-- Mode Selection Buttons -->
                        <div class="inline-flex flex-wrap gap-2 mb-4">
                            <button type="button" id="editorUploadModeBtn" class="py-2 sm:py-2.5 px-3 sm:px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus disabled:opacity-50 disabled:pointer-events-none">
                                <svg class="shrink-0 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                Upload
                            </button>
                            <button type="button" id="editorUrlModeBtn" class="py-2 sm:py-2.5 px-3 sm:px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-layer-line text-muted-foreground-1 hover:border-primary-hover hover:text-primary-hover focus:outline-hidden focus:border-primary-focus focus:text-primary-focus disabled:opacity-50 disabled:pointer-events-none">
                                <svg class="shrink-0 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                URL
                            </button>
                            <button type="button" id="editorBrowseModeBtn" class="py-2 sm:py-2.5 px-3 sm:px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-layer-line text-muted-foreground-1 hover:border-primary-hover hover:text-primary-hover focus:outline-hidden focus:border-primary-focus focus:text-primary-focus disabled:opacity-50 disabled:pointer-events-none">
                                <svg class="shrink-0 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Browse
                            </button>
                        </div>

                        <!-- Upload Mode -->
                        <div id="editorUploadMode" class="space-y-3">
                            <div id="editorFileInputContainer">
                                <label for="editorImageFile" class="sr-only">Choose file</label>
                                <input type="file" id="editorImageFile" accept="image/*" class="block w-full border border-layer-line shadow-sm rounded-lg text-sm focus:z-10 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none file:bg-layer file:border-0 file:me-4 file:py-2.5 file:px-4 file:sm:py-3 file:text-foreground">
                            </div>
                            <div id="editorFileUploadProgress" class="hidden bg-layer border border-layer-line rounded-lg p-4"></div>
                            <p id="editorUploadHint" class="text-xs text-muted-foreground-1">Supported formats: JPG, PNG, GIF, WebP (Max 1MB)</p>
                        </div>

                        <!-- URL Mode -->
                        <div id="editorUrlMode" class="hidden space-y-3">
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input type="text" id="editorImageUrl" placeholder="Enter image URL..." class="py-2.5 sm:py-3 px-4 rounded-lg block w-full bg-layer border-layer-line sm:text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none">
                            </div>
                            <div id="editorUrlPreview" class="hidden mt-3">
                                <p class="text-xs text-muted-foreground-1 mb-2">Preview:</p>
                                <img id="editorUrlPreviewImg" src="" alt="Preview" class="max-w-full h-64 object-contain rounded-lg border border-layer-line mx-auto bg-layer">
                            </div>
                        </div>

                        <!-- Browse Mode -->
                        <div id="editorBrowseMode" class="hidden">
                            <div id="editorBrowseLoading" class="hidden text-center py-8">
                                <div class="inline-block size-8 animate-spin rounded-full border-4 border-solid border-current border-e-transparent align-[-0.125em] text-primary motion-reduce:animate-[spin_1.5s_linear_infinite]" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="mt-3 text-sm text-muted-foreground-1">Loading images...</p>
                            </div>
                            <div id="editorBrowseEmpty" class="hidden text-center py-8">
                                <svg class="mx-auto size-16 text-muted-foreground-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="mt-3 text-sm text-muted-foreground-1">No images found</p>
                            </div>
                            <div id="editorBrowseGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4"></div>
                        </div>
                    </div>
                    <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t border-overlay-footer">
                        <button type="button" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-layer border border-layer-line text-layer-foreground shadow-2xs hover:bg-layer-hover focus:outline-hidden focus:bg-layer-focus disabled:opacity-50 disabled:pointer-events-none" id="cancelEditorUrl" data-hs-overlay="#editorImageModal">
                            Cancel
                        </button>
                        <button type="button" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus disabled:opacity-50 disabled:pointer-events-none" id="insertEditorUrl">
                            Insert Image
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endpush
</div>
