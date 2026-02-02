<x-app-layout>
    <div class="p-4" id="comments-container">
        <!-- Header -->
        <div class="page-header">
            <h1>Comments</h1>
            <div class="header-stats">
            <span class="stat-badge">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
                <span
                    id="comments-count">{{ method_exists($comments, 'total') ? $comments->total() : $comments->count() }}</span> comments
            </span>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <form id="filters-form" method="GET" action="{{ route('admin.comments.index') }}">
                <div class="filters-grid">
                    <!-- Search -->
                    <div class="filter-group">
                        <label for="search">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Search
                        </label>
                        <input
                            type="text"
                            id="search"
                            name="q"
                            value="{{ $terms }}"
                            placeholder="Search by name or content..."
                            class="filter-input"
                        >
                    </div>

                    <!-- Sort Order -->
                    <div class="filter-group">
                        <label for="order">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                            </svg>
                            Sort order
                        </label>
                        <select id="order" name="order" class="filter-select">
                            <option value="desc" {{ $order === 'desc' ? 'selected' : '' }}>Newest first</option>
                            <option value="asc" {{ $order === 'asc' ? 'selected' : '' }}>Oldest first</option>
                        </select>
                    </div>

                    <!-- Limit -->
                    <div class="filter-group">
                        <label for="limit">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                            Limit
                        </label>
                        <select id="limit" name="limit" class="filter-select">
                            <option value="20" {{ $limit == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $limit == 100 ? 'selected' : '' }}>100</option>
                            <option value="0" {{ $limit == 0 ? 'selected' : '' }}>All</option>
                        </select>
                    </div>

                    <!-- User Filter (only for super users) -->
                    @if(Auth::user()->hasPermissionTo('comment-super-list'))
                        <div class="filter-group">
                            <label for="users">
                                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                Post authors
                            </label>
                            <select id="users" name="users[]" multiple class="filter-select">
                                @foreach($users as $user)
                                    <option
                                        value="{{ $user->id }}" {{ in_array($user->id, $selectedUserIds) ? 'selected' : '' }}>
                                        {{ $user->firstname }} {{ $user->lastname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <!-- Active Filters -->
                @if($terms || count($selectedUserIds) > 0)
                    <div class="active-filters">
                        <span class="filter-label">Active filters:</span>

                        @if($terms)
                            <span class="filter-tag">
                            Search: "{{ $terms }}"
                            <button type="button" class="filter-tag-remove" data-clear="search">×</button>
                        </span>
                        @endif

                        @foreach($selectedUsers as $user)
                            <span class="filter-tag">
                            {{ $user->firstname }} {{ $user->lastname }}
                            <button type="button" class="filter-tag-remove" data-clear-user="{{ $user->id }}">×</button>
                        </span>
                        @endforeach

                        <button type="button" id="clear-all-filters" class="btn-clear-filters">
                            Clear all
                        </button>
                    </div>
                @endif
            </form>
        </div>

        <!-- Flash Messages -->
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

        <!-- Comments List -->
        <div id="comments-list">
            @include('admin.comments.partials.comments-list', ['comments' => $comments])
        </div>

        <!-- Pagination -->
        @if(is_object($comments) && method_exists($comments, 'links'))
            <div class="pagination-wrapper" id="pagination-container">
                {{ $comments->appends(request()->query())->links() }}
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

            .header-stats {
                display: flex;
                gap: 1rem;
            }

            .stat-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 9999px;
                font-size: 0.875rem;
                font-weight: 600;
            }

            .filters-card {
                background: white;
                border-radius: 12px;
                padding: 1.5rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                margin-bottom: 2rem;
            }

            .filters-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1.5rem;
                margin-bottom: 1rem;
            }

            .filter-group {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .filter-group label {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.875rem;
                font-weight: 600;
                color: #374151;
            }

            .filter-input, .filter-select {
                padding: 0.625rem 1rem;
                border: 1px solid #d1d5db;
                border-radius: 8px;
                font-size: 0.875rem;
                transition: all 0.2s;
            }

            .filter-input:focus, .filter-select:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }

            .filter-select[multiple] {
                min-height: 100px;
            }

            .active-filters {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                flex-wrap: wrap;
                padding-top: 1rem;
                border-top: 1px solid #e5e7eb;
            }

            .filter-label {
                font-size: 0.875rem;
                font-weight: 600;
                color: #6b7280;
            }

            .filter-tag {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.25rem 0.75rem;
                background: #f3f4f6;
                border-radius: 9999px;
                font-size: 0.875rem;
                color: #374151;
            }

            .filter-tag-remove {
                background: none;
                border: none;
                color: #6b7280;
                font-size: 1.25rem;
                cursor: pointer;
                padding: 0;
                line-height: 1;
            }

            .filter-tag-remove:hover {
                color: #111827;
            }

            .btn-clear-filters {
                padding: 0.25rem 0.75rem;
                background: transparent;
                border: 1px solid #d1d5db;
                border-radius: 9999px;
                font-size: 0.875rem;
                color: #6b7280;
                cursor: pointer;
                transition: all 0.2s;
            }

            .btn-clear-filters:hover {
                background: #f3f4f6;
                border-color: #9ca3af;
            }

            .alert {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
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

            .icon {
                width: 1.125rem;
                height: 1.125rem;
            }

            .pagination-wrapper {
                margin-top: 2rem;
            }

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
                border-top: 3px solid #667eea;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% {
                    transform: translate(-50%, -50%) rotate(0deg);
                }
                100% {
                    transform: translate(-50%, -50%) rotate(360deg);
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function () {
                'use strict';

                // Configuration
                const CONFIG = {
                    debounceDelay: 300,
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                };

                // Elements
                const elements = {
                    searchInput: document.getElementById('search'),
                    orderSelect: document.getElementById('order'),
                    limitSelect: document.getElementById('limit'),
                    usersSelect: document.getElementById('users'),
                    clearAllBtn: document.getElementById('clear-all-filters'),
                    commentsList: document.getElementById('comments-list'),
                    paginationContainer: document.getElementById('pagination-container'),
                    commentsCount: document.getElementById('comments-count'),
                    filtersForm: document.getElementById('filters-form'),
                };

                // State
                let debounceTimer = null;
                let isLoading = false;

                /**
                 * Initialize the page
                 */
                function init() {
                    setupEventListeners();
                    setupDeleteButtons();
                }

                /**
                 * Setup all event listeners
                 */
                function setupEventListeners() {
                    // Search input with debounce
                    if (elements.searchInput) {
                        elements.searchInput.addEventListener('input', handleSearchInput);
                    }

                    // Sort order change
                    if (elements.orderSelect) {
                        elements.orderSelect.addEventListener('change', handleFilterChange);
                    }

                    // Limit change
                    if (elements.limitSelect) {
                        elements.limitSelect.addEventListener('change', handleFilterChange);
                    }

                    // Users filter change
                    if (elements.usersSelect) {
                        elements.usersSelect.addEventListener('change', handleFilterChange);
                    }

                    // Clear all filters
                    if (elements.clearAllBtn) {
                        elements.clearAllBtn.addEventListener('click', handleClearAllFilters);
                    }

                    // Clear individual filters
                    document.querySelectorAll('[data-clear]').forEach(btn => {
                        btn.addEventListener('click', function () {
                            const clearType = this.getAttribute('data-clear');
                            if (clearType === 'search') {
                                elements.searchInput.value = '';
                                handleFilterChange();
                            }
                        });
                    });

                    document.querySelectorAll('[data-clear-user]').forEach(btn => {
                        btn.addEventListener('click', function () {
                            const userId = this.getAttribute('data-clear-user');
                            if (elements.usersSelect) {
                                const options = elements.usersSelect.options;
                                for (let i = 0; i < options.length; i++) {
                                    if (options[i].value === userId) {
                                        options[i].selected = false;
                                        break;
                                    }
                                }
                                handleFilterChange();
                            }
                        });
                    });

                    // Pagination links
                    setupPaginationListeners();
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
                    loadComments(params);
                }

                /**
                 * Get current filter parameters
                 */
                function getFilterParams() {
                    const params = new URLSearchParams();

                    if (elements.searchInput?.value) {
                        params.append('q', elements.searchInput.value);
                    }

                    if (elements.orderSelect?.value) {
                        params.append('order', elements.orderSelect.value);
                    }

                    if (elements.limitSelect?.value) {
                        params.append('limit', elements.limitSelect.value);
                    }

                    if (elements.usersSelect) {
                        const selectedUsers = Array.from(elements.usersSelect.selectedOptions)
                            .map(option => option.value);
                        selectedUsers.forEach(userId => {
                            params.append('users[]', userId);
                        });
                    }

                    return params;
                }

                /**
                 * Load comments via AJAX
                 */
                function loadComments(params, page = null) {
                    if (isLoading) return;

                    isLoading = true;
                    showLoading();

                    // Add page parameter if provided
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
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Update comments list
                                if (elements.commentsList && data.html) {
                                    elements.commentsList.innerHTML = data.html;
                                    setupDeleteButtons();
                                }

                                // Update pagination
                                if (elements.paginationContainer && data.pagination) {
                                    elements.paginationContainer.innerHTML = data.pagination;
                                    setupPaginationListeners();
                                }

                                // Update URL without page reload
                                window.history.pushState({}, '', url);
                            }
                        })
                        .catch(error => {
                            console.error('Error loading comments:', error);
                            showNotification('Failed to load comments. Please try again.', 'error');
                        })
                        .finally(() => {
                            isLoading = false;
                            hideLoading();
                        });
                }

                /**
                 * Setup pagination listeners
                 */
                function setupPaginationListeners() {
                    const paginationLinks = document.querySelectorAll('.pagination a');
                    paginationLinks.forEach(link => {
                        link.addEventListener('click', function (e) {
                            e.preventDefault();
                            const url = new URL(this.href);
                            const page = url.searchParams.get('page');
                            const params = getFilterParams();
                            loadComments(params, page);
                        });
                    });
                }

                /**
                 * Setup delete buttons
                 */
                function setupDeleteButtons() {
                    document.querySelectorAll('.delete-comment-btn').forEach(btn => {
                        btn.addEventListener('click', function () {
                            const commentId = this.getAttribute('data-comment-id');
                            const commentName = this.getAttribute('data-comment-name');
                            handleDeleteComment(commentId, commentName);
                        });
                    });
                }

                /**
                 * Handle comment deletion
                 */
                function handleDeleteComment(commentId, commentName) {
                    if (!confirm(`Are you sure you want to delete the comment from "${commentName}"?`)) {
                        return;
                    }

                    const deleteUrl = `/admin/comments/${commentId}`;

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
                                // Remove the comment card from DOM
                                const commentCard = document.querySelector(`[data-comment-id="${commentId}"]`);
                                if (commentCard) {
                                    commentCard.style.transition = 'opacity 0.3s';
                                    commentCard.style.opacity = '0';
                                    setTimeout(() => {
                                        commentCard.remove();

                                        // Check if there are no more comments
                                        const remainingComments = document.querySelectorAll('.comment-card');
                                        if (remainingComments.length === 0) {
                                            elements.commentsList.innerHTML = `
                                <div class="empty-state">
                                    <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                    <h3>No comments</h3>
                                    <p>No comments matching the search criteria were found.</p>
                                </div>
                            `;
                                        }
                                    }, 300);
                                }

                                showNotification(data.message || 'Comment has been deleted', 'success');

                                // Update count if element exists
                                updateCommentsCount();
                            } else {
                                showNotification(data.message || 'Failed to delete comment', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting comment:', error);
                            showNotification('Failed to delete comment. Please try again.', 'error');
                        });
                }

                /**
                 * Update comments count
                 */
                function updateCommentsCount() {
                    const commentCards = document.querySelectorAll('.comment-card');
                    if (elements.commentsCount) {
                        elements.commentsCount.textContent = commentCards.length;
                    }
                }

                /**
                 * Clear all filters
                 */
                function handleClearAllFilters() {
                    if (elements.searchInput) elements.searchInput.value = '';
                    if (elements.orderSelect) elements.orderSelect.value = 'desc';
                    if (elements.limitSelect) elements.limitSelect.value = '20';
                    if (elements.usersSelect) {
                        Array.from(elements.usersSelect.options).forEach(option => {
                            option.selected = false;
                        });
                    }

                    handleFilterChange();
                }

                /**
                 * Show loading state
                 */
                function showLoading() {
                    if (elements.commentsList) {
                        elements.commentsList.classList.add('loading-overlay');
                    }
                }

                /**
                 * Hide loading state
                 */
                function hideLoading() {
                    if (elements.commentsList) {
                        elements.commentsList.classList.remove('loading-overlay');
                    }
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

                // Add keyframe animations
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
                `;
                document.head.appendChild(style);

            })();
        </script>
    @endpush
</x-app-layout>
