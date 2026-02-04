<x-app-layout>
    <div class="mw-full px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <a href="{{ route('admin.advertisements.index') }}"
                   class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Advertisement</h1>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-400">Update advertisement details</p>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <div id="flashMessage" class="hidden mb-4"></div>

        <!-- Form -->
        <form action="{{ route('admin.advertisements.update', $advertisement->id) }}" method="POST" id="ad-form" class="space-y-6">
            @csrf
            @method('PUT')

            <input type="hidden" name="image_path" id="image_path" value="{{ old('image_path', $advertisement->image_path) }}">

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-6">

                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="title"
                           value="{{ old('title', $advertisement->title) }}"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           placeholder="Enter advertisement title"
                           required>
                    @error('title')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea name="description"
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                              placeholder="Enter advertisement description">{{ old('description', $advertisement->description) }}</textarea>
                    @error('description')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image Upload Section -->
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 bg-gray-50 dark:bg-gray-700/50">
                    <label class="block font-medium text-gray-700 dark:text-gray-300 mb-4">
                        Advertisement Image <span class="text-red-500">*</span>
                    </label>

                    <!-- Current Image Display -->
                    <div id="current-image" class="{{ $advertisement->image_path ? '' : 'hidden' }}">
                        <div class="relative inline-block">
                            <img id="current-image-preview"
                                 src="{{ $advertisement->image_path }}"
                                 alt="{{ $advertisement->title }}"
                                 class="max-w-md w-full h-auto rounded-lg border-4 border-white dark:border-gray-600 shadow-lg">
                            <button type="button"
                                    id="remove-image-btn"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600 shadow-lg transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div id="image-message" class="mb-4 hidden"></div>

                    <!-- Upload Mode Toggle -->
                    <div class="flex gap-2 mb-4">
                        <button type="button"
                                id="upload-mode-btn"
                                class="px-4 py-2 rounded-lg font-medium transition-all bg-blue-500 text-white shadow-md">
                            <i class="fa-solid fa-upload mr-2"></i>Upload File
                        </button>
                        <button type="button"
                                id="url-mode-btn"
                                class="px-4 py-2 rounded-lg font-medium transition-all bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500">
                            <i class="fa-solid fa-link mr-2"></i>Use URL
                        </button>
                    </div>

                    <!-- File Upload Mode -->
                    <div id="upload-mode" class="space-y-4">
                        <div>
                            <input type="file"
                                   id="image-file"
                                   accept="image/*"
                                   class="block w-full text-sm text-gray-500 dark:text-gray-400
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-lg file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100
                                        file:cursor-pointer cursor-pointer
                                        dark:file:bg-blue-900 dark:file:text-blue-300">
                            <span id="image-file-error" class="text-red-600 dark:text-red-400 text-sm mt-1 hidden"></span>
                        </div>

                        <!-- Live Preview -->
                        <div id="upload-preview" class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600 hidden">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Preview:</p>
                            <div class="flex items-center gap-4">
                                <img id="upload-preview-img"
                                     src=""
                                     class="max-w-xs w-full h-auto rounded-lg border-2 border-gray-300 dark:border-gray-600">
                                <button type="button"
                                        id="confirm-upload-btn"
                                        class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 font-medium transition-all shadow-md hover:shadow-lg">
                                    <i class="fa-solid fa-check mr-2"></i>Confirm Upload
                                </button>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                            <i class="fa-solid fa-circle-info"></i>
                            Supported formats: JPG, PNG, GIF, WebP (Max 2MB)
                        </p>
                    </div>

                    <!-- URL Input Mode -->
                    <div id="url-mode" class="space-y-3 hidden">
                        <div class="flex gap-2">
                            <input type="text"
                                   id="image-url"
                                   class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 px-4 py-2 dark:bg-gray-700 dark:text-white"
                                   placeholder="https://example.com/ad-image.jpg or /storage/advertisements/example.jpg">
                            <button type="button"
                                    id="set-url-btn"
                                    class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 font-medium transition-all shadow-md hover:shadow-lg whitespace-nowrap">
                                <i class="fa-solid fa-check mr-2"></i>Set Image
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
                            <i class="fa-solid fa-circle-info"></i>
                            You can use a full URL or a path to an existing image in your storage
                        </p>
                    </div>

                    @error('image_path')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Link URL -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Link URL
                    </label>
                    <input type="url"
                           name="link_url"
                           value="{{ old('link_url', $advertisement->link_url) }}"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           placeholder="https://example.com">
                    @error('link_url')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Position & Size -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Position <span class="text-red-500">*</span>
                        </label>
                        <select name="position"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                required>
                            <option value="header" {{ old('position', $advertisement->position) === 'header' ? 'selected' : '' }}>Header</option>
                            <option value="sidebar" {{ old('position', $advertisement->position) === 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                            <option value="footer" {{ old('position', $advertisement->position) === 'footer' ? 'selected' : '' }}>Footer</option>
                            <option value="between-posts" {{ old('position', $advertisement->position) === 'between-posts' ? 'selected' : '' }}>Between Posts</option>
                            <option value="popup" {{ old('position', $advertisement->position) === 'popup' ? 'selected' : '' }}>Popup</option>
                        </select>
                        @error('position')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Size <span class="text-red-500">*</span>
                        </label>
                        <select name="size"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                required>
                            <option value="small" {{ old('size', $advertisement->size) === 'small' ? 'selected' : '' }}>Small</option>
                            <option value="medium" {{ old('size', $advertisement->size) === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="large" {{ old('size', $advertisement->size) === 'large' ? 'selected' : '' }}>Large</option>
                            <option value="banner" {{ old('size', $advertisement->size) === 'banner' ? 'selected' : '' }}>Banner</option>
                        </select>
                        @error('size')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Start Date
                        </label>
                        <input type="date"
                               name="start_date"
                               value="{{ old('start_date', $advertisement->start_date?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        @error('start_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            End Date
                        </label>
                        <input type="date"
                               name="end_date"
                               value="{{ old('end_date', $advertisement->end_date?->format('Y-m-d')) }}"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        @error('end_date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Display Order -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Display Order
                    </label>
                    <input type="number"
                           name="display_order"
                           value="{{ old('display_order', $advertisement->display_order) }}"
                           min="0"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           placeholder="0">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lower numbers appear first</p>
                    @error('display_order')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Checkboxes -->
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               id="is_active"
                               value="1"
                               {{ old('is_active', $advertisement->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Active
                        </label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox"
                               name="open_new_tab"
                               id="open_new_tab"
                               value="1"
                               {{ old('open_new_tab', $advertisement->open_new_tab) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="open_new_tab" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Open link in new tab
                        </label>
                    </div>
                </div>

            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('admin.advertisements.index') }}"
                   class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit"
                        id="submit-btn"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all shadow-md hover:shadow-lg">
                    <i class="fa-solid fa-save mr-2"></i>Update Advertisement
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            (function() {
                'use strict';

                const CONFIG = {
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    uploadUrl: '/admin/advertisements/upload-image',
                    deleteUrl: '/admin/advertisements/delete-image',
                };

                const elements = {
                    uploadModeBtn: document.getElementById('upload-mode-btn'),
                    urlModeBtn: document.getElementById('url-mode-btn'),
                    uploadMode: document.getElementById('upload-mode'),
                    urlMode: document.getElementById('url-mode'),
                    imageFile: document.getElementById('image-file'),
                    imageFileError: document.getElementById('image-file-error'),
                    uploadPreview: document.getElementById('upload-preview'),
                    uploadPreviewImg: document.getElementById('upload-preview-img'),
                    confirmUploadBtn: document.getElementById('confirm-upload-btn'),
                    imageUrl: document.getElementById('image-url'),
                    setUrlBtn: document.getElementById('set-url-btn'),
                    currentImage: document.getElementById('current-image'),
                    currentImagePreview: document.getElementById('current-image-preview'),
                    removeImageBtn: document.getElementById('remove-image-btn'),
                    imagePathInput: document.getElementById('image_path'),
                    imageMessage: document.getElementById('image-message'),
                    form: document.getElementById('ad-form'),
                };

                let currentMode = 'upload';
                let currentImagePath = '{{ $advertisement->image_path }}';
                let originalImagePath = '{{ $advertisement->image_path }}';
                let hasUnsavedChanges = false;

                function init() {
                    setupEventListeners();
                }

                function setupEventListeners() {
                    elements.uploadModeBtn.addEventListener('click', () => switchMode('upload'));
                    elements.urlModeBtn.addEventListener('click', () => switchMode('url'));
                    elements.imageFile.addEventListener('change', handleFileSelect);
                    elements.confirmUploadBtn.addEventListener('click', handleUpload);
                    elements.setUrlBtn.addEventListener('click', handleSetUrl);
                    elements.removeImageBtn.addEventListener('click', handleRemoveImage);
                    elements.form.addEventListener('submit', handleSubmit);

                    // Track changes
                    elements.form.addEventListener('input', () => {
                        hasUnsavedChanges = true;
                    });
                }

                // Page abandon protection
                window.addEventListener('beforeunload', function(e) {
                    if (hasUnsavedChanges && currentImagePath !== originalImagePath) {
                        // If user uploaded a new image but didn't save, delete it
                        if (currentImagePath && currentImagePath.startsWith('/images/') && currentImagePath !== originalImagePath) {
                            navigator.sendBeacon(
                                CONFIG.deleteUrl,
                                new Blob([JSON.stringify({
                                    _token: CONFIG.csrfToken,
                                    path: currentImagePath
                                })], {type: 'application/json'})
                            );
                        }

                        e.preventDefault();
                        e.returnValue = '';
                        return '';
                    }
                });

                function switchMode(mode) {
                    currentMode = mode;

                    if (mode === 'upload') {
                        elements.uploadMode.classList.remove('hidden');
                        elements.urlMode.classList.add('hidden');
                        elements.uploadModeBtn.classList.add('bg-blue-500', 'text-white', 'shadow-md');
                        elements.uploadModeBtn.classList.remove('bg-gray-200', 'dark:bg-gray-600', 'text-gray-700', 'dark:text-gray-300');
                        elements.urlModeBtn.classList.remove('bg-blue-500', 'text-white', 'shadow-md');
                        elements.urlModeBtn.classList.add('bg-gray-200', 'dark:bg-gray-600', 'text-gray-700', 'dark:text-gray-300');
                    } else {
                        elements.uploadMode.classList.add('hidden');
                        elements.urlMode.classList.remove('hidden');
                        elements.urlModeBtn.classList.add('bg-blue-500', 'text-white', 'shadow-md');
                        elements.urlModeBtn.classList.remove('bg-gray-200', 'dark:bg-gray-600', 'text-gray-700', 'dark:text-gray-300');
                        elements.uploadModeBtn.classList.remove('bg-blue-500', 'text-white', 'shadow-md');
                        elements.uploadModeBtn.classList.add('bg-gray-200', 'dark:bg-gray-600', 'text-gray-700', 'dark:text-gray-300');
                    }
                }

                function handleFileSelect(e) {
                    const file = e.target.files[0];

                    if (!file) {
                        elements.uploadPreview.classList.add('hidden');
                        return;
                    }

                    if (!file.type.startsWith('image/')) {
                        showError('Please select an image file');
                        elements.imageFile.value = '';
                        return;
                    }

                    if (file.size > 2 * 1024 * 1024) {
                        showError('Image must be less than 2MB');
                        elements.imageFile.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        elements.uploadPreviewImg.src = e.target.result;
                        elements.uploadPreview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);

                    hideError();
                }

                function handleUpload() {
                    const file = elements.imageFile.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('image', file);

                    elements.confirmUploadBtn.disabled = true;
                    elements.confirmUploadBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Uploading...';

                    fetch(CONFIG.uploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Delete old image if it's different and from storage
                                if (currentImagePath && currentImagePath.startsWith('/images/') && currentImagePath !== originalImagePath) {
                                    deleteImage(currentImagePath);
                                }

                                currentImagePath = data.path;
                                elements.imagePathInput.value = data.path;
                                elements.currentImagePreview.src = data.path;
                                elements.currentImage.classList.remove('hidden');
                                elements.uploadPreview.classList.add('hidden');
                                elements.imageFile.value = '';

                                showMessage(data.message, 'success');
                            } else {
                                showMessage(data.message || 'Upload failed', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Upload error:', error);
                            showMessage('Upload failed. Please try again.', 'error');
                        })
                        .finally(() => {
                            elements.confirmUploadBtn.disabled = false;
                            elements.confirmUploadBtn.innerHTML = '<i class="fa-solid fa-check mr-2"></i>Confirm Upload';
                        });
                }

                function handleSetUrl() {
                    const url = elements.imageUrl.value.trim();

                    if (!url) {
                        showMessage('Please enter an image URL', 'error');
                        return;
                    }

                    // Delete old uploaded image if it's different from original
                    if (currentImagePath && currentImagePath.startsWith('/images/') && currentImagePath !== originalImagePath) {
                        deleteImage(currentImagePath);
                    }

                    currentImagePath = url;
                    elements.imagePathInput.value = url;
                    elements.currentImagePreview.src = url;
                    elements.currentImage.classList.remove('hidden');
                    elements.imageUrl.value = '';

                    showMessage('Image URL set successfully!', 'success');
                }

                function handleRemoveImage() {
                    if (!currentImagePath) return;

                    if (!confirm('Are you sure you want to remove this image?')) {
                        return;
                    }

                    // Only delete if it's a new upload (not the original)
                    if (currentImagePath.startsWith('/images/') && currentImagePath !== originalImagePath) {
                        deleteImage(currentImagePath);
                    }

                    currentImagePath = '';
                    elements.imagePathInput.value = '';
                    elements.currentImagePreview.src = '';
                    elements.currentImage.classList.add('hidden');

                    showMessage('Image removed successfully!', 'success');
                }

                function deleteImage(path) {
                    fetch(CONFIG.deleteUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ path: path })
                    })
                        .then(response => response.json())
                        .then(data => {
                            console.log('Image deleted:', data);
                        })
                        .catch(error => {
                            console.error('Delete error:', error);
                        });
                }

                function handleSubmit(e) {
                    if (!elements.imagePathInput.value) {
                        e.preventDefault();
                        showMessage('Please upload an image or provide an image URL', 'error');
                        return false;
                    }

                    // Clear unsaved changes flag
                    hasUnsavedChanges = false;

                    return true;
                }

                function showError(message) {
                    elements.imageFileError.textContent = message;
                    elements.imageFileError.classList.remove('hidden');
                }

                function hideError() {
                    elements.imageFileError.classList.add('hidden');
                }

                function showMessage(message, type = 'success') {
                    const bgColor = type === 'success'
                        ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-700 dark:text-green-400'
                        : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-400';

                    elements.imageMessage.className = `mb-4 p-3 border rounded ${bgColor} text-sm`;
                    elements.imageMessage.textContent = message;
                    elements.imageMessage.classList.remove('hidden');

                    setTimeout(() => {
                        elements.imageMessage.classList.add('hidden');
                    }, 5000);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    init();
                }

            })();
        </script>
    @endpush
</x-app-layout>
