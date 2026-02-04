<x-app-layout>
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-4 mb-2">
                <a href="{{ route('admin.users.index') }}"
                   class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit User</h1>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Update user information and permissions</p>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        @if (session('imageMessage'))
            <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg">
                {{ session('imageMessage') }}
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" id="user-edit-form" class="max-w-2xl">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-6">

                <!-- Avatar Upload Section -->
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Avatar</label>

                    <!-- Current Avatar Preview -->
                    <div id="current-avatar-container" class="mb-4">
                        <div class="relative inline-block" id="avatar-preview-wrapper">
                            <img src="{{ old('image_path', $user->image_path) ?? '/images/user.jpg' }}"
                                 alt="{{ $user->firstname }}"
                                 id="avatar-preview"
                                 class="w-32 h-32 rounded-full object-cover border-4 border-gray-200 dark:border-gray-700">
                            @if($user->image_path && !str_contains($user->image_path, 'default-avatar') && !str_contains($user->image_path, 'user.jpg'))
                                <button
                                    type="button"
                                    id="remove-avatar-btn"
                                    class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-2 hover:bg-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Upload Mode Toggle -->
                    <div class="flex gap-2 mb-4">
                        <button
                            type="button"
                            id="upload-mode-btn"
                            class="px-4 py-2 rounded bg-blue-600 text-white">
                            📤 Upload File
                        </button>
                        <button
                            type="button"
                            id="url-mode-btn"
                            class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                            🔗 Use URL
                        </button>
                    </div>

                    <!-- File Upload Mode -->
                    <div id="upload-mode" class="space-y-3">
                        <div>
                            <input
                                type="file"
                                id="avatar-file-input"
                                accept="image/*"
                                class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                    dark:file:bg-blue-900 dark:file:text-blue-200">
                            <div id="upload-error" class="text-red-600 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Live Preview -->
                        <div id="upload-preview" class="hidden">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Preview:</p>
                            <img id="upload-preview-img" class="w-32 h-32 rounded-full object-cover border-4 border-gray-200 dark:border-gray-700">
                            <button
                                type="button"
                                id="confirm-upload-btn"
                                class="mt-3 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                                ✓ Confirm Upload
                            </button>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Supported formats: JPG, PNG, GIF, WebP (Max 5MB)
                        </p>
                    </div>

                    <!-- URL Input Mode -->
                    <div id="url-mode" class="hidden space-y-3">
                        <div class="flex gap-2">
                            <input
                                type="text"
                                id="avatar-url-input"
                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                placeholder="Enter avatar URL (e.g., /images/avatars/example.jpg or https://...)">
                            <button
                                type="button"
                                id="set-avatar-url-btn"
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                Set Avatar
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            You can use a full URL or a path to an existing image in your storage
                        </p>
                    </div>

                    <!-- Hidden input to store final avatar path -->
                    <input type="hidden" id="image_path" name="image_path" value="{{ old('image_path', $user->image_path) }}">
                </div>

                <!-- First Name -->
                <div>
                    <label for="firstname" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="firstname"
                        name="firstname"
                        value="{{ old('firstname', $user->firstname) }}"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('firstname') border-red-500 @enderror"
                        required>
                    @error('firstname')
                    <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Last Name -->
                <div>
                    <label for="lastname" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="lastname"
                        name="lastname"
                        value="{{ old('lastname', $user->lastname) }}"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('lastname') border-red-500 @enderror"
                        required>
                    @error('lastname')
                    <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('email') border-red-500 @enderror"
                        required>
                    @error('email')
                    <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        New Password
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('password') border-red-500 @enderror">
                    @error('password')
                    <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Leave blank to keep current password. Minimum 8 characters if changing.
                    </p>
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Confirm Password
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('password_confirmation') border-red-500 @enderror">
                    @error('password_confirmation')
                    <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Roles -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Roles <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2">
                        @foreach($roles as $key => $role)
                            <label class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    name="roles[]"
                                    value="{{ $key }}"
                                    {{ in_array($key, old('roles', $user->roles->pluck('name')->toArray())) ? 'checked' : '' }}
                                    class="form-checkbox h-4 w-4 text-blue-600 rounded focus:ring-blue-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $role }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles')
                    <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Send Email -->
                <div>
                    <label class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="send_mail"
                            value="1"
                            {{ old('send_mail') ? 'checked' : '' }}
                            class="form-checkbox h-4 w-4 text-blue-600 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Send update email to user</span>
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        <i class="fa-solid fa-save mr-2"></i>Update User
                    </button>

                    <a href="{{ route('admin.users.index') }}"
                       class="px-6 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg transition">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            (function() {
                'use strict';

                const CONFIG = {
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    uploadAvatarUrl: '{{ route("admin.users.upload-avatar") }}',
                    deleteAvatarUrl: '{{ route("admin.users.delete-avatar") }}',
                    cleanupAvatarsUrl: '{{ route("admin.users.cleanup-avatars") }}',
                };

                const elements = {
                    uploadModeBtn: document.getElementById('upload-mode-btn'),
                    urlModeBtn: document.getElementById('url-mode-btn'),
                    uploadMode: document.getElementById('upload-mode'),
                    urlMode: document.getElementById('url-mode'),
                    avatarFileInput: document.getElementById('avatar-file-input'),
                    uploadPreview: document.getElementById('upload-preview'),
                    uploadPreviewImg: document.getElementById('upload-preview-img'),
                    confirmUploadBtn: document.getElementById('confirm-upload-btn'),
                    avatarUrlInput: document.getElementById('avatar-url-input'),
                    setAvatarUrlBtn: document.getElementById('set-avatar-url-btn'),
                    avatarPreview: document.getElementById('avatar-preview'),
                    imagePathInput: document.getElementById('image_path'),
                    removeAvatarBtn: document.getElementById('remove-avatar-btn'),
                    uploadError: document.getElementById('upload-error'),
                };

                let currentMode = 'upload';
                let selectedFile = null;
                let uploadedAvatars = []; // Track uploaded avatars
                let currentAvatarPath = '{{ old("image_path", $user->image_path) }}'; // ✅ Use old() for validation failures
                let hasUnsavedChanges = false;

                /**
                 * Initialize
                 */
                function init() {
                    setupEventListeners();
                    setupPageAbandonProtection();
                }

                /**
                 * Setup page abandon protection (like PostController)
                 */
                function setupPageAbandonProtection() {
                    window.addEventListener('beforeunload', function(e) {
                        if (hasUnsavedChanges && uploadedAvatars.length > 0) {
                            // Cleanup uploaded avatars that weren't saved
                            const cleanupData = JSON.stringify({
                                _token: CONFIG.csrfToken,
                                images: uploadedAvatars
                            });

                            navigator.sendBeacon(
                                CONFIG.cleanupAvatarsUrl,
                                new Blob([cleanupData], {type: 'application/json'})
                            );

                            e.preventDefault();
                            e.returnValue = '';
                            return '';
                        }
                    });

                    // Clear flag when form is submitted
                    document.getElementById('user-edit-form').addEventListener('submit', function() {
                        hasUnsavedChanges = false;
                        uploadedAvatars = []; // Don't cleanup on save
                    });
                }

                /**
                 * Setup event listeners
                 */
                function setupEventListeners() {
                    // Mode toggle buttons
                    elements.uploadModeBtn?.addEventListener('click', () => switchMode('upload'));
                    elements.urlModeBtn?.addEventListener('click', () => switchMode('url'));

                    // File input
                    elements.avatarFileInput?.addEventListener('change', handleFileSelect);

                    // Confirm upload
                    elements.confirmUploadBtn?.addEventListener('click', uploadAvatar);

                    // URL input
                    elements.setAvatarUrlBtn?.addEventListener('click', setAvatarFromUrl);

                    // Remove avatar
                    elements.removeAvatarBtn?.addEventListener('click', removeAvatar);
                }

                /**
                 * Switch upload mode
                 */
                function switchMode(mode) {
                    currentMode = mode;

                    if (mode === 'upload') {
                        elements.uploadModeBtn.classList.remove('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
                        elements.uploadModeBtn.classList.add('bg-blue-600', 'text-white');
                        elements.urlModeBtn.classList.remove('bg-blue-600', 'text-white');
                        elements.urlModeBtn.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
                        elements.uploadMode.classList.remove('hidden');
                        elements.urlMode.classList.add('hidden');
                    } else {
                        elements.urlModeBtn.classList.remove('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
                        elements.urlModeBtn.classList.add('bg-blue-600', 'text-white');
                        elements.uploadModeBtn.classList.remove('bg-blue-600', 'text-white');
                        elements.uploadModeBtn.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
                        elements.urlMode.classList.remove('hidden');
                        elements.uploadMode.classList.add('hidden');
                    }
                }

                /**
                 * Handle file select
                 */
                function handleFileSelect(e) {
                    const file = e.target.files[0];

                    if (!file) return;

                    // Validate file type
                    if (!file.type.match('image.*')) {
                        showError('Please select an image file');
                        return;
                    }

                    // Validate file size (5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        showError('File size must be less than 5MB');
                        return;
                    }

                    selectedFile = file;
                    hideError();
                    hasUnsavedChanges = true;

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        elements.uploadPreviewImg.src = e.target.result;
                        elements.uploadPreview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }

                /**
                 * Upload avatar
                 */
                function uploadAvatar() {
                    if (!selectedFile) return;

                    const formData = new FormData();
                    formData.append('image', selectedFile);
                    formData.append('user_id', {{ $user->id }});

                    elements.confirmUploadBtn.disabled = true;
                    elements.confirmUploadBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Uploading...';

                    fetch(CONFIG.uploadAvatarUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                        .then(response => {
                            console.log('Response status:', response.status); // ✅ Log status
                            console.log('Response headers:', response.headers); // ✅ Log headers
                            return response.json();
                        })
                        .then(data => {
                            console.log('Response data:', data); // ✅ Log actual data

                            if (data.success) {
                                uploadedAvatars.push(data.path);
                                elements.avatarPreview.src = data.path;
                                elements.imagePathInput.value = data.path;
                                currentAvatarPath = data.path;
                                elements.avatarFileInput.value = '';
                                elements.uploadPreview.classList.add('hidden');
                                selectedFile = null;
                                showNotification(data.message, 'success');
                            } else {
                                showError(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Fetch error:', error); // ✅ Log fetch errors
                            showError('Failed to upload avatar. Please try again.');
                        })
                        .finally(() => {
                            elements.confirmUploadBtn.disabled = false;
                            elements.confirmUploadBtn.innerHTML = '✓ Confirm Upload';
                        });
                }

                /**
                 * Set avatar from URL
                 */
                function setAvatarFromUrl() {
                    const url = elements.avatarUrlInput.value.trim();

                    if (!url) {
                        showError('Please enter a URL');
                        return;
                    }

                    hasUnsavedChanges = true;

                    // Update avatar preview
                    elements.avatarPreview.src = url;
                    elements.imagePathInput.value = url;
                    currentAvatarPath = url;

                    // Clear input
                    elements.avatarUrlInput.value = '';

                    showNotification('Avatar URL set successfully!', 'success');
                }

                /**
                 * Remove avatar
                 */
                function removeAvatar() {
                    if (!confirm('Are you sure you want to remove this avatar?')) {
                        return;
                    }

                    hasUnsavedChanges = true;

                    // Set to default avatar
                    elements.avatarPreview.src = '/images/user.jpg';
                    elements.imagePathInput.value = '/images/user.jpg';
                    currentAvatarPath = '/images/user.jpg';

                    showNotification('Avatar removed. Click "Update User" to save changes.', 'success');
                }

                /**
                 * Show error
                 */
                function showError(message) {
                    elements.uploadError.textContent = message;
                    elements.uploadError.classList.remove('hidden');
                }

                /**
                 * Hide error
                 */
                function hideError() {
                    elements.uploadError.classList.add('hidden');
                }

                /**
                 * Show notification
                 */
                function showNotification(message, type = 'success') {
                    const existingNotifications = document.querySelectorAll('.notification');
                    existingNotifications.forEach(n => n.remove());

                    const notification = document.createElement('div');
                    const bgColor = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
                    notification.className = `${bgColor} border px-4 py-3 rounded mb-4 flex items-center justify-between notification`;
                    notification.style.position = 'fixed';
                    notification.style.top = '20px';
                    notification.style.right = '20px';
                    notification.style.zIndex = '9999';
                    notification.style.minWidth = '300px';

                    notification.innerHTML = `
                    <span>${message}</span>
                    <button onclick="this.parentElement.remove()" class="${type === 'success' ? 'text-green-700 hover:text-green-900' : 'text-red-700 hover:text-red-900'}">
                        <i class="fa-solid fa-times"></i>
                    </button>
                `;

                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.style.transition = 'opacity 0.3s';
                        notification.style.opacity = '0';
                        setTimeout(() => notification.remove(), 300);
                    }, 5000);
                }

                // Initialize when DOM is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    init();
                }

            })();
        </script>
    @endpush
</x-app-layout>
