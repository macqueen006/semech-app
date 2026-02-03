<div class="divide-y">
    @forelse($messages as $message)
        <a href="{{ route('admin.contact.show', $message->id) }}"
           class="block p-4 hover:bg-gray-50 transition {{ $message->status === 'unread' ? 'bg-blue-50' : '' }}">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-medium">{{ $message->name }}</span>
                        <span class="text-sm text-gray-500">{{ $message->email }}</span>
                        @if($message->status === 'unread')
                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">New</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-600 line-clamp-2">{{ $message->body }}</p>
                </div>
                <div class="text-right text-sm text-gray-500">
                    {{ $message->created_at->diffForHumans() }}
                </div>
            </div>
        </a>
    @empty
        <div class="p-8 text-center text-gray-500">
            No messages found
        </div>
    @endforelse
</div>
