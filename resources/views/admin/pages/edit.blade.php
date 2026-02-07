<x-app-layout>
    <div class="p-4">
        <!-- Flash Message -->
        <div id="flashMessage" class="hidden max-w-4xl mb-4 p-3 rounded-lg"></div>

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-foreground">Edit Page: {{ $page->title }}</h1>
        </div>

        <form id="pageForm" class="max-w-5xl">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div class="mb-6">
                <label for="title" class="block font-medium mb-2 text-foreground">Page Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title', $page->title) }}"
                    class="w-full px-3 py-2 border border-layer-line rounded-lg bg-layer text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                    required
                >
                <span class="text-red-600 text-sm error-message" data-field="title"></span>
            </div>

            <!-- Slug (Read-only) -->
            <div class="mb-6">
                <label class="block font-medium mb-2 text-foreground">Slug</label>
                <input
                    type="text"
                    value="{{ $page->slug }}"
                    class="w-full px-3 py-2 border border-layer-line rounded-lg bg-gray-100 text-muted-foreground-1 cursor-not-allowed"
                    disabled
                >
                <p class="text-sm text-muted-foreground-1 mt-1">Slug cannot be changed</p>
            </div>

            <!-- Content Editor -->
            <div class="mb-6">
                <label for="content" class="block font-medium mb-2 text-foreground">Content</label>
                <div class="border border-layer-line rounded-lg overflow-hidden bg-layer">
                    <div id="editor" class="bg-white"></div>
                </div>
                <input type="hidden" name="content" id="content" value="{{ old('content', $page->content) }}">
                <span class="text-red-600 text-sm error-message" data-field="content"></span>

                <!-- Word Count -->
                <div class="mt-2 text-sm text-muted-foreground-1">
                    <span id="wordCount">0</span> <span id="wordLabel">words</span> •
                    <span id="readTimeCount">0</span> <span id="readTimeLabel">min</span> read
                </div>
            </div>

            <!-- Published Status -->
            <div class="mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        id="is_published"
                        name="is_published"
                        value="1"
                        {{ old('is_published', $page->is_published) ? 'checked' : '' }}
                        class="form-checkbox h-4 w-4 text-primary border-layer-line rounded focus:ring-2 focus:ring-primary"
                    >
                    <span class="font-medium text-foreground">Published</span>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button
                    type="submit"
                    id="updateBtn"
                    class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-none focus:bg-primary-focus"
                >
                    Update Page
                </button>
                <a
                    href="{{ route('admin.pages.index') }}"
                    class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-layer border border-layer-line text-layer-foreground hover:bg-layer-hover focus:outline-none focus:bg-layer-focus"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <div id="editorImageModal" class="hidden fixed inset-0 z-[9999] overflow-y-auto" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <!-- Modal Header -->
                <div class="flex justify-between items-center py-3 px-4 border-b border-gray-200 bg-white">
                    <h3 class="font-bold text-gray-800">Insert Image</h3>
                    <button
                        type="button"
                        onclick="closeEditorImageModal()"
                        class="flex justify-center items-center size-7 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100"
                    >
                        <span class="sr-only">Close</span>
                        <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"/>
                            <path d="m6 6 12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-4 bg-white max-h-[600px] overflow-y-auto">
                    {{-- Mode Tabs --}}
                    <div class="flex gap-2 mb-4">
                        <button
                            type="button"
                            id="editorUploadModeBtn"
                            class="flex-1 py-2 px-3 text-sm font-medium rounded-lg bg-blue-600 text-white"
                        >
                            Upload
                        </button>
                        <button
                            type="button"
                            id="editorUrlModeBtn"
                            class="flex-1 py-2 px-3 text-sm font-medium rounded-lg border border-gray-200 text-gray-800 hover:bg-gray-50"
                        >
                            URL
                        </button>
                        <button
                            type="button"
                            id="editorBrowseModeBtn"
                            class="flex-1 py-2 px-3 text-sm font-medium rounded-lg border border-gray-200 text-gray-800 hover:bg-gray-50"
                        >
                            Browse
                        </button>
                    </div>

                    {{-- Upload Mode --}}
                    <div id="editorUploadMode">
                        <div id="editorFileInputContainer" class="flex items-center justify-center w-full">
                            <label for="editorImageFile" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="mb-2 text-sm text-gray-500">
                                        <span class="font-semibold">Click to upload</span> or drag and drop
                                    </p>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF, SVG, WEBP (MAX. 1MB)</p>
                                </div>
                                <input id="editorImageFile" type="file" class="hidden" accept="image/*" />
                            </label>
                        </div>
                        <p id="editorUploadHint" class="text-xs text-gray-500 mt-2">Upload an image from your computer</p>
                        <div id="editorFileUploadProgress" class="hidden mt-4"></div>
                    </div>

                    {{-- URL Mode --}}
                    <div id="editorUrlMode" class="hidden">
                        <input
                            type="text"
                            id="editorImageUrl"
                            placeholder="https://example.com/image.jpg"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        <div id="editorUrlPreview" class="hidden mt-4">
                            <p class="text-xs text-gray-500 mb-2">Preview:</p>
                            <img id="editorUrlPreviewImg" src="" class="max-w-full h-64 object-contain rounded-lg border border-gray-200 mx-auto bg-white">
                        </div>
                        <div class="flex gap-2 justify-end mt-4">
                            <button
                                type="button"
                                onclick="closeEditorImageModal()"
                                class="py-2 px-3 text-sm font-medium rounded-lg border border-gray-200 text-gray-800 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                id="insertEditorUrl"
                                class="py-2 px-3 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                            >
                                Insert Image
                            </button>
                        </div>
                    </div>

                    {{-- Browse Mode --}}
                    <div id="editorBrowseMode" class="hidden">
                        <div id="editorBrowseLoading" class="flex justify-center items-center py-8">
                            <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <div id="editorBrowseGrid" class="grid grid-cols-3 gap-3 max-h-96 overflow-y-auto hidden"></div>
                        <div id="editorBrowseEmpty" class="hidden text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p>No images found</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    @endpush

    @push('scripts')
        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const pageId = {{ $page->id }};

                // ========================================
                // STATE MANAGEMENT
                // ========================================
                let uploadedEditorImages = [];
                let hasUnsavedChanges = false;

                // ========================================
                // QUILL EDITOR SETUP
                // ========================================
                const toolbarOptions = [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    ['link', 'image', 'video', 'formula'],
                    [{'header': 1}, {'header': 2}],
                    [{'list': 'ordered'}, {'list': 'bullet'}, {'list': 'check'}],
                    [{'script': 'sub'}, {'script': 'super'}],
                    [{'indent': '-1'}, {'indent': '+1'}],
                    [{'direction': 'rtl'}],
                    [{'size': ['small', false, 'large', 'huge']}],
                    [{'header': [1, 2, 3, 4, 5, 6, false]}],
                    [{'color': []}, {'background': []}],
                    [{'font': []}],
                    [{'align': []}],
                    ['clean']
                ];

                const quill = new Quill('#editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: {
                            container: toolbarOptions,
                            handlers: {
                                image: function () {
                                    openEditorImageModal();
                                }
                            }
                        }
                    },
                    placeholder: 'Write your page content...',
                });

                window.quillEditor = quill;

                const initialContent = document.getElementById('content').value;
                if (initialContent) {
                    quill.root.innerHTML = initialContent;
                    uploadedEditorImages = extractImagesFromContent(initialContent);
                }

                quill.on('text-change', function (delta, oldDelta, source) {
                    hasUnsavedChanges = true;
                    document.getElementById('content').value = quill.root.innerHTML;
                    updateWordCount();
                });

                function updateWordCount() {
                    const text = quill.getText();
                    const words = text.trim().split(/\s+/).filter(word => word.length > 0);
                    const wordCount = words.length;
                    const readTime = Math.max(1, Math.ceil(wordCount / 200));

                    const wordCountEl = document.getElementById('wordCount');
                    const wordLabelEl = document.getElementById('wordLabel');
                    const readTimeCountEl = document.getElementById('readTimeCount');
                    const readTimeLabelEl = document.getElementById('readTimeLabel');

                    if (wordCountEl) wordCountEl.textContent = wordCount;
                    if (wordLabelEl) wordLabelEl.textContent = wordCount === 1 ? 'word' : 'words';
                    if (readTimeCountEl) readTimeCountEl.textContent = readTime;
                    if (readTimeLabelEl) readTimeLabelEl.textContent = readTime === 1 ? 'min' : 'mins';
                }

                updateWordCount();

                // ========================================
                // IMAGE CLEANUP FUNCTIONS
                // ========================================
                function extractImagesFromContent(html) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const images = tempDiv.querySelectorAll('img');
                    return Array.from(images).map(img => {
                        try {
                            const url = new URL(img.src);
                            return url.pathname;
                        } catch (e) {
                            return img.src;
                        }
                    });
                }

                function cleanupOrphanImages() {
                    const currentContent = quill.root.innerHTML;
                    const currentImages = extractImagesFromContent(currentContent);

                    const orphanedImages = uploadedEditorImages.filter(img =>
                        !currentImages.includes(img)
                    );

                    if (orphanedImages.length > 0) {
                        fetch('{{ route('admin.pages.cleanup-images') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({images: orphanedImages})
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    console.log('Cleaned up ' + orphanedImages.length + ' orphaned images');
                                    uploadedEditorImages = currentImages;
                                }
                            })
                            .catch(error => {
                                console.error('Cleanup error:', error);
                            });
                    }
                }

                // ========================================
                // PAGE ABANDON PROTECTION
                // ========================================
                window.addEventListener('beforeunload', function(e) {
                    if (hasUnsavedChanges) {
                        if (uploadedEditorImages.length > 0) {
                            const cleanupData = JSON.stringify({
                                _token: '{{ csrf_token() }}',
                                images: uploadedEditorImages
                            });

                            navigator.sendBeacon(
                                '{{ route('admin.pages.cleanup-images') }}',
                                new Blob([cleanupData], {type: 'application/json'})
                            );
                        }

                        e.preventDefault();
                        e.returnValue = '';
                        return '';
                    }
                });

                // ========================================
                // MODAL FUNCTIONS (Custom - No Preline)
                // ========================================
                window.openEditorImageModal = function() {
                    const modal = document.getElementById('editorImageModal');
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    switchEditorMode('upload');
                };

                window.closeEditorImageModal = function() {
                    const modal = document.getElementById('editorImageModal');
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';

                    // Reset inputs
                    const editorImageFile = document.getElementById('editorImageFile');
                    const editorImageUrl = document.getElementById('editorImageUrl');

                    if (editorImageFile) editorImageFile.value = '';
                    if (editorImageUrl) editorImageUrl.value = '';

                    // Reset UI
                    const editorFileInputContainer = document.getElementById('editorFileInputContainer');
                    const editorUploadHint = document.getElementById('editorUploadHint');
                    const editorFileUploadProgress = document.getElementById('editorFileUploadProgress');
                    const editorUrlPreview = document.getElementById('editorUrlPreview');

                    if (editorFileInputContainer) editorFileInputContainer.classList.remove('hidden');
                    if (editorUploadHint) editorUploadHint.classList.remove('hidden');
                    if (editorFileUploadProgress) editorFileUploadProgress.classList.add('hidden');
                    if (editorUrlPreview) editorUrlPreview.classList.add('hidden');
                };

                // Close modal on backdrop click
                document.getElementById('editorImageModal').addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeEditorImageModal();
                    }
                });

                // ========================================
                // MODE SWITCHING
                // ========================================
                const editorUploadModeBtn = document.getElementById('editorUploadModeBtn');
                const editorUrlModeBtn = document.getElementById('editorUrlModeBtn');
                const editorBrowseModeBtn = document.getElementById('editorBrowseModeBtn');
                const editorUploadMode = document.getElementById('editorUploadMode');
                const editorUrlMode = document.getElementById('editorUrlMode');
                const editorBrowseMode = document.getElementById('editorBrowseMode');

                if (editorUploadModeBtn) editorUploadModeBtn.addEventListener('click', () => switchEditorMode('upload'));
                if (editorUrlModeBtn) editorUrlModeBtn.addEventListener('click', () => switchEditorMode('url'));
                if (editorBrowseModeBtn) editorBrowseModeBtn.addEventListener('click', () => switchEditorMode('browse'));

                function switchEditorMode(mode) {
                    // Hide all modes
                    if (editorUploadMode) editorUploadMode.classList.add('hidden');
                    if (editorUrlMode) editorUrlMode.classList.add('hidden');
                    if (editorBrowseMode) editorBrowseMode.classList.add('hidden');

                    // Reset all buttons
                    [editorUploadModeBtn, editorUrlModeBtn, editorBrowseModeBtn].forEach(btn => {
                        if (btn) {
                            btn.classList.remove('bg-blue-600', 'text-white');
                            btn.classList.add('border', 'border-gray-200', 'text-gray-800', 'hover:bg-gray-50');
                        }
                    });

                    // Show selected mode
                    if (mode === 'upload') {
                        if (editorUploadMode) editorUploadMode.classList.remove('hidden');
                        if (editorUploadModeBtn) {
                            editorUploadModeBtn.classList.remove('border', 'border-gray-200', 'text-gray-800', 'hover:bg-gray-50');
                            editorUploadModeBtn.classList.add('bg-blue-600', 'text-white');
                        }
                    } else if (mode === 'url') {
                        if (editorUrlMode) editorUrlMode.classList.remove('hidden');
                        if (editorUrlModeBtn) {
                            editorUrlModeBtn.classList.remove('border', 'border-gray-200', 'text-gray-800', 'hover:bg-gray-50');
                            editorUrlModeBtn.classList.add('bg-blue-600', 'text-white');
                        }
                    } else if (mode === 'browse') {
                        if (editorBrowseMode) editorBrowseMode.classList.remove('hidden');
                        if (editorBrowseModeBtn) {
                            editorBrowseModeBtn.classList.remove('border', 'border-gray-200', 'text-gray-800', 'hover:bg-gray-50');
                            editorBrowseModeBtn.classList.add('bg-blue-600', 'text-white');
                        }
                        loadEditorBrowseImages();
                    }
                }

                // ========================================
                // UPLOAD MODE
                // ========================================
                const editorImageFile = document.getElementById('editorImageFile');

                if (editorImageFile) {
                    editorImageFile.addEventListener('change', function () {
                        if (this.files && this.files[0]) {
                            const file = this.files[0];

                            document.getElementById('editorFileInputContainer').classList.add('hidden');
                            document.getElementById('editorUploadHint').classList.add('hidden');
                            const progressContainer = document.getElementById('editorFileUploadProgress');
                            progressContainer.classList.remove('hidden');

                            const reader = new FileReader();
                            reader.onload = function (e) {
                                progressContainer.innerHTML = `
                                <div class="mb-3 flex justify-between items-center">
                                    <div class="flex items-center gap-x-3">
                                        <span class="size-8 flex justify-center items-center border border-gray-300 text-gray-500 rounded-lg bg-gray-50">
                                            <svg class="shrink-0 size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-800 truncate">${file.name}</p>
                                            <p class="text-xs text-gray-500">${(file.size / 1024).toFixed(2)} KB</p>
                                        </div>
                                    </div>
                                    <button type="button" id="editorCancelUpload" class="text-gray-500 hover:text-red-600">
                                        <svg class="shrink-0 size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex items-center gap-x-3 whitespace-nowrap">
                                    <div class="flex w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div id="editorUploadProgress" class="flex flex-col justify-center rounded-full overflow-hidden bg-blue-600 transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                    <div class="w-16 text-end">
                                        <span id="editorUploadPercent" class="text-sm text-gray-800">0%</span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <p class="text-xs text-gray-500 mb-2">Preview:</p>
                                    <img src="${e.target.result}" class="max-w-full h-64 object-contain rounded-lg border border-gray-200 mx-auto bg-white">
                                </div>
                                <div class="flex gap-2 justify-end mt-4">
                                    <button type="button" id="editorCancelUploadBtn" class="py-2 px-3 text-sm font-medium rounded-lg border border-gray-200 text-gray-800 hover:bg-gray-50">Cancel</button>
                                    <button type="button" id="editorInsertUpload" class="py-2 px-3 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700">Insert Image</button>
                                </div>
                            `;

                                document.getElementById('editorInsertUpload').addEventListener('click', uploadEditorImage);
                                document.getElementById('editorCancelUploadBtn').addEventListener('click', cancelEditorUpload);
                                document.getElementById('editorCancelUpload').addEventListener('click', cancelEditorUpload);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }

                function uploadEditorImage() {
                    const file = editorImageFile.files[0];
                    const formData = new FormData();
                    formData.append('image', file);

                    const uploadProgress = document.getElementById('editorUploadProgress');
                    const uploadPercent = document.getElementById('editorUploadPercent');

                    let progress = 0;
                    const interval = setInterval(() => {
                        progress += 10;
                        if (progress >= 100) {
                            clearInterval(interval);
                            if (uploadProgress) uploadProgress.style.width = '100%';
                            if (uploadPercent) uploadPercent.textContent = '100%';
                        } else {
                            if (uploadProgress) uploadProgress.style.width = progress + '%';
                            if (uploadPercent) uploadPercent.textContent = progress + '%';
                        }
                    }, 100);

                    fetch('{{ route('admin.pages.upload-editor-image') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            clearInterval(interval);
                            if (data.success) {
                                insertImageIntoEditor(data.path);
                                uploadedEditorImages.push(data.path);
                                closeEditorImageModal();
                                showMessage(data.message, 'success');
                            } else {
                                showMessage(data.message || 'Upload failed', 'error');
                                cancelEditorUpload();
                            }
                        })
                        .catch(error => {
                            clearInterval(interval);
                            console.error('Upload error:', error);
                            showMessage('Upload failed. Please try again.', 'error');
                            cancelEditorUpload();
                        });
                }

                function cancelEditorUpload() {
                    const editorFileInputContainer = document.getElementById('editorFileInputContainer');
                    const editorUploadHint = document.getElementById('editorUploadHint');
                    const editorFileUploadProgress = document.getElementById('editorFileUploadProgress');

                    if (editorFileInputContainer) editorFileInputContainer.classList.remove('hidden');
                    if (editorUploadHint) editorUploadHint.classList.remove('hidden');
                    if (editorFileUploadProgress) editorFileUploadProgress.classList.add('hidden');
                    if (editorImageFile) editorImageFile.value = '';
                }

                // ========================================
                // URL MODE
                // ========================================
                const editorImageUrl = document.getElementById('editorImageUrl');
                const insertEditorUrl = document.getElementById('insertEditorUrl');

                if (editorImageUrl) {
                    editorImageUrl.addEventListener('input', function () {
                        const editorUrlPreview = document.getElementById('editorUrlPreview');
                        const editorUrlPreviewImg = document.getElementById('editorUrlPreviewImg');

                        if (this.value.trim()) {
                            if (editorUrlPreview) editorUrlPreview.classList.remove('hidden');
                            if (editorUrlPreviewImg) editorUrlPreviewImg.src = this.value.trim();
                        } else {
                            if (editorUrlPreview) editorUrlPreview.classList.add('hidden');
                        }
                    });
                }

                if (insertEditorUrl) {
                    insertEditorUrl.addEventListener('click', () => {
                        const url = editorImageUrl.value.trim();
                        if (url) {
                            insertImageIntoEditor(url);
                            uploadedEditorImages.push(url);
                            closeEditorImageModal();
                            showMessage('Image inserted successfully!', 'success');
                        } else {
                            showMessage('Please enter an image URL', 'error');
                        }
                    });
                }

                // ========================================
                // BROWSE MODE
                // ========================================
                function loadEditorBrowseImages() {
                    const loading = document.getElementById('editorBrowseLoading');
                    const grid = document.getElementById('editorBrowseGrid');
                    const empty = document.getElementById('editorBrowseEmpty');

                    if (loading) loading.classList.remove('hidden');
                    if (grid) grid.classList.add('hidden');
                    if (empty) empty.classList.add('hidden');

                    fetch('{{ route('admin.pages.browse-editor-images') }}')
                        .then(response => response.json())
                        .then(data => {
                            if (loading) loading.classList.add('hidden');

                            if (data.success && data.images.length > 0) {
                                if (grid) {
                                    grid.classList.remove('hidden');
                                    grid.innerHTML = data.images.map(image => `
                                    <div class="border border-gray-200 rounded-lg overflow-hidden cursor-pointer hover:ring-2 hover:ring-blue-500 transition-all duration-200 editor-browse-image" data-path="${image.path}">
                                        <img src="${image.path}" alt="${image.name}" class="w-full h-32 object-cover bg-white">
                                        <div class="p-2 bg-white">
                                            <p class="text-xs font-medium truncate text-gray-800">${image.name}</p>
                                            <p class="text-xs text-gray-500">${(image.size / 1024).toFixed(1)} KB</p>
                                        </div>
                                    </div>
                                `).join('');

                                    document.querySelectorAll('.editor-browse-image').forEach(el => {
                                        el.addEventListener('click', function () {
                                            insertImageIntoEditor(this.dataset.path);
                                            uploadedEditorImages.push(this.dataset.path);
                                            closeEditorImageModal();
                                            showMessage('Image inserted from storage!', 'success');
                                        });
                                    });
                                }
                            } else {
                                if (empty) empty.classList.remove('hidden');
                            }
                        })
                        .catch(error => {
                            console.error('Browse error:', error);
                            if (loading) loading.classList.add('hidden');
                            if (empty) empty.classList.remove('hidden');
                        });
                }

                // ========================================
                // INSERT IMAGE
                // ========================================
                function insertImageIntoEditor(url) {
                    if (window.quillEditor) {
                        const range = window.quillEditor.getSelection();
                        const index = range ? range.index : window.quillEditor.getLength();
                        window.quillEditor.insertEmbed(index, 'image', url);
                        window.quillEditor.setSelection(index + 1);
                        document.getElementById('content').value = window.quillEditor.root.innerHTML;
                        hasUnsavedChanges = true;
                    }
                }

                // ========================================
                // FORM SUBMISSION
                // ========================================
                const pageForm = document.getElementById('pageForm');
                if (pageForm) {
                    pageForm.addEventListener('submit', function (e) {
                        e.preventDefault();

                        hasUnsavedChanges = false;
                        cleanupOrphanImages();

                        const formData = new FormData(this);
                        formData.append('_method', 'PUT');

                        const updateBtn = document.getElementById('updateBtn');
                        const originalText = updateBtn ? updateBtn.textContent : 'Update Page';

                        if (updateBtn) {
                            updateBtn.disabled = true;
                            updateBtn.textContent = 'Updating...';
                        }

                        fetch('{{ route('admin.pages.update', $page->id) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        })
                            .then(response => {
                                const contentType = response.headers.get('content-type');
                                if (!contentType || !contentType.includes('application/json')) {
                                    throw new Error('Server returned non-JSON response');
                                }
                                return response.json().then(data => ({
                                    ok: response.ok,
                                    status: response.status,
                                    data: data
                                }));
                            })
                            .then(result => {
                                if (result.status === 422) {
                                    showMessage('Please fix the errors below', 'error');
                                    displayValidationErrors(result.data.errors);
                                    if (updateBtn) {
                                        updateBtn.disabled = false;
                                        updateBtn.textContent = originalText;
                                    }
                                } else if (result.ok && result.data.success) {
                                    showMessage(result.data.message, 'success');
                                    uploadedEditorImages = [];

                                    setTimeout(() => {
                                        window.location.href = result.data.redirect;
                                    }, 1500);
                                } else {
                                    showMessage(result.data.message || 'An error occurred', 'error');
                                    if (updateBtn) {
                                        updateBtn.disabled = false;
                                        updateBtn.textContent = originalText;
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Update error:', error);
                                showMessage('An error occurred while updating', 'error');
                                if (updateBtn) {
                                    updateBtn.disabled = false;
                                    updateBtn.textContent = originalText;
                                }
                            });
                    });
                }

                // ========================================
                // TRACK CHANGES
                // ========================================
                const titleInput = document.getElementById('title');
                if (titleInput) {
                    titleInput.addEventListener('input', function () {
                        hasUnsavedChanges = true;
                    });
                }

                const isPublishedCheckbox = document.getElementById('is_published');
                if (isPublishedCheckbox) {
                    isPublishedCheckbox.addEventListener('change', function () {
                        hasUnsavedChanges = true;
                    });
                }

                // ========================================
                // HELPER FUNCTIONS
                // ========================================
                function showMessage(message, type) {
                    const flashMessage = document.getElementById('flashMessage');
                    if (flashMessage) {
                        flashMessage.className = `max-w-4xl mb-4 p-3 rounded-lg ${
                            type === 'success'
                                ? 'bg-green-50 border border-green-200 text-green-700'
                                : 'bg-red-50 border border-red-200 text-red-700'
                        }`;
                        flashMessage.textContent = message;
                        flashMessage.classList.remove('hidden');

                        window.scrollTo({top: 0, behavior: 'smooth'});

                        setTimeout(() => {
                            flashMessage.classList.add('hidden');
                        }, 5000);
                    }
                }

                function displayValidationErrors(errors) {
                    document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
                    document.querySelectorAll('.border-red-500').forEach(el => {
                        el.classList.remove('border-red-500');
                    });

                    Object.keys(errors).forEach(field => {
                        const errorElement = document.querySelector(`.error-message[data-field="${field}"]`);
                        const inputElement = document.getElementById(field);

                        if (errorElement) {
                            errorElement.textContent = errors[field][0];
                        }

                        if (inputElement) {
                            inputElement.classList.add('border-red-500');
                        }
                    });

                    const firstError = document.querySelector('.error-message:not(:empty)');
                    if (firstError) {
                        firstError.scrollIntoView({behavior: 'smooth', block: 'center'});
                    }
                }
            });
        </script>
    @endpush
</x-app-layout>
