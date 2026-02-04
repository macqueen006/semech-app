<section>
    <div class="border border-gray-200 rounded-xl shadow-2xs">
        <div class="p-4 sm:p-7">
            <header>
                <h2 class="text-2xl font-bold text-foreground">
                    {{ __('Profile Information') }}
                </h2>
                <p class="mt-2 text-sm text-muted-foreground-2">
                    {{ __("Update your account's profile information and email address.") }}
                </p>
            </header>

            <!-- Flash Messages -->
            <div id="profile-flash-message" class="hidden mt-4"></div>

            <form id="profile-update-form" class="mt-5">
                @csrf
                @method('patch')

                <!-- Avatar Upload Section -->
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Avatar</label>

                    <!-- Current Avatar Preview -->
                    <div id="current-avatar-container" class="mb-4">
                        <div class="relative inline-block" id="avatar-preview-wrapper">
                            <img src="{{ $user->image_path ?? '/images/user.jpg' }}"
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
                                placeholder="Enter avatar URL">
                            <button
                                type="button"
                                id="set-avatar-url-btn"
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                Set Avatar
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            You can use a full URL or a path to an existing image
                        </p>
                    </div>

                    <!-- Hidden input to store final avatar path -->
                    <input type="hidden" id="image_path" name="image_path" value="{{ $user->image_path }}">
                </div>

                <!-- First Name -->
                <x-input-group>
                    <x-input-label for="firstname" label="First Name" />
                    <x-text-input
                        id="firstname"
                        name="firstname"
                        type="text"
                        :value="old('firstname', $user->firstname)"
                        required
                        autofocus
                    />
                    <span class="text-red-600 text-sm mt-1 hidden" id="error-firstname"></span>
                </x-input-group>

                <!-- Last Name -->
                <x-input-group>
                    <x-input-label for="lastname" label="Last Name" />
                    <x-text-input
                        id="lastname"
                        name="lastname"
                        type="text"
                        :value="old('lastname', $user->lastname)"
                        required
                    />
                    <span class="text-red-600 text-sm mt-1 hidden" id="error-lastname"></span>
                </x-input-group>

                <!-- Email -->
                <x-input-group>
                    <x-input-label for="email" label="Email" />
                    <x-text-input
                        id="email"
                        name="email"
                        type="email"
                        :value="old('email', $user->email)"
                        required
                        autocomplete="username"
                    />
                    <span class="text-red-600 text-sm mt-1 hidden" id="error-email"></span>

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-800">
                                {{ __('Your email address is unverified.') }}
                                <button
                                    form="send-verification"
                                    type="submit"
                                    class="underline text-sm text-yellow-900 hover:text-yellow-950 font-medium focus:outline-none">
                                    {{ __('Click here to re-send the verification email.') }}
                                </button>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 text-sm text-green-600 font-medium">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </x-input-group>

                <!-- Bio -->
                <x-input-group>
                    <x-input-label for="bio" label="Bio" />
                    <textarea
                        id="bio"
                        name="bio"
                        rows="4"
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Tell us about yourself...">{{ old('bio', $user->bio) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Brief description for your profile.</p>
                    <span class="text-red-600 text-sm mt-1 hidden" id="error-bio"></span>
                </x-input-group>

                <!-- Social Media Section -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                    <h3 class="text-lg font-semibold text-foreground mb-4">Social Media Links</h3>

                    <!-- Website -->
                    <x-input-group>
                        <x-input-label for="website" label="Website" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                            </div>
                            <x-text-input
                                id="website"
                                name="website"
                                type="url"
                                class="pl-10"
                                :value="old('website', $user->website)"
                                placeholder="https://yourwebsite.com"
                            />
                        </div>
                        <span class="text-red-600 text-sm mt-1 hidden" id="error-website"></span>
                    </x-input-group>

                    <!-- Twitter -->
                    <x-input-group>
                        <x-input-label for="twitter" label="Twitter / X" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </div>
                            <x-text-input
                                id="twitter"
                                name="twitter"
                                type="text"
                                class="pl-10"
                                :value="old('twitter', $user->twitter)"
                                placeholder="@username or profile URL"
                            />
                        </div>
                        <span class="text-red-600 text-sm mt-1 hidden" id="error-twitter"></span>
                    </x-input-group>

                    <!-- LinkedIn -->
                    <x-input-group>
                        <x-input-label for="linkedin" label="LinkedIn" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </div>
                            <x-text-input
                                id="linkedin"
                                name="linkedin"
                                type="text"
                                class="pl-10"
                                :value="old('linkedin', $user->linkedin)"
                                placeholder="LinkedIn profile URL"
                            />
                        </div>
                        <span class="text-red-600 text-sm mt-1 hidden" id="error-linkedin"></span>
                    </x-input-group>

                    <!-- GitHub -->
                    <x-input-group>
                        <x-input-label for="github" label="GitHub" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <x-text-input
                                id="github"
                                name="github"
                                type="text"
                                class="pl-10"
                                :value="old('github', $user->github)"
                                placeholder="GitHub username or profile URL"
                            />
                        </div>
                        <span class="text-red-600 text-sm mt-1 hidden" id="error-github"></span>
                    </x-input-group>
                </div>

                <!-- Submit Button -->
                <x-input-group>
                    <button
                        type="submit"
                        id="save-profile-btn"
                        class="py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-secondary border border-gray-200 text-white hover:bg-secondary focus:outline-hidden focus:bg-primary-focus disabled:opacity-50 disabled:pointer-events-none">
                        {{ __('Save') }}
                    </button>
                </x-input-group>
            </form>
        </div>
    </div>
    @push('scripts')
        <script>
            (function() {
                'use strict';

                const CONFIG = {
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    uploadAvatarUrl: '{{ route("admin.profile.upload-avatar") }}',
                    deleteAvatarUrl: '{{ route("admin.profile.delete-avatar") }}',
                    cleanupAvatarsUrl: '{{ route("admin.profile.cleanup-avatars") }}',
                    updateProfileUrl: '{{ route("admin.profile.update") }}',
                };

                const elements = {
                    form: document.getElementById('profile-update-form'),
                    saveBtn: document.getElementById('save-profile-btn'),
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
                    flashMessage: document.getElementById('profile-flash-message'),
                };

                let currentMode = 'upload';
                let selectedFile = null;
                let uploadedAvatars = [];
                let currentAvatarPath = '{{ $user->image_path }}';
                let hasUnsavedChanges = false;

                function init() {
                    setupEventListeners();
                    setupPageAbandonProtection();
                }

                function setupPageAbandonProtection() {
                    window.addEventListener('beforeunload', function(e) {
                        if (hasUnsavedChanges && uploadedAvatars.length > 0) {
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
                }

                function setupEventListeners() {
                    elements.uploadModeBtn?.addEventListener('click', () => switchMode('upload'));
                    elements.urlModeBtn?.addEventListener('click', () => switchMode('url'));
                    elements.avatarFileInput?.addEventListener('change', handleFileSelect);
                    elements.confirmUploadBtn?.addEventListener('click', uploadAvatar);
                    elements.setAvatarUrlBtn?.addEventListener('click', setAvatarFromUrl);
                    elements.removeAvatarBtn?.addEventListener('click', removeAvatar);
                    elements.form?.addEventListener('submit', handleFormSubmit);
                }

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

                function handleFileSelect(e) {
                    const file = e.target.files[0];

                    if (!file) return;

                    if (!file.type.match('image.*')) {
                        showError('Please select an image file');
                        return;
                    }

                    if (file.size > 5 * 1024 * 1024) {
                        showError('File size must be less than 5MB');
                        return;
                    }

                    selectedFile = file;
                    hideError();
                    hasUnsavedChanges = true;

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        elements.uploadPreviewImg.src = e.target.result;
                        elements.uploadPreview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }

                function uploadAvatar() {
                    if (!selectedFile) return;

                    const formData = new FormData();
                    formData.append('image', selectedFile);

                    elements.confirmUploadBtn.disabled = true;
                    elements.confirmUploadBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Uploading...';

                    fetch(CONFIG.uploadAvatarUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                uploadedAvatars.push(data.path);
                                elements.avatarPreview.src = data.path;
                                elements.imagePathInput.value = data.path;
                                currentAvatarPath = data.path;
                                elements.avatarFileInput.value = '';
                                elements.uploadPreview.classList.add('hidden');
                                selectedFile = null;
                                showFlashMessage(data.message, 'success');
                            } else {
                                showError(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error uploading avatar:', error);
                            showError('Failed to upload avatar. Please try again.');
                        })
                        .finally(() => {
                            elements.confirmUploadBtn.disabled = false;
                            elements.confirmUploadBtn.innerHTML = '✓ Confirm Upload';
                        });
                }

                function setAvatarFromUrl() {
                    const url = elements.avatarUrlInput.value.trim();

                    if (!url) {
                        showError('Please enter a URL');
                        return;
                    }

                    hasUnsavedChanges = true;
                    elements.avatarPreview.src = url;
                    elements.imagePathInput.value = url;
                    currentAvatarPath = url;
                    elements.avatarUrlInput.value = '';
                    showFlashMessage('Avatar URL set successfully!', 'success');
                }

                function removeAvatar() {
                    if (!confirm('Are you sure you want to remove this avatar?')) {
                        return;
                    }

                    hasUnsavedChanges = true;
                    elements.avatarPreview.src = '/images/user.jpg';
                    elements.imagePathInput.value = '/images/user.jpg';
                    currentAvatarPath = '/images/user.jpg';
                    showFlashMessage('Avatar removed. Click "Save" to update your profile.', 'success');
                }

                function handleFormSubmit(e) {
                    e.preventDefault();

                    clearErrors();

                    const formData = new FormData(elements.form);
                    formData.append('_method', 'PATCH');

                    elements.saveBtn.disabled = true;
                    elements.saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Saving...';

                    fetch(CONFIG.updateProfileUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                hasUnsavedChanges = false;
                                uploadedAvatars = [];
                                showFlashMessage(data.message, 'success');

                                // Update displayed values
                                if (data.user) {
                                    elements.avatarPreview.src = data.user.image_path;
                                }
                            } else {
                                if (data.errors) {
                                    displayErrors(data.errors);
                                } else {
                                    showFlashMessage(data.message || 'Failed to update profile', 'error');
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error updating profile:', error);
                            showFlashMessage('Failed to update profile. Please try again.', 'error');
                        })
                        .finally(() => {
                            elements.saveBtn.disabled = false;
                            elements.saveBtn.innerHTML = '{{ __("Save") }}';
                        });
                }

                function displayErrors(errors) {
                    Object.keys(errors).forEach(field => {
                        const errorElement = document.getElementById(`error-${field}`);
                        if (errorElement) {
                            errorElement.textContent = errors[field][0];
                            errorElement.classList.remove('hidden');
                        }
                    });
                }

                function clearErrors() {
                    document.querySelectorAll('[id^="error-"]').forEach(el => {
                        el.textContent = '';
                        el.classList.add('hidden');
                    });
                }

                function showError(message) {
                    elements.uploadError.textContent = message;
                    elements.uploadError.classList.remove('hidden');
                }

                function hideError() {
                    elements.uploadError.classList.add('hidden');
                }

                function showFlashMessage(message, type = 'success') {
                    const bgColor = type === 'success' ? 'bg-green-50 border-green-400 text-green-700' : 'bg-red-50 border-red-400 text-red-700';

                    elements.flashMessage.className = `${bgColor} border px-4 py-3 rounded mb-4`;
                    elements.flashMessage.innerHTML = `
                <div class="flex items-center justify-between">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.classList.add('hidden')" class="text-current hover:opacity-75">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            `;
                    elements.flashMessage.classList.remove('hidden');

                    setTimeout(() => {
                        elements.flashMessage.classList.add('hidden');
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
</section>
