<x-app-layout>
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Advertisements</h1>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">Manage your advertisement campaigns</p>
            </div>
            @can('advertisement-create')
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.advertisements.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create Advertisement
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

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                    <input type="text"
                           id="search-input"
                           value="{{ $search }}"
                           placeholder="Search advertisements..."
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Position</label>
                    <select id="position-filter"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Positions</option>
                        <option value="header" {{ $position === 'header' ? 'selected' : '' }}>Header</option>
                        <option value="sidebar" {{ $position === 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                        <option value="footer" {{ $position === 'footer' ? 'selected' : '' }}>Footer</option>
                        <option value="between-posts" {{ $position === 'between-posts' ? 'selected' : '' }}>Between Posts</option>
                        <option value="popup" {{ $position === 'popup' ? 'selected' : '' }}>Popup</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select id="status-filter"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">All Status</option>
                        <option value="1" {{ $status === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $status === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Advertisements Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto" id="table-container">
                @include('admin.advertisements.partials.table', ['advertisements' => $advertisements])
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700" id="pagination-container">
                {{ $advertisements->links() }}
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
                    searchInput: document.getElementById('search-input'),
                    positionFilter: document.getElementById('position-filter'),
                    statusFilter: document.getElementById('status-filter'),
                    tableContainer: document.getElementById('table-container'),
                    paginationContainer: document.getElementById('pagination-container'),
                };

                let debounceTimer;

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
                        debounceTimer = setTimeout(() => loadAdvertisements(), 300);
                    });

                    // Filters
                    elements.positionFilter.addEventListener('change', loadAdvertisements);
                    elements.statusFilter.addEventListener('change', loadAdvertisements);

                    // Pagination (delegated)
                    elements.paginationContainer.addEventListener('click', handlePaginationClick);
                }

                /**
                 * Handle pagination click
                 */
                function handlePaginationClick(e) {
                    const link = e.target.closest('a');
                    if (!link) return;

                    e.preventDefault();
                    const url = new URL(link.href);
                    loadAdvertisements(url.searchParams.get('page'));
                }

                /**
                 * Load advertisements via AJAX
                 */
                function loadAdvertisements(page = 1) {
                    const params = new URLSearchParams({
                        search: elements.searchInput.value,
                        position: elements.positionFilter.value,
                        status: elements.statusFilter.value,
                        page: page
                    });

                    // Remove empty parameters and default page
                    for (let [key, value] of [...params.entries()]) {
                        if (!value || value === '' || (key === 'page' && value === '1')) {
                            params.delete(key);
                        }
                    }

                    const baseUrl = `{{ route('admin.advertisements.index') }}`;
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

                                // Update URL without reload (only if there are actual parameters)
                                if (queryString) {
                                    window.history.pushState({}, '', url);
                                } else {
                                    window.history.pushState({}, '', baseUrl);
                                }

                                // Re-attach event listeners for new table
                                attachTableEventListeners();
                            }
                        })
                        .catch(error => {
                            console.error('Error loading advertisements:', error);
                        });
                }

                /**
                 * Attach event listeners to table elements
                 */
                function attachTableEventListeners() {
                    // Toggle status buttons
                    document.querySelectorAll('.toggle-status-btn').forEach(btn => {
                        btn.addEventListener('click', handleToggleStatus);
                    });

                    // Delete buttons
                    document.querySelectorAll('.delete-ad-btn').forEach(btn => {
                        btn.addEventListener('click', handleDelete);
                    });
                }

                /**
                 * Handle toggle status
                 */
                function handleToggleStatus(e) {
                    e.preventDefault();
                    const btn = e.currentTarget;
                    const adId = btn.getAttribute('data-id');

                    fetch(`/admin/advertisements/${adId}/toggle-status`, {
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
                                // Update toggle button appearance
                                const isActive = data.is_active;
                                btn.classList.toggle('bg-blue-600', isActive);
                                btn.classList.toggle('bg-gray-200', !isActive);
                                btn.classList.toggle('dark:bg-gray-700', !isActive);

                                const slider = btn.querySelector('span');
                                slider.classList.toggle('translate-x-6', isActive);
                                slider.classList.toggle('translate-x-1', !isActive);

                                showNotification(data.message, 'success');
                            } else {
                                showNotification(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error toggling status:', error);
                            showNotification('Failed to update status. Please try again.', 'error');
                        });
                }

                /**
                 * Handle delete
                 */
                function handleDelete(e) {
                    e.preventDefault();
                    const btn = e.currentTarget;
                    const adId = btn.getAttribute('data-id');

                    if (!confirm('Are you sure you want to delete this advertisement?')) {
                        return;
                    }

                    fetch(`/admin/advertisements/${adId}`, {
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
                                loadAdvertisements(); // Reload table
                            } else {
                                showNotification(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting advertisement:', error);
                            showNotification('Failed to delete advertisement. Please try again.', 'error');
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
