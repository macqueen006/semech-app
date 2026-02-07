<x-app-layout>
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-4 mb-2">
                <a href="{{ route('admin.users.index') }}"
                   class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create User</h1>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Add a new user to the system</p>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div
                class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div
                class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('admin.users.store') }}" method="POST" class="max-w-2xl">
            @csrf

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-6">
                <!-- First Name -->
                <div>
                    <label for="firstname" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="firstname"
                        name="firstname"
                        value="{{ old('firstname') }}"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('firstname') border-red-500 @enderror"
                        required>
                    @error('firstname')
                    <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Last Name -->
                <div>
                    <label for="lastname" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="lastname"
                        name="lastname"
                        value="{{ old('lastname') }}"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('lastname') border-red-500 @enderror"
                        required>
                    @error('lastname')
                    <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('email') border-red-500 @enderror"
                        required>
                    @error('email')
                    <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('password') border-red-500 @enderror"
                        required>
                    @error('password')
                    <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Minimum 8 characters
                    </p>
                </div>

                <!-- Roles -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Roles <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2">
                        @foreach($roles as $key => $role)
                            <label class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    name="roles[]"
                                    value="{{ $key }}"
                                    {{ in_array($key, old('roles', [])) ? 'checked' : '' }}
                                    class="form-checkbox h-4 w-4 text-blue-600 rounded focus:ring-blue-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $role }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles')
                    <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <label class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="is_admin"
                            value="1"
                            {{ old('is_admin') ? 'checked' : '' }}
                            class="form-checkbox h-4 w-4 text-red-600 rounded focus:ring-red-500">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Mark as Administrator
                            <span class="text-xs text-gray-500 dark:text-gray-400 block">
                                Grants full access to admin area regardless of roles
                            </span>
                        </span>
                    </label>
                </div>

                <!-- Send Email -->
                <div>
                    <label class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="send_mail"
                            value="1"
                            {{ old('send_mail') ? 'checked' : '' }}
                            class="form-checkbox h-4 w-4 text-blue-600 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Send welcome email</span>
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        <i class="fa-solid fa-save mr-2"></i>Create User
                    </button>

                    <a href="{{ route('admin.users.index') }}"
                       class="px-6 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg transition">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
\\\
