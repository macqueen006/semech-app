<x-app-layout>
    <div class="p-4">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('admin.subscribers.index') }}" class="text-blue-600 hover:underline mb-2 inline-block">
                    <i class="fa-solid fa-arrow-left"></i> Back to Subscribers
                </a>
                <h1 class="text-2xl font-bold">Subscriber Details</h1>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center justify-between alert-dismissible">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center justify-between alert-dismissible">
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info Card -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-bold">Subscriber Information</h2>
                </div>

                <div class="p-6 space-y-6" id="subscriber-info">
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-envelope text-gray-400"></i>
                            <span class="text-lg font-medium">{{ $subscriber->email }}</span>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div id="status-badge">
                            @if($subscriber->isSubscribed())
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-200">
                                <i class="fa-solid fa-check-circle mr-2"></i> Active Subscriber
                            </span>
                            @else
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                                <i class="fa-solid fa-times-circle mr-2"></i> Unsubscribed
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Subscribed At -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subscribed At</label>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-calendar-check text-gray-400"></i>
                            @if($subscriber->subscribed_at)
                                <span>{{ $subscriber->subscribed_at->format('l, F j, Y \a\t g:i A') }}</span>
                                <span class="text-sm text-gray-500">({{ $subscriber->subscribed_at->diffForHumans() }})</span>
                            @else
                                <span class="text-gray-500">Not available</span>
                            @endif
                        </div>
                    </div>

                    <!-- Unsubscribed At -->
                    @if($subscriber->unsubscribed_at)
                        <div id="unsubscribed-section">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unsubscribed At</label>
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-calendar-xmark text-gray-400"></i>
                                <span>{{ $subscriber->unsubscribed_at->format('l, F j, Y \a\t g:i A') }}</span>
                                <span class="text-sm text-gray-500">({{ $subscriber->unsubscribed_at->diffForHumans() }})</span>
                            </div>
                        </div>
                    @endif

                    <!-- Token -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unsubscribe Token</label>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-key text-gray-400"></i>
                            <code class="bg-gray-100 px-3 py-1 rounded text-sm font-mono" id="subscriber-token">{{ $subscriber->token }}</code>
                            <button
                                id="regenerate-token-btn"
                                data-subscriber-id="{{ $subscriber->id }}"
                                class="text-sm text-blue-600 hover:underline">
                                Regenerate
                            </button>
                        </div>
                    </div>

                    <!-- Timestamps -->
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Created At</label>
                            <div class="text-sm text-gray-600">
                                {{ $subscriber->created_at->format('M d, Y H:i') }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Updated At</label>
                            <div class="text-sm text-gray-600">
                                {{ $subscriber->updated_at->format('M d, Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="space-y-4">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold mb-4">Quick Actions</h3>

                    <div class="space-y-3" id="action-buttons">
                        @if($subscriber->isSubscribed())
                            <button
                                id="unsubscribe-btn"
                                data-subscriber-id="{{ $subscriber->id }}"
                                class="w-full px-4 py-3 bg-orange-600 text-white rounded hover:bg-orange-700 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-ban"></i>
                                Unsubscribe
                            </button>
                        @else
                            <button
                                id="resubscribe-btn"
                                data-subscriber-id="{{ $subscriber->id }}"
                                class="w-full px-4 py-3 bg-green-600 text-white rounded hover:bg-green-700 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-check"></i>
                                Resubscribe
                            </button>
                        @endif

                        <button
                            id="delete-subscriber-btn"
                            data-subscriber-id="{{ $subscriber->id }}"
                            class="w-full px-4 py-3 bg-red-600 text-white rounded hover:bg-red-700 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-trash"></i>
                            Delete Subscriber
                        </button>
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold mb-4">Statistics</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Subscriber ID</span>
                            <span class="font-medium">{{ $subscriber->id }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Days Subscribed</span>
                            <span class="font-medium">
                            @if($subscriber->subscribed_at)
                                    @if($subscriber->unsubscribed_at)
                                        {{ $subscriber->subscribed_at->diffInDays($subscriber->unsubscribed_at) }} days
                                    @else
                                        {{ $subscriber->subscribed_at->diffInDays(now()) }} days
                                    @endif
                                @else
                                    N/A
                                @endif
                        </span>
                        </div>

                        @if($subscriber->unsubscribed_at && $subscriber->subscribed_at)
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Subscription Duration</span>
                                <span class="font-medium">
                                {{ $subscriber->subscribed_at->diffForHumans($subscriber->unsubscribed_at, true) }}
                            </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Unsubscribe Link Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold mb-4">Unsubscribe Link</h3>

                    <div class="space-y-2">
                        <p class="text-sm text-gray-600">This is the unique unsubscribe link for this subscriber:</p>
                        <div class="bg-gray-50 p-3 rounded border text-xs font-mono break-all" id="unsubscribe-link">
                            {{ url('/newsletter/unsubscribe/' . $subscriber->token) }}
                        </div>
                        <button
                            id="copy-link-btn"
                            data-link="{{ url('/newsletter/unsubscribe/' . $subscriber->token) }}"
                            class="text-sm text-blue-600 hover:underline">
                            <i class="fa-solid fa-copy"></i> Copy Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .alert-dismissible {
                animation: slideInDown 0.3s ease-out;
            }

            @keyframes slideInDown {
                from {
                    transform: translateY(-100%);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            .btn-loading {
                position: relative;
                pointer-events: none;
                opacity: 0.6;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            /**
             * Subscriber Show Page JavaScript
             * Handles resubscribe, unsubscribe, delete, and token regeneration
             */

            (function() {
                'use strict';

                // Configuration
                const CONFIG = {
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                };

                // Elements
                let elements = {
                    resubscribeBtn: document.getElementById('resubscribe-btn'),
                    unsubscribeBtn: document.getElementById('unsubscribe-btn'),
                    deleteBtn: document.getElementById('delete-subscriber-btn'),
                    regenerateTokenBtn: document.getElementById('regenerate-token-btn'),
                    copyLinkBtn: document.getElementById('copy-link-btn'),
                    statusBadge: document.getElementById('status-badge'),
                    actionButtons: document.getElementById('action-buttons'),
                    subscriberToken: document.getElementById('subscriber-token'),
                    unsubscribeLink: document.getElementById('unsubscribe-link'),
                    subscriberInfo: document.getElementById('subscriber-info'),
                };

                /**
                 * Initialize the page
                 */
                function init() {
                    console.log('Initializing subscriber show page...');
                    setupEventListeners();
                }

                /**
                 * Setup all event listeners
                 */
                function setupEventListeners() {
                    // Resubscribe button
                    if (elements.resubscribeBtn) {
                        elements.resubscribeBtn.addEventListener('click', handleResubscribe);
                    }

                    // Unsubscribe button
                    if (elements.unsubscribeBtn) {
                        elements.unsubscribeBtn.addEventListener('click', handleUnsubscribe);
                    }

                    // Delete button
                    if (elements.deleteBtn) {
                        elements.deleteBtn.addEventListener('click', handleDelete);
                    }

                    // Regenerate token button
                    if (elements.regenerateTokenBtn) {
                        elements.regenerateTokenBtn.addEventListener('click', handleRegenerateToken);
                    }

                    // Copy link button
                    if (elements.copyLinkBtn) {
                        elements.copyLinkBtn.addEventListener('click', handleCopyLink);
                    }
                }

                /**
                 * Handle resubscribe action
                 */
                function handleResubscribe() {
                    const subscriberId = elements.resubscribeBtn.getAttribute('data-subscriber-id');

                    if (!subscriberId) return;

                    const url = `/admin/subscribers/${subscriberId}/resubscribe`;

                    setButtonLoading(elements.resubscribeBtn, true);

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message || 'Subscriber resubscribed successfully!', 'success');

                                // Update UI immediately
                                updateStatusBadge(true);
                                switchActionButton('unsubscribe', subscriberId);
                                removeUnsubscribedSection();
                            } else {
                                showNotification(data.message || 'Failed to resubscribe subscriber', 'error');
                                setButtonLoading(elements.resubscribeBtn, false);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Failed to resubscribe subscriber. Please try again.', 'error');
                            setButtonLoading(elements.resubscribeBtn, false);
                        });
                }

                /**
                 * Handle unsubscribe action
                 */
                function handleUnsubscribe() {
                    const subscriberId = elements.unsubscribeBtn.getAttribute('data-subscriber-id');

                    if (!subscriberId) return;

                    if (!confirm('Are you sure you want to unsubscribe this user?')) {
                        return;
                    }

                    const url = `/admin/subscribers/${subscriberId}/unsubscribe`;

                    setButtonLoading(elements.unsubscribeBtn, true);

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message || 'Subscriber unsubscribed successfully!', 'success');

                                // Update UI immediately
                                updateStatusBadge(false);
                                switchActionButton('resubscribe', subscriberId);
                                addUnsubscribedSection();
                            } else {
                                showNotification(data.message || 'Failed to unsubscribe subscriber', 'error');
                                setButtonLoading(elements.unsubscribeBtn, false);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Failed to unsubscribe subscriber. Please try again.', 'error');
                            setButtonLoading(elements.unsubscribeBtn, false);
                        });
                }

                /**
                 * Handle delete action
                 */
                function handleDelete() {
                    const subscriberId = elements.deleteBtn.getAttribute('data-subscriber-id');

                    if (!subscriberId) return;

                    if (!confirm('Are you sure you want to permanently delete this subscriber? This action cannot be undone.')) {
                        return;
                    }

                    const url = `/admin/subscribers/${subscriberId}`;

                    setButtonLoading(elements.deleteBtn, true);

                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message || 'Subscriber deleted successfully!', 'success');

                                // Redirect to index after a short delay
                                setTimeout(() => {
                                    window.location.href = '/admin/subscribers';
                                }, 1500);
                            } else {
                                showNotification(data.message || 'Failed to delete subscriber', 'error');
                                setButtonLoading(elements.deleteBtn, false);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Failed to delete subscriber. Please try again.', 'error');
                            setButtonLoading(elements.deleteBtn, false);
                        });
                }

                /**
                 * Handle token regeneration
                 */
                function handleRegenerateToken() {
                    const subscriberId = elements.regenerateTokenBtn.getAttribute('data-subscriber-id');

                    if (!subscriberId) return;

                    if (!confirm('Are you sure you want to regenerate the token? This will invalidate any existing unsubscribe links.')) {
                        return;
                    }

                    const url = `/admin/subscribers/${subscriberId}/regenerate-token`;

                    setButtonLoading(elements.regenerateTokenBtn, true);

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message || 'Token regenerated successfully!', 'success');

                                // Update token display immediately
                                if (data.token) {
                                    if (elements.subscriberToken) {
                                        elements.subscriberToken.textContent = data.token;
                                    }

                                    // Update unsubscribe link
                                    const newLink = window.location.origin + '/newsletter/unsubscribe/' + data.token;
                                    if (elements.unsubscribeLink) {
                                        elements.unsubscribeLink.textContent = newLink;
                                    }
                                    if (elements.copyLinkBtn) {
                                        elements.copyLinkBtn.setAttribute('data-link', newLink);
                                    }
                                }
                            } else {
                                showNotification(data.message || 'Failed to regenerate token', 'error');
                            }
                            setButtonLoading(elements.regenerateTokenBtn, false);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Failed to regenerate token. Please try again.', 'error');
                            setButtonLoading(elements.regenerateTokenBtn, false);
                        });
                }

                /**
                 * Handle copy link
                 */
                function handleCopyLink() {
                    const link = elements.copyLinkBtn.getAttribute('data-link');

                    if (!link) return;

                    navigator.clipboard.writeText(link)
                        .then(() => {
                            showNotification('Link copied to clipboard!', 'success');

                            // Temporary visual feedback
                            const originalText = elements.copyLinkBtn.innerHTML;
                            elements.copyLinkBtn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
                            elements.copyLinkBtn.classList.add('text-green-600');

                            setTimeout(() => {
                                elements.copyLinkBtn.innerHTML = originalText;
                                elements.copyLinkBtn.classList.remove('text-green-600');
                            }, 2000);
                        })
                        .catch(error => {
                            console.error('Error copying to clipboard:', error);
                            showNotification('Failed to copy link', 'error');
                        });
                }

                /**
                 * Update status badge
                 */
                function updateStatusBadge(isActive) {
                    console.log('Updating status badge:', isActive);

                    if (!elements.statusBadge) {
                        console.error('Status badge element not found!');
                        return;
                    }

                    if (isActive) {
                        elements.statusBadge.innerHTML = `
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-200">
                                <i class="fa-solid fa-check-circle mr-2"></i> Active Subscriber
                            </span>
                        `;
                    } else {
                        elements.statusBadge.innerHTML = `
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                                <i class="fa-solid fa-times-circle mr-2"></i> Unsubscribed
                            </span>
                        `;
                    }

                    console.log('Status badge updated successfully');
                }

                /**
                 * Switch action button between resubscribe and unsubscribe
                 */
                function switchActionButton(toButton, subscriberId) {
                    console.log('Switching action button to:', toButton);

                    if (!elements.actionButtons) {
                        console.error('Action buttons container not found!');
                        return;
                    }

                    const firstButton = elements.actionButtons.querySelector('button:first-child');
                    if (!firstButton) {
                        console.error('First button not found!');
                        return;
                    }

                    if (toButton === 'resubscribe') {
                        // Create resubscribe button
                        const newButton = document.createElement('button');
                        newButton.id = 'resubscribe-btn';
                        newButton.setAttribute('data-subscriber-id', subscriberId);
                        newButton.className = 'w-full px-4 py-3 bg-green-600 text-white rounded hover:bg-green-700 flex items-center justify-center gap-2';
                        newButton.innerHTML = `
                            <i class="fa-solid fa-check"></i>
                            Resubscribe
                        `;

                        // Replace the button
                        firstButton.parentNode.replaceChild(newButton, firstButton);

                        // Update reference and attach event listener
                        elements.resubscribeBtn = newButton;
                        newButton.addEventListener('click', handleResubscribe);

                        console.log('Resubscribe button created and listener attached');
                    } else {
                        // Create unsubscribe button
                        const newButton = document.createElement('button');
                        newButton.id = 'unsubscribe-btn';
                        newButton.setAttribute('data-subscriber-id', subscriberId);
                        newButton.className = 'w-full px-4 py-3 bg-orange-600 text-white rounded hover:bg-orange-700 flex items-center justify-center gap-2';
                        newButton.innerHTML = `
                            <i class="fa-solid fa-ban"></i>
                            Unsubscribe
                        `;

                        // Replace the button
                        firstButton.parentNode.replaceChild(newButton, firstButton);

                        // Update reference and attach event listener
                        elements.unsubscribeBtn = newButton;
                        newButton.addEventListener('click', handleUnsubscribe);

                        console.log('Unsubscribe button created and listener attached');
                    }
                }

                /**
                 * Add unsubscribed section
                 */
                function addUnsubscribedSection() {
                    console.log('Adding unsubscribed section');

                    // Check if section already exists
                    const existingSection = document.getElementById('unsubscribed-section');
                    if (existingSection) {
                        console.log('Unsubscribed section already exists');
                        return;
                    }

                    const now = new Date();
                    const formatted = now.toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }) + ' at ' + now.toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });

                    // Create the new section
                    const section = document.createElement('div');
                    section.id = 'unsubscribed-section';
                    section.className = '';
                    section.innerHTML = `
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unsubscribed At</label>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-calendar-xmark text-gray-400"></i>
                            <span>${formatted}</span>
                            <span class="text-sm text-gray-500">(just now)</span>
                        </div>
                    `;

                    // Find the parent container and insert after "Subscribed At" section
                    if (elements.subscriberInfo) {
                        const children = Array.from(elements.subscriberInfo.children);
                        // Insert after the 3rd child (index 2) which is "Subscribed At"
                        if (children[2]) {
                            children[2].insertAdjacentElement('afterend', section);
                            console.log('Unsubscribed section added successfully');
                        } else {
                            console.error('Could not find insertion point for unsubscribed section');
                        }
                    }
                }

                /**
                 * Remove unsubscribed section
                 */
                function removeUnsubscribedSection() {
                    console.log('Removing unsubscribed section');

                    const section = document.getElementById('unsubscribed-section');
                    if (section) {
                        section.remove();
                        console.log('Unsubscribed section removed successfully');
                    } else {
                        console.log('Unsubscribed section not found (may already be removed)');
                    }
                }

                /**
                 * Set button loading state
                 */
                function setButtonLoading(button, isLoading) {
                    if (!button) return;

                    if (isLoading) {
                        button.disabled = true;
                        button.classList.add('btn-loading');
                        button.style.opacity = '0.6';
                    } else {
                        button.disabled = false;
                        button.classList.remove('btn-loading');
                        button.style.opacity = '1';
                    }
                }

                /**
                 * Show notification message
                 */
                function showNotification(message, type = 'success') {
                    const existingNotifications = document.querySelectorAll('.notification');
                    existingNotifications.forEach(n => n.remove());

                    const notification = document.createElement('div');
                    const bgColor = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
                    notification.className = `${bgColor} border px-4 py-3 rounded mb-4 flex items-center justify-between notification alert-dismissible`;
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
