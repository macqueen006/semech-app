<x-app-layout>
    <div class="p-4">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Role Details</h1>

            <div class="flex gap-2">
                @can('role-edit')
                    @if($role->name !== 'Admin' && (!auth()->user()->roles->isEmpty() && $role->id !== auth()->user()->roles[0]->id))
                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-primary">
                            Edit Role
                        </a>
                    @endif
                @endcan

                <a href="{{ route('admin.roles.index') }}" class="btn">
                    Back to Roles
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-gray-600 font-medium">ID:</span>
                    <span class="ml-2">{{ $role->id }}</span>
                </div>
                <div>
                    <span class="text-gray-600 font-medium">Name:</span>
                    <span class="ml-2">{{ $role->name }}</span>
                </div>
                <div>
                    <span class="text-gray-600 font-medium">Created:</span>
                    <span class="ml-2">{{ $role->created_at->format('M d, Y') }}</span>
                </div>
                <div>
                    <span class="text-gray-600 font-medium">Updated:</span>
                    <span class="ml-2">{{ $role->updated_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Permissions ({{ collect($grouped)->flatten()->count() }})</h2>

            @if(empty($grouped))
                <p class="text-gray-500">No permissions assigned to this role.</p>
            @else
                @foreach($grouped as $group => $permissions)
                    <div class="mb-6">
                        <h3 class="font-semibold text-lg mb-3 capitalize border-b pb-2">{{ $group }}</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach($permissions as $permissionName)
                                <div class="flex items-center gap-2 p-2 bg-gray-50 rounded">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                              clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm">{{ $permissionName }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>
