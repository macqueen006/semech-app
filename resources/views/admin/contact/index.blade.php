<x-app-layout>
    <div class="p-4">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Contact Messages</h1>
        </div>

        <!-- Filter Tabs -->
        <div class="mb-6 flex gap-2 border-b" id="filter-tabs">
            <button
                data-status="all"
                class="filter-tab px-4 py-2 {{ $statusFilter === 'all' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600' }}">
                All (<span class="count-all">{{ $counts['all'] }}</span>)
            </button>
            <button
                data-status="unread"
                class="filter-tab px-4 py-2 {{ $statusFilter === 'unread' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600' }}">
                Unread (<span class="count-unread">{{ $counts['unread'] }}</span>)
            </button>
            <button
                data-status="read"
                class="filter-tab px-4 py-2 {{ $statusFilter === 'read' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600' }}">
                Read (<span class="count-read">{{ $counts['read'] }}</span>)
            </button>
            <button
                data-status="replied"
                class="filter-tab px-4 py-2 {{ $statusFilter === 'replied' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600' }}">
                Replied (<span class="count-replied">{{ $counts['replied'] }}</span>)
            </button>
        </div>

        <!-- Messages List -->
        <div class="bg-white rounded-lg shadow" id="messages-container">
            @include('admin.contact.partials.messages-list', ['messages' => $messages])
        </div>

        <!-- Pagination -->
        <div class="mt-4" id="pagination-container">
            {{ $messages->links() }}
        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                'use strict';

                const CONFIG = {
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    currentStatus: '{{ $statusFilter }}'
                };

                const elements = {
                    filterTabs: document.querySelectorAll('.filter-tab'),
                    messagesContainer: document.getElementById('messages-container'),
                    paginationContainer: document.getElementById('pagination-container')
                };

                /**
                 * Initialize
                 */
                function init() {
                    setupEventListeners();
                }

                /**
                 * Setup event listeners
                 */
                function setupEventListeners() {
                    // Filter tabs
                    elements.filterTabs.forEach(tab => {
                        tab.addEventListener('click', handleFilterClick);
                    });

                    // Pagination links (delegated)
                    elements.paginationContainer.addEventListener('click', handlePaginationClick);
                }

                /**
                 * Handle filter tab click
                 */
                function handleFilterClick(e) {
                    const status = e.currentTarget.getAttribute('data-status');
                    loadMessages(status, 1);
                }

                /**
                 * Handle pagination click
                 */
                function handlePaginationClick(e) {
                    const link = e.target.closest('a');
                    if (!link) return;

                    e.preventDefault();

                    const url = new URL(link.href);
                    const page = url.searchParams.get('page') || 1;

                    loadMessages(CONFIG.currentStatus, page);
                }

                /**
                 * Load messages via AJAX
                 */
                function loadMessages(status, page = 1) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('status', status);
                    url.searchParams.set('page', page);

                    fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update messages list
                                elements.messagesContainer.innerHTML = data.html;

                                // Update pagination
                                elements.paginationContainer.innerHTML = data.pagination;

                                // Update counts
                                updateCounts(data.counts);

                                // Update active tab
                                updateActiveTab(status);

                                // Update current status
                                CONFIG.currentStatus = status;

                                // Update URL without reload
                                window.history.pushState({}, '', url.toString());
                            }
                        })
                        .catch(error => {
                            console.error('Error loading messages:', error);
                        });
                }

                /**
                 * Update counts in tabs
                 */
                function updateCounts(counts) {
                    document.querySelector('.count-all').textContent = counts.all;
                    document.querySelector('.count-unread').textContent = counts.unread;
                    document.querySelector('.count-read').textContent = counts.read;
                    document.querySelector('.count-replied').textContent = counts.replied;
                }

                /**
                 * Update active tab styling
                 */
                function updateActiveTab(status) {
                    elements.filterTabs.forEach(tab => {
                        const tabStatus = tab.getAttribute('data-status');
                        if (tabStatus === status) {
                            tab.classList.add('border-b-2', 'border-blue-500', 'text-blue-600');
                            tab.classList.remove('text-gray-600');
                        } else {
                            tab.classList.remove('border-b-2', 'border-blue-500', 'text-blue-600');
                            tab.classList.add('text-gray-600');
                        }
                    });
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
