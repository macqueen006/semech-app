<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
    <thead class="bg-gray-50 dark:bg-gray-900">
    <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Advertisement</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Position</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Performance</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date Range</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
    </tr>
    </thead>
    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
    @forelse ($advertisements as $ad)
        <tr>
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <img src="{{ asset($ad->image_path) }}"
                         alt="{{ $ad->title }}"
                         class="h-16 w-24 object-cover rounded">
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $ad->title }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($ad->description, 40) }}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    {{ ucfirst(str_replace('-', ' ', $ad->position)) }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">
                    {{ number_format($ad->impressions) }} views
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ number_format($ad->clicks) }} clicks ({{ $ad->ctr }}% CTR)
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                @if($ad->start_date || $ad->end_date)
                    <div>{{ $ad->start_date?->format('M d, Y') ?? 'No start' }}</div>
                    <div>{{ $ad->end_date?->format('M d, Y') ?? 'No end' }}</div>
                @else
                    <span class="text-gray-400">Unlimited</span>
                @endif
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <button
                    data-id="{{ $ad->id }}"
                    class="toggle-status-btn relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $ad->is_active ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-700' }}"
                    @cannot('advertisement-edit') disabled @endcannot>
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $ad->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center gap-2">
                    @can('advertisement-edit')
                        <a href="{{ route('admin.advertisements.edit', $ad->id) }}"
                           class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                            Edit
                        </a>
                    @endcan

                    @can('advertisement-delete')
                        <button
                            data-id="{{ $ad->id }}"
                            class="delete-ad-btn text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                            Delete
                        </button>
                    @endcan
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No advertisements</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new advertisement.</p>
                @can('advertisement-create')
                    <div class="mt-6">
                        <a href="{{ route('admin.advertisements.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Create Advertisement
                        </a>
                    </div>
                @endcan
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
