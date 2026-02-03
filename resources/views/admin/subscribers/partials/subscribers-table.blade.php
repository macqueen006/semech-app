<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left">
                <input
                    type="checkbox"
                    id="select-all-checkbox"
                    class="form-checkbox">
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subscribed At</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unsubscribed At</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
        </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
        @forelse($subscribers as $subscriber)
            <tr class="subscriber-row" data-subscriber-id="{{ $subscriber->id }}">
                <td class="px-6 py-4">
                    <input
                        type="checkbox"
                        class="subscriber-checkbox form-checkbox"
                        data-subscriber-id="{{ $subscriber->id }}">
                </td>
                <td class="px-6 py-4 text-sm">{{ $subscriber->id }}</td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ $subscriber->email }}</div>
                </td>
                <td class="px-6 py-4">
                    @if($subscriber->isSubscribed())
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                            <i class="fa-solid fa-check-circle mr-1"></i> Active
                        </span>
                    @else
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                            <i class="fa-solid fa-times-circle mr-1"></i> Unsubscribed
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    {{ $subscriber->subscribed_at ? $subscriber->subscribed_at->format('M d, Y H:i') : 'N/A' }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    {{ $subscriber->unsubscribed_at ? $subscriber->unsubscribed_at->format('M d, Y H:i') : '-' }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.subscribers.show', $subscriber->id) }}"
                           class="text-sm text-blue-600 hover:underline">
                            View
                        </a>
                        <button
                            class="delete-subscriber-btn text-sm text-red-600 hover:underline"
                            data-subscriber-id="{{ $subscriber->id }}"
                            data-subscriber-email="{{ $subscriber->email }}">
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                    <i class="fa-solid fa-inbox text-4xl mb-2"></i>
                    <p>No subscribers found.</p>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

