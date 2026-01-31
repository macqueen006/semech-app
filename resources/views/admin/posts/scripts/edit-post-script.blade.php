<script>
    document.addEventListener('DOMContentLoaded', function () {
        const postId = {{ $postId }};

        // ========================================
        // STATE MANAGEMENT
        // ========================================
        let uploadedEditorImages = @json($post->uploadedEditorImages ?? []);


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
            placeholder: 'Write something inspiring...',
        });

        window.quillEditor = quill;

        const initialBody = document.getElementById('body').value;
        if (initialBody) {
            quill.root.innerHTML = initialBody;
            uploadedEditorImages = extractImagesFromBody(initialBody);
        }

        quill.on('text-change', function (delta, oldDelta, source) {
            document.getElementById('body').value = quill.root.innerHTML;
            updateWordCount();
        });

        function updateWordCount() {
            const text = quill.getText();
            const words = text.trim().split(/\s+/).filter(word => word.length > 0);
            const wordCount = words.length;
            const readTime = Math.max(1, Math.ceil(wordCount / 200));

            document.getElementById('wordCount').textContent = wordCount;
            document.getElementById('wordLabel').textContent = wordCount === 1 ? 'word' : 'words';
            document.getElementById('readTimeCount').textContent = readTime;
            document.getElementById('readTimeLabel').textContent = readTime === 1 ? 'minute' : 'minutes';
            document.getElementById('readTime').value = readTime;
        }

        updateWordCount();

        // ========================================
        // IMAGE CLEANUP FUNCTIONS
        // ========================================
        function extractImagesFromBody(html) {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const images = tempDiv.querySelectorAll('img');
            return Array.from(images).map(img => {
                // Extract relative path if it's a full URL
                try {
                    const url = new URL(img.src);
                    return url.pathname;
                } catch (e) {
                    return img.src;
                }
            });
        }

        function cleanupOrphanImages() {
            const bodyContent = quill.root.innerHTML;
            const currentImages = extractImagesFromBody(bodyContent);
            const orphanedImages = uploadedEditorImages.filter(img => !currentImages.includes(img));

            if (orphanedImages.length > 0) {
                fetch('{{ route('admin.posts.cleanup-images') }}', {
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
                        }
                    })
                    .catch(error => {
                        console.error('Cleanup error:', error);
                    });

                uploadedEditorImages = currentImages;
            }
        }

        // ========================================
        // SEO AUTO-FILL FUNCTIONS
        // ========================================
        const autoFillSeoCheckbox = document.getElementById('autoFillSeo');
        let autoFillSeo = autoFillSeoCheckbox ? autoFillSeoCheckbox.checked : true;

        if (autoFillSeoCheckbox) {
            autoFillSeoCheckbox.addEventListener('change', function () {
                autoFillSeo = this.checked;
                autoFillSeoFields();
            });
        }

        function autoFillSeoFields() {
            if (!autoFillSeo) return;

            const title = document.getElementById('title').value;
            const excerpt = document.getElementById('excerpt').value;
            const imagePath = document.getElementById('imagePath').value;

            const metaTitleInput = document.getElementById('metaTitle');
            const metaDescriptionInput = document.getElementById('metaDescription');

            // ALWAYS fill when checkbox is checked (like Livewire wire:model.live)
            if (title) {
                metaTitleInput.value = title.substring(0, 80); // Changed from 57 to 80
                updateCharCount('metaTitle', metaTitleInput.value.length);
            }

            if (excerpt) {
                metaDescriptionInput.value = excerpt.substring(0, 160); // Changed from 157 to 160
                updateCharCount('metaDescription', metaDescriptionInput.value.length);
            }

            // Auto-fill OG fields
            const ogTitle = document.getElementById('ogTitle');
            const ogDescription = document.getElementById('ogDescription');
            const ogImage = document.getElementById('ogImage');

            if (ogTitle) {
                ogTitle.value = metaTitleInput.value || title.substring(0, 80);
                updateCharCount('ogTitle', ogTitle.value.length);
            }
            if (ogDescription) {
                ogDescription.value = metaDescriptionInput.value || excerpt.substring(0, 160);
                updateCharCount('ogDescCount', ogDescription.value.length);
            }
            if (ogImage && imagePath) {
                ogImage.value = imagePath;
            }

            // Auto-fill Twitter fields
            const twitterTitle = document.getElementById('twitterTitle');
            const twitterDescription = document.getElementById('twitterDescription');
            const twitterImage = document.getElementById('twitterImage');

            if (twitterTitle) {
                twitterTitle.value = ogTitle ? ogTitle.value : '';
                updateCharCount('twitterTitle', twitterTitle.value.length);
            }
            if (twitterDescription) {
                twitterDescription.value = ogDescription ? ogDescription.value : '';
                updateCharCount('twitterDescCount', twitterDescription.value.length);
            }
            if (twitterImage) {
                twitterImage.value = ogImage ? ogImage.value : '';
            }

            updateSeoPreview();
        }

        // ========================================
        // SEO PREVIEW FUNCTIONS
        // ========================================
        function updateSeoPreview() {
            const title = document.getElementById('title').value;
            const metaTitleInput = document.getElementById('metaTitle');
            const metaDescriptionInput = document.getElementById('metaDescription');
            const excerpt = document.getElementById('excerpt').value;

            const seoPreview = document.getElementById('seoPreview');
            const previewTitle = document.getElementById('previewTitle');
            const previewUrl = document.getElementById('previewUrl');
            const previewDescription = document.getElementById('previewDescription');

            if (title && (metaDescriptionInput.value || excerpt)) {
                if (seoPreview) seoPreview.classList.remove('hidden');
                if (previewTitle) previewTitle.textContent = metaTitleInput.value || title;
                if (previewUrl) previewUrl.textContent = '{{ url('/post/') }}/' + slugify(title);
                if (previewDescription) {
                    previewDescription.textContent = (metaDescriptionInput.value || excerpt).substring(0, 157);
                }
            } else {
                if (seoPreview) seoPreview.classList.add('hidden');
            }
        }

        function slugify(text) {
            return text.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/--+/g, '-')
                .trim();
        }

        function updateCharCount(fieldId, count) {
            const countEl = document.getElementById(fieldId + 'Count');
            const warningEl = document.getElementById(fieldId + 'Warning');

            if (countEl) {
                countEl.textContent = count;
            }

            if (warningEl) {
                const limits = {
                    'metaTitle': 50,
                    'metaDescription': 150,
                    'ogTitle': 50,
                    'ogDescription': 150,
                    'twitterTitle': 50,
                    'twitterDescription': 150
                };

                if (limits[fieldId] && count > limits[fieldId]) {
                    warningEl.classList.remove('hidden');
                } else {
                    warningEl.classList.add('hidden');
                }
            }
        }

        // ========================================
        // AUTO-SAVE ON PAGE LOAD CHECK
        // ========================================
        function checkAutoSave() {
            fetch('{{ route('admin.posts.auto-save-check', $postId) }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.has_auto_save) {
                        document.getElementById('autoSaveWarning').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Auto-save check error:', error);
                });
        }

        checkAutoSave();

        // Load auto-saved version
        const loadAutoSaveBtn = document.getElementById('loadAutoSaveBtn');
        if (loadAutoSaveBtn) {
            loadAutoSaveBtn.addEventListener('click', function () {
                fetch('{{ route('admin.posts.auto-save-check', $postId) }}', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.has_auto_save) {
                            const saved = data.auto_save;

                            // Restore fields
                            document.getElementById('title').value = saved.title || '';
                            document.getElementById('excerpt').value = saved.excerpt || '';
                            document.getElementById('body').value = saved.body || '';
                            document.getElementById('imagePath').value = saved.image_path || '';
                            document.getElementById('category_id').value = saved.category_id || '';
                            document.getElementById('readTime').value = saved.read_time || 1;

                            // Restore Quill editor
                            if (saved.body && window.quillEditor) {
                                window.quillEditor.root.innerHTML = saved.body;
                                uploadedEditorImages = extractImagesFromBody(saved.body);
                                updateWordCount();
                            }

                            // Restore featured image preview
                            if (saved.image_path) {
                                const imagePreview = document.getElementById('imagePreview');
                                const imagePreviewImg = document.getElementById('imagePreviewImg');
                                if (imagePreview && imagePreviewImg) {
                                    imagePreviewImg.src = saved.image_path;
                                    imagePreview.classList.remove('hidden');
                                }
                            }

                            // Restore SEO fields
                            setFieldIfExists('metaTitle', saved.meta_title);
                            setFieldIfExists('metaDescription', saved.meta_description);
                            setFieldIfExists('focusKeyword', saved.focus_keyword);
                            setFieldIfExists('imageAlt', saved.image_alt);
                            setFieldIfExists('ogTitle', saved.og_title);
                            setFieldIfExists('ogDescription', saved.og_description);
                            setFieldIfExists('ogImage', saved.og_image);
                            setFieldIfExists('twitterTitle', saved.twitter_title);
                            setFieldIfExists('twitterDescription', saved.twitter_description);
                            setFieldIfExists('twitterImage', saved.twitter_image);

                            // Restore scheduling
                            if (saved.scheduled_at) {
                                const useScheduling = document.getElementById('useScheduling');
                                const scheduledAt = document.getElementById('scheduledAt');
                                if (useScheduling) useScheduling.checked = true;
                                if (scheduledAt) {
                                    scheduledAt.value = saved.scheduled_at.replace(' ', 'T').substring(0, 16);
                                    scheduledAt.dispatchEvent(new Event('change'));
                                }
                                const schedulingInputs = document.getElementById('schedulingInputs');
                                if (schedulingInputs) schedulingInputs.classList.remove('hidden');
                            }

                            if (saved.expires_at) {
                                const useExpiration = document.getElementById('useExpiration');
                                const expiresAt = document.getElementById('expiresAt');
                                if (useExpiration) useExpiration.checked = true;
                                if (expiresAt) {
                                    expiresAt.value = saved.expires_at.replace(' ', 'T').substring(0, 16);
                                    expiresAt.dispatchEvent(new Event('change'));
                                }
                                const expirationInputs = document.getElementById('expirationInputs');
                                if (expirationInputs) expirationInputs.classList.add('hidden');
                            }

                            // Update character counts
                            updateCharCount('title', saved.title ? saved.title.length : 0);
                            updateCharCount('excerpt', saved.excerpt ? saved.excerpt.length : 0);
                            updateCharCount('metaTitle', saved.meta_title ? saved.meta_title.length : 0);
                            updateCharCount('metaDescription', saved.meta_description ? saved.meta_description.length : 0);

                            // Update SEO preview
                            updateSeoPreview();

                            // Hide warning banner
                            document.getElementById('autoSaveWarning').classList.add('hidden');
                            showMessage('Auto-saved version loaded.', 'success');
                        }
                    })
                    .catch(error => {
                        console.error('Load auto-save error:', error);
                        showMessage('Failed to load auto-saved version.', 'error');
                    });
            });
        }

        // Reject / discard auto-save
        const rejectAutoSaveBtn = document.getElementById('rejectAutoSaveBtn');
        if (rejectAutoSaveBtn) {
            rejectAutoSaveBtn.addEventListener('click', function () {
                fetch('{{ route('admin.posts.reject', $postId) }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('autoSaveWarning').classList.add('hidden');
                        showMessage('Auto-saved version discarded.', 'success');
                    })
                    .catch(error => {
                        console.error('Reject auto-save error:', error);
                        showMessage('Failed to discard auto-save.', 'error');
                    });
            });
        }

        // Helper: set field value if element exists
        function setFieldIfExists(id, value) {
            const el = document.getElementById(id);
            if (el && value !== null && value !== undefined) {
                el.value = value;
            }
        }

        // ========================================
        // AUTO-SAVE INTERVAL (every 30 seconds)
        // ========================================
        let autoSaveInterval = setInterval(() => {
            autoSave();
        }, 30000);

        function autoSave() {
            cleanupOrphanImages();

            const formData = new FormData(document.getElementById('postForm'));

            fetch('{{ route('admin.posts.auto-save-edit', $postId) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                    }
                })
                .catch(error => {
                    console.error('Auto-save error:', error);
                });
        }

        // Manual "Save Draft" button
        const saveDraftBtn = document.getElementById('saveDraftBtn');
        if (saveDraftBtn) {
            saveDraftBtn.addEventListener('click', function () {
                autoSave();
            });
        }

        // ========================================
        // FORM SUBMISSION
        // ========================================
        document.getElementById('postForm').addEventListener('submit', function (e) {
            e.preventDefault();
            clearInterval(autoSaveInterval);

            cleanupOrphanImages();
            autoFillSeoFields();

            const formData = new FormData(this);
            formData.append('_method', 'PUT');
            const updateBtn = document.getElementById('updateBtn');
            const originalText = updateBtn.textContent;

            updateBtn.disabled = true;
            updateBtn.textContent = 'Updating...';

            fetch('{{ route('admin.posts.update', $postId) }}', {
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
                    } else if (result.ok && result.data.success) {
                        showMessage(result.data.message, 'success');
                        setTimeout(() => {
                            window.location.href = result.data.redirect;
                        }, 1500);
                    } else {
                        showMessage(result.data.message || 'An error occurred', 'error');
                    }
                    updateBtn.disabled = false;
                    updateBtn.textContent = originalText;
                })
                .catch(error => {
                    console.error('Update error:', error);
                    showMessage('An error occurred while updating', 'error');
                    updateBtn.disabled = false;
                    updateBtn.textContent = originalText;
                });
        });

        // ========================================
        // FEATURED IMAGE HANDLING
        // ========================================
        const uploadModeBtn = document.getElementById('uploadModeBtn');
        const urlModeBtn = document.getElementById('urlModeBtn');
        const browseModeBtn = document.getElementById('browseModeBtn');
        const uploadMode = document.getElementById('uploadMode');
        const urlMode = document.getElementById('urlMode');
        const imageFile = document.getElementById('imageFile');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewImg = document.getElementById('imagePreviewImg');
        const removeImageBtn = document.getElementById('removeImageBtn');
        const imagePath = document.getElementById('imagePath');
        const imageUrl = document.getElementById('imageUrl');
        const setImageUrlBtn = document.getElementById('setImageUrlBtn');

        if (uploadModeBtn) uploadModeBtn.addEventListener('click', () => switchImageMode('upload'));
        if (urlModeBtn) urlModeBtn.addEventListener('click', () => switchImageMode('url'));
        if (browseModeBtn) browseModeBtn.addEventListener('click', () => openBrowseImagesModal());

        function switchImageMode(mode) {
            if (mode === 'upload') {
                uploadMode.classList.remove('hidden');
                urlMode.classList.add('hidden');
                uploadModeBtn.classList.remove('border-layer-line', 'text-muted-foreground-1');
                uploadModeBtn.classList.add('bg-primary', 'border-primary-line', 'text-primary-foreground');
                urlModeBtn.classList.add('border-layer-line', 'text-muted-foreground-1');
                urlModeBtn.classList.remove('bg-primary', 'border-primary-line', 'text-primary-foreground');
            } else {
                uploadMode.classList.add('hidden');
                urlMode.classList.remove('hidden');
                urlModeBtn.classList.remove('border-layer-line', 'text-muted-foreground-1');
                urlModeBtn.classList.add('bg-primary', 'border-primary-line', 'text-primary-foreground');
                uploadModeBtn.classList.add('border-layer-line', 'text-muted-foreground-1');
                uploadModeBtn.classList.remove('bg-primary', 'border-primary-line', 'text-primary-foreground');
            }
        }

        if (imageFile) {
            imageFile.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    document.getElementById('fileInputContainer').classList.add('hidden');
                    document.getElementById('uploadHint').classList.add('hidden');
                    const progressContainer = document.getElementById('fileUploadProgress');
                    progressContainer.classList.remove('hidden');

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        progressContainer.innerHTML = `
                        <div class="mb-3 flex justify-between items-center">
                            <div class="flex items-center gap-x-3">
                                <span class="size-10 flex justify-center items-center border border-layer-line text-muted-foreground-1 rounded-lg bg-layer">
                                    <svg class="shrink-0 size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-foreground truncate">${file.name}</p>
                                    <p class="text-xs text-muted-foreground-1">${(file.size / 1024).toFixed(2)} KB</p>
                                </div>
                            </div>
                            <div class="inline-flex items-center gap-x-2">
                                <button type="button" id="confirmUpload" class="text-green-600 hover:text-green-800">
                                    <svg class="shrink-0 size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                                <button type="button" id="cancelUpload" class="text-red-500 hover:text-red-700">
                                    <svg class="shrink-0 size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="mt-3">
                            <img src="${e.target.result}" class="max-w-full h-48 object-cover rounded-lg border border-layer-line">
                        </div>
                    `;

                        document.getElementById('confirmUpload').addEventListener('click', uploadFeaturedImage);
                        document.getElementById('cancelUpload').addEventListener('click', cancelImageUpload);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        function uploadFeaturedImage() {
            const file = imageFile.files[0];
            const formData = new FormData();
            formData.append('image', file);

            fetch('{{ route('admin.posts.upload-image') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        imagePath.value = data.path;
                        imagePreviewImg.src = data.path;
                        imagePreview.classList.remove('hidden');
                        cancelImageUpload();
                        showImageMessage(data.message, 'success');
                        autoFillSeoFields(); // Auto-fill OG/Twitter images
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    showImageMessage('Upload failed. Please try again.', 'error');
                    cancelImageUpload();
                });
        }

        function cancelImageUpload() {
            document.getElementById('fileInputContainer').classList.remove('hidden');
            document.getElementById('uploadHint').classList.remove('hidden');
            document.getElementById('fileUploadProgress').classList.add('hidden');
            imageFile.value = '';
        }

        if (setImageUrlBtn) {
            setImageUrlBtn.addEventListener('click', () => {
                const url = imageUrl.value.trim();
                if (!url) {
                    showImageMessage('Please enter an image URL', 'error');
                    return;
                }
                imagePath.value = url;
                imagePreviewImg.src = url;
                imagePreview.classList.remove('hidden');
                showImageMessage('Image URL set successfully!', 'success');
                autoFillSeoFields(); // Auto-fill OG/Twitter images
            });
        }

        if (removeImageBtn) {
            removeImageBtn.addEventListener('click', () => {
                const pathToDelete = currentFeaturedImagePath || imagePath.value;

                // Only delete if it's a storage path (not external URL)
                if (pathToDelete && pathToDelete.startsWith('/storage/')) {
                    deleteImageFromStorage(pathToDelete);
                } else {
                    showImageMessage('Image removed', 'success');
                }

                // Clear everything
                currentFeaturedImagePath = '';
                imagePath.value = '';
                imagePreviewImg.src = '';
                imagePreview.classList.add('hidden');
                imageFile.value = '';
                imageUrl.value = '';
            });
        }

        function deleteImageFromStorage(path) {
            if (!path) return;

            console.log('Attempting to delete image:', path);

            fetch('{{ route('admin.posts.cleanup-images') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ images: path })
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Delete response:', data);
                    if (data.success) {
                        console.log('Image deleted from storage');
                        showImageMessage('Image removed and deleted from storage', 'success');
                    } else {
                        console.error('Failed to delete:', data.message);
                        showImageMessage('Image removed from preview', 'success');
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    showImageMessage('Image removed from preview', 'success');
                });
        }

        function showImageMessage(message, type) {
            const successEl = document.getElementById('imageSuccess');
            const errorEl = document.getElementById('imageError');

            if (type === 'success') {
                if (successEl) {
                    successEl.textContent = message;
                    successEl.classList.remove('hidden');
                }
                if (errorEl) errorEl.classList.add('hidden');
                setTimeout(() => {
                    if (successEl) successEl.classList.add('hidden');
                }, 3000);
            } else {
                if (errorEl) {
                    errorEl.textContent = message;
                    errorEl.classList.remove('hidden');
                }
                if (successEl) successEl.classList.add('hidden');
                setTimeout(() => {
                    if (errorEl) errorEl.classList.add('hidden');
                }, 3000);
            }
        }

        // ========================================
        // EDITOR IMAGE MODAL
        // ========================================
        const editorUploadModeBtn = document.getElementById('editorUploadModeBtn');
        const editorUrlModeBtn = document.getElementById('editorUrlModeBtn');
        const editorBrowseModeBtn = document.getElementById('editorBrowseModeBtn');
        const editorUploadMode = document.getElementById('editorUploadMode');
        const editorUrlMode = document.getElementById('editorUrlMode');
        const editorBrowseMode = document.getElementById('editorBrowseMode');
        const editorImageFile = document.getElementById('editorImageFile');
        const editorImageUrl = document.getElementById('editorImageUrl');

        function openEditorImageModal() {
            window.HSOverlay.open('#editorImageModal');
            switchEditorMode('upload');
        }

        function closeEditorImageModal() {
            window.HSOverlay.close('#editorImageModal');
            if (editorImageFile) editorImageFile.value = '';
            if (editorImageUrl) editorImageUrl.value = '';

            const editorFileInputContainer = document.getElementById('editorFileInputContainer');
            const editorUploadHint = document.getElementById('editorUploadHint');
            const editorFileUploadProgress = document.getElementById('editorFileUploadProgress');
            const editorUrlPreview = document.getElementById('editorUrlPreview');

            if (editorFileInputContainer) editorFileInputContainer.classList.remove('hidden');
            if (editorUploadHint) editorUploadHint.classList.remove('hidden');
            if (editorFileUploadProgress) editorFileUploadProgress.classList.add('hidden');
            if (editorUrlPreview) editorUrlPreview.classList.add('hidden');
        }

        if (editorUploadModeBtn) editorUploadModeBtn.addEventListener('click', () => switchEditorMode('upload'));
        if (editorUrlModeBtn) editorUrlModeBtn.addEventListener('click', () => switchEditorMode('url'));
        if (editorBrowseModeBtn) editorBrowseModeBtn.addEventListener('click', () => switchEditorMode('browse'));

        function switchEditorMode(mode) {
            if (editorUploadMode) editorUploadMode.classList.add('hidden');
            if (editorUrlMode) editorUrlMode.classList.add('hidden');
            if (editorBrowseMode) editorBrowseMode.classList.add('hidden');

            const resetBtn = (btn) => {
                if (btn) {
                    btn.classList.remove('bg-primary', 'border-primary-line', 'text-primary-foreground');
                    btn.classList.add('border-layer-line', 'text-muted-foreground-1');
                }
            };

            resetBtn(editorUploadModeBtn);
            resetBtn(editorUrlModeBtn);
            resetBtn(editorBrowseModeBtn);

            if (mode === 'upload') {
                if (editorUploadMode) editorUploadMode.classList.remove('hidden');
                if (editorUploadModeBtn) {
                    editorUploadModeBtn.classList.remove('border-layer-line', 'text-muted-foreground-1');
                    editorUploadModeBtn.classList.add('bg-primary', 'border-primary-line', 'text-primary-foreground');
                }
            } else if (mode === 'url') {
                if (editorUrlMode) editorUrlMode.classList.remove('hidden');
                if (editorUrlModeBtn) {
                    editorUrlModeBtn.classList.remove('border-layer-line', 'text-muted-foreground-1');
                    editorUrlModeBtn.classList.add('bg-primary', 'border-primary-line', 'text-primary-foreground');
                }
            } else if (mode === 'browse') {
                if (editorBrowseMode) editorBrowseMode.classList.remove('hidden');
                if (editorBrowseModeBtn) {
                    editorBrowseModeBtn.classList.remove('border-layer-line', 'text-muted-foreground-1');
                    editorBrowseModeBtn.classList.add('bg-primary', 'border-primary-line', 'text-primary-foreground');
                }
                loadEditorBrowseImages();
            }
        }

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
                                <span class="size-8 flex justify-center items-center border border-layer-line text-muted-foreground-1 rounded-lg bg-layer">
                                    <svg class="shrink-0 size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-foreground truncate">${file.name}</p>
                                    <p class="text-xs text-muted-foreground-1">${(file.size / 1024).toFixed(2)} KB</p>
                                </div>
                            </div>
                            <div class="inline-flex items-center gap-x-2">
                                <button type="button" id="editorCancelUpload" class="text-muted-foreground-1 hover:text-red-600">
                                    <svg class="shrink-0 size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center gap-x-3 whitespace-nowrap">
                            <div class="flex w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div id="editorUploadProgress" class="flex flex-col justify-center rounded-full overflow-hidden bg-primary transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <div class="w-16 text-end">
                                <span id="editorUploadPercent" class="text-sm text-foreground">0%</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <p class="text-xs text-muted-foreground-1 mb-2">Preview:</p>
                            <img src="${e.target.result}" class="max-w-full h-64 object-contain rounded-lg border border-layer-line mx-auto bg-layer">
                        </div>
                        <div class="flex gap-2 justify-end mt-4">
                            <button type="button" id="editorCancelUploadBtn" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-layer border border-layer-line text-layer-foreground shadow-2xs hover:bg-layer-hover focus:outline-hidden focus:bg-layer-focus">Cancel</button>
                            <button type="button" id="editorInsertUpload" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover focus:outline-hidden focus:bg-primary-focus">Insert Image</button>
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

            fetch('{{ route('admin.posts.upload-editor-image') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        insertImageIntoEditor(data.path);
                        uploadedEditorImages.push(data.path);
                        closeEditorImageModal();
                        showMessage(data.message, 'success');
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    showMessage('Upload failed. Please try again.', 'error');
                });

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

        const cancelEditorUrl = document.getElementById('cancelEditorUrl');
        const insertEditorUrl = document.getElementById('insertEditorUrl');

        if (cancelEditorUrl) cancelEditorUrl.addEventListener('click', closeEditorImageModal);
        if (insertEditorUrl) {
            insertEditorUrl.addEventListener('click', () => {
                const url = editorImageUrl.value.trim();
                if (url) {
                    insertImageIntoEditor(url);
                    uploadedEditorImages.push(url);
                    closeEditorImageModal();
                    showMessage('Image inserted successfully!', 'success');
                }
            });
        }

        function insertImageIntoEditor(url) {
            if (window.quillEditor) {
                const range = window.quillEditor.getSelection();
                const index = range ? range.index : window.quillEditor.getLength();
                window.quillEditor.insertEmbed(index, 'image', url);
                window.quillEditor.setSelection(index + 1);
                document.getElementById('body').value = window.quillEditor.root.innerHTML;
            }
        }

        function loadEditorBrowseImages() {
            const loading = document.getElementById('editorBrowseLoading');
            const grid = document.getElementById('editorBrowseGrid');
            const empty = document.getElementById('editorBrowseEmpty');

            if (loading) loading.classList.remove('hidden');
            if (grid) grid.classList.add('hidden');
            if (empty) empty.classList.add('hidden');

            fetch('{{ route('admin.posts.browse-editor-images') }}')
                .then(response => response.json())
                .then(data => {
                    if (loading) loading.classList.add('hidden');

                    if (data.success && data.images.length > 0) {
                        if (grid) {
                            grid.classList.remove('hidden');
                            grid.innerHTML = data.images.map(image => `
                            <div class="border border-layer-line rounded-lg overflow-hidden cursor-pointer hover:ring-2 hover:ring-primary transition-all duration-200 editor-browse-image" data-path="${image.path}">
                                <img src="${image.path}" alt="${image.name}" class="w-full h-32 object-cover bg-layer">
                                <div class="p-2 bg-layer">
                                    <p class="text-xs font-medium truncate text-foreground">${image.name}</p>
                                    <p class="text-xs text-muted-foreground-1">${(image.size / 1024).toFixed(1)} KB</p>
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
        // BROWSE IMAGES MODAL (Featured Image)
        // ========================================
        function openBrowseImagesModal() {
            window.HSOverlay.open('#browseImagesModal');
            loadBrowseImages();
        }

        function closeBrowseImagesModal() {
            window.HSOverlay.close('#browseImagesModal');
        }

        function loadBrowseImages() {
            const loading = document.getElementById('browseLoading');
            const grid = document.getElementById('browseGrid');
            const empty = document.getElementById('browseEmpty');

            if (loading) loading.classList.remove('hidden');
            if (grid) grid.classList.add('hidden');
            if (empty) empty.classList.add('hidden');

            fetch('{{ route('admin.posts.browse-images') }}')
                .then(response => response.json())
                .then(data => {
                    if (loading) loading.classList.add('hidden');

                    if (data.success && data.images.length > 0) {
                        if (grid) {
                            grid.classList.remove('hidden');
                            grid.innerHTML = data.images.map(image => `
                            <div class="border border-layer-line rounded-lg overflow-hidden cursor-pointer hover:ring-2 hover:ring-primary transition-all duration-200 browse-image" data-path="${image.path}">
                                <img src="${image.path}" alt="${image.name}" class="w-full h-40 object-cover bg-layer">
                                <div class="p-2 bg-layer">
                                    <p class="text-xs font-medium truncate text-foreground">${image.name}</p>
                                    <p class="text-xs text-muted-foreground-1">${(image.size / 1024).toFixed(1)} KB</p>
                                </div>
                            </div>
                        `).join('');

                            document.querySelectorAll('.browse-image').forEach(el => {
                                el.addEventListener('click', function () {
                                    imagePath.value = this.dataset.path;
                                    imagePreviewImg.src = this.dataset.path;
                                    imagePreview.classList.remove('hidden');
                                    closeBrowseImagesModal();
                                    showImageMessage('Image selected from storage!', 'success');
                                    autoFillSeoFields(); // Auto-fill OG/Twitter images
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
        // SCHEDULING HANDLERS
        // ========================================
        const useScheduling = document.getElementById('useScheduling');
        const schedulingInputs = document.getElementById('schedulingInputs');
        const scheduledAt = document.getElementById('scheduledAt');
        const scheduledAtPreview = document.getElementById('scheduledAtPreview');
        const isPublishedCheckbox = document.getElementById('is_published');

        if (useScheduling) {
            useScheduling.addEventListener('change', function () {
                if (this.checked) {
                    if (schedulingInputs) schedulingInputs.classList.remove('hidden');
                    if (isPublishedCheckbox) {
                        isPublishedCheckbox.disabled = true;
                        isPublishedCheckbox.checked = false;
                    }
                } else {
                    if (schedulingInputs) schedulingInputs.classList.add('hidden');
                    if (scheduledAt) scheduledAt.value = '';
                    if (scheduledAtPreview) scheduledAtPreview.classList.add('hidden');
                    if (isPublishedCheckbox) {
                        isPublishedCheckbox.disabled = false;
                    }
                }
            });
        }

        if (scheduledAt) {
            scheduledAt.addEventListener('change', function () {
                if (this.value) {
                    const date = new Date(this.value);
                    const now = new Date();
                    const diff = date - now;
                    const hours = Math.floor(diff / 1000 / 60 / 60);
                    const days = Math.floor(hours / 24);

                    let preview = 'Will be published ';
                    if (days > 0) {
                        preview += `in ${days} day${days > 1 ? 's' : ''}`;
                    } else if (hours > 0) {
                        preview += `in ${hours} hour${hours > 1 ? 's' : ''}`;
                    } else {
                        preview += 'soon';
                    }
                    preview += ` (${date.toLocaleDateString()} at ${date.toLocaleTimeString()})`;

                    if (scheduledAtPreview) {
                        const previewSpan = scheduledAtPreview.querySelector('span');
                        if (previewSpan) previewSpan.textContent = preview;
                        scheduledAtPreview.classList.remove('hidden');
                    }
                } else {
                    if (scheduledAtPreview) scheduledAtPreview.classList.add('hidden');
                }
            });
        }

        const useExpiration = document.getElementById('useExpiration');
        const expirationInputs = document.getElementById('expirationInputs');
        const expiresAt = document.getElementById('expiresAt');
        const expiresAtPreview = document.getElementById('expiresAtPreview');

        if (useExpiration) {
            useExpiration.addEventListener('change', function () {
                if (this.checked) {
                    if (expirationInputs) expirationInputs.classList.remove('hidden');
                } else {
                    if (expirationInputs) expirationInputs.classList.add('hidden');
                    if (expiresAt) expiresAt.value = '';
                    if (expiresAtPreview) expiresAtPreview.classList.add('hidden');
                }
            });
        }

        if (expiresAt) {
            expiresAt.addEventListener('change', function () {
                if (this.value) {
                    const date = new Date(this.value);
                    const now = new Date();
                    const diff = date - now;
                    const hours = Math.floor(diff / 1000 / 60 / 60);
                    const days = Math.floor(hours / 24);

                    let preview = 'Will expire ';
                    if (days > 0) {
                        preview += `in ${days} day${days > 1 ? 's' : ''}`;
                    } else if (hours > 0) {
                        preview += `in ${hours} hour${hours > 1 ? 's' : ''}`;
                    } else {
                        preview += 'soon';
                    }
                    preview += ` (${date.toLocaleDateString()} at ${date.toLocaleTimeString()})`;

                    if (expiresAtPreview) {
                        const previewSpan = expiresAtPreview.querySelector('span');
                        if (previewSpan) previewSpan.textContent = preview;
                        expiresAtPreview.classList.remove('hidden');
                    }
                } else {
                    if (expiresAtPreview) expiresAtPreview.classList.add('hidden');
                }
            });
        }

        // ========================================
        // SEO FIELD LISTENERS
        // ========================================
        const titleInput = document.getElementById('title');
        const excerptInput = document.getElementById('excerpt');
        const metaTitleInput = document.getElementById('metaTitle');
        const metaDescriptionInput = document.getElementById('metaDescription');

        if (metaTitleInput) {
            metaTitleInput.addEventListener('input', function () {
                updateCharCount('metaTitle', this.value.length);
                updateSeoPreview();
            });
        }

        if (metaDescriptionInput) {
            metaDescriptionInput.addEventListener('input', function () {
                updateCharCount('metaDescription', this.value.length);
                updateSeoPreview();
            });
        }

        if (titleInput) {
            titleInput.addEventListener('input', function () {
                updateCharCount('title', this.value.length);
                if (autoFillSeo) {
                    autoFillSeoFields(); // Call this every time when checkbox is checked
                }
                updateSeoPreview();
            });
        }

        if (excerptInput) {
            excerptInput.addEventListener('input', function () {
                updateCharCount('excerpt', this.value.length);
                if (autoFillSeo) {
                    autoFillSeoFields(); // Call this every time when checkbox is checked
                }
                updateSeoPreview();
            });
        }

        // Add listeners for other SEO fields
        const seoFields = [
            'focusKeyword', 'imageAlt', 'ogTitle', 'ogDescription',
            'ogImage', 'twitterTitle', 'twitterDescription', 'twitterImage'
        ];

        seoFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', function () {
                    const countId = fieldId.charAt(0).toLowerCase() + fieldId.slice(1);
                    updateCharCount(countId, this.value.length);
                    updateSeoPreview();
                });
            }
        });

        // Initialize character counts on load
        updateCharCount('title', titleInput ? titleInput.value.length : 0);
        updateCharCount('excerpt', excerptInput ? excerptInput.value.length : 0);
        if (metaTitleInput) updateCharCount('metaTitle', metaTitleInput.value.length);
        if (metaDescriptionInput) updateCharCount('metaDescription', metaDescriptionInput.value.length);
        updateSeoPreview();

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
