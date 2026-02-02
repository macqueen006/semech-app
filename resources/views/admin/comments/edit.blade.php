<x-app-layout>
    <div class="p-4">
        <div class="page-header">
            <h1>Edit comment</h1>
            <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary">
                ← Back to list
            </a>
        </div>

        <div class="form-card">
            <form id="edit-comment-form" method="POST" action="{{ route('admin.comments.update', $comment->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $comment->name) }}"
                        class="form-input @error('name') error @enderror"
                        required
                    >
                    @error('name')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="body">Comment Content</label>
                    <textarea
                        id="body"
                        name="body"
                        rows="6"
                        class="form-textarea @error('body') error @enderror"
                        required
                    >{{ old('body', $comment->body) }}</textarea>
                    @error('body')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                        Save changes
                    </button>
                    <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-error">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif
    </div>

    @push('styles')
        <style>
            .page-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 2rem;
                padding-bottom: 1rem;
                border-bottom: 2px solid #e5e7eb;
            }

            .page-header h1 {
                font-size: 1.875rem;
                font-weight: 700;
                color: #111827;
                margin: 0;
            }

            .form-card {
                background: white;
                border-radius: 12px;
                padding: 2rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .form-group {
                margin-bottom: 1.5rem;
            }

            .form-group label {
                display: block;
                font-size: 0.875rem;
                font-weight: 600;
                color: #374151;
                margin-bottom: 0.5rem;
            }

            .form-input, .form-textarea {
                width: 100%;
                padding: 0.75rem 1rem;
                border: 1px solid #d1d5db;
                border-radius: 8px;
                font-size: 0.875rem;
                transition: all 0.2s;
            }

            .form-input:focus, .form-textarea:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }

            .form-input.error, .form-textarea.error {
                border-color: #dc2626;
            }

            .form-textarea {
                resize: vertical;
                min-height: 120px;
            }

            .error-message {
                display: block;
                color: #dc2626;
                font-size: 0.813rem;
                margin-top: 0.25rem;
            }

            .form-actions {
                display: flex;
                gap: 0.75rem;
                margin-top: 2rem;
            }

            .btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.625rem 1.25rem;
                border-radius: 8px;
                font-weight: 500;
                font-size: 0.875rem;
                text-decoration: none;
                transition: all 0.2s;
                border: none;
                cursor: pointer;
            }

            .btn-primary {
                background: #667eea;
                color: white;
            }

            .btn-primary:hover {
                background: #5568d3;
            }

            .btn-primary:disabled {
                background: #9ca3af;
                cursor: not-allowed;
            }

            .btn-secondary {
                background: #f3f4f6;
                color: #374151;
            }

            .btn-secondary:hover {
                background: #e5e7eb;
            }

            .icon {
                width: 1.125rem;
                height: 1.125rem;
            }

            .alert {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                margin-top: 1.5rem;
            }

            .alert-success {
                background: #d1fae5;
                color: #065f46;
                border: 1px solid #6ee7b7;
            }

            .alert-error {
                background: #fee2e2;
                color: #991b1b;
                border: 1px solid #fca5a5;
            }

            .loading {
                position: relative;
                pointer-events: none;
                opacity: 0.6;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                'use strict';

                // Configuration
                const CONFIG = {
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                };

                // Elements
                const elements = {
                    form: document.getElementById('edit-comment-form'),
                    nameInput: document.getElementById('name'),
                    bodyTextarea: document.getElementById('body'),
                    submitButton: null,
                };

                /**
                 * Initialize the page
                 */
                function init() {
                    if (!elements.form) return;

                    elements.submitButton = elements.form.querySelector('button[type="submit"]');
                    setupEventListeners();
                    setupRealTimeValidation();
                }

                /**
                 * Setup all event listeners
                 */
                function setupEventListeners() {
                    // Form submission
                    elements.form.addEventListener('submit', handleFormSubmit);

                    // Character count for textarea (optional)
                    if (elements.bodyTextarea) {
                        addCharacterCounter();
                    }
                }

                /**
                 * Setup real-time validation
                 */
                function setupRealTimeValidation() {
                    // Name validation
                    if (elements.nameInput) {
                        elements.nameInput.addEventListener('blur', function () {
                            validateField(this, 'Name is required and must be less than 255 characters');
                        });

                        elements.nameInput.addEventListener('input', function () {
                            if (this.classList.contains('error')) {
                                validateField(this, 'Name is required and must be less than 255 characters');
                            }
                        });
                    }

                    // Body validation
                    if (elements.bodyTextarea) {
                        elements.bodyTextarea.addEventListener('blur', function () {
                            validateField(this, 'Comment content is required');
                        });

                        elements.bodyTextarea.addEventListener('input', function () {
                            if (this.classList.contains('error')) {
                                validateField(this, 'Comment content is required');
                            }
                        });
                    }
                }

                /**
                 * Validate a single field
                 */
                function validateField(field, errorMessage) {
                    const value = field.value.trim();
                    const errorElement = field.parentElement.querySelector('.error-message');

                    if (!value) {
                        field.classList.add('error');
                        if (errorElement) {
                            errorElement.textContent = errorMessage;
                        } else {
                            showFieldError(field, errorMessage);
                        }
                        return false;
                    }

                    // Additional validation for name length
                    if (field.id === 'name' && value.length > 255) {
                        field.classList.add('error');
                        if (errorElement) {
                            errorElement.textContent = 'Name must be less than 255 characters';
                        } else {
                            showFieldError(field, 'Name must be less than 255 characters');
                        }
                        return false;
                    }

                    field.classList.remove('error');
                    if (errorElement) {
                        errorElement.remove();
                    }
                    return true;
                }

                /**
                 * Show field error message
                 */
                function showFieldError(field, message) {
                    const errorElement = document.createElement('span');
                    errorElement.className = 'error-message';
                    errorElement.textContent = message;
                    field.parentElement.appendChild(errorElement);
                }

                /**
                 * Validate entire form
                 */
                function validateForm() {
                    let isValid = true;

                    if (elements.nameInput) {
                        const nameValid = validateField(elements.nameInput, 'Name is required and must be less than 255 characters');
                        isValid = isValid && nameValid;
                    }

                    if (elements.bodyTextarea) {
                        const bodyValid = validateField(elements.bodyTextarea, 'Comment content is required');
                        isValid = isValid && bodyValid;
                    }

                    return isValid;
                }

                /**
                 * Handle form submission
                 */
                function handleFormSubmit(e) {
                    e.preventDefault();

                    // Validate form
                    if (!validateForm()) {
                        showNotification('Please fix the errors before submitting', 'error');
                        return;
                    }

                    // Disable submit button
                    if (elements.submitButton) {
                        elements.submitButton.disabled = true;
                        elements.submitButton.innerHTML = `
                <svg class="icon animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Saving...
            `;
                    }

                    // Get form data
                    const formData = new FormData(elements.form);
                    const url = elements.form.action;

                    // Submit via AJAX
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message || 'Comment has been updated', 'success');

                                // Redirect after a short delay
                                setTimeout(() => {
                                    window.location.href = data.redirect || '/admin/comments';
                                }, 1500);
                            } else {
                                // Handle validation errors
                                if (data.errors) {
                                    handleValidationErrors(data.errors);
                                } else {
                                    showNotification(data.message || 'Failed to update comment', 'error');
                                }

                                // Re-enable submit button
                                resetSubmitButton();
                            }
                        })
                        .catch(error => {
                            console.error('Error updating comment:', error);
                            showNotification('Failed to update comment. Please try again.', 'error');
                            resetSubmitButton();
                        });
                }

                /**
                 * Handle validation errors from server
                 */
                function handleValidationErrors(errors) {
                    // Clear existing errors
                    document.querySelectorAll('.error-message').forEach(el => el.remove());
                    document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));

                    // Show new errors
                    Object.keys(errors).forEach(fieldName => {
                        const field = elements.form.querySelector(`[name="${fieldName}"]`);
                        if (field) {
                            field.classList.add('error');
                            const errorMessage = Array.isArray(errors[fieldName])
                                ? errors[fieldName][0]
                                : errors[fieldName];
                            showFieldError(field, errorMessage);
                        }
                    });

                    // Show general error notification
                    showNotification('Please fix the errors before submitting', 'error');
                }

                /**
                 * Reset submit button to original state
                 */
                function resetSubmitButton() {
                    if (elements.submitButton) {
                        elements.submitButton.disabled = false;
                        elements.submitButton.innerHTML = `
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save changes
            `;
                    }
                }

                /**
                 * Add character counter to textarea
                 */
                function addCharacterCounter() {
                    const counterElement = document.createElement('div');
                    counterElement.className = 'character-counter';
                    counterElement.style.cssText = 'text-align: right; font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;';

                    const updateCounter = () => {
                        const length = elements.bodyTextarea.value.length;
                        counterElement.textContent = `${length} characters`;
                    };

                    elements.bodyTextarea.parentElement.appendChild(counterElement);
                    elements.bodyTextarea.addEventListener('input', updateCounter);
                    updateCounter();
                }

                /**
                 * Show notification message
                 */
                function showNotification(message, type = 'success') {
                    // Remove existing notifications
                    const existingNotifications = document.querySelectorAll('.notification');
                    existingNotifications.forEach(n => n.remove());

                    // Create notification element
                    const notification = document.createElement('div');
                    notification.className = `alert alert-${type} notification`;
                    notification.style.position = 'fixed';
                    notification.style.top = '20px';
                    notification.style.right = '20px';
                    notification.style.zIndex = '9999';
                    notification.style.minWidth = '300px';
                    notification.style.animation = 'slideInRight 0.3s ease-out';

                    const icon = type === 'success'
                        ? '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                        : '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

                    notification.innerHTML = `${icon} ${message}`;

                    document.body.appendChild(notification);

                    // Auto remove after 5 seconds
                    setTimeout(() => {
                        notification.style.animation = 'slideOutRight 0.3s ease-out';
                        setTimeout(() => notification.remove(), 300);
                    }, 5000);
                }

                // Initialize when DOM is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    init();
                }

                // Add keyframe animations and spin animation
                const style = document.createElement('style');
                style.textContent = `
                            @keyframes slideInRight {
                                from {
                                    transform: translateX(100%);
                                    opacity: 0;
                                }
                                to {
                                    transform: translateX(0);
                                    opacity: 1;
                                }
                            }

                            @keyframes slideOutRight {
                                from {
                                    transform: translateX(0);
                                    opacity: 1;
                                }
                                to {
                                    transform: translateX(100%);
                                    opacity: 0;
                                }
                            }

                            @keyframes spin {
                                from {
                                    transform: rotate(0deg);
                                }
                                to {
                                    transform: rotate(360deg);
                                }
                            }

                            .animate-spin {
                                animation: spin 1s linear infinite;
                            }
                        `;
                document.head.appendChild(style);

            })();
        </script>
    @endpush
</x-app-layout>
