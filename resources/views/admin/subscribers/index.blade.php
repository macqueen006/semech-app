<x-app-layout>
    <div class="p-4" id="subscribers-container">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Newsletter Subscribers</h1>
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

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6" id="stats-container">
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Subscribers</p>
                        <p class="text-2xl font-bold" id="stat-total">{{ number_format($stats['total']) }}</p>
                    </div>
                    <i class="fa-solid fa-users text-blue-500 text-3xl"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Active</p>
                        <p class="text-2xl font-bold" id="stat-active">{{ number_format($stats['active']) }}</p>
                    </div>
                    <i class="fa-solid fa-user-check text-green-500 text-3xl"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Unsubscribed</p>
                        <p class="text-2xl font-bold" id="stat-unsubscribed">{{ number_format($stats['unsubscribed']) }}</p>
                    </div>
                    <i class="fa-solid fa-user-xmark text-red-500 text-3xl"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">This Month</p>
                        <p class="text-2xl font-bold" id="stat-month">{{ number_format($stats['this_month']) }}</p>
                    </div>
                    <i class="fa-solid fa-calendar-days text-purple-500 text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div id="bulk-actions-bar" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 hidden">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="font-medium text-blue-900">
                        <span id="selected-count">0</span> subscriber(s) selected
                    </span>

                    <button
                        id="bulk-resubscribe-btn"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        <i class="fa-solid fa-check"></i> Resubscribe
                    </button>

                    <button
                        id="bulk-unsubscribe-btn"
                        class="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">
                        <i class="fa-solid fa-ban"></i> Unsubscribe
                    </button>

                    <button
                        id="bulk-delete-btn"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </div>

                <button
                    id="clear-selection-btn"
                    class="text-blue-600 hover:text-blue-700 text-sm">
                    Clear Selection
                </button>
            </div>
        </div>
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <form id="filters-form" method="GET" action="{{ route('admin.subscribers.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Search</label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Search by email..."
                            class="w-full px-3 py-2 border rounded">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Status</label>
                        <select id="status-filter" name="status" class="w-full px-3 py-2 border rounded">
                            <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Subscribers</option>
                            <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Only</option>
                            <option value="unsubscribed" {{ $statusFilter === 'unsubscribed' ? 'selected' : '' }}>Unsubscribed Only</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Sort</label>
                        <select id="order" name="order" class="w-full px-3 py-2 border rounded">
                            <option value="desc" {{ $order === 'desc' ? 'selected' : '' }}>Newest First</option>
                            <option value="asc" {{ $order === 'asc' ? 'selected' : '' }}>Oldest First</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Per Page</label>
                        <select id="limit" name="limit" class="w-full px-3 py-2 border rounded">
                            <option value="20" {{ $limit == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $limit == 100 ? 'selected' : '' }}>100</option>
                            <option value="0" {{ $limit == 0 ? 'selected' : '' }}>All</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <button
                        type="button"
                        id="export-btn"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        <i class="fa-solid fa-download"></i> Export to CSV
                    </button>
                </div>
            </form>
        </div>

        <!-- Subscribers Table -->
        <div id="subscribers-table-container">
            @include('admin.subscribers.partials.subscribers-table', ['subscribers' => $subscribers])
        </div>

        <!-- Pagination -->
        @if($limit > 0)
            <div class="mt-4" id="pagination-container">
                {{ $subscribers->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

        @push('styles')
            <style>
                .loading-overlay {
                    position: relative;
                    pointer-events: none;
                    opacity: 0.6;
                }

                .loading-overlay::after {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 40px;
                    height: 40px;
                    border: 3px solid #f3f3f3;
                    border-top: 3px solid #3b82f6;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }

                @keyframes spin {
                    0% { transform: translate(-50%, -50%) rotate(0deg); }
                    100% { transform: translate(-50%, -50%) rotate(360deg); }
                }

                .form-checkbox {
                    width: 1rem;
                    height: 1rem;
                    cursor: pointer;
                }

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
            </style>
        @endpush

        @push('scripts')
        <script>
            (function() {
                'use strict';

                // Configuration
                const CONFIG = {
                    debounceDelay: 300,
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                };

                // Elements
                let elements = {
                    searchInput: null,
                    statusFilter: null,
                    orderSelect: null,
                    limitSelect: null,
                    exportBtn: null,
                    bulkActionsBar: null,
                    selectedCount: null,
                    bulkResubscribeBtn: null,
                    bulkUnsubscribeBtn: null,
                    bulkDeleteBtn: null,
                    clearSelectionBtn: null,
                    selectAllCheckbox: null,
                    subscribersTableContainer: null,
                    paginationContainer: null,
                };

                // State
                let debounceTimer = null;
                let isLoading = false;
                let selectedSubscribers = [];

                /**
                 * Initialize or refresh element references
                 */
                function refreshElements() {
                    elements.searchInput = document.getElementById('search');
                    elements.statusFilter = document.getElementById('status-filter');
                    elements.orderSelect = document.getElementById('order');
                    elements.limitSelect = document.getElementById('limit');
                    elements.exportBtn = document.getElementById('export-btn');
                    elements.bulkActionsBar = document.getElementById('bulk-actions-bar');
                    elements.selectedCount = document.getElementById('selected-count');
                    elements.bulkResubscribeBtn = document.getElementById('bulk-resubscribe-btn');
                    elements.bulkUnsubscribeBtn = document.getElementById('bulk-unsubscribe-btn');
                    elements.bulkDeleteBtn = document.getElementById('bulk-delete-btn');
                    elements.clearSelectionBtn = document.getElementById('clear-selection-btn');
                    elements.selectAllCheckbox = document.getElementById('select-all-checkbox');
                    elements.subscribersTableContainer = document.getElementById('subscribers-table-container');
                    elements.paginationContainer = document.getElementById('pagination-container');
                }

                /**
                 * Initialize the page
                 */
                function init() {
                    refreshElements();
                    setupEventListeners();
                    setupDeleteButtons();
                    setupCheckboxes();
                }

                /**
                 * Setup all event listeners
                 */
                function setupEventListeners() {
                    // Search input with debounce
                    if (elements.searchInput) {
                        elements.searchInput.addEventListener('input', handleSearchInput);
                    }

                    // Filter changes
                    if (elements.statusFilter) {
                        elements.statusFilter.addEventListener('change', handleFilterChange);
                    }

                    if (elements.orderSelect) {
                        elements.orderSelect.addEventListener('change', handleFilterChange);
                    }

                    if (elements.limitSelect) {
                        elements.limitSelect.addEventListener('change', handleFilterChange);
                    }

                    // Export button
                    if (elements.exportBtn) {
                        elements.exportBtn.addEventListener('click', handleExport);
                    }

                    // Bulk action buttons
                    if (elements.bulkResubscribeBtn) {
                        elements.bulkResubscribeBtn.addEventListener('click', handleBulkResubscribe);
                    }

                    if (elements.bulkUnsubscribeBtn) {
                        elements.bulkUnsubscribeBtn.addEventListener('click', handleBulkUnsubscribe);
                    }

                    if (elements.bulkDeleteBtn) {
                        elements.bulkDeleteBtn.addEventListener('click', handleBulkDelete);
                    }

                    if (elements.clearSelectionBtn) {
                        elements.clearSelectionBtn.addEventListener('click', clearSelection);
                    }

                    // Pagination
                    setupPaginationListeners();

                    // Setup select all - moved to setupCheckboxes
                }

                /**
                 * Setup checkboxes
                 */
                function setupCheckboxes() {
                    const checkboxes = document.querySelectorAll('.subscriber-checkbox');

                    if (checkboxes.length === 0) {
                        return; // No checkboxes to set up
                    }

                    // Setup individual checkboxes
                    checkboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            const subscriberId = parseInt(this.getAttribute('data-subscriber-id'));
                            toggleSubscriberSelection(subscriberId);
                        });
                    });

                    // Setup select all checkbox (do this every time checkboxes are set up)
                    const selectAllCheckbox = document.getElementById('select-all-checkbox');
                    if (selectAllCheckbox) {
                        // Remove old listener if exists (prevent duplicate listeners)
                        selectAllCheckbox.replaceWith(selectAllCheckbox.cloneNode(true));

                        // Get fresh reference and add listener
                        elements.selectAllCheckbox = document.getElementById('select-all-checkbox');
                        if (elements.selectAllCheckbox) {
                            elements.selectAllCheckbox.addEventListener('change', handleSelectAll);
                        }
                    }

                    updateSelectAllState();
                }

                /**
                 * Handle search input with debounce
                 */
                function handleSearchInput() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        handleFilterChange();
                    }, CONFIG.debounceDelay);
                }

                /**
                 * Handle filter changes
                 */
                function handleFilterChange() {
                    if (isLoading) return;

                    const params = getFilterParams();
                    loadSubscribers(params);
                }

                /**
                 * Get current filter parameters
                 */
                function getFilterParams() {
                    const params = new URLSearchParams();

                    if (elements.searchInput?.value) {
                        params.append('search', elements.searchInput.value);
                    }

                    if (elements.statusFilter?.value) {
                        params.append('status', elements.statusFilter.value);
                    }

                    if (elements.orderSelect?.value) {
                        params.append('order', elements.orderSelect.value);
                    }

                    if (elements.limitSelect?.value) {
                        params.append('limit', elements.limitSelect.value);
                    }

                    return params;
                }

                /**
                 * Load subscribers via AJAX
                 */
                function loadSubscribers(params, page = null) {
                    if (isLoading) return;

                    isLoading = true;
                    showLoading();

                    if (page) {
                        params.append('page', page);
                    }

                    const url = `${window.location.pathname}?${params.toString()}`;

                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update table
                                if (elements.subscribersTableContainer && data.html) {
                                    elements.subscribersTableContainer.innerHTML = data.html;
                                    // Refresh select all checkbox reference after table update
                                    refreshElements();
                                    setupDeleteButtons();
                                    setupCheckboxes();
                                }

                                // Update pagination
                                if (elements.paginationContainer) {
                                    if (data.pagination) {
                                        elements.paginationContainer.innerHTML = data.pagination;
                                        elements.paginationContainer.style.display = 'block';
                                    } else {
                                        elements.paginationContainer.innerHTML = '';
                                        elements.paginationContainer.style.display = 'none';
                                    }
                                    setupPaginationListeners();
                                }

                                // Update stats
                                if (data.stats) {
                                    updateStats(data.stats);
                                }

                                // Update URL
                                window.history.pushState({}, '', url);

                                // Clear selection after reload
                                clearSelection();
                            }
                        })
                        .catch(error => {
                            console.error('Error loading subscribers:', error);
                            showNotification('Failed to load subscribers. Please try again.', 'error');
                        })
                        .finally(() => {
                            isLoading = false;
                            hideLoading();
                        });
                }

                /**
                 * Update statistics
                 */
                function updateStats(stats) {
                    const statTotal = document.getElementById('stat-total');
                    const statActive = document.getElementById('stat-active');
                    const statUnsubscribed = document.getElementById('stat-unsubscribed');
                    const statMonth = document.getElementById('stat-month');

                    if (statTotal) statTotal.textContent = stats.total.toLocaleString();
                    if (statActive) statActive.textContent = stats.active.toLocaleString();
                    if (statUnsubscribed) statUnsubscribed.textContent = stats.unsubscribed.toLocaleString();
                    if (statMonth) statMonth.textContent = stats.this_month.toLocaleString();
                }

                /**
                 * Setup pagination listeners
                 */
                function setupPaginationListeners() {
                    if (!elements.paginationContainer) {
                        elements.paginationContainer = document.getElementById('pagination-container');
                    }

                    if (!elements.paginationContainer) {
                        return; // No pagination container
                    }

                    const paginationLinks = elements.paginationContainer.querySelectorAll('.pagination a');
                    paginationLinks.forEach(link => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            const url = new URL(this.href);
                            const page = url.searchParams.get('page');
                            const params = getFilterParams();
                            loadSubscribers(params, page);
                        });
                    });
                }

                /**
                 * Setup delete buttons
                 */
                function setupDeleteButtons() {
                    document.querySelectorAll('.delete-subscriber-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const subscriberId = this.getAttribute('data-subscriber-id');
                            const subscriberEmail = this.getAttribute('data-subscriber-email');
                            handleDeleteSubscriber(subscriberId, subscriberEmail);
                        });
                    });
                }

                /**
                 * Handle subscriber deletion
                 */
                function handleDeleteSubscriber(subscriberId, subscriberEmail) {
                    if (!confirm(`Are you sure you want to delete subscriber "${subscriberEmail}"?`)) {
                        return;
                    }

                    const deleteUrl = `/admin/subscribers/${subscriberId}`;

                    fetch(deleteUrl, {
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

                                // Reload the page
                                const params = getFilterParams();
                                loadSubscribers(params);
                            } else {
                                showNotification(data.message || 'Failed to delete subscriber', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting subscriber:', error);
                            showNotification('Failed to delete subscriber. Please try again.', 'error');
                        });
                }

                /**
                 * Toggle subscriber selection
                 */
                function toggleSubscriberSelection(subscriberId) {
                    const index = selectedSubscribers.indexOf(subscriberId);

                    if (index > -1) {
                        selectedSubscribers.splice(index, 1);
                    } else {
                        selectedSubscribers.push(subscriberId);
                    }

                    updateBulkActionsBar();
                    updateSelectAllState();
                    updateRowHighlighting();
                }

                /**
                 * Handle select all checkbox
                 */
                function handleSelectAll() {
                    const checkboxes = document.querySelectorAll('.subscriber-checkbox');

                    // Refresh checkbox reference
                    if (!elements.selectAllCheckbox) {
                        elements.selectAllCheckbox = document.getElementById('select-all-checkbox');
                    }

                    if (!elements.selectAllCheckbox) {
                        return;
                    }

                    const isChecked = elements.selectAllCheckbox.checked;

                    checkboxes.forEach(checkbox => {
                        const subscriberId = parseInt(checkbox.getAttribute('data-subscriber-id'));
                        checkbox.checked = isChecked;

                        if (isChecked && !selectedSubscribers.includes(subscriberId)) {
                            selectedSubscribers.push(subscriberId);
                        } else if (!isChecked) {
                            const index = selectedSubscribers.indexOf(subscriberId);
                            if (index > -1) {
                                selectedSubscribers.splice(index, 1);
                            }
                        }
                    });

                    updateBulkActionsBar();
                    updateRowHighlighting();
                }

                /**
                 * Update select all checkbox state
                 */
                function updateSelectAllState() {
                    // Refresh checkbox reference
                    if (!elements.selectAllCheckbox) {
                        elements.selectAllCheckbox = document.getElementById('select-all-checkbox');
                    }

                    if (!elements.selectAllCheckbox) {
                        return; // Checkbox not available
                    }

                    const checkboxes = document.querySelectorAll('.subscriber-checkbox');
                    const totalCheckboxes = checkboxes.length;
                    const checkedCheckboxes = Array.from(checkboxes).filter(cb => cb.checked).length;

                    elements.selectAllCheckbox.checked = totalCheckboxes > 0 && checkedCheckboxes === totalCheckboxes;
                }

                /**
                 * Update row highlighting
                 */
                function updateRowHighlighting() {
                    document.querySelectorAll('.subscriber-row').forEach(row => {
                        const subscriberId = parseInt(row.getAttribute('data-subscriber-id'));
                        if (selectedSubscribers.includes(subscriberId)) {
                            row.classList.add('bg-blue-50');
                        } else {
                            row.classList.remove('bg-blue-50');
                        }
                    });
                }

                /**
                 * Update bulk actions bar
                 */
                function updateBulkActionsBar() {
                    if (selectedSubscribers.length > 0) {
                        elements.bulkActionsBar?.classList.remove('hidden');
                        if (elements.selectedCount) {
                            elements.selectedCount.textContent = selectedSubscribers.length;
                        }
                    } else {
                        elements.bulkActionsBar?.classList.add('hidden');
                    }
                }

                /**
                 * Clear selection
                 */
                function clearSelection() {
                    selectedSubscribers = [];

                    document.querySelectorAll('.subscriber-checkbox').forEach(checkbox => {
                        checkbox.checked = false;
                    });

                    // Refresh checkbox reference
                    if (!elements.selectAllCheckbox) {
                        elements.selectAllCheckbox = document.getElementById('select-all-checkbox');
                    }

                    if (elements.selectAllCheckbox) {
                        elements.selectAllCheckbox.checked = false;
                    }

                    updateBulkActionsBar();
                    updateRowHighlighting();
                }

                /**
                 * Handle bulk resubscribe
                 */
                function handleBulkResubscribe() {
                    if (selectedSubscribers.length === 0) {
                        showNotification('No subscribers selected', 'error');
                        return;
                    }

                    if (!confirm(`Are you sure you want to resubscribe ${selectedSubscribers.length} subscriber(s)?`)) {
                        return;
                    }

                    fetch('/admin/subscribers/bulk-resubscribe', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ ids: selectedSubscribers })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message, 'success');
                                const params = getFilterParams();
                                loadSubscribers(params);
                            } else {
                                showNotification(data.message || 'Failed to resubscribe subscribers', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Failed to resubscribe subscribers. Please try again.', 'error');
                        });
                }

                /**
                 * Handle bulk unsubscribe
                 */
                function handleBulkUnsubscribe() {
                    if (selectedSubscribers.length === 0) {
                        showNotification('No subscribers selected', 'error');
                        return;
                    }

                    if (!confirm(`Are you sure you want to unsubscribe ${selectedSubscribers.length} subscriber(s)?`)) {
                        return;
                    }

                    fetch('/admin/subscribers/bulk-unsubscribe', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ ids: selectedSubscribers })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message, 'success');
                                const params = getFilterParams();
                                loadSubscribers(params);
                            } else {
                                showNotification(data.message || 'Failed to unsubscribe subscribers', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Failed to unsubscribe subscribers. Please try again.', 'error');
                        });
                }

                /**
                 * Handle bulk delete
                 */
                function handleBulkDelete() {
                    if (selectedSubscribers.length === 0) {
                        showNotification('No subscribers selected', 'error');
                        return;
                    }

                    if (!confirm(`Are you sure you want to delete ${selectedSubscribers.length} subscriber(s)? This action cannot be undone.`)) {
                        return;
                    }

                    fetch('/admin/subscribers/bulk-delete', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ ids: selectedSubscribers })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message, 'success');
                                const params = getFilterParams();
                                loadSubscribers(params);
                            } else {
                                showNotification(data.message || 'Failed to delete subscribers', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Failed to delete subscribers. Please try again.', 'error');
                        });
                }

                /**
                 * Handle export
                 */
                function handleExport() {
                    const statusFilter = elements.statusFilter?.value || 'all';
                    const url = `/admin/subscribers/export?status=${statusFilter}`;

                    // Create a temporary link and click it
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `subscribers_${new Date().toISOString().split('T')[0]}.csv`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    showNotification('Export started! Your download should begin shortly.', 'success');
                }

                /**
                 * Show loading state
                 */
                function showLoading() {
                    if (elements.subscribersTableContainer) {
                        elements.subscribersTableContainer.classList.add('loading-overlay');
                    }
                }

                /**
                 * Hide loading state
                 */
                function hideLoading() {
                    if (elements.subscribersTableContainer) {
                        elements.subscribersTableContainer.classList.remove('loading-overlay');
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
