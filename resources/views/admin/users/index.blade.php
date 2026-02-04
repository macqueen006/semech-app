<x-app-layout>
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users</h1>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">Manage user accounts and permissions</p>
            </div>
            @can('user-create')
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.users.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create User
                    </a>
                </div>
            @endcan
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

        <!-- Bulk Actions Bar -->
        @can('user-delete')
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button
                            id="bulk-mode-toggle"
                            class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:opacity-80 transition">
                            <i class="fa-solid fa-check-square mr-2"></i>
                            <span id="bulk-mode-text">Bulk Select</span>
                        </button>

                        <div id="bulk-actions" class="hidden items-center gap-2">
                            <button
                                id="select-all-btn"
                                class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600">
                                Select All
                            </button>
                            <button
                                id="deselect-all-btn"
                                class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600">
                                Deselect All
                            </button>
                            <span id="selected-count" class="text-sm text-gray-600 dark:text-gray-400">
                                0 selected
                            </span>
                        </div>
                    </div>

                    <button
                        id="bulk-delete-btn"
                        class="hidden px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                        <i class="fa-solid fa-trash mr-2"></i>
                        Delete Selected (<span id="delete-count">0</span>)
                    </button>
                </div>
            </div>
        @endcan

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                    <input type="text"
                           id="search-input"
                           value="{{ $search }}"
                           placeholder="Search users..."
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Order</label>
                    <select id="order-filter"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="desc" {{ $order === 'desc' ? 'selected' : '' }}>Newest</option>
                        <option value="asc" {{ $order === 'asc' ? 'selected' : '' }}>Oldest</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Limit</label>
                    <select id="limit-filter"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="10" {{ $limit == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $limit == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50</option>
                        <option value="0" {{ $limit == 0 ? 'selected' : '' }}>All</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Role</label>
                    <div class="space-y-2 max-h-32 overflow-y-auto p-2 border border-gray-300 dark:border-gray-600 rounded-lg">
                        @foreach($roles as $role)
                            <label class="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    class="role-filter form-checkbox"
                                    value="{{ $role->id }}"
                                    {{ in_array($role->id, $selectedRoles ?? []) ? 'checked' : '' }}>
                                <span class="dark:text-gray-300">{{ $role->name }} ({{ $role->users_count }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto" id="table-container">
                @include('admin.users.partials.table', ['users' => $users, 'userStats' => $userStats])
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700" id="pagination-container">
                @if($limit > 0)
                    {{ $users->links() }}
                @endif
            </div>
        </div>
    </div>

    <!-- User Modal Container -->
    <div id="user-modal-container"></div>

    @push('scripts')
        <script>
            (function() {
                'use strict';

                const CONFIG = {
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                };

                const elements = {
                    searchInput: document.getElementById('search-input'),
                    orderFilter: document.getElementById('order-filter'),
                    limitFilter: document.getElementById('limit-filter'),
                    roleFilters: document.querySelectorAll('.role-filter'),
                    tableContainer: document.getElementById('table-container'),
                    paginationContainer: document.getElementById('pagination-container'),
                    bulkModeToggle: document.getElementById('bulk-mode-toggle'),
                    bulkModeText: document.getElementById('bulk-mode-text'),
                    bulkActions: document.getElementById('bulk-actions'),
                    bulkDeleteBtn: document.getElementById('bulk-delete-btn'),
                    selectAllBtn: document.getElementById('select-all-btn'),
                    deselectAllBtn: document.getElementById('deselect-all-btn'),
                    selectedCount: document.getElementById('selected-count'),
                    deleteCount: document.getElementById('delete-count'),
                    userModalContainer: document.getElementById('user-modal-container'),
                };

                let debounceTimer;
                let bulkMode = false;
                let selectedUsers = [];

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
                    // Search with debounce
                    elements.searchInput.addEventListener('input', function() {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(() => loadUsers(), 300);
                    });

                    // Filters
                    elements.orderFilter.addEventListener('change', loadUsers);
                    elements.limitFilter.addEventListener('change', loadUsers);
                    elements.roleFilters.forEach(filter => {
                        filter.addEventListener('change', loadUsers);
                    });

                    // Pagination (delegated)
                    elements.paginationContainer.addEventListener('click', handlePaginationClick);

                    // Bulk mode toggle
                    if (elements.bulkModeToggle) {
                        elements.bulkModeToggle.addEventListener('click', toggleBulkMode);
                    }

                    // Bulk action buttons
                    if (elements.selectAllBtn) {
                        elements.selectAllBtn.addEventListener('click', selectAllUsers);
                    }
                    if (elements.deselectAllBtn) {
                        elements.deselectAllBtn.addEventListener('click', deselectAllUsers);
                    }
                    if (elements.bulkDeleteBtn) {
                        elements.bulkDeleteBtn.addEventListener('click', bulkDeleteUsers);
                    }
                }

                /**
                 * Handle pagination click
                 */
                function handlePaginationClick(e) {
                    const link = e.target.closest('a');
                    if (!link) return;

                    e.preventDefault();
                    const url = new URL(link.href);
                    loadUsers(url.searchParams.get('page'));
                }

                /**
                 * Load users via AJAX
                 */
                function loadUsers(page = 1) {
                    const selectedRoles = Array.from(elements.roleFilters)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);

                    const params = new URLSearchParams({
                        search: elements.searchInput.value,
                        order: elements.orderFilter.value,
                        limit: elements.limitFilter.value,
                        page: page
                    });

                    selectedRoles.forEach(roleId => {
                        params.append('roles[]', roleId);
                    });

                    // Remove empty parameters and default page
                    for (let [key, value] of [...params.entries()]) {
                        if (!value || value === '' || (key === 'page' && value === '1')) {
                            params.delete(key);
                        }
                    }

                    const baseUrl = `{{ route('admin.users.index') }}`;
                    const queryString = params.toString();
                    const url = queryString ? `${baseUrl}?${queryString}` : baseUrl;

                    fetch(url, {
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
                                elements.tableContainer.innerHTML = data.html;
                                elements.paginationContainer.innerHTML = data.pagination;

                                // Update URL without reload
                                if (queryString) {
                                    window.history.pushState({}, '', url);
                                } else {
                                    window.history.pushState({}, '', baseUrl);
                                }

                                // Re-attach event listeners for new table
                                attachTableEventListeners();

                                // Reset bulk selections
                                selectedUsers = [];
                                updateBulkUI();
                            }
                        })
                        .catch(error => {
                            console.error('Error loading users:', error);
                        });
                }

                /**
                 * Attach event listeners to table elements
                 */
                function attachTableEventListeners() {
                    // View user buttons
                    document.querySelectorAll('.view-user-btn').forEach(btn => {
                        btn.addEventListener('click', handleViewUser);
                    });

                    // Delete buttons
                    document.querySelectorAll('.delete-user-btn').forEach(btn => {
                        btn.addEventListener('click', handleDelete);
                    });

                    // Bulk checkboxes - always attach listeners
                    document.querySelectorAll('.user-checkbox').forEach(cb => {
                        cb.addEventListener('change', handleUserSelection);

                        // Show/hide based on current bulk mode state
                        if (bulkMode) {
                            cb.classList.remove('hidden');
                        } else {
                            cb.classList.add('hidden');
                        }
                    });
                }

                /**
                 * Toggle bulk mode
                 */
                function toggleBulkMode() {
                    bulkMode = !bulkMode;

                    if (bulkMode) {
                        elements.bulkModeToggle.classList.remove('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
                        elements.bulkModeToggle.classList.add('bg-blue-600', 'text-white');
                        elements.bulkModeText.textContent = 'Exit Bulk Mode';
                        elements.bulkActions.classList.remove('hidden');
                        elements.bulkActions.classList.add('flex');

                        // Show all checkboxes
                        document.querySelectorAll('.user-checkbox').forEach(cb => {
                            cb.classList.remove('hidden');
                        });
                    } else {
                        elements.bulkModeToggle.classList.remove('bg-blue-600', 'text-white');
                        elements.bulkModeToggle.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
                        elements.bulkModeText.textContent = 'Bulk Select';
                        elements.bulkActions.classList.add('hidden');
                        elements.bulkActions.classList.remove('flex');
                        selectedUsers = [];

                        // Hide all checkboxes and uncheck them
                        document.querySelectorAll('.user-checkbox').forEach(cb => {
                            cb.checked = false;
                            cb.classList.add('hidden');
                        });
                    }
                }

                /**
                 * Handle user selection
                 */
                function handleUserSelection(e) {
                    const userId = parseInt(e.target.value);

                    if (e.target.checked) {
                        if (!selectedUsers.includes(userId)) {
                            selectedUsers.push(userId);
                        }
                    } else {
                        selectedUsers = selectedUsers.filter(id => id !== userId);
                    }

                    updateBulkUI();
                }

                /**
                 * Select all users
                 */
                function selectAllUsers() {
                    const checkboxes = document.querySelectorAll('.user-checkbox');
                    selectedUsers = [];

                    checkboxes.forEach(cb => {
                        if (!cb.classList.contains('hidden')) {
                            cb.checked = true;
                            selectedUsers.push(parseInt(cb.value));
                        }
                    });

                    updateBulkUI();
                }

                /**
                 * Deselect all users
                 */
                function deselectAllUsers() {
                    const checkboxes = document.querySelectorAll('.user-checkbox');
                    checkboxes.forEach(cb => {
                        cb.checked = false;
                    });

                    selectedUsers = [];
                    updateBulkUI();
                }

                /**
                 * Update bulk UI
                 */
                function updateBulkUI() {
                    const count = selectedUsers.length;
                    elements.selectedCount.textContent = `${count} selected`;
                    elements.deleteCount.textContent = count;

                    if (count > 0) {
                        elements.bulkDeleteBtn.classList.remove('hidden');
                    } else {
                        elements.bulkDeleteBtn.classList.add('hidden');
                    }
                }

                /**
                 * Bulk delete users
                 */
                function bulkDeleteUsers() {
                    if (selectedUsers.length === 0) {
                        showNotification('No users selected', 'error');
                        return;
                    }

                    if (!confirm(`Are you sure you want to delete ${selectedUsers.length} user(s)?`)) {
                        return;
                    }

                    fetch(`{{ route('admin.users.bulk-delete') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CONFIG.csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            user_ids: selectedUsers
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(data.message, 'success');
                                selectedUsers = [];
                                updateBulkUI();
                                loadUsers();
                            } else {
                                showNotification(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error bulk deleting users:', error);
                            showNotification('Failed to delete users. Please try again.', 'error');
                        });
                }

                /**
                 * Handle view user
                 */
                function handleViewUser(e) {
                    e.preventDefault();
                    const btn = e.currentTarget;
                    const userId = btn.getAttribute('data-id');

                    fetch(`{{ url('admin/users') }}/${userId}`, {
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
                                elements.userModalContainer.innerHTML = data.html;
                                attachModalEventListeners();
                            }
                        })
                        .catch(error => {
                            console.error('Error loading user details:', error);
                        });
                }

                /**
                 * Attach modal event listeners
                 */
                function attachModalEventListeners() {
                    const closeBtn = document.getElementById('close-user-modal');
                    if (closeBtn) {
                        closeBtn.addEventListener('click', closeUserModal);
                    }

                    const modalOverlay = document.getElementById('user-modal-overlay');
                    if (modalOverlay) {
                        modalOverlay.addEventListener('click', closeUserModal);
                    }

                    const modalContent = document.getElementById('user-modal-content');
                    if (modalContent) {
                        modalContent.addEventListener('click', (e) => e.stopPropagation());
                    }
                }

                /**
                 * Close user modal
                 */
                function closeUserModal() {
                    elements.userModalContainer.innerHTML = '';
                }

                /**
                 * Handle delete
                 */
                function handleDelete(e) {
                    e.preventDefault();
                    const btn = e.currentTarget;
                    const userId = btn.getAttribute('data-id');

                    if (!confirm('Are you sure you want to delete this user?')) {
                        return;
                    }

                    fetch(`{{ url('admin/users') }}/${userId}`, {
                        method: 'DELETE',
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
                                loadUsers();
                            } else {
                                showNotification(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting user:', error);
                            showNotification('Failed to delete user. Please try again.', 'error');
                        });
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

                // Initial attachment of table event listeners
                attachTableEventListeners();

            })();
        </script>
    @endpush
</x-app-layout>
