<x-app-layout>
    <div class="p-4">
        <div class="mb-6">
            <a href="{{ route('admin.contact.index') }}" class="text-blue-500 hover:text-blue-700">
                ← Back to Messages
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <!-- Header -->
            <div class="border-b pb-4 mb-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h1 class="text-2xl font-bold mb-2">{{ $message->name }}</h1>
                        <p class="text-gray-600">{{ $message->email }}</p>
                    </div>
                    <div class="text-right">
                        <span id="status-badge" class="px-3 py-1 rounded text-sm {{
                            $message->status === 'unread' ? 'bg-blue-100 text-blue-700' :
                            ($message->status === 'replied' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700')
                        }}">
                            {{ ucfirst($message->status) }}
                        </span>
                        <p class="text-sm text-gray-500 mt-2">{{ $message->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Message Body -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-3">Message:</h2>
                <div class="bg-gray-50 rounded-lg p-4 whitespace-pre-wrap">{{ $message->body }}</div>
            </div>

            <!-- Meta Info -->
            <div class="text-sm text-gray-500 mb-6 space-y-1" id="meta-info">
                <p><strong>IP Address:</strong> {{ $message->ip_address }}</p>
                <p><strong>User Agent:</strong> {{ $message->user_agent }}</p>
                @if($message->replied_by)
                    <p id="replied-info">
                        <strong>Replied by:</strong>
                        {{ $message->replier->firstname }} {{ $message->replier->lastname }}
                        on {{ $message->replied_at->format('M d, Y H:i') }}
                    </p>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex gap-2" id="action-buttons">
                <a href="mailto:{{ $message->email }}?subject=Re: Your message"
                   class="btn btn-primary">
                    <i class="fa-solid fa-reply mr-2"></i>Reply via Email
                </a>

                @if($message->status !== 'replied')
                    <button
                        id="mark-replied-btn"
                        data-message-id="{{ $message->id }}"
                        class="btn btn-secondary">
                        <i class="fa-solid fa-check mr-2"></i>Mark as Replied
                    </button>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                'use strict';

                const CONFIG = {
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                };

                const elements = {
                    markRepliedBtn: document.getElementById('mark-replied-btn'),
                    statusBadge: document.getElementById('status-badge'),
                    metaInfo: document.getElementById('meta-info'),
                    actionButtons: document.getElementById('action-buttons')
                };

                /**
                 * Initialize
                 */
                function init() {
                    if (elements.markRepliedBtn) {
                        elements.markRepliedBtn.addEventListener('click', handleMarkAsReplied);
                    }
                }

                /**
                 * Handle mark as replied
                 */
                function handleMarkAsReplied() {
                    const messageId = elements.markRepliedBtn.getAttribute('data-message-id');

                    if (!messageId) return;

                    const url = `/admin/contact-messages/${messageId}/mark-replied`;

                    setButtonLoading(elements.markRepliedBtn, true);

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message, 'success');

                                // Update status badge
                                updateStatusBadge('replied');

                                // Add replied info
                                addRepliedInfo(data.replied_by, data.replied_at);

                                // Remove the mark as replied button
                                elements.markRepliedBtn.remove();
                            } else {
                                showNotification(data.message || 'Failed to mark as replied', 'error');
                                setButtonLoading(elements.markRepliedBtn, false);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Failed to mark as replied. Please try again.', 'error');
                            setButtonLoading(elements.markRepliedBtn, false);
                        });
                }

                /**
                 * Update status badge
                 */
                function updateStatusBadge(status) {
                    if (!elements.statusBadge) return;

                    elements.statusBadge.className = 'px-3 py-1 rounded text-sm bg-green-100 text-green-700';
                    elements.statusBadge.textContent = 'Replied';
                }

                /**
                 * Add replied info to meta section
                 */
                function addRepliedInfo(repliedBy, repliedAt) {
                    // Check if already exists
                    const existingInfo = document.getElementById('replied-info');
                    if (existingInfo) return;

                    const repliedInfo = document.createElement('p');
                    repliedInfo.id = 'replied-info';
                    repliedInfo.innerHTML = `<strong>Replied by:</strong> ${repliedBy} on ${repliedAt}`;

                    elements.metaInfo.appendChild(repliedInfo);
                }

                /**
                 * Set button loading state
                 */
                function setButtonLoading(button, isLoading) {
                    if (!button) return;

                    if (isLoading) {
                        button.disabled = true;
                        button.style.opacity = '0.6';
                    } else {
                        button.disabled = false;
                        button.style.opacity = '1';
                    }
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
