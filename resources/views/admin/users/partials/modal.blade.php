<div id="user-modal-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div id="user-modal-content" class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-start mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">User Details</h2>
                <button id="close-user-modal" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </div>

            <!-- User Profile Section -->
            <div class="flex items-start gap-6 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <img
                    src="{{ $userData['user']->image_path }}"
                    alt="{{ $userData['user']->firstname }}"
                    class="w-24 h-24 rounded-full object-cover border-4 border-gray-200 dark:border-gray-700">

                <div class="flex-1">
                    <h3 class="text-xl font-bold mb-1 text-gray-900 dark:text-white">
                        {{ $userData['user']->firstname }} {{ $userData['user']->lastname }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-2">{{ $userData['user']->email }}</p>

                    @if($userData['user']->roles->isNotEmpty())
                        <span class="inline-block px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm font-medium">
                            <i class="fa-solid fa-user-tag mr-1"></i>
                            {{ $userData['user']->roles[0]->name }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Bio Section -->
            @if($userData['user']->bio)
                <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="font-bold mb-2 text-gray-700 dark:text-gray-300">
                        <i class="fa-solid fa-user mr-2"></i>Bio
                    </h4>
                    <p class="text-gray-600 dark:text-gray-400">{{ $userData['user']->bio }}</p>
                </div>
            @endif

            <!-- Statistics Section -->
            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <h4 class="font-bold mb-4 text-gray-700 dark:text-gray-300">
                    <i class="fa-solid fa-chart-line mr-2"></i>Statistics
                </h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg text-center">
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                            {{ $userData['posts_count'] }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Total Posts</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg text-center">
                        <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                            {{ number_format($userData['total_views']) }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Total Views</div>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg text-center">
                        <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                            0
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Comments</div>
                    </div>
                </div>
            </div>

            <!-- Social Links Section -->
            @if($userData['user']->website || $userData['user']->twitter || $userData['user']->linkedin || $userData['user']->github)
                <div class="mb-6">
                    <h4 class="font-bold mb-3 text-gray-700 dark:text-gray-300">
                        <i class="fa-solid fa-link mr-2"></i>Social Links
                    </h4>
                    <div class="space-y-2">
                        @if($userData['user']->website)
                            <a href="{{ $userData['user']->website }}" target="_blank"
                               class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <i class="fa-solid fa-globe text-gray-600 dark:text-gray-400 w-5"></i>
                                <span class="text-blue-600 dark:text-blue-400 hover:underline">{{ $userData['user']->website }}</span>
                            </a>
                        @endif

                        @if($userData['user']->twitter)
                            <a href="https://twitter.com/{{ $userData['user']->twitter }}" target="_blank"
                               class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <i class="fa-brands fa-twitter text-blue-400 w-5"></i>
                                <span class="text-blue-600 dark:text-blue-400 hover:underline">@{{ $userData['user']->twitter }}</span>
                            </a>
                        @endif

                        @if($userData['user']->linkedin)
                            <a href="https://linkedin.com/in/{{ $userData['user']->linkedin }}" target="_blank"
                               class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <i class="fa-brands fa-linkedin text-blue-700 dark:text-blue-500 w-5"></i>
                                <span class="text-blue-600 dark:text-blue-400 hover:underline">{{ $userData['user']->linkedin }}</span>
                            </a>
                        @endif

                        @if($userData['user']->github)
                            <a href="https://github.com/{{ $userData['user']->github }}" target="_blank"
                               class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <i class="fa-brands fa-github text-gray-800 dark:text-gray-200 w-5"></i>
                                <span class="text-blue-600 dark:text-blue-400 hover:underline">@{{ $userData['user']->github }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                @can('user-edit')
                    @if($userData['user']->id !== auth()->id())
                        <a href="{{ route('admin.users.edit', $userData['user']->id) }}"
                           class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center px-4 py-2 rounded-lg transition">
                            <i class="fa-solid fa-edit mr-2"></i>Edit User
                        </a>
                    @endif
                @endcan

                <button id="close-user-modal" class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2 rounded-lg transition">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
