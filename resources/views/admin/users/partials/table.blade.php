<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
    <thead class="bg-gray-50 dark:bg-gray-900">
    <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            ID
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Avatar
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Name
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Email
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Role
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Actions
        </th>
    </tr>
    </thead>
    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
    @forelse($users as $user)
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" data-user-id="{{ $user->id }}">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                <div class="flex items-center gap-3">
                    <!-- Bulk mode checkbox (hidden by default, shown via JS) -->
                    @if($user->id !== auth()->id())
                        <input
                            type="checkbox"
                            class="user-checkbox form-checkbox h-4 w-4 text-blue-600 hidden"
                            value="{{ $user->id }}">
                    @endif
                    <span>{{ $user->id }}</span>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <img src="{{ $user->image_path }}"
                     alt="{{ $user->firstname }}"
                     class="w-10 h-10 rounded-full object-cover">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ $user->firstname }} {{ $user->lastname }}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                @if($user->roles->isNotEmpty())
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ $user->roles[0]->name }}
                        </span>
                @endif
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center gap-2">
                    <button
                        class="view-user-btn text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                        data-id="{{ $user->id }}">
                        <i class="fa-solid fa-eye"></i> View
                    </button>

                    @can('user-edit')
                        @if($user->id !== auth()->id())
                            <a href="{{ route('admin.users.edit', $user->id) }}"
                               class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                <i class="fa-solid fa-edit"></i> Edit
                            </a>
                        @endif
                    @endcan

                    @can('user-delete')
                        @if($user->id !== auth()->id())
                            <button
                                class="delete-user-btn text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                data-id="{{ $user->id }}">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        @endif
                    @endcan
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                No users found.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
