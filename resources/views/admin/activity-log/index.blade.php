<x-app-layout>
    <div class="p-4" id="activityLogApp">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Activity Log</h1>
            <p class="text-gray-600">Track all system activities and changes</p>
        </div>

        <!-- Success Message -->
        @if(session('message'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                 role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif

        <!-- Bulk Delete Confirmation Modal -->
        <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg max-w-md w-full mx-4 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="fa-solid fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold">Confirm Deletion</h3>
                </div>

                <p class="text-gray-600 mb-6">
                    Are you sure you want to delete <strong id="deleteCount"></strong> selected
                    <span id="deleteText"></span>?
                    This action cannot be undone.
                </p>

                <div class="flex gap-3 justify-end">
                    <button id="cancelDelete" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button id="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Activity Detail Modal -->
        <div id="activityModal"
             class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
                    <h3 class="text-xl font-bold">Activity Details</h3>
                    <button id="closeActivityModal" class="text-gray-500 hover:text-gray-700">
                        <i class="fa-solid fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Content -->
                <div id="activityModalContent" class="p-6 space-y-6"></div>

                <!-- Modal Footer -->
                <div class="sticky bottom-0 bg-gray-50 border-t px-6 py-4 flex justify-end">
                    <button id="closeActivityModalFooter"
                            class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <!-- Filters Sidebar -->
            <div class="w-80 bg-white border rounded-lg p-4 h-fit sticky top-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold">Filters</h2>
                    <button id="clearFilters" class="text-sm text-blue-600 hover:underline" style="display: none;">
                        Clear All
                    </button>
                </div>

                <form id="filterForm" method="GET" action="{{ route('admin.activity.index') }}">
                    <div class="space-y-6">
                        <!-- Search -->
                        <div>
                            <label class="block font-medium mb-2">Search</label>
                            <input
                                type="text"
                                name="search"
                                id="searchInput"
                                value="{{ $search }}"
                                placeholder="Search activities..."
                                class="w-full px-3 py-2 border rounded">
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label class="block font-medium mb-2">Date Range</label>
                            <div class="space-y-2">
                                <input
                                    type="date"
                                    name="date_from"
                                    id="dateFrom"
                                    value="{{ $dateFrom }}"
                                    placeholder="From"
                                    class="w-full px-3 py-2 border rounded">
                                <input
                                    type="date"
                                    name="date_to"
                                    id="dateTo"
                                    value="{{ $dateTo }}"
                                    placeholder="To"
                                    class="w-full px-3 py-2 border rounded">
                            </div>
                        </div>

                        <!-- Records Per Page -->
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
                                    <span class="text-sm">All records</span>
                                </label>
                            </div>
                        </div>

                        <!-- Event Types -->
                        @if(count($eventTypes) > 0)
                            <div>
                                <label class="block font-medium mb-2">Event Type</label>
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    @foreach($eventTypes as $type)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                name="types[]"
                                                value="{{ $type }}"
                                                class="form-checkbox type-checkbox"
                                                {{ in_array($type, $selectedTypes) ? 'checked' : '' }}>
                                            <span class="text-sm capitalize">{{ $type }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Subject Types -->
                        @if(count($subjectTypes) > 0)
                            <div>
                                <label class="block font-medium mb-2">Subject Type</label>
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    @foreach($subjectTypes as $subject)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                name="subjects[]"
                                                value="App\Models\{{ $subject }}"
                                                class="form-checkbox subject-checkbox"
                                                {{ in_array("App\\Models\\$subject", $selectedSubjects) ? 'checked' : '' }}>
                                            <span class="text-sm">{{ $subject }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Users -->
                        @if(count($users) > 0)
                            <div>
                                <label class="block font-medium mb-2">Users</label>
                                <div class="space-y-2 max-h-64 overflow-y-auto">
                                    @foreach($users as $user)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                name="users[]"
                                                value="{{ $user->id }}"
                                                class="form-checkbox user-checkbox"
                                                {{ in_array($user->id, $selectedUsers) ? 'checked' : '' }}>
                                            <span class="text-sm">{{ $user->firstname }} {{ $user->lastname }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Activities List -->
            <div class="flex-1">
                <!-- Bulk Actions Toolbar -->
                <div id="bulkToolbar"
                     class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-check-circle text-blue-600 text-xl"></i>
                        <span class="font-medium text-gray-900">
                        <span id="selectedCount"></span>
                        <span id="selectedLabel"></span> selected
                    </span>
                    </div>
                    <div class="flex gap-2">
                        <button id="clearSelection"
                                class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded hover:bg-gray-50">
                            Clear Selection
                        </button>
                        <button id="deleteSelected"
                                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 flex items-center gap-2">
                            <i class="fa-solid fa-trash"></i>
                            Delete Selected
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" id="selectAll" class="form-checkbox rounded">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($activities as $activity)
                                <tr class="hover:bg-gray-50 activity-row" data-activity-id="{{ $activity->id }}">
                                    <!-- Checkbox -->
                                    <td class="px-6 py-4">
                                        <input type="checkbox" class="form-checkbox rounded activity-checkbox"
                                               data-id="{{ $activity->id }}">
                                    </td>

                                    <!-- Event -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            @php
                                                $iconMap = [
                                                    'created' => ['icon' => 'fa-plus-circle', 'color' => 'text-green-600'],
                                                    'updated' => ['icon' => 'fa-edit', 'color' => 'text-blue-600'],
                                                    'deleted' => ['icon' => 'fa-trash', 'color' => 'text-red-600'],
                                                    'published' => ['icon' => 'fa-paper-plane', 'color' => 'text-purple-600'],
                                                    'unpublished' => ['icon' => 'fa-eye-slash', 'color' => 'text-gray-600'],
                                                    'logged in' => ['icon' => 'fa-sign-in-alt', 'color' => 'text-green-600'],
                                                    'logged out' => ['icon' => 'fa-sign-out-alt', 'color' => 'text-gray-600'],
                                                ];
                                                $icon = $iconMap[$activity->description] ?? ['icon' => 'fa-circle', 'color' => 'text-gray-400'];
                                            @endphp
                                            <i class="fa-solid {{ $icon['icon'] }} {{ $icon['color'] }}"></i>
                                            <span class="font-medium capitalize">{{ $activity->description }}</span>
                                        </div>
                                    </td>

                                    <!-- Subject -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <div class="font-medium text-gray-900">
                                                {{ class_basename($activity->subject_type) }}
                                            </div>
                                            @if($activity->subject)
                                                <div class="text-gray-500">
                                                    {{ $activity->subject->title ?? $activity->subject->name ?? "#$activity->subject_id" }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- User -->
                                    <td class="px-6 py-4">
                                        @if($activity->causer)
                                            <div class="flex items-center gap-2">
                                                @if($activity->causer->image_path ?? false)
                                                    <img src="{{ asset($activity->causer->image_path) }}"
                                                         alt="{{ $activity->causer->firstname }}"
                                                         class="w-8 h-8 rounded-full">
                                                @endif
                                                <div class="text-sm">
                                                    <div class="font-medium text-gray-900">
                                                        {{ $activity->causer->firstname }} {{ $activity->causer->lastname }}
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500">System</span>
                                        @endif
                                    </td>

                                    <!-- Date -->
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div>{{ $activity->created_at->format('M d, Y') }}</div>
                                        <div
                                            class="text-xs text-gray-400">{{ $activity->created_at->format('H:i:s') }}</div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4">
                                        <button class="text-sm text-blue-600 hover:underline view-activity"
                                                data-id="{{ $activity->id }}">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        No activities found.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                @if($limit > 0 && method_exists($activities, 'links'))
                    <div class="mt-6">
                        {{ $activities->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const selectedIds = [];
                const selectAllCheckbox = document.getElementById('selectAll');
                const activityCheckboxes = document.querySelectorAll('.activity-checkbox');
                const bulkToolbar = document.getElementById('bulkToolbar');
                const selectedCount = document.getElementById('selectedCount');
                const selectedLabel = document.getElementById('selectedLabel');
                const clearSelection = document.getElementById('clearSelection');
                const deleteSelected = document.getElementById('deleteSelected');
                const deleteModal = document.getElementById('deleteModal');
                const cancelDelete = document.getElementById('cancelDelete');
                const confirmDelete = document.getElementById('confirmDelete');
                const deleteCount = document.getElementById('deleteCount');
                const deleteText = document.getElementById('deleteText');
                const activityModal = document.getElementById('activityModal');
                const closeActivityModal = document.getElementById('closeActivityModal');
                const closeActivityModalFooter = document.getElementById('closeActivityModalFooter');
                const activityModalContent = document.getElementById('activityModalContent');
                const filterForm = document.getElementById('filterForm');
                const searchInput = document.getElementById('searchInput');
                const clearFiltersBtn = document.getElementById('clearFilters');

                // Filter form auto-submit
                let filterTimeout;
                searchInput.addEventListener('input', function () {
                    clearTimeout(filterTimeout);
                    filterTimeout = setTimeout(() => filterForm.submit(), 300);
                });

                document.querySelectorAll('.limit-radio, .type-checkbox, .subject-checkbox, .user-checkbox, #dateFrom, #dateTo').forEach(input => {
                    input.addEventListener('change', () => filterForm.submit());
                });

                // Clear filters
                clearFiltersBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    window.location.href = '{{ route('admin.activity.index') }}';
                });

                // Show/hide clear filters button
                function updateClearButton() {
                    const hasFilters = searchInput.value ||
                        document.querySelector('.type-checkbox:checked') ||
                        document.querySelector('.subject-checkbox:checked') ||
                        document.querySelector('.user-checkbox:checked') ||
                        document.getElementById('dateFrom').value ||
                        document.getElementById('dateTo').value;
                    clearFiltersBtn.style.display = hasFilters ? 'block' : 'none';
                }

                updateClearButton();

                // Select all toggle
                selectAllCheckbox.addEventListener('change', function () {
                    const isChecked = this.checked;
                    activityCheckboxes.forEach(checkbox => {
                        checkbox.checked = isChecked;
                        const id = parseInt(checkbox.dataset.id);
                        if (isChecked && !selectedIds.includes(id)) {
                            selectedIds.push(id);
                        } else if (!isChecked) {
                            const index = selectedIds.indexOf(id);
                            if (index > -1) selectedIds.splice(index, 1);
                        }
                    });
                    updateUI();
                });

                // Individual checkbox toggle
                activityCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function () {
                        const id = parseInt(this.dataset.id);
                        if (this.checked) {
                            if (!selectedIds.includes(id)) selectedIds.push(id);
                        } else {
                            const index = selectedIds.indexOf(id);
                            if (index > -1) selectedIds.splice(index, 1);
                        }
                        updateSelectAll();
                        updateUI();
                    });
                });

                function updateSelectAll() {
                    const allChecked = Array.from(activityCheckboxes).every(cb => cb.checked);
                    const noneChecked = Array.from(activityCheckboxes).every(cb => !cb.checked);
                    selectAllCheckbox.checked = allChecked;
                    selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
                }

                function updateUI() {
                    if (selectedIds.length > 0) {
                        bulkToolbar.classList.remove('hidden');
                        selectedCount.textContent = selectedIds.length;
                        selectedLabel.textContent = selectedIds.length === 1 ? 'activity' : 'activities';

                        document.querySelectorAll('.activity-row').forEach(row => {
                            const id = parseInt(row.dataset.activityId);
                            if (selectedIds.includes(id)) {
                                row.classList.add('bg-blue-50');
                            } else {
                                row.classList.remove('bg-blue-50');
                            }
                        });
                    } else {
                        bulkToolbar.classList.add('hidden');
                        document.querySelectorAll('.activity-row').forEach(row => {
                            row.classList.remove('bg-blue-50');
                        });
                    }
                }

                // Clear selection
                clearSelection.addEventListener('click', function () {
                    selectedIds.length = 0;
                    activityCheckboxes.forEach(cb => cb.checked = false);
                    selectAllCheckbox.checked = false;
                    updateUI();
                });

                // Delete selected
                deleteSelected.addEventListener('click', function () {
                    deleteCount.textContent = selectedIds.length;
                    deleteText.textContent = selectedIds.length === 1 ? 'activity' : 'activities';
                    deleteModal.classList.remove('hidden');
                });

                cancelDelete.addEventListener('click', () => deleteModal.classList.add('hidden'));
                deleteModal.addEventListener('click', function (e) {
                    if (e.target === deleteModal) deleteModal.classList.add('hidden');
                });

                confirmDelete.addEventListener('click', function () {
                    fetch('{{ route('admin.activity.bulk-delete') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ids: selectedIds})
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            }
                        });
                });

                // View activity details
                document.querySelectorAll('.view-activity').forEach(button => {
                    button.addEventListener('click', function () {
                        const activityId = this.dataset.id;
                        fetch(`{{ url('admin/activity-log') }}/${activityId}`)
                            .then(response => response.json())
                            .then(activity => {
                                activityModalContent.innerHTML = renderActivityDetails(activity);
                                activityModal.classList.remove('hidden');
                            });
                    });
                });

                closeActivityModal.addEventListener('click', () => activityModal.classList.add('hidden'));
                closeActivityModalFooter.addEventListener('click', () => activityModal.classList.add('hidden'));
                activityModal.addEventListener('click', function (e) {
                    if (e.target === activityModal) activityModal.classList.add('hidden');
                });

                function renderActivityDetails(activity) {
                    const iconMap = {
                        'created': {icon: 'fa-plus-circle', color: 'text-green-600'},
                        'updated': {icon: 'fa-edit', color: 'text-blue-600'},
                        'deleted': {icon: 'fa-trash', color: 'text-red-600'},
                        'published': {icon: 'fa-paper-plane', color: 'text-purple-600'},
                        'unpublished': {icon: 'fa-eye-slash', color: 'text-gray-600'},
                        'logged in': {icon: 'fa-sign-in-alt', color: 'text-green-600'},
                        'logged out': {icon: 'fa-sign-out-alt', color: 'text-gray-600'}
                    };
                    const icon = iconMap[activity.description] || {icon: 'fa-circle', color: 'text-gray-400'};

                    let html = `
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Event</label>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid ${icon.icon} ${icon.color}"></i>
                        <span class="font-medium capitalize">${activity.description}</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Log Name</label>
                    <span class="text-gray-900">${activity.log_name || 'default'}</span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <span class="text-gray-900">${activity.subject_type_basename}${activity.subject_id ? ' #' + activity.subject_id : ''}</span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date & Time</label>
                    <span class="text-gray-900">${activity.created_at_formatted}</span>
                </div>`;

                    if (activity.causer) {
                        html += `
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Performed By</label>
                    <div class="flex items-center gap-3">
                        ${activity.causer.image_path ? `<img src="${activity.causer.image_path}" alt="${activity.causer.firstname}" class="w-10 h-10 rounded-full">` : ''}
                        <div>
                            <div class="font-medium text-gray-900">${activity.causer.firstname} ${activity.causer.lastname}</div>
                            <div class="text-sm text-gray-500">${activity.causer.email}</div>
                        </div>
                    </div>
                </div>`;
                    }

                    html += '</div>';

                    if (activity.properties && Object.keys(activity.properties).length > 0) {
                        html += '<div><label class="block text-sm font-medium text-gray-700 mb-2">Properties</label>';

                        if (activity.properties.old) {
                            html += `
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-red-700 mb-2">Old Values</h4>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-sm text-gray-800">${JSON.stringify(activity.properties.old, null, 2)}</pre>
                        </div>
                    </div>`;
                        }

                        if (activity.properties.attributes) {
                            html += `
                    <div>
                        <h4 class="text-sm font-medium text-green-700 mb-2">New Values</h4>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-sm text-gray-800">${JSON.stringify(activity.properties.attributes, null, 2)}</pre>
                        </div>
                    </div>`;
                        }

                        if (!activity.properties.old && !activity.properties.attributes) {
                            html += `
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 overflow-x-auto">
                        <pre class="text-sm text-gray-800">${JSON.stringify(activity.properties, null, 2)}</pre>
                    </div>`;
                        }

                        html += '</div>';
                    }

                    return html;
                }
            });
        </script>
    @endpush
</x-app-layout>
