<script>
    document.addEventListener('DOMContentLoaded', function () {
        const postId = {{ $postId }};

        // ════════════════════════════════════════════════════════════════════
        // STATE  ── THE ONLY SOURCE OF TRUTH FOR IMAGE LIFECYCLE
        // ════════════════════════════════════════════════════════════════════
        //
        // For the EDIT page, the post already has a committed image_path.
        // That image is always "borrowed" — it belongs to the live post.
        // We must never delete it until the UPDATE form is successfully
        // submitted with a different image (which the PostObserver handles).
        // ════════════════════════════════════════════════════════════════════

        let sessionUploadedImages       = [];
        let sessionUploadedEditorImages = [];
        let currentFeaturedImagePath    = document.getElementById('imagePath').value || '';
        let currentFeaturedIsBorrowed   = true;  // existing post image is always borrowed
        const originalFeaturedImagePath = currentFeaturedImagePath;
        let hasUnsavedChanges           = false;

        // ════════════════════════════════════════════════════════════════════
        // QUILL EDITOR
        // ════════════════════════════════════════════════════════════════════
        const toolbarOptions = [
            ['bold','italic','underline','strike'],
            ['blockquote','code-block'],
            ['link','image','video','formula'],
            [{'header':1},{'header':2}],
            [{'list':'ordered'},{'list':'bullet'},{'list':'check'}],
            [{'script':'sub'},{'script':'super'}],
            [{'indent':'-1'},{'indent':'+1'}],
            [{'direction':'rtl'}],
            [{'size':['small',false,'large','huge']}],
            [{'header':[1,2,3,4,5,6,false]}],
            [{'color':[]},{'background':[]}],
            [{'font':[]}],
            [{'align':[]}],
            ['clean']
        ];

        const quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: {
                    container: toolbarOptions,
                    handlers: { image: () => openEditorImageModal() }
                }
            },
            placeholder: 'Write something inspiring...',
        });

        window.quillEditor = quill;

        const initialBody = document.getElementById('body').value;
        if (initialBody) {
            quill.root.innerHTML = initialBody;
            // Pre-existing editor images are "borrowed" — don't track as session-owned
        }

        quill.on('text-change', () => {
            hasUnsavedChanges = true;
            document.getElementById('body').value = quill.root.innerHTML;
            updateWordCount();
        });

        function updateWordCount() {
            const text    = quill.getText();
            const words   = text.trim().split(/\s+/).filter(w => w.length > 0);
            const wCount  = words.length;
            const rTime   = Math.max(1, Math.ceil(wCount / 200));
            document.getElementById('wordCount').textContent    = wCount;
            document.getElementById('wordLabel').textContent    = wCount === 1 ? 'word' : 'words';
            document.getElementById('readTimeCount').textContent= rTime;
            document.getElementById('readTimeLabel').textContent= rTime === 1 ? 'minute' : 'minutes';
            document.getElementById('readTime').value           = rTime;
        }

        updateWordCount();

        // ════════════════════════════════════════════════════════════════════
        // IMAGE PATH HELPERS
        // ════════════════════════════════════════════════════════════════════
        function extractImagesFromBody(html) {
            const div = document.createElement('div');
            div.innerHTML = html;
            return Array.from(div.querySelectorAll('img')).map(img => {
                try { return new URL(img.src).pathname; }
                catch(e) { return img.src; }
            });
        }

        /** Only clean up images that were uploaded in THIS session and are no longer in the editor. */
        function cleanupOrphanEditorImages() {
            const currentEditorImages = extractImagesFromBody(quill.root.innerHTML);
            const orphaned = sessionUploadedEditorImages.filter(p => !currentEditorImages.includes(p));
            if (orphaned.length === 0) return;

            fetch('{{ route('admin.posts.cleanup-images') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ images: orphaned })
            })
                .then(r => r.json())
                .then(() => {
                    sessionUploadedEditorImages = sessionUploadedEditorImages.filter(p => !orphaned.includes(p));
                })
                .catch(() => {});
        }

        /**
         * Called only from removeImageBtn and from uploadFeaturedImage (replacing).
         * NEVER called on browse-select — browsed images are shared assets.
         */
        function deleteSessionOwnedFeaturedImage(path) {
            if (!path || !path.startsWith('/images/') || currentFeaturedIsBorrowed) return;
            if (path === originalFeaturedImagePath) return; // never delete the original draft image

            fetch('{{ route('admin.posts.cleanup-images') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ images: [path] })
            })
                .then(r => r.json())
                .catch(() => {});
        }

        // ════════════════════════════════════════════════════════════════════
        // PAGE ABANDON PROTECTION
        // Only cleans up session-uploaded images — never borrowed ones.
        // ════════════════════════════════════════════════════════════════════
        window.addEventListener('beforeunload', function(e) {
            if (!hasUnsavedChanges) return;

            const toClean = [
                ...sessionUploadedEditorImages,
                // Only include featured if it was session-uploaded (not borrowed, not the original draft image)
                ...(currentFeaturedImagePath
                && !currentFeaturedIsBorrowed
                && currentFeaturedImagePath !== originalFeaturedImagePath
                && currentFeaturedImagePath.startsWith('/images/')
                    ? [currentFeaturedImagePath]
                    : [])
            ];

            if (toClean.length > 0) {
                navigator.sendBeacon(
                    '{{ route('admin.posts.cleanup-images') }}',
                    new Blob([JSON.stringify({ _token: '{{ csrf_token() }}', images: toClean })], { type: 'application/json' })
                );
            }

            e.preventDefault();
            e.returnValue = '';
            return '';
        });

        // ════════════════════════════════════════════════════════════════════
        // AUTO-SAVE (edit: saves to HistoryPost with additional_info=2)
        // ════════════════════════════════════════════════════════════════════
        let autoSaveInterval = setInterval(autoSave, 30000);

        function autoSave() {
            cleanupOrphanEditorImages();
            const formData = new FormData(document.getElementById('postForm'));
            fetch(`/admin/posts/${postId}/auto-save`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            })
                .then(r => r.json())
                .then(data => { if (data.success) showMessage(data.message, 'success'); })
                .catch(() => {});
        }

        // ── Auto-save recovery check on page load ─────────────────────────
        fetch(`/admin/posts/${postId}/auto-save-check`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.has_auto_save) {
                    document.getElementById('autoSaveWarning')?.classList.remove('hidden');
                }
            })
            .catch(() => {});

        document.getElementById('restoreAutoSaveBtn')?.addEventListener('click', function() {
            fetch(`/admin/posts/${postId}/auto-save-check`, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    if (!data.has_auto_save) return;
                    const s = data.auto_save;
                    if (s.title)       document.getElementById('title').value   = s.title;
                    if (s.excerpt)     document.getElementById('excerpt').value = s.excerpt;
                    if (s.body)        { quill.root.innerHTML = s.body; document.getElementById('body').value = s.body; }
                    if (s.image_path)  {
                        document.getElementById('imagePath').value = s.image_path;
                        document.getElementById('imagePreviewImg').src = s.image_path;
                        document.getElementById('imagePreview').classList.remove('hidden');
                        currentFeaturedImagePath = s.image_path;
                        currentFeaturedIsBorrowed = true;
                    }
                    if (s.category_id)       document.getElementById('category_id').value = s.category_id;
                    if (s.meta_title)        document.getElementById('metaTitle').value = s.meta_title;
                    if (s.meta_description)  document.getElementById('metaDescription').value = s.meta_description;
                    document.getElementById('autoSaveWarning')?.classList.add('hidden');
                    hasUnsavedChanges = true;
                    showMessage('Auto-saved version restored. Review and save when ready.', 'success');
                })
                .catch(() => {});
        });

        document.getElementById('discardAutoSaveBtn')?.addEventListener('click', function() {
            fetch(`/admin/posts/${postId}/reject`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            })
                .then(r => r.json())
                .then(() => document.getElementById('autoSaveWarning')?.classList.add('hidden'))
                .catch(() => {});
        });

        document.getElementById('saveDraftBtn')?.addEventListener('click', autoSave);

        // ════════════════════════════════════════════════════════════════════
        // FORM SUBMIT
        // ════════════════════════════════════════════════════════════════════
        document.getElementById('postForm').addEventListener('submit', function(e) {
            e.preventDefault();
            clearInterval(autoSaveInterval);

            // ✅ Clear state SYNCHRONOUSLY before the fetch — if beforeunload
            //    fires during the network request, there is nothing to delete.
            hasUnsavedChanges = false;
            const savedEditorImages  = [...sessionUploadedEditorImages];
            const savedFeaturedPath  = currentFeaturedImagePath;
            const savedFeaturedBorrowed = currentFeaturedIsBorrowed;
            sessionUploadedEditorImages = [];
            currentFeaturedImagePath    = '';

            cleanupOrphanEditorImages();
            autoFillSeoFields();

            const formData     = new FormData(this);
            const publishBtn   = document.getElementById('updateBtn');
            const originalText = publishBtn.textContent;
            publishBtn.disabled    = true;
            publishBtn.textContent = 'Updating...';

            formData.append('_method', 'PUT');
            fetch(`/admin/posts/${postId}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            })
                .then(r => {
                    if (!r.headers.get('content-type')?.includes('application/json')) throw new Error('Non-JSON response');
                    return r.json().then(data => ({ ok: r.ok, status: r.status, data }));
                })
                .then(result => {
                    if (result.status === 422) {
                        // Restore state so user can keep editing
                        hasUnsavedChanges           = true;
                        sessionUploadedEditorImages = savedEditorImages;
                        currentFeaturedImagePath    = savedFeaturedPath;
                        currentFeaturedIsBorrowed   = savedFeaturedBorrowed;
                        showMessage('Please fix the errors below', 'error');
                        displayValidationErrors(result.data.errors);
                        publishBtn.disabled    = false;
                        publishBtn.textContent = originalText;
                    } else if (result.ok && result.data.success) {
                        showMessage(result.data.message, 'success');
                        setTimeout(() => { window.location.href = result.data.redirect; }, 1500);
                    } else {
                        // Restore state on server error
                        hasUnsavedChanges           = true;
                        sessionUploadedEditorImages = savedEditorImages;
                        currentFeaturedImagePath    = savedFeaturedPath;
                        currentFeaturedIsBorrowed   = savedFeaturedBorrowed;
                        showMessage(result.data.message || 'An error occurred', 'error');
                        publishBtn.disabled    = false;
                        publishBtn.textContent = originalText;
                    }
                })
                .catch(err => {
                    hasUnsavedChanges           = true;
                    sessionUploadedEditorImages = savedEditorImages;
                    currentFeaturedImagePath    = savedFeaturedPath;
                    currentFeaturedIsBorrowed   = savedFeaturedBorrowed;
                    showMessage('An error occurred while publishing', 'error');
                    publishBtn.disabled    = false;
                    publishBtn.textContent = originalText;
                });
        });

        // ════════════════════════════════════════════════════════════════════
        // FEATURED IMAGE HANDLING
        // ════════════════════════════════════════════════════════════════════
        const uploadModeBtn  = document.getElementById('uploadModeBtn');
        const urlModeBtn     = document.getElementById('urlModeBtn');
        const browseModeBtn  = document.getElementById('browseModeBtn');
        const uploadMode     = document.getElementById('uploadMode');
        const urlMode        = document.getElementById('urlMode');
        const imageFile      = document.getElementById('imageFile');
        const imagePreview   = document.getElementById('imagePreview');
        const imagePreviewImg= document.getElementById('imagePreviewImg');
        const removeImageBtn = document.getElementById('removeImageBtn');
        const imagePath      = document.getElementById('imagePath');
        const imageUrl       = document.getElementById('imageUrl');
        const setImageUrlBtn = document.getElementById('setImageUrlBtn');

        if (uploadModeBtn) uploadModeBtn.addEventListener('click', () => switchImageMode('upload'));
        if (urlModeBtn)    urlModeBtn.addEventListener('click', () => switchImageMode('url'));
        if (browseModeBtn) browseModeBtn.addEventListener('click', openBrowseImagesModal);

        function switchImageMode(mode) {
            uploadMode.classList.toggle('hidden', mode !== 'upload');
            urlMode.classList.toggle('hidden', mode !== 'url');
            const active = mode === 'upload' ? uploadModeBtn : urlModeBtn;
            const inactive = mode === 'upload' ? urlModeBtn : uploadModeBtn;
            active?.classList.add('bg-primary','border-primary-line','text-primary-foreground');
            active?.classList.remove('border-layer-line','text-muted-foreground-1');
            inactive?.classList.remove('bg-primary','border-primary-line','text-primary-foreground');
            inactive?.classList.add('border-layer-line','text-muted-foreground-1');
        }

        if (imageFile) {
            imageFile.addEventListener('change', function() {
                if (!this.files?.[0]) return;
                const file = this.files[0];
                document.getElementById('fileInputContainer').classList.add('hidden');
                document.getElementById('uploadHint').classList.add('hidden');
                const progressContainer = document.getElementById('fileUploadProgress');
                progressContainer.classList.remove('hidden');

                const reader = new FileReader();
                reader.onload = e => {
                    progressContainer.innerHTML = `
                        <div class="mb-3 flex justify-between items-center">
                            <div class="flex items-center gap-x-3">
                                <span class="size-10 flex justify-center items-center border border-layer-line text-muted-foreground-1 rounded-lg bg-layer">
                                    <svg class="shrink-0 size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-foreground truncate">${file.name}</p>
                                    <p class="text-xs text-muted-foreground-1">${(file.size/1024).toFixed(2)} KB</p>
                                </div>
                            </div>
                            <div class="inline-flex items-center gap-x-2">
                                <button type="button" id="confirmUpload" class="text-green-600 hover:text-green-800">
                                    <svg class="shrink-0 size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button" id="cancelUpload" class="text-red-500 hover:text-red-700">
                                    <svg class="shrink-0 size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="mt-3">
                            <img src="${e.target.result}" class="max-w-full h-48 object-cover rounded-lg border border-layer-line">
                        </div>`;
                    document.getElementById('confirmUpload').addEventListener('click', uploadFeaturedImage);
                    document.getElementById('cancelUpload').addEventListener('click', cancelImageUpload);
                };
                reader.readAsDataURL(file);
            });
        }

        function uploadFeaturedImage() {
            const file = imageFile.files[0];
            const formData = new FormData();
            formData.append('image', file);

            fetch('{{ route('admin.posts.upload-image') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // ✅ If replacing a session-uploaded image (not borrowed), delete the old one
                        if (currentFeaturedImagePath && !currentFeaturedIsBorrowed
                            && currentFeaturedImagePath !== originalFeaturedImagePath
                            && currentFeaturedImagePath.startsWith('/images/')) {
                            deleteSessionOwnedFeaturedImage(currentFeaturedImagePath);
                        }

                        // Register new upload as session-owned
                        sessionUploadedImages.push(data.path);
                        currentFeaturedImagePath  = data.path;
                        currentFeaturedIsBorrowed = false;

                        imagePath.value       = data.path;
                        imagePreviewImg.src   = data.path;
                        imagePreview.classList.remove('hidden');
                        cancelImageUpload();
                        showImageMessage(data.message, 'success');
                        autoFillSeoFields();
                    }
                })
                .catch(() => {
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
                if (!url) { showImageMessage('Please enter an image URL', 'error'); return; }

                // External URL — treat as borrowed (don't delete)
                if (currentFeaturedImagePath && !currentFeaturedIsBorrowed
                    && currentFeaturedImagePath !== originalFeaturedImagePath
                    && currentFeaturedImagePath.startsWith('/images/')) {
                    deleteSessionOwnedFeaturedImage(currentFeaturedImagePath);
                }

                currentFeaturedImagePath  = url;
                currentFeaturedIsBorrowed = !url.startsWith('/images/'); // external URLs are always borrowed
                imagePath.value       = url;
                imagePreviewImg.src   = url;
                imagePreview.classList.remove('hidden');
                showImageMessage('Image URL set successfully!', 'success');
                autoFillSeoFields();
            });
        }

        if (removeImageBtn) {
            removeImageBtn.addEventListener('click', () => {
                // ✅ Only delete if this was a session-uploaded image, NEVER a browsed one
                if (!currentFeaturedIsBorrowed
                    && currentFeaturedImagePath
                    && currentFeaturedImagePath.startsWith('/images/')
                    && currentFeaturedImagePath !== originalFeaturedImagePath) {
                    deleteSessionOwnedFeaturedImage(currentFeaturedImagePath);
                }

                currentFeaturedImagePath  = '';
                currentFeaturedIsBorrowed = false;
                imagePath.value       = '';
                imagePreviewImg.src   = '';
                imagePreview.classList.add('hidden');
                imageFile.value       = '';
                imageUrl.value        = '';
                showImageMessage('Image removed', 'success');
            });
        }

        function showImageMessage(message, type) {
            const successEl = document.getElementById('imageSuccess');
            const errorEl   = document.getElementById('imageError');
            if (type === 'success') {
                if (successEl) { successEl.textContent = message; successEl.classList.remove('hidden'); }
                if (errorEl) errorEl.classList.add('hidden');
                setTimeout(() => successEl?.classList.add('hidden'), 3000);
            } else {
                if (errorEl) { errorEl.textContent = message; errorEl.classList.remove('hidden'); }
                if (successEl) successEl.classList.add('hidden');
                setTimeout(() => errorEl?.classList.add('hidden'), 3000);
            }
        }

        // ════════════════════════════════════════════════════════════════════
        // BROWSE IMAGES MODAL (Featured Image)
        // ════════════════════════════════════════════════════════════════════
        function openBrowseImagesModal() {
            window.HSOverlay.open('#browseImagesModal');
            loadBrowseImages();
        }

        function closeBrowseImagesModal() {
            window.HSOverlay.close('#browseImagesModal');
        }

        function loadBrowseImages() {
            const loading = document.getElementById('browseLoading');
            const grid    = document.getElementById('browseGrid');
            const empty   = document.getElementById('browseEmpty');

            if (loading) loading.classList.remove('hidden');
            if (grid)    grid.classList.add('hidden');
            if (empty)   empty.classList.add('hidden');

            fetch('{{ route('admin.posts.browse-images') }}')
                .then(r => r.json())
                .then(data => {
                    if (loading) loading.classList.add('hidden');
                    if (data.success && data.images.length > 0) {
                        if (grid) {
                            grid.classList.remove('hidden');
                            grid.innerHTML = data.images.map(image => `
                            <div class="border border-layer-line rounded-lg overflow-hidden cursor-pointer hover:ring-2 hover:ring-primary transition-all duration-200 browse-image relative" data-path="${image.path}">
                                <img src="${image.path}" alt="${image.name}" class="w-full h-40 object-cover bg-layer" loading="lazy">
                                <div class="p-2 bg-layer">
                                    <p class="text-xs font-medium truncate text-foreground">${image.name}</p>
                                    <p class="text-xs text-muted-foreground-1">${(image.size/1024).toFixed(1)} KB</p>
                                </div>
                                ${image.usage_count > 0
                                ? `<span class="absolute top-1 right-1 text-xs bg-blue-600 text-white px-1.5 py-0.5 rounded-full font-medium">Used: ${image.usage_count}</span>`
                                : ''
                            }
                            </div>`).join('');

                            document.querySelectorAll('.browse-image').forEach(el => {
                                el.addEventListener('click', function() {
                                    // ✅ NEVER delete anything here — just select
                                    currentFeaturedImagePath  = this.dataset.path;
                                    currentFeaturedIsBorrowed = true; // it's a shared asset

                                    imagePath.value       = this.dataset.path;
                                    imagePreviewImg.src   = this.dataset.path;
                                    imagePreview.classList.remove('hidden');
                                    closeBrowseImagesModal();
                                    showImageMessage('Image selected from storage!', 'success');
                                    autoFillSeoFields();
                                });
                            });
                        }
                    } else {
                        if (empty) empty.classList.remove('hidden');
                    }
                })
                .catch(() => {
                    if (loading) loading.classList.add('hidden');
                    if (empty)   empty.classList.remove('hidden');
                });
        }

        // ════════════════════════════════════════════════════════════════════
        // EDITOR IMAGE MODAL
        // ════════════════════════════════════════════════════════════════════
        const editorImageFile    = document.getElementById('editorImageFile');
        const editorImageUrl     = document.getElementById('editorImageUrl');

        function openEditorImageModal() {
            window.HSOverlay.open('#editorImageModal');
            switchEditorMode('upload');
        }

        function closeEditorImageModal() {
            window.HSOverlay.close('#editorImageModal');
            if (editorImageFile) editorImageFile.value = '';
            if (editorImageUrl)  editorImageUrl.value  = '';
            document.getElementById('editorFileInputContainer')?.classList.remove('hidden');
            document.getElementById('editorUploadHint')?.classList.remove('hidden');
            document.getElementById('editorFileUploadProgress')?.classList.add('hidden');
            document.getElementById('editorUrlPreview')?.classList.add('hidden');
        }

        document.getElementById('editorUploadModeBtn')?.addEventListener('click', () => switchEditorMode('upload'));
        document.getElementById('editorUrlModeBtn')?.addEventListener('click',    () => switchEditorMode('url'));
        document.getElementById('editorBrowseModeBtn')?.addEventListener('click', () => switchEditorMode('browse'));

        function switchEditorMode(mode) {
            ['editorUploadMode','editorUrlMode','editorBrowseMode'].forEach(id => document.getElementById(id)?.classList.add('hidden'));
            ['editorUploadModeBtn','editorUrlModeBtn','editorBrowseModeBtn'].forEach(id => {
                const btn = document.getElementById(id);
                if (btn) { btn.classList.remove('bg-primary','border-primary-line','text-primary-foreground'); btn.classList.add('border-layer-line','text-muted-foreground-1'); }
            });
            const modeMap = { upload: 'editorUploadMode', url: 'editorUrlMode', browse: 'editorBrowseMode' };
            const btnMap  = { upload: 'editorUploadModeBtn', url: 'editorUrlModeBtn', browse: 'editorBrowseModeBtn' };
            document.getElementById(modeMap[mode])?.classList.remove('hidden');
            const activeBtn = document.getElementById(btnMap[mode]);
            if (activeBtn) { activeBtn.classList.add('bg-primary','border-primary-line','text-primary-foreground'); activeBtn.classList.remove('border-layer-line','text-muted-foreground-1'); }
            if (mode === 'browse') loadEditorBrowseImages();
        }

        if (editorImageFile) {
            editorImageFile.addEventListener('change', function() {
                if (!this.files?.[0]) return;
                const file = this.files[0];
                document.getElementById('editorFileInputContainer').classList.add('hidden');
                document.getElementById('editorUploadHint').classList.add('hidden');
                const progressContainer = document.getElementById('editorFileUploadProgress');
                progressContainer.classList.remove('hidden');

                const reader = new FileReader();
                reader.onload = e => {
                    progressContainer.innerHTML = `
                        <div class="mb-3 flex justify-between items-center">
                            <div class="flex items-center gap-x-3">
                                <span class="size-8 flex justify-center items-center border border-layer-line text-muted-foreground-1 rounded-lg bg-layer">
                                    <svg class="shrink-0 size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-foreground truncate">${file.name}</p>
                                    <p class="text-xs text-muted-foreground-1">${(file.size/1024).toFixed(2)} KB</p>
                                </div>
                            </div>
                            <button type="button" id="editorCancelUpload" class="text-muted-foreground-1 hover:text-red-600">
                                <svg class="shrink-0 size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                        <div class="flex items-center gap-x-3 whitespace-nowrap">
                            <div class="flex w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div id="editorUploadProgress" class="flex flex-col justify-center rounded-full overflow-hidden bg-primary transition-all duration-100" style="width:0%"></div>
                            </div>
                            <span id="editorUploadPercent" class="text-sm text-foreground w-10 text-end">0%</span>
                        </div>
                        <div class="mt-3">
                            <p class="text-xs text-muted-foreground-1 mb-2">Preview:</p>
                            <img src="${e.target.result}" class="max-w-full h-64 object-contain rounded-lg border border-layer-line mx-auto bg-layer">
                        </div>
                        <div class="flex gap-2 justify-end mt-4">
                            <button type="button" id="editorCancelUploadBtn" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-layer border border-layer-line text-layer-foreground shadow-2xs hover:bg-layer-hover">Cancel</button>
                            <button type="button" id="editorInsertUpload"    class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-primary border border-primary-line text-primary-foreground hover:bg-primary-hover">Insert Image</button>
                        </div>`;
                    document.getElementById('editorInsertUpload')?.addEventListener('click', uploadEditorImage);
                    document.getElementById('editorCancelUploadBtn')?.addEventListener('click', cancelEditorUpload);
                    document.getElementById('editorCancelUpload')?.addEventListener('click', cancelEditorUpload);
                };
                reader.readAsDataURL(file);
            });
        }

        function uploadEditorImage() {
            const file = editorImageFile.files[0];
            const formData = new FormData();
            formData.append('image', file);

            // ✅ Real XHR progress — no fake timer
            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', e => {
                if (e.lengthComputable) {
                    const pct = Math.round((e.loaded / e.total) * 100);
                    const bar = document.getElementById('editorUploadProgress');
                    const pct_el = document.getElementById('editorUploadPercent');
                    if (bar)    bar.style.width  = pct + '%';
                    if (pct_el) pct_el.textContent = pct + '%';
                }
            });

            xhr.addEventListener('load', () => {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        // Register as session-owned — can be cleaned up on abandon
                        sessionUploadedEditorImages.push(data.path);
                        insertImageIntoEditor(data.path);
                        closeEditorImageModal();
                        showMessage(data.message, 'success');
                    } else {
                        showMessage('Upload failed. Please try again.', 'error');
                    }
                } catch(err) {
                    showMessage('Upload failed. Please try again.', 'error');
                }
            });

            xhr.addEventListener('error', () => showMessage('Upload failed. Please try again.', 'error'));

            xhr.open('POST', '{{ route('admin.posts.upload-editor-image') }}');
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
            xhr.send(formData);
        }

        function cancelEditorUpload() {
            document.getElementById('editorFileInputContainer')?.classList.remove('hidden');
            document.getElementById('editorUploadHint')?.classList.remove('hidden');
            document.getElementById('editorFileUploadProgress')?.classList.add('hidden');
            if (editorImageFile) editorImageFile.value = '';
        }

        if (editorImageUrl) {
            editorImageUrl.addEventListener('input', function() {
                const preview    = document.getElementById('editorUrlPreview');
                const previewImg = document.getElementById('editorUrlPreviewImg');
                if (this.value.trim()) {
                    preview?.classList.remove('hidden');
                    if (previewImg) previewImg.src = this.value.trim();
                } else {
                    preview?.classList.add('hidden');
                }
            });
        }

        document.getElementById('cancelEditorUrl')?.addEventListener('click', closeEditorImageModal);
        document.getElementById('insertEditorUrl')?.addEventListener('click', () => {
            const url = editorImageUrl?.value.trim();
            if (!url) return;
            // External URL images in editor: don't track as session-owned
            insertImageIntoEditor(url);
            closeEditorImageModal();
            showMessage('Image inserted successfully!', 'success');
        });

        function insertImageIntoEditor(url) {
            if (!window.quillEditor) return;
            const range = window.quillEditor.getSelection();
            const index = range ? range.index : window.quillEditor.getLength();
            window.quillEditor.insertEmbed(index, 'image', url);
            window.quillEditor.setSelection(index + 1);
            document.getElementById('body').value = window.quillEditor.root.innerHTML;
        }

        function loadEditorBrowseImages() {
            const loading = document.getElementById('editorBrowseLoading');
            const grid    = document.getElementById('editorBrowseGrid');
            const empty   = document.getElementById('editorBrowseEmpty');

            if (loading) loading.classList.remove('hidden');
            if (grid)    grid.classList.add('hidden');
            if (empty)   empty.classList.add('hidden');

            fetch('{{ route('admin.posts.browse-editor-images') }}')
                .then(r => r.json())
                .then(data => {
                    if (loading) loading.classList.add('hidden');
                    if (data.success && data.images.length > 0) {
                        if (grid) {
                            grid.classList.remove('hidden');
                            grid.innerHTML = data.images.map(image => `
                            <div class="border border-layer-line rounded-lg overflow-hidden cursor-pointer hover:ring-2 hover:ring-primary transition-all duration-200 editor-browse-image relative" data-path="${image.path}">
                                <img src="${image.path}" alt="${image.name}" class="w-full h-32 object-cover bg-layer" loading="lazy">
                                <div class="p-2 bg-layer">
                                    <p class="text-xs font-medium truncate text-foreground">${image.name}</p>
                                    <p class="text-xs text-muted-foreground-1">${(image.size/1024).toFixed(1)} KB</p>
                                </div>
                                ${image.usage_count > 0
                                ? `<span class="absolute top-1 right-1 text-xs bg-blue-600 text-white px-1.5 py-0.5 rounded-full font-medium">Used: ${image.usage_count}</span>`
                                : ''
                            }
                            </div>`).join('');

                            document.querySelectorAll('.editor-browse-image').forEach(el => {
                                el.addEventListener('click', function() {
                                    // Browsed images are shared — never track as session-owned
                                    insertImageIntoEditor(this.dataset.path);
                                    closeEditorImageModal();
                                    showMessage('Image inserted from storage!', 'success');
                                });
                            });
                        }
                    } else {
                        if (empty) empty.classList.remove('hidden');
                    }
                })
                .catch(() => {
                    if (loading) loading.classList.add('hidden');
                    if (empty)   empty.classList.remove('hidden');
                });
        }

        // ════════════════════════════════════════════════════════════════════
        // SCHEDULING
        // ════════════════════════════════════════════════════════════════════
        const useScheduling       = document.getElementById('useScheduling');
        const schedulingInputs    = document.getElementById('schedulingInputs');
        const scheduledAt         = document.getElementById('scheduledAt');
        const scheduledAtPreview  = document.getElementById('scheduledAtPreview');
        const isPublishedCheckbox = document.getElementById('is_published');

        useScheduling?.addEventListener('change', function() {
            schedulingInputs?.classList.toggle('hidden', !this.checked);
            if (!this.checked) { scheduledAt && (scheduledAt.value=''); scheduledAtPreview?.classList.add('hidden'); }
            if (isPublishedCheckbox) { isPublishedCheckbox.disabled = this.checked; if (this.checked) isPublishedCheckbox.checked = false; }
        });

        scheduledAt?.addEventListener('change', function() {
            if (!this.value) { scheduledAtPreview?.classList.add('hidden'); return; }
            const date = new Date(this.value), now = new Date(), diff = date - now;
            const days  = Math.floor(diff/86400000), hours = Math.floor((diff%86400000)/3600000);
            let preview = 'Will be published ';
            if (days > 0) preview += `in ${days} day${days>1?'s':''}`;
            else if (hours > 0) preview += `in ${hours} hour${hours>1?'s':''}`;
            else preview += 'soon';
            preview += ` (${date.toLocaleDateString()} at ${date.toLocaleTimeString()})`;
            const span = scheduledAtPreview?.querySelector('span');
            if (span) span.textContent = preview;
            scheduledAtPreview?.classList.remove('hidden');
        });

        const useExpiration      = document.getElementById('useExpiration');
        const expirationInputs   = document.getElementById('expirationInputs');
        const expiresAt          = document.getElementById('expiresAt');
        const expiresAtPreview   = document.getElementById('expiresAtPreview');

        useExpiration?.addEventListener('change', function() {
            expirationInputs?.classList.toggle('hidden', !this.checked);
            if (!this.checked) { expiresAt && (expiresAt.value=''); expiresAtPreview?.classList.add('hidden'); }
        });

        expiresAt?.addEventListener('change', function() {
            if (!this.value) { expiresAtPreview?.classList.add('hidden'); return; }
            const date = new Date(this.value), now = new Date(), diff = date - now;
            const days  = Math.floor(diff/86400000), hours = Math.floor((diff%86400000)/3600000);
            let preview = 'Will expire ';
            if (days > 0) preview += `in ${days} day${days>1?'s':''}`;
            else if (hours > 0) preview += `in ${hours} hour${hours>1?'s':''}`;
            else preview += 'soon';
            preview += ` (${date.toLocaleDateString()} at ${date.toLocaleTimeString()})`;
            const span = expiresAtPreview?.querySelector('span');
            if (span) span.textContent = preview;
            expiresAtPreview?.classList.remove('hidden');
        });

        // ════════════════════════════════════════════════════════════════════
        // SEO AUTO-FILL
        // ════════════════════════════════════════════════════════════════════
        const autoFillSeoCheckbox  = document.getElementById('autoFillSeo');
        let autoFillSeo = autoFillSeoCheckbox ? autoFillSeoCheckbox.checked : true;
        autoFillSeoCheckbox?.addEventListener('change', function() { autoFillSeo = this.checked; autoFillSeoFields(); });

        function autoFillSeoFields() {
            if (!autoFillSeo) return;
            const title     = document.getElementById('title')?.value || '';
            const excerpt   = document.getElementById('excerpt')?.value || '';
            const imgPath   = document.getElementById('imagePath')?.value || '';
            const metaTitle = document.getElementById('metaTitle');
            const metaDesc  = document.getElementById('metaDescription');
            if (title  && metaTitle) { metaTitle.value = title.substring(0,80);    updateCharCount('metaTitle', metaTitle.value.length); }
            if (excerpt && metaDesc) { metaDesc.value  = excerpt.substring(0,160); updateCharCount('metaDescription', metaDesc.value.length); }
            const ogTitle = document.getElementById('ogTitle'); if (ogTitle) { ogTitle.value = metaTitle?.value || title.substring(0,80); updateCharCount('ogTitle', ogTitle.value.length); }
            const ogDesc  = document.getElementById('ogDescription'); if (ogDesc) { ogDesc.value = metaDesc?.value || excerpt.substring(0,160); updateCharCount('ogDescCount', ogDesc.value.length); }
            const ogImg   = document.getElementById('ogImage'); if (ogImg && imgPath) ogImg.value = imgPath;
            const twTitle = document.getElementById('twitterTitle'); if (twTitle) { twTitle.value = ogTitle?.value || ''; updateCharCount('twitterTitle', twTitle.value.length); }
            const twDesc  = document.getElementById('twitterDescription'); if (twDesc) { twDesc.value = ogDesc?.value || ''; updateCharCount('twitterDescCount', twDesc.value.length); }
            const twImg   = document.getElementById('twitterImage'); if (twImg) twImg.value = ogImg?.value || '';
            updateSeoPreview();
        }

        function updateSeoPreview() {
            const title     = document.getElementById('title')?.value || '';
            const metaTitle = document.getElementById('metaTitle')?.value || '';
            const metaDesc  = document.getElementById('metaDescription')?.value || '';
            const excerpt   = document.getElementById('excerpt')?.value || '';
            const seoPreview = document.getElementById('seoPreview');
            if (title && (metaDesc || excerpt)) {
                seoPreview?.classList.remove('hidden');
                const el = id => document.getElementById(id);
                if (el('previewTitle'))       el('previewTitle').textContent       = metaTitle || title;
                if (el('previewUrl'))         el('previewUrl').textContent         = '{{ url('/post/') }}/' + slugify(title);
                if (el('previewDescription')) el('previewDescription').textContent = (metaDesc || excerpt).substring(0,160);
            } else {
                seoPreview?.classList.add('hidden');
            }
        }

        function slugify(text) {
            return text.toLowerCase().replace(/[^\w\s-]/g,'').replace(/\s+/g,'-').replace(/--+/g,'-').trim();
        }

        function updateCharCount(fieldId, count) {
            const countEl   = document.getElementById(fieldId + 'Count');
            const warningEl = document.getElementById(fieldId + 'Warning');
            if (countEl) countEl.textContent = count;
            const limits = { metaTitle:50, metaDescription:150, ogTitle:50, ogDescription:150, twitterTitle:50, twitterDescription:150 };
            if (warningEl) warningEl.classList.toggle('hidden', !limits[fieldId] || count <= limits[fieldId]);
        }

        // SEO field listeners
        const titleInput  = document.getElementById('title');
        const excerptInput= document.getElementById('excerpt');
        titleInput?.addEventListener('input', function() { hasUnsavedChanges=true; updateCharCount('title',this.value.length); if(autoFillSeo)autoFillSeoFields(); updateSeoPreview(); });
        excerptInput?.addEventListener('input', function() { hasUnsavedChanges=true; updateCharCount('excerpt',this.value.length); if(autoFillSeo)autoFillSeoFields(); updateSeoPreview(); });
        document.getElementById('metaTitle')?.addEventListener('input', function() { updateCharCount('metaTitle',this.value.length); updateSeoPreview(); });
        document.getElementById('metaDescription')?.addEventListener('input', function() { updateCharCount('metaDescription',this.value.length); updateSeoPreview(); });
        ['focusKeyword','imageAlt','ogTitle','ogDescription','ogImage','twitterTitle','twitterDescription','twitterImage'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', function() { updateCharCount(id.charAt(0).toLowerCase()+id.slice(1), this.value.length); updateSeoPreview(); });
        });

        // Init counts
        updateCharCount('title',   titleInput?.value.length  || 0);
        updateCharCount('excerpt', excerptInput?.value.length || 0);
        updateCharCount('metaTitle',       document.getElementById('metaTitle')?.value.length       || 0);
        updateCharCount('metaDescription', document.getElementById('metaDescription')?.value.length || 0);
        updateSeoPreview();

        // ════════════════════════════════════════════════════════════════════
        // HELPERS
        // ════════════════════════════════════════════════════════════════════
        function showMessage(message, type) {
            const flash = document.getElementById('flashMessage');
            if (!flash) return;
            flash.className = `max-w-4xl mb-4 p-3 rounded-lg ${type === 'success' ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'}`;
            flash.textContent = message;
            flash.classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
            setTimeout(() => flash.classList.add('hidden'), 5000);
        }

        /**
         * Maps Laravel snake_case error keys to camelCase element IDs.
         * e.g. "meta_title" → looks for id="metaTitle" first, then "meta_title".
         */
        function displayValidationErrors(errors) {
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            document.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

            const toCamel = s => s.replace(/_([a-z])/g, (_,c) => c.toUpperCase());

            Object.keys(errors).forEach(field => {
                const camelField = toCamel(field);
                const errorEl    = document.querySelector(`.error-message[data-field="${camelField}"]`)
                    || document.querySelector(`.error-message[data-field="${field}"]`);
                const inputEl    = document.getElementById(camelField) || document.getElementById(field);
                if (errorEl) errorEl.textContent = errors[field][0];
                if (inputEl) inputEl.classList.add('border-red-500');
            });

            document.querySelector('.error-message:not(:empty)')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
</script>
