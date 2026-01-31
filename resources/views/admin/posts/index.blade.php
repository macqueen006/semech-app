<x-app-layout>
    <div class="p-4" id="postsApp">
        @if(session('success'))
            <div
                class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div
                class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        @endif

        <!-- Bulk Category Change Modal -->
        <div id="bulkCategoryModal"
             class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold mb-4">Change Category</h3>
                <p class="text-gray-600 mb-4">Select a category for <span id="selectedPostsCount"></span> selected
                    post(s):</p>

                <select id="bulkCategorySelect" class="w-full px-4 py-2 border rounded mb-4">
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2 justify-end">
                    <button id="cancelBulkCategory"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button id="confirmBulkCategory" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Change Category
                    </button>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <!-- Filter Sidebar -->
            <div class="w-80 bg-white border rounded-lg p-4 h-fit sticky top-4">
                <div class="flex justify-between items-center mb-4 cursor-pointer" id="filterToggle">
                    <h2 class="text-lg font-bold">Posts</h2>
                    <i class="fa-solid fa-caret-up" id="filterIcon"></i>
                </div>

                <form id="filterForm" method="GET" action="{{ route('admin.posts.index') }}">
                    <div id="filterContent" class="space-y-6">
                        <!-- View Mode Toggle -->
                        <div>
                            <p class="font-medium mb-2">View</p>
                            <div class="flex gap-2">
                                <button type="button" id="listViewBtn"
                                        class="flex-1 px-4 py-2 rounded hover:opacity-80 bg-blue-600 text-white">
                                    <i class="fa-solid fa-bars"></i>
                                </button>
                                <button type="button" id="tileViewBtn"
                                        class="flex-1 px-4 py-2 rounded hover:opacity-80 bg-gray-200">
                                    <i class='bx bxs-grid-alt'></i>
                                </button>
                            </div>
                        </div>

                        <!-- Sorting -->
                        <div>
                            <label class="block font-medium mb-2">Sorting</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="order" value="desc"
                                           class="form-radio order-radio" {{ $order === 'desc' ? 'checked' : '' }}>
                                    <span class="text-sm">Newest Posts</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="order" value="asc"
                                           class="form-radio order-radio" {{ $order === 'asc' ? 'checked' : '' }}>
                                    <span class="text-sm">Oldest Posts</span>
                                </label>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block font-medium mb-2">Status</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="status[]" value="published"
                                           class="form-checkbox status-checkbox" {{ in_array('published', $statusFilter) ? 'checked' : '' }}>
                                    <span class="text-sm">Published</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="status[]" value="scheduled"
                                           class="form-checkbox status-checkbox" {{ in_array('scheduled', $statusFilter) ? 'checked' : '' }}>
                                    <span class="text-sm">Scheduled</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="status[]" value="draft"
                                           class="form-checkbox status-checkbox" {{ in_array('draft', $statusFilter) ? 'checked' : '' }}>
                                    <span class="text-sm">Draft</span>
                                </label>
                            </div>
                        </div>

                        <!-- Search -->
                        <div>
                            <label class="block font-medium mb-2">Search</label>
                            <input type="text" name="search" id="searchInput" value="{{ $search }}"
                                   placeholder="Search posts..." class="w-full px-3 py-2 border rounded">
                        </div>

                        <!-- Records -->
                        <div>
                            <label class="block font-medium mb-2">Records</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="limit" value="20"
                                           class="form-radio limit-radio" {{ $limit == 20 ? 'checked' : '' }}>
                                    <span class="text-sm">20 records</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="limit" value="50"
                                           class="form-radio limit-radio" {{ $limit == 50 ? 'checked' : '' }}>
                                    <span class="text-sm">50 records</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="limit" value="100"
                                           class="form-radio limit-radio" {{ $limit == 100 ? 'checked' : '' }}>
                                    <span class="text-sm">100 records</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="limit" value="0"
                                           class="form-radio limit-radio" {{ $limit == 0 ? 'checked' : '' }}>
                                    <span class="text-sm">Max records</span>
                                </label>
                            </div>
                        </div>

                        <!-- Highlight Filter -->
                        @can('post-super-list')
                            <div>
                                <label class="block font-medium mb-2">Highlighted</label>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="highlight[]" value="1"
                                               class="form-checkbox highlight-checkbox" {{ in_array('1', $highlightFilter) ? 'checked' : '' }}>
                                        <span class="text-sm">Yes ({{ $countHighlighted }})</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="highlight[]" value="0"
                                               class="form-checkbox highlight-checkbox" {{ in_array('0', $highlightFilter) ? 'checked' : '' }}>
                                        <span class="text-sm">No ({{ $countPosts - $countHighlighted }})</span>
                                    </label>
                                </div>
                            </div>
                        @endcan

                        <!-- Categories -->
                        <div>
                            <label class="block font-medium mb-2">Categories</label>
                            <div class="space-y-2" id="categoriesList">
                                @foreach($categories as $index => $category)
                                    <label
                                        class="flex items-center gap-2 cursor-pointer category-item {{ $index >= 3 && empty($selectedCategories) ? 'hidden' : '' }}">
                                        <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                               class="form-checkbox category-checkbox"
                                               style="color: {{ $category->backgroundColor }}" {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                                        <span
                                            class="text-sm">{{ $category->name }} ({{ $category->posts_count }})</span>
                                    </label>
                                @endforeach

                                @if(count($categories) > 3 && empty($selectedCategories))
                                    <button type="button" id="toggleCategories"
                                            class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                                        <i class="fa-solid fa-chevron-down"></i>
                                        <span>Show more</span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Users Filter -->
                        @can('post-super-list')
                            <div>
                                <label class="block font-medium mb-2">Users</label>
                                <div class="space-y-2 max-h-64 overflow-y-auto">
                                    @foreach($users as $user)
                                        @if($user->posts_count > 0)
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" name="users[]" value="{{ $user->id }}"
                                                       class="form-checkbox user-checkbox" {{ in_array($user->id, $selectedUsers) ? 'checked' : '' }}>
                                                <span class="text-sm">{{ $user->firstname }} {{ $user->lastname }} ({{ $user->posts_count }})</span>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endcan
                    </div>
                </form>
            </div>

            <!-- Posts Display -->
            <div class="flex-1">
                <!-- Bulk Actions Bar -->
                <div id="bulkToolbar" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                        <span class="font-medium text-blue-900">
                            <span id="bulkSelectedCount"></span> post(s) selected
                        </span>

                            <select id="bulkActionSelect" class="px-3 py-2 border rounded bg-white">
                                <option value="">-- Bulk Actions --</option>
                                @can('post-delete')
                                    <option value="delete">Delete</option>
                                @endcan
                                @can('post-edit')
                                    <option value="publish">Publish</option>
                                    <option value="unpublish">Unpublish</option>
                                    <option value="change_category">Change Category</option>
                                @endcan
                                @can('post-highlight')
                                    <option value="highlight">Highlight</option>
                                    <option value="remove_highlight">Remove Highlight</option>
                                @endcan
                            </select>

                            <button id="applyBulkAction"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                Apply
                            </button>
                        </div>

                        <button id="clearBulkSelection" class="text-blue-600 hover:text-blue-700 text-sm">
                            Clear Selection
                        </button>
                    </div>
                </div>

                <!-- List View -->
                <div id="listView" class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" id="selectAllCheckbox" class="form-checkbox">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Author</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($posts as $post)
                            <tr class="post-row" data-post-id="{{ $post->id }}">
                                <!-- Checkbox -->
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="form-checkbox post-checkbox"
                                           data-id="{{ $post->id }}">
                                </td>

                                <!-- Title -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($post->is_highlighted)
                                            <span class="text-yellow-500 text-lg" title="Highlighted">★</span>
                                        @endif
                                        <div>
                                            <a href="{{ route('post.show', $post->slug) }}"
                                               class="font-medium hover:text-blue-600" target="_blank">
                                                {{ $post->title }}
                                            </a>
                                            @if($post->read_time)
                                                <p class="text-sm text-gray-500">
                                                    <i class="fa-solid fa-clock"></i> {{ $post->read_time }} min
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    @php
                                        $status = $post->publishing_status;
                                        $statusConfig = [
                                            'published' => ['color' => 'bg-green-100 text-green-700 border border-green-200', 'icon' => 'fa-circle-check', 'text' => 'Published'],
                                            'scheduled' => ['color' => 'bg-blue-100 text-blue-700 border border-blue-200', 'icon' => 'fa-clock', 'text' => 'Scheduled'],
                                            'draft' => ['color' => 'bg-gray-100 text-gray-700 border border-gray-200', 'icon' => 'fa-file-lines', 'text' => 'Draft'],
                                        ];
                                        $badge = $statusConfig[$status] ?? $statusConfig['draft'];
                                    @endphp

                                    <div
                                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium {{ $badge['color'] }}">
                                        <i class="fa-solid {{ $badge['icon'] }}"></i>
                                        <span>{{ $badge['text'] }}</span>
                                    </div>

                                    @if($status === 'scheduled' && $post->scheduled_at)
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $post->scheduled_at->format('M d, Y H:i') }}
                                        </p>
                                    @endif
                                </td>

                                <!-- Category -->
                                <td class="px-6 py-4">
                                    @if($post->category)
                                        <span class="inline-block px-3 py-1 text-xs rounded font-medium"
                                              style="background-color: {{ $post->category->backgroundColor }}CC; color: {{ $post->category->textColor }}">
                                        {{ $post->category->name }}
                                    </span>
                                    @endif
                                </td>

                                <!-- Author -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ asset($post->user->image_path) }}"
                                             alt="{{ $post->user->firstname }}" class="w-8 h-8 rounded-full">
                                        <span
                                            class="text-sm">{{ $post->user->firstname }} {{ $post->user->lastname }}</span>
                                    </div>
                                </td>

                                <!-- Views -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1 text-sm text-gray-600">
                                        <i class="fa-solid fa-eye"></i>
                                        <span>{{ number_format($post->view_count) }}</span>
                                    </div>
                                </td>

                                <!-- Date -->
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($post->created_at)->translatedFormat('d F, Y') }}
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('post.show', $post->slug) }}"
                                           class="text-sm text-blue-600 hover:underline" target="_blank">
                                            View
                                        </a>

                                        @can('post-edit')
                                            @if($post->user_id === auth()->id() || auth()->user()->hasPermissionTo('post-super-list'))
                                                <a href="{{ route('admin.posts.edit', $post->id) }}"
                                                   class="text-sm text-blue-600 hover:underline">
                                                    Edit
                                                </a>
                                            @endif
                                        @endcan

                                        @can('post-delete')
                                            @if($post->user_id === auth()->id() || auth()->user()->hasPermissionTo('post-super-list'))
                                                <button class="text-sm text-red-600 hover:underline delete-post"
                                                        data-id="{{ $post->id }}">
                                                    Delete
                                                </button>
                                            @endif
                                        @endcan

                                        @can('post-highlight')
                                            <button
                                                class="text-lg {{ $post->is_highlighted ? 'text-yellow-500' : 'text-gray-300' }} hover:text-yellow-400 highlight-post"
                                                data-id="{{ $post->id }}"
                                                data-highlighted="{{ $post->is_highlighted ? '1' : '0' }}"
                                                title="{{ $post->is_highlighted ? 'Remove highlight' : 'Highlight' }}">
                                                ★
                                            </button>
                                        @endcan

                                        <a href="{{ route('admin.posts.analytics', $post->slug) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1 text-sm bg-purple-100 text-purple-700 rounded hover:bg-purple-200 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            </svg>
                                            Analytics
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                    No posts found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Tile/Card View -->
                <div id="tileView" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($posts as $post)
                        <div
                            class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition post-card relative"
                            data-post-id="{{ $post->id }}">
                            <!-- Selection Checkbox -->
                            <div class="absolute top-2 left-2 z-10">
                                <input type="checkbox" class="form-checkbox w-5 h-5 rounded post-checkbox"
                                       data-id="{{ $post->id }}">
                            </div>

                            <!-- Status Badge -->
                            <div class="absolute top-2 right-2 z-10">
                                @php
                                    $status = $post->publishing_status;
                                    $statusConfig = [
                                        'published' => ['color' => 'bg-green-100 text-green-700 border border-green-200', 'icon' => 'fa-circle-check', 'text' => 'Published'],
                                        'scheduled' => ['color' => 'bg-blue-100 text-blue-700 border border-blue-200', 'icon' => 'fa-clock', 'text' => 'Scheduled'],
                                        'draft' => ['color' => 'bg-gray-100 text-gray-700 border border-gray-200', 'icon' => 'fa-file-lines', 'text' => 'Draft'],
                                    ];
                                    $badge = $statusConfig[$status] ?? $statusConfig['draft'];
                                @endphp

                                <div
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium shadow-sm {{ $badge['color'] }}">
                                    <i class="fa-solid {{ $badge['icon'] }}"></i>
                                    <span>{{ $badge['text'] }}</span>
                                </div>
                            </div>

                            <img src="{{ asset($post->image_path) }}" alt="{{ $post->title }}"
                                 class="w-full h-48 object-cover">

                            <div class="p-4">
                                <!-- Top Info -->
                                <div class="flex items-center gap-2 mb-2 flex-wrap">
                                    @if($post->category)
                                        <span class="px-3 py-1 text-xs rounded font-medium"
                                              style="background-color: {{ $post->category->backgroundColor }}CC; color: {{ $post->category->textColor }}">
                                        {{ $post->category->name }}
                                    </span>
                                    @endif
                                    @if($post->read_time)
                                        <span class="text-sm text-gray-500">
                                        <i class="fa-solid fa-clock"></i> {{ $post->read_time }} min
                                    </span>
                                    @endif
                                    <span class="text-sm text-gray-500">
                                    <i class="fa-solid fa-eye"></i> {{ number_format($post->view_count) }}
                                </span>

                                    @if($status === 'scheduled' && $post->scheduled_at)
                                        <span class="text-xs text-blue-600">
                                        <i class="fa-solid fa-calendar"></i> {{ $post->scheduled_at->format('M d, H:i') }}
                                    </span>
                                    @endif
                                </div>

                                <!-- Title -->
                                <h3 class="font-bold text-lg mb-3 line-clamp-2">{{ $post->title }}</h3>

                                <!-- Author -->
                                <div class="flex items-center gap-2 mb-3">
                                    <img src="{{ asset($post->user->image_path) }}" alt="{{ $post->user->firstname }}"
                                         class="w-6 h-6 rounded-full">
                                    <span
                                        class="text-sm text-gray-600">{{ $post->user->firstname }} {{ $post->user->lastname }}</span>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('post.show', $post->slug) }}"
                                       class="text-sm text-blue-600 hover:underline" target="_blank">
                                        View
                                    </a>

                                    @can('post-edit')
                                        @if($post->user_id === auth()->id() || auth()->user()->hasPermissionTo('post-super-list'))
                                            <a href="{{ route('admin.posts.edit', $post->id) }}"
                                               class="text-sm text-blue-600 hover:underline">
                                                Edit
                                            </a>
                                        @endif
                                    @endcan

                                    @can('post-delete')
                                        @if($post->user_id === auth()->id() || auth()->user()->hasPermissionTo('post-super-list'))
                                            <button class="text-sm text-red-600 hover:underline delete-post"
                                                    data-id="{{ $post->id }}">
                                                Delete
                                            </button>
                                        @endif
                                    @endcan

                                    @can('post-highlight')
                                        <button
                                            class="text-lg {{ $post->is_highlighted ? 'text-yellow-500' : 'text-gray-300' }} hover:text-yellow-400 highlight-post"
                                            data-id="{{ $post->id }}"
                                            data-highlighted="{{ $post->is_highlighted ? '1' : '0' }}">
                                            ★
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12 text-gray-500">
                            No posts found.
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($limit > 0 && method_exists($posts, 'links'))
                    <div class="mt-6">
                        {{ $posts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // View mode
                const viewMode = localStorage.getItem('postView') || 'list';
                const listView = document.getElementById('listView');
                const tileView = document.getElementById('tileView');
                const listViewBtn = document.getElementById('listViewBtn');
                const tileViewBtn = document.getElementById('tileViewBtn');

                function setViewMode(mode) {
                    if (mode === 'list') {
                        listView.classList.remove('hidden');
                        tileView.classList.add('hidden');
                        listViewBtn.classList.add('bg-blue-600', 'text-white');
                        listViewBtn.classList.remove('bg-gray-200');
                        tileViewBtn.classList.remove('bg-blue-600', 'text-white');
                        tileViewBtn.classList.add('bg-gray-200');
                    } else {
                        listView.classList.add('hidden');
                        tileView.classList.remove('hidden');
                        tileViewBtn.classList.add('bg-blue-600', 'text-white');
                        tileViewBtn.classList.remove('bg-gray-200');
                        listViewBtn.classList.remove('bg-blue-600', 'text-white');
                        listViewBtn.classList.add('bg-gray-200');
                    }
                    localStorage.setItem('postView', mode);
                }

                setViewMode(viewMode);

                listViewBtn.addEventListener('click', () => setViewMode('list'));
                tileViewBtn.addEventListener('click', () => setViewMode('tile'));

                // Filter toggle
                const filterToggle = document.getElementById('filterToggle');
                const filterContent = document.getElementById('filterContent');
                const filterIcon = document.getElementById('filterIcon');
                let filtersCollapsed = false;

                filterToggle.addEventListener('click', () => {
                    filtersCollapsed = !filtersCollapsed;
                    if (filtersCollapsed) {
                        filterContent.classList.add('hidden');
                        filterIcon.classList.remove('fa-caret-up');
                        filterIcon.classList.add('fa-caret-down');
                    } else {
                        filterContent.classList.remove('hidden');
                        filterIcon.classList.remove('fa-caret-down');
                        filterIcon.classList.add('fa-caret-up');
                    }
                });

                // Categories toggle
                const toggleCategoriesBtn = document.getElementById('toggleCategories');
                if (toggleCategoriesBtn) {
                    let showMore = false;
                    toggleCategoriesBtn.addEventListener('click', () => {
                        showMore = !showMore;
                        const categoryItems = document.querySelectorAll('.category-item');
                        categoryItems.forEach((item, index) => {
                            if (index >= 3) {
                                if (showMore) {
                                    item.classList.remove('hidden');
                                } else {
                                    item.classList.add('hidden');
                                }
                            }
                        });
                        const icon = toggleCategoriesBtn.querySelector('i');
                        const span = toggleCategoriesBtn.querySelector('span');
                        if (showMore) {
                            icon.classList.remove('fa-chevron-down');
                            icon.classList.add('fa-chevron-up');
                            span.textContent = 'Show less';
                        } else {
                            icon.classList.remove('fa-chevron-up');
                            icon.classList.add('fa-chevron-down');
                            span.textContent = 'Show more';
                        }
                    });
                }

                // Auto-submit filters
                const filterForm = document.getElementById('filterForm');
                const searchInput = document.getElementById('searchInput');
                let searchTimeout;

                searchInput.addEventListener('input', () => {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => filterForm.submit(), 300);
                });

                document.querySelectorAll('.order-radio, .status-checkbox, .limit-radio, .highlight-checkbox, .category-checkbox, .user-checkbox').forEach(input => {
                    input.addEventListener('change', () => filterForm.submit());
                });

                // Bulk selection
                const selectedPosts = [];
                const selectAllCheckbox = document.getElementById('selectAllCheckbox');
                const postCheckboxes = document.querySelectorAll('.post-checkbox');
                const bulkToolbar = document.getElementById('bulkToolbar');
                const bulkSelectedCount = document.getElementById('bulkSelectedCount');
                const clearBulkSelection = document.getElementById('clearBulkSelection');
                const bulkActionSelect = document.getElementById('bulkActionSelect');
                const applyBulkAction = document.getElementById('applyBulkAction');

                function updateBulkUI() {
                    if (selectedPosts.length > 0) {
                        bulkToolbar.classList.remove('hidden');
                        bulkSelectedCount.textContent = selectedPosts.length;

                        document.querySelectorAll('.post-row, .post-card').forEach(row => {
                            const id = parseInt(row.dataset.postId);
                            if (selectedPosts.includes(id)) {
                                row.classList.add('bg-blue-50');
                            } else {
                                row.classList.remove('bg-blue-50');
                            }
                        });
                    } else {
                        bulkToolbar.classList.add('hidden');
                        document.querySelectorAll('.post-row, .post-card').forEach(row => {
                            row.classList.remove('bg-blue-50');
                        });
                    }
                }

                function updateSelectAll() {
                    const allChecked = Array.from(postCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                }

                selectAllCheckbox.addEventListener('change', function() {
                    const isChecked = this.checked;
                    selectedPosts.length = 0; // Clear array first

                    // Only select checkboxes from the currently visible view
                    const currentView = listView.classList.contains('hidden') ? tileView : listView;
                    const visibleCheckboxes = currentView.querySelectorAll('.post-checkbox');

                    visibleCheckboxes.forEach(cb => {
                        cb.checked = isChecked;
                        const id = parseInt(cb.dataset.id);
                        if (isChecked) {
                            selectedPosts.push(id);
                        }
                    });

                    // Also update checkboxes in the hidden view to keep them in sync
                    const hiddenView = listView.classList.contains('hidden') ? listView : tileView;
                    const hiddenCheckboxes = hiddenView.querySelectorAll('.post-checkbox');
                    hiddenCheckboxes.forEach(cb => {
                        const id = parseInt(cb.dataset.id);
                        cb.checked = isChecked;
                    });

                    updateBulkUI();
                });

                postCheckboxes.forEach(cb => {
                    cb.addEventListener('change', function() {
                        const id = parseInt(this.dataset.id);
                        if (this.checked) {
                            if (!selectedPosts.includes(id)) selectedPosts.push(id);
                        } else {
                            const index = selectedPosts.indexOf(id);
                            if (index > -1) selectedPosts.splice(index, 1);
                        }

                        // Sync the corresponding checkbox in the other view
                        const otherCheckbox = document.querySelector(`.post-checkbox[data-id="${id}"]:not([checked="${this.checked}"])`);
                        postCheckboxes.forEach(checkbox => {
                            if (checkbox !== this && parseInt(checkbox.dataset.id) === id) {
                                checkbox.checked = this.checked;
                            }
                        });

                        updateSelectAll();
                        updateBulkUI();
                    });
                });

                clearBulkSelection.addEventListener('click', () => {
                    selectedPosts.length = 0;
                    postCheckboxes.forEach(cb => cb.checked = false);
                    selectAllCheckbox.checked = false;
                    bulkActionSelect.value = '';
                    updateBulkUI();
                });

                // Bulk actions
                applyBulkAction.addEventListener('click', () => {
                    if (selectedPosts.length === 0) {
                        alert('Please select at least one post.');
                        return;
                    }

                    const action = bulkActionSelect.value;
                    if (!action) {
                        alert('Please select a bulk action.');
                        return;
                    }

                    switch (action) {
                        case 'delete':
                            if (confirm(`Are you sure you want to delete ${selectedPosts.length} post(s)?`)) {
                                executeBulkAction('{{ route('admin.posts.bulk-delete') }}');
                            }
                            break;
                        case 'publish':
                            executeBulkAction('{{ route('admin.posts.bulk-publish') }}');
                            break;
                        case 'unpublish':
                            executeBulkAction('{{ route('admin.posts.bulk-unpublish') }}');
                            break;
                        case 'change_category':
                            document.getElementById('selectedPostsCount').textContent = selectedPosts.length;
                            document.getElementById('bulkCategoryModal').classList.remove('hidden');
                            break;
                        case 'highlight':
                            executeBulkAction('{{ route('admin.posts.bulk-highlight') }}');
                            break;
                        case 'remove_highlight':
                            executeBulkAction('{{ route('admin.posts.bulk-remove-highlight') }}');
                            break;
                    }
                });

                function showMessage(message, type) {
                    // Remove any existing messages
                    const existingMessage = document.querySelector('.flash-message');
                    if (existingMessage) {
                        existingMessage.remove();
                    }

                    // Create message element
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `flash-message mb-4 px-4 py-3 rounded flex items-center justify-between ${
                        type === 'success'
                            ? 'bg-green-100 border border-green-400 text-green-700'
                            : 'bg-red-100 border border-red-400 text-red-700'
                    }`;

                    messageDiv.innerHTML = `
                    <span>${message}</span>
                    <button onclick="this.parentElement.remove()" class="${type === 'success' ? 'text-green-700 hover:text-green-900' : 'text-red-700 hover:text-red-900'}">
                        <i class="fa-solid fa-times"></i>
                    </button>
                     `;

                    // Insert at the top of the posts app
                    const postsApp = document.getElementById('postsApp');
                    postsApp.insertBefore(messageDiv, postsApp.firstChild);

                    // Scroll to top to see the message
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }

                function executeBulkAction(url) {
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ids: selectedPosts})
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert(data.message);
                            }
                        });
                }

                // Bulk category change modal
                const bulkCategoryModal = document.getElementById('bulkCategoryModal');
                const cancelBulkCategory = document.getElementById('cancelBulkCategory');
                const confirmBulkCategory = document.getElementById('confirmBulkCategory');
                const bulkCategorySelect = document.getElementById('bulkCategorySelect');

                cancelBulkCategory.addEventListener('click', () => {
                    bulkCategoryModal.classList.add('hidden');
                    bulkCategorySelect.value = '';
                    bulkActionSelect.value = '';
                });

                bulkCategoryModal.addEventListener('click', (e) => {
                    if (e.target === bulkCategoryModal) {
                        bulkCategoryModal.classList.add('hidden');
                        bulkCategorySelect.value = '';
                        bulkActionSelect.value = '';
                    }
                });

                confirmBulkCategory.addEventListener('click', () => {
                    const categoryId = bulkCategorySelect.value;
                    if (!categoryId) {
                        alert('Please select a category.');
                        return;
                    }

                    fetch('{{ route('admin.posts.bulk-change-category') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ids: selectedPosts, category_id: categoryId})
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // window.location.reload();
                                showMessage(data.message, 'success');
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                showMessage(data.message, 'error');
                            }
                        });
                });

                // Delete post
                document.querySelectorAll('.delete-post').forEach(btn => {
                    btn.addEventListener('click', function () {
                        if (!confirm('Are you sure you want to delete this post?')) return;

                        const postId = this.dataset.id;
                        fetch(`{{ url('admin/posts') }}/${postId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    const row = document.querySelector(`[data-post-id="${postId}"]`);
                                    if (row) {
                                        row.style.transition = 'opacity 0.3s, transform 0.3s';
                                        row.style.opacity = '0.5';
                                        row.style.transform = 'scale(0.95)';
                                        row.classList.add('bg-red-50');

                                        showMessage(data.message, 'success');
                                        setTimeout(() => {
                                            window.location.reload();
                                        }, 1500);
                                    }
                                } else {
                                    showMessage(data.message, 'error');
                                    button.disabled = false;
                                    button.textContent = originalText;
                                    button.classList.remove('opacity-50', 'cursor-not-allowed');
                                }
                            })
                            .catch(error => {
                                showMessage('An error occurred while deleting the post.', 'error');
                                button.disabled = false;
                                button.textContent = originalText;
                                button.classList.remove('opacity-50', 'cursor-not-allowed');
                            });
                    });
                });

                // Highlight post
                document.querySelectorAll('.highlight-post').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const postId = this.dataset.id;

                        fetch('{{ route('admin.posts.toggle-highlight') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({post_id: postId})
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // window.location.reload();
                                    showMessage(data.message, 'success');
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1500);
                                } else {
                                    // alert(data.message);
                                    showMessage(data.message, 'error');
                                }
                            });
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
