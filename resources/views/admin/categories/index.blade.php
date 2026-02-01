<x-app-layout>
    <div class="w-full px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
        <div class="flex flex-col">
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                        <!-- Header -->
                        <div
                            class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800">Categories</h2>
                                <p class="text-sm text-gray-600">Manage categories, add new ones and more.</p>
                            </div>

                            <div>
                                @can('category-create')
                                    <a href="{{ route('admin.categories.create') }}"
                                       class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                             height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 12h14"/>
                                            <path d="M12 5v14"/>
                                        </svg>
                                        Add Category
                                    </a>
                                @endcan
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <input
                                    type="text"
                                    id="searchInput"
                                    placeholder="Search categories..."
                                    class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                                >

                                <select id="orderSelect"
                                        class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="desc">Newest</option>
                                    <option value="asc">Oldest</option>
                                    <option value="Alphabeticalаsc">Alphabetical A-Z</option>
                                    <option value="Alphabeticaldesc">Alphabetical Z-A</option>
                                </select>

                                <select id="limitSelect"
                                        class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="10">10</option>
                                    <option value="20" selected>20</option>
                                    <option value="50">50</option>
                                    <option value="0">All</option>
                                </select>
                            </div>
                        </div>

                        <!-- Table -->
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">ID
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Name
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Preview
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Posts Count
                                </th>
                                <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase">
                                    Actions
                                </th>
                            </tr>
                            </thead>
                            <tbody id="categoryTableBody" class="divide-y divide-gray-200">
                            <!-- Populated by JavaScript -->
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div id="paginationContainer" class="px-6 py-4 border-t border-gray-200"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let currentPage = 1;
            let debounceTimer;

            function loadCategories() {
                const search = document.getElementById('searchInput').value;
                const order = document.getElementById('orderSelect').value;
                const limit = document.getElementById('limitSelect').value;

                fetch(`{{ route('admin.categories.index') }}?search=${search}&order=${order}&limit=${limit}&page=${currentPage}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        renderTable(data);
                        renderPagination(data);
                    })
                    .catch(error => console.error('Error:', error));
            }

            function renderTable(data) {
                const tbody = document.getElementById('categoryTableBody');
                const categories = data.data || data;

                if (categories.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No categories found</td></tr>';
                    return;
                }

                const editRoute = "{{ route('admin.categories.edit', ':id') }}";
                const destroyRoute = "{{ route('admin.categories.destroy', ':id') }}";

                tbody.innerHTML = categories.map(category => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">${category.id}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">${category.name}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span style="background: ${category.backgroundColor}; color: ${category.textColor}"
                      class="px-3 py-1 rounded text-sm">
                    ${category.name}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">${category.posts_count || 0}</td>
            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                <div class="flex justify-end gap-2">
                    @can('category-edit')
                <a href="${editRoute.replace(':id', category.id)}"
                       class="inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-blue-600 hover:text-blue-800">
                        Edit
                    </a>
                    @endcan
                @can('category-delete')
                <button onclick="deleteCategory(${category.id})"
                            class="inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-red-600 hover:text-red-800">
                        Delete
                    </button>
                    @endcan
                </div>
            </td>
        </tr>
    `).join('');
            }

            function renderPagination(data) {
                const container = document.getElementById('paginationContainer');

                if (!data.links) {
                    container.innerHTML = '';
                    return;
                }

                container.innerHTML = `
        <div class="flex justify-between items-center">
            <p class="text-sm text-gray-600">
                Showing <span class="font-semibold">${data.from || 0}</span> to <span class="font-semibold">${data.to || 0}</span> of <span class="font-semibold">${data.total || 0}</span> results
            </p>
            <div class="inline-flex gap-x-2">
                <button onclick="changePage(${data.current_page - 1})"
                        ${!data.prev_page_url ? 'disabled' : ''}
                        class="py-1.5 px-2 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none">
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Prev
                </button>
                <button onclick="changePage(${data.current_page + 1})"
                        ${!data.next_page_url ? 'disabled' : ''}
                        class="py-1.5 px-2 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none">
                    Next
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </div>
    `;
            }

            function changePage(page) {
                currentPage = page;
                loadCategories();
            }

            function deleteCategory(id) {
                if (!confirm('Are you sure you want to delete this category?')) return;

                const destroyRoute = "{{ route('admin.categories.destroy', ':id') }}";

                fetch(destroyRoute.replace(':id', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadCategories();
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            // Event listeners
            document.getElementById('searchInput').addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    currentPage = 1;
                    loadCategories();
                }, 300);
            });

            document.getElementById('orderSelect').addEventListener('change', function () {
                currentPage = 1;
                loadCategories();
            });

            document.getElementById('limitSelect').addEventListener('change', function () {
                currentPage = 1;
                loadCategories();
            });

            // Initial load
            loadCategories();
        </script>
    @endpush
</x-app-layout>
