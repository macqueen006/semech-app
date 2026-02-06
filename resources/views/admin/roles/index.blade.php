<x-app-layout>
    <div class="p-4">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Roles</h1>

            @can('role-create')
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                    Create Role
                </a>
            @endcan
        </div>

        <div id="alert-container"></div>

        <div class="mb-4 flex gap-4">
            <input
                type="text"
                id="search"
                placeholder="Search roles..."
                class="form-input flex-1 border border-gray-200"
            >

            <select id="order" class="form-select">
                <option value="desc">Newest</option>
                <option value="asc">Oldest</option>
            </select>

            <select id="limit" class="form-select">
                <option value="10">10</option>
                <option value="20" selected>20</option>
                <option value="50">50</option>
                <option value="0">All</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users Count</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
                </thead>
                <tbody id="roles-tbody" class="bg-white divide-y divide-gray-200">
                <!-- Roles will be loaded here -->
                </tbody>
            </table>
        </div>

        <div id="pagination-container" class="mt-4"></div>
    </div>

    @push('scripts')
        <script>
            // Pass Laravel data to JavaScript
            const permissions = {
                canView: {{ auth()->user()->can('role-list') ? 'true' : 'false' }},
                canEdit: {{ auth()->user()->can('role-edit') ? 'true' : 'false' }},
                canDelete: {{ auth()->user()->can('role-delete') ? 'true' : 'false' }}
            };

            const currentUserRoleId = {{ auth()->user()->roles->first()->id ?? 'null' }};
            const routes = {
                index: '{{ route('admin.roles.index') }}',
                show: '{{ route('admin.roles.index') }}',
                edit: '{{ route('admin.roles.index') }}',
                destroy: '{{ route('admin.roles.index') }}'
            };

            // Debounce function
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            // Show alert
            function showAlert(message, type = 'success') {
                const alertContainer = document.getElementById('alert-container');
                const alertClass = type === 'success' ? 'alert-success' : 'alert-error';

                alertContainer.innerHTML = `
                    <div class="alert ${alertClass} mb-4">
                        ${message}
                    </div>
                `;

                setTimeout(() => {
                    alertContainer.innerHTML = '';
                }, 5000);
            }

            // Load roles
            function loadRoles(page = 1) {
                const search = document.getElementById('search').value;
                const order = document.getElementById('order').value;
                const limit = document.getElementById('limit').value;

                fetch(`${routes.index}?page=${page}&search=${search}&order=${order}&limit=${limit}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        renderRoles(data);
                        renderPagination(data);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('Error loading roles', 'error');
                    });
            }

            // Render roles
            function renderRoles(data) {
                const tbody = document.getElementById('roles-tbody');
                const roles = data.data || data;

                if (!Array.isArray(roles) || roles.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No roles found
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = roles.map(role => {
                    const canEditThisRole = role.name !== 'Admin' && role.id !== currentUserRoleId;
                    const canDeleteThisRole = role.name !== 'Admin' && role.id !== currentUserRoleId;

                    let actions = '<div class="flex gap-2">';

                    if (permissions.canView) {
                        actions += `
                            <a href="${routes.show}/${role.id}" class="btn btn-sm btn-info">
                                View
                            </a>
                        `;
                    }

                    if (permissions.canEdit && canEditThisRole) {
                        actions += `
                            <a href="${routes.edit}/${role.id}/edit" class="btn btn-sm">
                                Edit
                            </a>
                        `;
                    }

                    if (permissions.canDelete && canDeleteThisRole) {
                        actions += `
                            <button onclick="deleteRole(${role.id})" class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        `;
                    }

                    actions += '</div>';

                    return `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">${role.id}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${role.name}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${role.users_count || 0}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                ${actions}
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            // Render pagination
            function renderPagination(data) {
                const container = document.getElementById('pagination-container');

                if (!data.links || data.total <= data.per_page) {
                    container.innerHTML = '';
                    return;
                }

                const links = data.links.map(link => {
                    if (!link.url) {
                        return `<span class="px-3 py-2 text-gray-400">${link.label}</span>`;
                    }

                    const page = new URL(link.url).searchParams.get('page') || 1;
                    const activeClass = link.active ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50';

                    return `
                        <button
                            onclick="loadRoles(${page})"
                            class="px-3 py-2 border ${activeClass}">
                            ${link.label}
                        </button>
                    `;
                }).join('');

                container.innerHTML = `<div class="flex gap-1">${links}</div>`;
            }

            // Delete role
            function deleteRole(id) {
                if (!confirm('Are you sure you want to delete this role?')) {
                    return;
                }

                fetch(`${routes.destroy}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert(data.message);
                            loadRoles();
                        } else {
                            showAlert(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('Error deleting role', 'error');
                    });
            }

            // Initialize
            document.addEventListener('DOMContentLoaded', function() {
                loadRoles();

                // Search with debounce
                const searchInput = document.getElementById('search');
                searchInput.addEventListener('input', debounce(() => loadRoles(), 300));

                // Order change
                document.getElementById('order').addEventListener('change', () => loadRoles());

                // Limit change
                document.getElementById('limit').addEventListener('change', () => loadRoles());
            });
        </script>
    @endpush
</x-app-layout>
