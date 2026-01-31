import './bootstrap';
import 'preline';

document.addEventListener('DOMContentLoaded', () => {
    // Check if HSStaticMethods exists before calling
    if (window.HSStaticMethods) {
        window.HSStaticMethods.autoInit();
    }

    // Optional: Use a MutationObserver for deep DOM updates
    if (document.body && window.HSStaticMethods) {
        const observer = new MutationObserver(() => {
            window.HSStaticMethods.autoInit();
        });

        observer.observe(document.body, {
            attributes: true,
            subtree: true,
            childList: true,
            characterData: true,
        });
    }
});

// Search Modal - Vanilla JavaScript Implementation
class SearchModal {
    constructor() {
        this.modal = null;
        this.searchInput = null;
        this.clearButton = null;
        this.loadingSpinner = null;
        this.searchButton = null;
        this.resultsContainer = null;
        this.emptyState = null;
        this.loadingState = null;
        this.noResultsState = null;

        // Filter buttons
        this.filterButtons = {
            all: null,
            posts: null,
            categories: null
        };

        // State
        this.searchTerm = '';
        this.selectedType = 'all';
        this.isSearching = false;
        this.searchResults = [];
        this.counts = { all: 0, posts: 0, categories: 0 };
        this.searchTimeout = null;

        this.init();
    }

    init() {
        // Get DOM elements
        this.modal = document.getElementById('hs-scale-animation-modal');
        if (!this.modal) return;

        this.searchInput = this.modal.querySelector('input[type="text"]');
        this.clearButton = this.createClearButton();
        this.loadingSpinner = this.createLoadingSpinner();
        this.resultsContainer = this.modal.querySelector('.h-\\[calc\\(20rem\\*1\\.25\\)\\]');

        // Setup initial states
        this.setupEmptyState();
        this.setupLoadingState();
        this.setupNoResultsState();
        this.setupFilterButtons();
        this.setupEventListeners();
        this.setupKeyboardShortcuts();
    }

    setupFilterButtons() {
        const filterContainer = this.modal.querySelector('.mt-3.flex.flex-wrap');
        if (!filterContainer) return;

        // Clear existing buttons and create new ones
        filterContainer.querySelector('.flex.flex-wrap.gap-2').innerHTML = `
            <button type="button" data-filter="all"
                    class="px-2.5 py-1.5 inline-flex items-center text-xs cursor-pointer rounded-lg focus:outline-hidden transition-colors bg-gray-100 text-secondary">
                <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 7h-3a2 2 0 0 1-2-2V2"></path>
                    <path d="M9 18a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h7l4 4v10a2 2 0 0 1-2 2Z"></path>
                    <path d="M3 7.6v12.8A1.6 1.6 0 0 0 4.6 22h9.8"></path>
                </svg>
                All <span class="font-semibold count-all"></span>
            </button>

            <button type="button" data-filter="posts"
                    class="px-2.5 py-1.5 inline-flex items-center text-xs cursor-pointer rounded-lg focus:outline-hidden transition-colors bg-gray-100 hover:text-secondary">
                <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"></path>
                    <path d="M18 14h-8"></path>
                    <path d="M15 18h-5"></path>
                    <path d="M10 6h8v4h-8V6Z"></path>
                </svg>
                Posts <span class="font-semibold count-posts"></span>
            </button>

            <button type="button" data-filter="categories"
                    class="px-2.5 py-1.5 inline-flex items-center text-xs cursor-pointer rounded-lg focus:outline-hidden transition-colors bg-gray-100 hover:text-secondary">
                <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"></path>
                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
            </svg>
                Categories <span class="font-semibold count-categories"></span>
            </button>
        `;

        // Get button references
        this.filterButtons.all = this.modal.querySelector('[data-filter="all"]');
        this.filterButtons.posts = this.modal.querySelector('[data-filter="posts"]');
        this.filterButtons.categories = this.modal.querySelector('[data-filter="categories"]');

        // Add click handlers
        Object.keys(this.filterButtons).forEach(type => {
            this.filterButtons[type].addEventListener('click', () => {
                this.setFilter(type);
            });
        });
    }

    createClearButton() {
        const buttonContainer = this.searchInput.parentElement.querySelector('.absolute.pe-2');

        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'text-gray-500 rounded-full mr-2.5 justify-center items-center shrink-0 size-6 cursor-pointer focus:outline-hidden hover:text-gray-700 hidden';
        clearBtn.setAttribute('aria-label', 'Clear search');
        clearBtn.innerHTML = `
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="m15 9-6 6"></path>
                <path d="m9 9 6 6"></path>
            </svg>
        `;

        clearBtn.addEventListener('click', () => this.clearSearch());
        buttonContainer.insertBefore(clearBtn, buttonContainer.firstChild);

        return clearBtn;
    }

    createLoadingSpinner() {
        const buttonContainer = this.searchInput.parentElement.querySelector('.absolute.pe-2');

        const spinner = document.createElement('div');
        spinner.className = 'hidden';
        spinner.innerHTML = `
            <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        `;

        buttonContainer.insertBefore(spinner, buttonContainer.firstChild);

        return spinner;
    }

    setupEmptyState() {
        this.emptyState = document.createElement('div');
        this.emptyState.className = 'flex items-center justify-center h-full text-gray-400 hidden';
        this.emptyState.innerHTML = `
            <div class="text-center">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <p class="text-lg font-medium mb-1 text-gray-900">Start Searching</p>
                <p class="text-sm">Type at least 2 characters to search</p>
                <div class="mt-4 flex flex-wrap gap-2 justify-center text-xs">
                    <span class="px-2 py-1 bg-gray-100 rounded">Press / to focus</span>
                    <span class="px-2 py-1 bg-gray-100 rounded">ESC to close</span>
                </div>
            </div>
        `;
    }

    setupLoadingState() {
        this.loadingState = document.createElement('div');
        this.loadingState.className = 'flex items-center justify-center h-full hidden';
        this.loadingState.innerHTML = `
            <div class="text-center">
                <svg class="animate-spin h-12 w-12 mx-auto text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-600">Searching...</p>
            </div>
        `;
    }

    setupNoResultsState() {
        this.noResultsState = document.createElement('div');
        this.noResultsState.className = 'flex items-center justify-center h-full text-gray-400 hidden';
        this.noResultsState.innerHTML = `
            <div class="text-center">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-lg font-medium mb-1 text-gray-900">No Results Found</p>
                <p class="text-sm">No results match "<span class="search-term-placeholder"></span>"</p>
            </div>
        `;
    }

    setupEventListeners() {
        // Search input
        this.searchInput.addEventListener('input', (e) => {
            this.searchTerm = e.target.value;
            this.updateClearButton();
            this.debouncedSearch();
        });

        // Modal open/close listeners
        this.modal.addEventListener('open.hs.overlay', () => {
            this.onModalOpen();
        });

        this.modal.addEventListener('close.hs.overlay', () => {
            this.onModalClose();
        });
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Press '/' to open search
            if (e.key === '/' && !e.ctrlKey && !e.metaKey) {
                const activeElement = document.activeElement;
                const isInputFocused = activeElement.tagName === 'INPUT' ||
                    activeElement.tagName === 'TEXTAREA' ||
                    activeElement.isContentEditable;

                if (!isInputFocused) {
                    e.preventDefault();
                    HSOverlay.open(this.modal);
                }
            }
        });
    }

    onModalOpen() {
        setTimeout(() => {
            this.searchInput.focus();
        }, 100);
        this.showEmptyState();
    }

    onModalClose() {
        this.clearSearch();
    }

    updateClearButton() {
        if (this.searchTerm.length > 0) {
            this.clearButton.classList.remove('hidden');
            this.clearButton.classList.add('flex');
        } else {
            this.clearButton.classList.add('hidden');
            this.clearButton.classList.remove('flex');
        }
    }

    setFilter(type) {
        this.selectedType = type;

        // Update button styles
        Object.keys(this.filterButtons).forEach(key => {
            const btn = this.filterButtons[key];
            if (key === type) {
                btn.classList.add('text-secondary');
                btn.classList.remove('hover:text-secondary');
            } else {
                btn.classList.remove('text-secondary');
                btn.classList.add('hover:text-secondary');
            }
        });

        this.performSearch();
    }

    debouncedSearch() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.performSearch();
        }, 300);
    }

    async performSearch() {
        if (this.searchTerm.length < 2 && this.selectedType === 'all') {
            this.searchResults = [];
            this.showEmptyState();
            return;
        }

        this.setSearching(true);

        try {
            const response = await fetch(`/search?q=${encodeURIComponent(this.searchTerm)}&type=${this.selectedType}`);
            const data = await response.json();

            this.searchResults = data.results || [];
            this.counts = data.counts || { all: 0, posts: 0, categories: 0 };

            this.updateCounts();
            this.displayResults();
        } catch (error) {
            console.error('Search error:', error);
            this.searchResults = [];
            this.showNoResults();
        } finally {
            this.setSearching(false);
        }
    }

    setSearching(searching) {
        this.isSearching = searching;

        if (searching) {
            this.loadingSpinner.classList.remove('hidden');
            this.showLoadingState();
        } else {
            this.loadingSpinner.classList.add('hidden');
        }
    }

    updateCounts() {
        const countAll = this.modal.querySelector('.count-all');
        const countPosts = this.modal.querySelector('.count-posts');
        const countCategories = this.modal.querySelector('.count-categories');

        if (countAll) countAll.textContent = this.counts.all > 0 ? this.counts.all : '';
        if (countPosts) countPosts.textContent = this.counts.posts > 0 ? this.counts.posts : '';
        if (countCategories) countCategories.textContent = this.counts.categories > 0 ? this.counts.categories : '';
    }

    clearSearch() {
        this.searchTerm = '';
        this.searchInput.value = '';
        this.selectedType = 'all';
        this.searchResults = [];
        this.counts = { all: 0, posts: 0, categories: 0 };
        this.updateClearButton();
        this.updateCounts();
        this.setFilter('all');
        this.showEmptyState();
    }

    showEmptyState() {
        this.hideAllStates();
        this.resultsContainer.innerHTML = '';
        this.resultsContainer.appendChild(this.emptyState);
        this.emptyState.classList.remove('hidden');
    }

    showLoadingState() {
        this.hideAllStates();
        this.resultsContainer.innerHTML = '';
        this.resultsContainer.appendChild(this.loadingState);
        this.loadingState.classList.remove('hidden');
    }

    showNoResults() {
        this.hideAllStates();
        this.resultsContainer.innerHTML = '';
        this.noResultsState.querySelector('.search-term-placeholder').textContent = this.searchTerm;
        this.resultsContainer.appendChild(this.noResultsState);
        this.noResultsState.classList.remove('hidden');
    }

    hideAllStates() {
        this.emptyState.classList.add('hidden');
        this.loadingState.classList.add('hidden');
        this.noResultsState.classList.add('hidden');
    }

    displayResults() {
        if (this.searchResults.length === 0) {
            this.showNoResults();
            return;
        }

        this.hideAllStates();
        this.resultsContainer.innerHTML = '';

        const resultsWrapper = document.createElement('div');
        resultsWrapper.className = 'space-y-4';

        const grouped = this.groupResults();

        // Display Posts
        if ((this.selectedType === 'all' || this.selectedType === 'posts') && grouped.posts.length > 0) {
            const postsSection = this.createPostsSection(grouped.posts);
            resultsWrapper.appendChild(postsSection);
        }

        // Display Categories
        if ((this.selectedType === 'all' || this.selectedType === 'categories') && grouped.categories.length > 0) {
            const categoriesSection = this.createCategoriesSection(grouped.categories);
            resultsWrapper.appendChild(categoriesSection);
        }

        this.resultsContainer.appendChild(resultsWrapper);
    }

    groupResults() {
        return {
            posts: this.searchResults.filter(r => r.type === 'post'),
            categories: this.searchResults.filter(r => r.type === 'category')
        };
    }

    createPostsSection(posts) {
        const section = document.createElement('div');

        const limit = this.selectedType === 'posts' ? 50 : 3;
        const displayPosts = posts.slice(0, limit);
        const hasMore = this.selectedType === 'all' && posts.length > 3;

        section.innerHTML = `
            <h4 class="uppercase text-gray-500 text-[11px] mb-1">Posts</h4>
            <ul class="-mx-2.5 space-y-0.5">
                ${displayPosts.map(post => `
                    <li>
                        <a class="px-3 py-2 rounded-lg flex items-center gap-3 focus:outline-hidden hover:bg-gray-100 transition-colors group"
                           href="/article/${post.slug}">
                            <svg class="shrink-0 text-gray-500 size-4 block" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                            </svg>
                            <div class="grow">
                                <span class="text-sm text-body">${this.escapeHtml(post.title)}</span>
                            </div>
                            <div class="ms-auto">
                                <svg class="shrink-0 text-gray-500 size-4 block" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m9 18 6-6-6-6"></path>
                                </svg>
                            </div>
                        </a>
                    </li>
                `).join('')}
            </ul>
            ${hasMore ? `
                <p class="mt-1 -ms-1.5">
                    <button class="show-more-posts text-secondary text-sm py-1 px-2 gap-1 inline-flex items-center hover:underline decoration-2 focus:outline-hidden">
                        ${posts.length - 3} more results
                        <svg class="shrink-0 text-secondary size-4 block" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"></path>
                        </svg>
                    </button>
                </p>
            ` : ''}
        `;

        // Add event listener for "show more" button
        if (hasMore) {
            const showMoreBtn = section.querySelector('.show-more-posts');
            showMoreBtn.addEventListener('click', () => {
                this.setFilter('posts');
            });
        }

        return section;
    }

    createCategoriesSection(categories) {
        const section = document.createElement('div');

        section.innerHTML = `
            <h4 class="uppercase text-gray-500 text-[11px] mb-1">Categories</h4>
            <ul class="-mx-2.5 space-y-0.5">
                ${categories.map(category => `
                    <li>
                        <a class="px-3 py-2 text-sm rounded-lg flex items-center gap-3 focus:outline-hidden hover:bg-gray-100 transition-colors"
                           href="/category/${category.slug}">
                            <svg class="shrink-0 text-gray-500 size-4 block" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="4" x2="20" y1="9" y2="9"></line>
                                <line x1="4" x2="20" y1="15" y2="15"></line>
                                <line x1="10" x2="8" y1="3" y2="21"></line>
                                <line x1="16" x2="14" y1="3" y2="21"></line>
                            </svg>
                            <div class="grow inline-flex gap-2">
                                <span>${this.escapeHtml(category.name)}</span>
                                <span class="inline-flex items-center gap-x-2 py-[2px] px-3 rounded-full text-xs font-medium"
                                      style="background-color: ${category.backgroundColor}; color: ${category.textColor}">
                                    ${category.posts_count} posts
                                </span>
                            </div>
                            <div class="ms-auto">
                                <svg class="shrink-0 text-gray-500 size-4 block" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m9 18 6-6-6-6"></path>
                                </svg>
                            </div>
                        </a>
                    </li>
                `).join('')}
            </ul>
        `;

        return section;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    new SearchModal();
});
