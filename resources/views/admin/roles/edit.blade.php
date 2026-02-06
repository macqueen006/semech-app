<x-app-layout>
    <div class="p-4">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">Edit Role</h1>
        </div>

        <div id="alert-container"></div>

        <form id="edit-role-form" class="max-w-3xl">
            @csrf
            @method('PUT')
            <input type="hidden" name="role_id" value="{{ $role->id }}">

            <div class="mb-6">
                <label for="name" class="block font-medium mb-2">Role Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ $role->name }}"
                    class="form-input w-full"
                    required
                >
                <span class="text-red-600 text-sm error-message" id="name-error"></span>
            </div>

            <div class="mb-6">
                <label class="block font-medium mb-4">Permissions</label>

                <span class="text-red-600 text-sm error-message" id="permissions-error"></span>

                <div class="mb-3">
                    <button
                        type="button"
                        id="select-all"
                        class="btn btn-sm btn-secondary">
                        Select All
                    </button>
                    <button
                        type="button"
                        id="deselect-all"
                        class="btn btn-sm btn-secondary ml-2">
                        Deselect All
                    </button>
                </div>

                <div id="permissions-container">
                    @foreach($permissions as $group => $perms)
                        <div class="mb-4 p-4 border rounded permission-group">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-semibold text-lg capitalize">{{ $group }}</h3>
                                <div class="text-sm text-gray-500">
                                    <span class="selected-count">0</span> / {{ $perms->count() }} selected
                                </div>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($perms as $permission)
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $permission->id }}"
                                            class="form-checkbox permission-checkbox"
                                            data-group="{{ $group }}"
                                            {{ in_array($permission->id, $selectedPermissions) ? 'checked' : '' }}
                                        >
                                        <span class="text-sm">{{ $permission->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">
                    Update Role
                </button>
                <a href="{{ route('admin.roles.index') }}" class="btn">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
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

            // Clear errors
            function clearErrors() {
                document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            }

            // Show errors
            function showErrors(errors) {
                clearErrors();

                for (const [field, messages] of Object.entries(errors)) {
                    const errorElement = document.getElementById(`${field}-error`);
                    if (errorElement) {
                        errorElement.textContent = messages[0];
                    }
                }
            }

            // Update selected count
            function updateSelectedCount() {
                document.querySelectorAll('.permission-group').forEach(group => {
                    const checkboxes = group.querySelectorAll('.permission-checkbox');
                    const checkedCount = group.querySelectorAll('.permission-checkbox:checked').length;
                    const countSpan = group.querySelector('.selected-count');
                    if (countSpan) {
                        countSpan.textContent = checkedCount;
                    }
                });
            }

            // Initialize
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('edit-role-form');
                const roleId = document.querySelector('input[name="role_id"]').value;

                // Update initial counts
                updateSelectedCount();

                // Listen to checkbox changes
                document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', updateSelectedCount);
                });

                // Select all
                document.getElementById('select-all').addEventListener('click', function() {
                    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                        checkbox.checked = true;
                    });
                    updateSelectedCount();
                });

                // Deselect all
                document.getElementById('deselect-all').addEventListener('click', function() {
                    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    updateSelectedCount();
                });

                // Form submission
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    clearErrors();

                    const formData = new FormData(form);

                    fetch(`{{ route('admin.roles.index') }}/${roleId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect;
                        } else if (data.errors) {
                            showErrors(data.errors);
                        } else {
                            showAlert(data.message || 'An error occurred', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('An error occurred while updating the role', 'error');
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
