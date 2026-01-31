<x-app-layout>
    <div id="dashboard-container">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(isset($error))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ $error }}
            </div>
        @endif

        <div class="flex-1 flex flex-col lg:flex-row">
            <div class="flex-1 min-w-0 flex flex-col border-e border-gray-200 dark:border-neutral-700">
                <!-- Featured News Blog Section -->
                <div class="p-4 flex flex-col bg-white dark:bg-neutral-800">
                    <!-- Header -->
                    <div class="pb-2 flex flex-wrap justify-between items-center gap-2 border-b border-dashed border-gray-200 dark:border-neutral-700">
                        <h2 class="font-medium text-gray-800 dark:text-neutral-200">
                            Top posts
                            <span id="live-indicator" class="inline-flex items-center gap-1 ml-2" style="{{ $autoRefresh ? '' : 'display:none;' }}">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            <span class="text-xs text-green-600 dark:text-green-400">Live</span>
                        </span>
                        </h2>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Last updated: <span id="last-updated">{{ $lastUpdated }}</span>
                        </p>

                        <button type="button" id="refresh-btn"
                                class="py-1.5 px-2.5 flex items-center justify-center gap-x-1.5 border border-gray-200 text-gray-800 text-[13px] rounded-lg hover:bg-indigo-50 hover:border-indigo-100 hover:text-indigo-700 focus:outline-none dark:text-neutral-200 dark:border-neutral-700 dark:hover:bg-indigo-500/20">
                            <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"></path>
                                <path d="M21 3v5h-5"></path>
                            </svg>
                            Refresh
                        </button>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <label for="dateRange" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Range</label>
                        <select id="dateRange"
                                class="mt-1 block w-full md:w-64 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ $dateRange == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="this_month" {{ $dateRange == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month" {{ $dateRange == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        </select>
                    </div>
                    <!-- Stats Cards -->
                    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 p-4">
                        <!-- Total Views Card -->
                        <div class="flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
                            <div class="p-4 md:p-5">
                                <div class="flex items-center gap-x-2">
                                    <p class="text-xs uppercase text-gray-500 dark:text-neutral-500">
                                        Total Views
                                    </p>
                                    <div class="hs-tooltip">
                                        <div class="hs-tooltip-toggle">
                                            <svg class="shrink-0 size-4 text-gray-500 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                <path d="M12 17h.01"></path>
                                            </svg>
                                            <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded-md shadow-2xs dark:bg-neutral-700" role="tooltip">
                            {{ number_format($viewsInPeriod) }} views ({{ number_format($uniqueViewsInPeriod) }} unique) in period
                        </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-1 flex items-center gap-x-2">
                                    <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                        {{ number_format($totalViews) }}
                                    </h3>
                                    @if(isset($comparison['changes']['views']) && is_numeric($comparison['changes']['views']))
                                        <span class="flex items-center gap-x-1 {{ $comparison['changes']['views'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        @if($comparison['changes']['views'] >= 0)
                                                <svg class="inline-block size-4 self-center" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                                <polyline points="16 7 22 7 22 13"></polyline>
                            </svg>
                                            @else
                                                <svg class="inline-block size-4 self-center" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 17 13.5 8.5 8.5 13.5 2 7"></polyline>
                                <polyline points="16 17 22 17 22 11"></polyline>
                            </svg>
                                            @endif
                        <span class="inline-block text-sm">
                            {{ abs($comparison['changes']['views']) }}%
                        </span>
                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- End Total Views Card -->

                        <!-- Total Posts Card -->
                        <div class="flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
                            <div class="p-4 md:p-5">
                                <div class="flex items-center gap-x-2">
                                    <p class="text-xs uppercase text-gray-500 dark:text-neutral-500">
                                        Total Posts
                                    </p>
                                    <div class="hs-tooltip">
                                        <div class="hs-tooltip-toggle">
                                            <svg class="shrink-0 size-4 text-gray-500 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                <path d="M12 17h.01"></path>
                                            </svg>
                                            <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded-md shadow-2xs dark:bg-neutral-700" role="tooltip">
                            {{ $publishedPosts }} published · {{ $draftPosts }} drafts
                        </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-1 flex items-center gap-x-2">
                                    <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                        {{ number_format($totalPosts) }}
                                    </h3>
                                    @if(isset($comparison['changes']['posts']))
                                        <span class="flex items-center gap-x-1 {{ $comparison['changes']['posts'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        @if($comparison['changes']['posts'] >= 0)
                                                <svg class="inline-block size-4 self-center" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                                <polyline points="16 7 22 7 22 13"></polyline>
                            </svg>
                                            @else
                                                <svg class="inline-block size-4 self-center" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="22 17 13.5 8.5 8.5 13.5 2 7"></polyline>
                                <polyline points="16 17 22 17 22 11"></polyline>
                            </svg>
                                            @endif
                        <span class="inline-block text-sm">
                            {{ abs($comparison['changes']['posts']) }}%
                        </span>
                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- End Total Posts Card -->

                        <!-- Total Users Card -->
                        <div class="flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
                            <div class="p-4 md:p-5">
                                <div class="flex items-center gap-x-2">
                                    <p class="text-xs uppercase text-gray-500 dark:text-neutral-500">
                                        Total Users
                                    </p>
                                    <div class="hs-tooltip">
                                        <div class="hs-tooltip-toggle">
                                            <svg class="shrink-0 size-4 text-gray-500 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                <path d="M12 17h.01"></path>
                                            </svg>
                                            <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded-md shadow-2xs dark:bg-neutral-700" role="tooltip">
                            +{{ $newUsersInPeriod }} new users · {{ $activeAuthors }} active authors
                        </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-1 flex items-center gap-x-2">
                                    <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                        {{ number_format($totalUsers) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <!-- End Total Users Card -->

                        <!-- Newsletter Subscribers -->
                        <div class="flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
                            <div class="p-4 md:p-5">
                                <div class="flex items-center gap-x-2">
                                    <p class="text-xs uppercase text-gray-500 dark:text-neutral-500">
                                        Newsletter Subscribers
                                    </p>
                                    <div class="hs-tooltip">
                                        <div class="hs-tooltip-toggle">
                                            <svg class="shrink-0 size-4 text-gray-500 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                <path d="M12 17h.01"></path>
                                            </svg>
                                            <span class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible opacity-0 transition-opacity inline-block absolute invisible z-10 py-1 px-2 bg-gray-900 text-xs font-medium text-white rounded-md shadow-2xs dark:bg-neutral-700" role="tooltip">
                        +{{ $newSubscribersInPeriod }} new
                        @if($unsubscribedInPeriod > 0)
                                                    · -{{ $unsubscribedInPeriod }} unsubscribed
                                                @endif
                    </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-1 flex items-center gap-x-2">
                                    <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                        {{ number_format($activeSubscribers) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Stats Cards -->

                    <!-- End Header -->
                    @if($topPosts && $topPosts->count() > 0)
                        @foreach($topPosts->take(2) as $index => $post)
                            <!-- Featured News Blog -->
                            <div
                                class="flex flex-col bg-white pb-4 last:pb-0 last:border-b-0 border-b border-gray-200 dark:bg-neutral-800 dark:border-neutral-700">
                                <div class="pt-4 flex flex-col md:flex-row gap-5">
                                    <div
                                        class="relative aspect-4/2 md:aspect-4/3 w-full md:max-w-80 bg-gray-100 rounded-lg dark:bg-neutral-700">
                                        @if($post->image_path)
                                            <img
                                                class="absolute inset-0 size-full object-cover object-center rounded-lg"
                                                src="{{ asset($post->image_path) }}"
                                                alt="{{ $post->title }}">
                                        @else
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="grow">
                                        <div class="h-full flex flex-col">
                                            <p class="text-sm text-gray-500 dark:text-neutral-500">
                                                Post title:
                                            </p>
                                            <h3 class="font-medium text-gray-800 dark:text-neutral-200">
                                                {{ Str::limit($post->title, 60) }}
                                            </h3>

                                            <div class="mt-4 grid grid-cols-2 xl:grid-cols-3 gap-y-4 gap-x-2">
                                                <!-- Position -->
                                                <div class="flex flex-col gap-y-1">
                                    <span class="text-[13px] text-gray-500 dark:text-neutral-500">
                                        Position:
                                    </span>

                                                    <div class="flex items-center gap-x-1.5">
                                                        <svg class="shrink-0 size-4 text-gray-800 dark:text-neutral-200"
                                                             xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                             stroke-width="2" stroke-linecap="round"
                                                             stroke-linejoin="round">
                                                            <path
                                                                d="M10 14.66v1.626a2 2 0 0 1-.976 1.696A5 5 0 0 0 7 21.978"/>
                                                            <path
                                                                d="M14 14.66v1.626a2 2 0 0 0 .976 1.696A5 5 0 0 1 17 21.978"/>
                                                            <path d="M18 9h1.5a1 1 0 0 0 0-5H18"/>
                                                            <path d="M4 22h16"/>
                                                            <path
                                                                d="M6 9a6 6 0 0 0 12 0V3a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1z"/>
                                                            <path d="M6 9H4.5a1 1 0 0 1 0-5H6"/>
                                                        </svg>

                                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-sm text-gray-800 dark:text-neutral-200">
                                                #{{ $index + 1 }}
                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End Position -->

                                                <!-- Published Date -->
                                                <div class="flex flex-col gap-y-1">
                                    <span class="text-[13px] text-gray-500 dark:text-neutral-500">
                                        Published date:
                                    </span>

                                                    <div class="flex items-center gap-x-1.5">
                                                        <svg class="shrink-0 size-4 text-gray-800 dark:text-neutral-200"
                                                             xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                             stroke-width="2" stroke-linecap="round"
                                                             stroke-linejoin="round">
                                                            <path d="M8 2v4"/>
                                                            <path d="M16 2v4"/>
                                                            <path
                                                                d="M21 17V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h11Z"/>
                                                            <path d="M3 10h18"/>
                                                            <path d="M15 22v-4a2 2 0 0 1 2-2h4"/>
                                                        </svg>

                                                        <span
                                                            class="font-medium text-sm text-gray-800 dark:text-neutral-200">
                                            {{ $post->created_at->format('M d, Y') }}
                                        </span>
                                                    </div>
                                                </div>
                                                <!-- End Published Date -->

                                                <!-- Author -->
                                                <div class="flex flex-col gap-y-1">
                                    <span class="text-[13px] text-gray-500 dark:text-neutral-500">
                                        Author:
                                    </span>

                                                    <div class="flex items-center gap-x-1.5">
                                                        <svg class="shrink-0 size-4 text-gray-800 dark:text-neutral-200"
                                                             xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                             stroke-width="2" stroke-linecap="round"
                                                             stroke-linejoin="round">
                                                            <path d="M11.5 15H7a4 4 0 0 0-4 4v2"/>
                                                            <path
                                                                d="M21.378 16.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/>
                                                            <circle cx="10" cy="7" r="4"/>
                                                        </svg>

                                                        <span
                                                            class="font-medium text-sm text-gray-800 dark:text-neutral-200">
                                            {{ $post->user->firstname ?? 'Unknown' }} {{ $post->user->lastname ?? 'Author' }}
                                        </span>
                                                    </div>
                                                </div>
                                                <!-- End Author -->

                                                <!-- Category -->
                                                <div class="flex flex-col gap-y-1">
                                    <span class="text-[13px] text-gray-500 dark:text-neutral-500">
                                        Category:
                                    </span>

                                                    <div class="flex items-center gap-x-1.5">
                                                        <svg class="shrink-0 size-4 text-gray-800 dark:text-neutral-200"
                                                             xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                             stroke-width="2" stroke-linecap="round"
                                                             stroke-linejoin="round">
                                                            <rect width="7" height="7" x="3" y="3" rx="1"/>
                                                            <rect width="7" height="7" x="14" y="3" rx="1"/>
                                                            <rect width="7" height="7" x="14" y="14" rx="1"/>
                                                            <rect width="7" height="7" x="3" y="14" rx="1"/>
                                                        </svg>

                                                        <span
                                                            class="font-medium text-sm text-gray-800 dark:text-neutral-200">
                                            {{ $post->category->name ?? 'Uncategorized' }}
                                        </span>
                                                    </div>
                                                </div>
                                                <!-- End Category -->

                                                <!-- Read Time -->
                                                <div class="flex flex-col gap-y-1">
                                    <span class="text-[13px] text-gray-500 dark:text-neutral-500">
                                        Read time:
                                    </span>

                                                    <div class="flex items-center gap-x-1.5">
                                                        <svg class="shrink-0 size-4 text-gray-800 dark:text-neutral-200"
                                                             xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                             stroke-width="2" stroke-linecap="round"
                                                             stroke-linejoin="round">
                                                            <circle cx="12" cy="12" r="10"/>
                                                            <polyline points="12 6 12 12 16 14"/>
                                                        </svg>

                                                        <span
                                                            class="font-medium text-sm text-gray-800 dark:text-neutral-200">
                                            {{ $post->read_time ?? 5 }} min
                                        </span>
                                                    </div>
                                                </div>
                                                <!-- End Read Time -->

                                                <!-- Views -->
                                                <div class="flex flex-col gap-y-1">
                                    <span class="text-[13px] text-gray-500 dark:text-neutral-500">
                                        Views:
                                    </span>

                                                    <div class="flex items-center gap-x-1.5">
                                                        <svg class="shrink-0 size-4 text-gray-800 dark:text-neutral-200"
                                                             xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                             stroke-width="2" stroke-linecap="round"
                                                             stroke-linejoin="round">
                                                            <path
                                                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                                                            <circle cx="12" cy="12" r="3"/>
                                                        </svg>

                                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-sm text-gray-800 dark:text-neutral-200">
                                                {{ number_format($post->view_count ?? 0) }}
                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End Views -->
                                            </div>

                                            <!-- Footer -->
                                            <div
                                                class="mt-4 xl:mt-auto pt-4 border-t border-gray-200 dark:border-neutral-700">
                                                <div class="flex flex-wrap justify-between items-center gap-1.5">
                                                    <div>
                                                        <a class="inline-flex items-center gap-x-0.5 text-[13px] text-indigo-700 underline underline-offset-2 hover:decoration-2 focus:outline-hidden focus:decoration-2 disabled:opacity-50 disabled:pointer-events-none dark:text-indigo-400"
                                                           href="{{ route('post.show', $post->slug) }}">
                                                            View post
                                                            <svg class="shrink-0 size-4"
                                                                 xmlns="http://www.w3.org/2000/svg" width="24"
                                                                 height="24" viewBox="0 0 24 24" fill="none"
                                                                 stroke="currentColor" stroke-width="2"
                                                                 stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="m9 18 6-6-6-6"></path>
                                                            </svg>
                                                        </a>
                                                    </div>

                                                    <a class="py-1.5 px-2.5 flex items-center justify-center gap-x-1.5 border border-transparent text-gray-500 text-[13px] rounded-lg hover:bg-gray-100 hover:text-gray-800 focus:outline-none focus:bg-gray-100 focus:text-gray-800 dark:text-neutral-200 dark:hover:bg-neutral-700 dark:hover:text-neutral-200 dark:focus:bg-neutral-700 dark:focus:border-indigo-500/20 dark:focus:text-neutral-200"
                                                       href="{{ route('admin.posts.edit', $post->id) }}">
                                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                                             width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                             stroke="currentColor" stroke-width="2"
                                                             stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M12 16v5"/>
                                                            <path d="M16 14v7"/>
                                                            <path d="M20 10v11"/>
                                                            <path
                                                                d="m22 3-8.646 8.646a.5.5 0 0 1-.708 0L9.354 8.354a.5.5 0 0 0-.707 0L2 15"/>
                                                            <path d="M4 18v3"/>
                                                            <path d="M8 14v7"/>
                                                        </svg>
                                                        Metrics
                                                    </a>
                                                </div>
                                            </div>
                                            <!-- End Footer -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Featured News Blog -->
                        @endforeach
                    @else
                        <!-- No Posts Message -->
                        <div class="py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No top posts available</p>
                        </div>
                    @endif
                    <!-- Top Posts Display - Moved from original file -->
                    {{-- Include your top posts display here from the original file --}}
                </div>

                <!-- Device Breakdown Card -->
                @if(isset($deviceBreakdown) && $deviceBreakdown->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Device Breakdown</h3>
                        <div class="grid grid-cols-3 gap-4">
                            @foreach($deviceBreakdown as $device)
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($device->count) }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 capitalize">{{ $device->device_type }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Comparison Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Today vs Yesterday -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Today vs Yesterday</h3>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Posts Created</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $postsToday }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Yesterday: {{ $postsYesterday }}</p>
                                <p class="text-lg font-semibold {{ $postsChange >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $postsChange >= 0 ? '+' : '' }}{{ number_format($postsChange, 1) }}%
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- This Week vs Last Week -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">This Week vs Last Week</h3>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Posts Created</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $postsThisWeek }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Last Week: {{ $postsLastWeek }}</p>
                                <p class="text-lg font-semibold {{ $weekChange >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $weekChange >= 0 ? '+' : '' }}{{ number_format($weekChange, 1) }}%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Posts Growth Chart -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Posts Created (Last 30
                            Days)</h3>

                        @php
                            $maxCount = $postsGrowth->max('count') ?? 0;
                        @endphp

                        @if($postsGrowth->count() > 0 && $maxCount > 0)
                            <div class="h-64 flex items-end justify-between space-x-1">
                                @foreach($postsGrowth as $data)
                                    @php
                                        $percentage = ($data->count / $maxCount) * 100;
                                    @endphp
                                    <div
                                        class="flex-1 bg-blue-500 dark:bg-blue-600 rounded-t hover:bg-blue-600 dark:hover:bg-blue-700 transition-colors relative group"
                                        style="height: {{ $percentage }}%; min-height: {{ $data->count > 0 ? '2px' : '0' }}">
                                        <div
                                            class="hidden group-hover:block absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 whitespace-nowrap z-10">
                                            {{ $data->date ? \Carbon\Carbon::parse($data->date)->format('M d') : 'N/A' }}
                                            : {{ $data->count }} posts
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="h-64 flex items-center justify-center">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No posts created in the last 30
                                        days</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Engagement Stats -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Engagement Overview</h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between mb-1">
                                <span
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Bookmarks</span>
                                    <span
                                        class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($totalBookmarks) }}</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: 100%"></div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">+{{ $bookmarksInPeriod }} in
                                    selected period</p>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Comments</span>
                                    <span
                                        class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($totalComments) }}</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    @php
                                        $maxEngagement = max($totalBookmarks, $totalComments, 1);
                                    @endphp
                                    <div class="bg-green-600 h-2 rounded-full"
                                         style="width: {{ ($totalComments / $maxEngagement) * 100 }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">+{{ $commentsInPeriod }} in
                                    selected period</p>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                <span
                                    class="text-sm font-medium text-gray-700 dark:text-gray-300">Scheduled Posts</span>
                                    <span
                                        class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($scheduledPosts) }}</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-yellow-600 h-2 rounded-full"
                                         style="width: {{ $totalPosts > 0 ? ($scheduledPosts / $totalPosts) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Posts -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Performing Posts</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Post
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Author
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Category
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Views
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Published
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @if($topPosts && $topPosts->count() > 0)
                                @foreach($topPosts as $post)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4">
                                            <div
                                                class="text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($post->title, 50) }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div
                                                class="text-sm text-gray-900 dark:text-white">{{ $post->user?->firstname ?? 'Unknown'  }} {{ $post->user?->lastname ?? 'Author'  }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                            {{ $post->category->name ?? 'Uncategorized' }}
                                        </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white font-semibold">
                                            {{ number_format($post->view_count) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $post->created_at->format('M d, Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No posts available
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bottom Grid: Categories, Authors, Engagement -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Popular Categories -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Popular Categories</h3>

                        @php
                            $maxCategoryViews = $categoryViews->max('total_views') ?? 0;
                        @endphp

                        @if($categoryViews->count() > 0)
                            <div class="space-y-3">
                                @foreach($categoryViews as $item)
                                    @php
                                        $categoryPercentage = $maxCategoryViews > 0 ? ($item->total_views / $maxCategoryViews) * 100 : 0;
                                    @endphp
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->category->name ?? 'Uncategorized' }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($item->total_views) }}
                                                views</p>
                                        </div>
                                        <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full"
                                                 style="width: {{ $categoryPercentage }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No category data available</p>
                            </div>
                        @endif
                    </div>

                    <!-- Top Authors -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Authors by Views</h3>
                        <div class="space-y-3">
                            @forelse($topAuthorsByViews as $author)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center flex-1">
                                        <div
                                            class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                        <span
                                            class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ mb_substr($author->firstname, 0, 1) }}{{ mb_substr($author->lastname, 0, 1) }}</span>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $author->firstname }} {{ $author->lastname }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($author->total_views) }}
                                                views</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No author data available</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Most Bookmarked Posts -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Most Bookmarked Posts</h3>
                        <div class="space-y-3">
                            @forelse($mostBookmarkedPosts as $post)
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($post->title, 40) }}</p>
                                    <div class="flex items-center justify-between mt-1">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            by {{ $post->user?->firstname ?? 'Unknown'  }} {{ $post->user?->lastname ?? 'Author'  }}</p>
                                        <span class="text-xs font-semibold text-blue-600 dark:text-blue-400">{{ $post->bookmarks_count }} bookmarks</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No bookmarks data available</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <!-- End Most Bookmarked Posts -->
                </div>
            </div>
            <!-- sidebar -->
            <div class="flex-shrink-0">
                <div class="lg:w-80">
                    <!-- Card Group -->
                    <div class="relative z-1 bg-white dark:bg-neutral-800">
                        <!-- Heading -->
                        <div class="p-4 pb-0">
                            <div
                                class="pb-2 flex flex-wrap justify-between items-center gap-2 border-b border-dashed border-gray-200 dark:border-neutral-700">
                                <h2 class="font-medium text-sm text-gray-800 dark:text-neutral-200">
                                    Top authors
                                </h2>

                                <!-- Avatar Media -->
                                <button type="button"
                                        class="group inline-flex items-center text-[13px] text-start text-gray-500 dark:text-neutral-500">
                          <span class="block me-1">
                            Next:
                          </span>
                                    <span
                                        class="block text-gray-800 underline-offset-2 decoration-2 group-hover:underline group-hover:text-indigo-700 group-focus:underline group-focus:text-indigo-700 dark:text-neutral-200 group-hover:text-indigo-400 group-focus:text-indigo-400">
                            Niki Kray
                          </span>
                                    <svg class="shrink-0 size-4 ms-0.5" xmlns="http://www.w3.org/2000/svg" width="24"
                                         height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m9 18 6-6-6-6"></path>
                                    </svg>
                                </button>
                                <!-- End Avatar Media -->
                            </div>
                        </div>
                        <!-- End Heading -->

                        <!-- Body -->
                        <div class="p-4">
                            <!-- Profile -->
                            <div class="flex items-center gap-x-3">
                        <span class="relative size-14 shrink-0 bg-gray-100 rounded-full dark:bg-neutral-700">
                          <img class="absolute inset-0 size-full object-cover rounded-full"
                               src="https://images.unsplash.com/photo-1719937206140-c4b208c78aa7?q=80&w=160&h=160&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                               alt="Post Image">
                        </span>
                                <div class="grow">
                                    <h3 class="font-medium text-lg text-gray-800 dark:text-neutral-200">
                                        Brian Williams
                                    </h3>
                                </div>
                            </div>
                            <!-- End Profile -->

                            <!-- Grid List -->
                            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-2">
                                <!-- Item -->
                                <div class="flex flex-col gap-y-1">
                          <span class="text-[13px] text-gray-500 dark:text-neutral-500">
                            Published posts:
                          </span>

                                    <div class="flex items-center gap-x-1.5">
                                        <svg class="shrink-0 size-4 text-gray-800 dark:text-neutral-200"
                                             xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M13.4 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.4"/>
                                            <path d="M2 6h4"/>
                                            <path d="M2 10h4"/>
                                            <path d="M2 14h4"/>
                                            <path d="M2 18h4"/>
                                            <path
                                                d="M21.378 5.626a1 1 0 1 0-3.004-3.004l-5.01 5.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/>
                                        </svg>

                                        <span class="font-medium text-sm text-gray-800 dark:text-neutral-200">
                              48
                            </span>
                                    </div>
                                </div>
                                <!-- End Item -->

                                <!-- Item -->
                                <div class="flex flex-col gap-y-1">
                          <span class="text-[13px] text-gray-500 dark:text-neutral-500">
                            Avg. post views:
                          </span>

                                    <div class="flex items-center gap-x-1.5">
                                        <svg class="shrink-0 size-4 text-gray-800 dark:text-neutral-200"
                                             xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>

                                        <span class="font-medium text-sm text-gray-800 dark:text-neutral-200">
                              285
                            </span>
                                    </div>
                                </div>
                                <!-- End Item -->

                                <!-- Item -->
                                <div class="flex flex-col gap-y-1">
                          <span class="text-[13px] text-gray-500 dark:text-neutral-500">
                            Total comments:
                          </span>

                                    <div class="flex items-center gap-x-1.5">
                                        <svg class="shrink-0 size-4 text-gray-800 dark:text-neutral-200"
                                             xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>
                                        </svg>

                                        <span class="font-medium text-sm text-gray-800 dark:text-neutral-200">
                              18
                            </span>
                                    </div>
                                </div>
                                <!-- End Item -->

                                <!-- Item -->
                                <div class="flex flex-col gap-y-1">
                          <span class="text-[13px] text-gray-500 dark:text-neutral-500">
                            Posts referred:
                          </span>

                                    <div class="flex items-center gap-x-1.5">
                                        <svg class="shrink-0 size-4 text-gray-800 dark:text-neutral-200"
                                             xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h6"/>
                                            <path d="m21 3-9 9"/>
                                            <path d="M15 3h6v6"/>
                                        </svg>

                                        <span class="font-medium text-sm text-gray-800 dark:text-neutral-200">
                              62
                            </span>
                                    </div>
                                </div>
                                <!-- End Item -->
                            </div>
                            <!-- End Grid List -->

                            <!-- Card -->
                            <div class="pt-5 mt-5 border-t border-gray-200 dark:border-neutral-700">
                                <!-- Header -->
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="flex flex-col">
                                        <!-- Avatar Group -->
                                        <div class="my-3 flex -space-x-3">
                                            <img
                                                class="shrink-0 size-6 relative z-2 ring-2 ring-white rounded-full dark:ring-neutral-800"
                                                src="https://images.unsplash.com/photo-1708443683276-8a3eb30faef2?q=80&w=160&h=160&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                                alt="Avatar">
                                            <img
                                                class="shrink-0 size-6 relative z-1 -mt-3 ring-2 ring-white rounded-full dark:ring-neutral-800"
                                                src="https://images.unsplash.com/photo-1659482633369-9fe69af50bfb?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=facearea&facepad=3&w=320&h=320&q=80"
                                                alt="Avatar">
                                            <img
                                                class="shrink-0 size-6 relative ring-2 ring-white rounded-full dark:ring-neutral-800"
                                                src="https://images.unsplash.com/photo-1541101767792-f9b2b1c4f127?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=facearea&facepad=3&w=320&h=320&q=80"
                                                alt="Avatar">
                                        </div>
                                        <!-- End Avatar Group -->

                                        <h3 class="text-sm text-gray-500 dark:text-neutral-500">
                                            Total views
                                        </h3>

                                        <p class="text-xl text-gray-800 dark:text-neutral-200">
                                            1,420
                                        </p>
                                    </div>
                                    <!-- End Col -->

                                    <!-- Apex Chart -->
                                    <div id="hs-pro-atatpvch" class="min-h-22.5"></div>
                                </div>
                                <!-- End Header -->

                                <div class="mt-3">
                                    <a class="inline-flex items-center gap-x-0.5 text-[13px] text-indigo-700 underline underline-offset-2 hover:decoration-2 focus:outline-hidden focus:decoration-2 disabled:opacity-50 disabled:pointer-events-none dark:text-indigo-400"
                                       href="#">
                                        View all
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                             height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m9 18 6-6-6-6"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <!-- End Card -->

                            <!-- Card -->
                            <div class="pt-5 mt-5 border-t border-gray-200 dark:border-neutral-700">
                                <!-- Progress Content -->
                                <div class="relative flex items-center gap-1">
                                    <!-- Circular Progress -->
                                    <svg class="shrink-0 size-12" width="64" height="64" viewBox="0 0 64 64"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <g transform="translate(32,32)">
                                            <g>
                                                <rect x="-3" y="-28" width="6" height="14" rx="3" fill="currentColor"
                                                      transform="rotate(0)"
                                                      class="text-indigo-700 dark:text-indigo-400"></rect>
                                                <rect x="-3" y="-28" width="6" height="14" rx="3" fill="currentColor"
                                                      transform="rotate(30)"
                                                      class="text-indigo-700 dark:text-indigo-400"></rect>
                                                <rect x="-3" y="-28" width="6" height="14" rx="3" fill="currentColor"
                                                      transform="rotate(60)"
                                                      class="text-indigo-700 dark:text-indigo-400"></rect>
                                                <rect x="-3" y="-28" width="6" height="14" rx="3" fill="currentColor"
                                                      transform="rotate(90)"
                                                      class="text-indigo-700 dark:text-indigo-400"></rect>
                                                <rect x="-3" y="-28" width="6" height="14" rx="3" fill="currentColor"
                                                      transform="rotate(120)"
                                                      class="text-indigo-700 dark:text-indigo-400"></rect>
                                                <rect x="-3" y="-28" width="6" height="14" rx="3" fill="currentColor"
                                                      transform="rotate(150)"
                                                      class="text-indigo-700 dark:text-indigo-400"></rect>
                                                <rect x="-3" y="-28" width="6" height="14" rx="3" fill="currentColor"
                                                      transform="rotate(180)"
                                                      class="text-indigo-700 dark:text-indigo-400"></rect>
                                                <rect x="-3" y="-28" width="6" height="14" rx="3" fill="currentColor"
                                                      transform="rotate(210)"
                                                      class="text-indigo-700 dark:text-indigo-400"></rect>
                                                <rect x="-3" y="-28" width="6" height="14" rx="3" fill="currentColor"
                                                      transform="rotate(240)"
                                                      class="text-indigo-700 dark:text-indigo-400"></rect>
                                                <rect x="-3" y="-28" width="6" height="14" rx="3" fill="currentColor"
                                                      transform="rotate(270)"
                                                      class="text-indigo-700 dark:text-indigo-400"></rect>
                                                <rect x="-3" y="-28" width="6" height="14" rx="3" fill="currentColor"
                                                      transform="rotate(300)"
                                                      class="text-white dark:text-neutral-800"></rect>
                                                <rect x="-3" y="-28" width="6" height="14" rx="3" fill="currentColor"
                                                      transform="rotate(330)"
                                                      class="text-white dark:text-neutral-800"></rect>
                                            </g>
                                        </g>
                                    </svg>
                                    <!-- End Circular Progress -->

                                    <div class="grow pe-20">
                                        <div class="flex flex-col">
                              <span class="block text-[13px] text-gray-500 dark:text-neutral-500">
                                Content quality score
                              </span>
                                            <div class="flex items-center gap-2">
                                <span class="block font-medium text-sm text-gray-800 dark:text-neutral-200">
                                  76%
                                </span>
                                                <span
                                                    class="flex justify-center items-center gap-x-1 text-sm text-green-600 dark:text-green-500">
                                  <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                       viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                       stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m5 12 7-7 7 7"></path>
                                    <path d="M12 19V5"></path>
                                  </svg>
                                  3.4%
                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Button -->
                                    <div class="absolute top-1/2 end-0 -translate-y-1/2">
                                        <button type="button"
                                                class="group size-7 lg:size-auto lg:py-1.5 lg:px-2 flex items-center justify-center border border-gray-200 text-gray-600 text-xs rounded-full py-1 hover:bg-indigo-50 hover:border-indigo-100 hover:text-indigo-700 focus:outline-none focus:bg-indigo-50 focus:border-indigo-100 focus:text-indigo-700 dark:text-neutral-200 dark:border-neutral-700 dark:hover:bg-indigo-500/20 dark:hover:border-indigo-500/20 dark:hover:text-indigo-400 dark:focus:bg-indigo-500/20 dark:focus:border-indigo-500/20 dark:focus:text-indigo-400">
                                                      <span
                                                          class="lg:block hidden max-w-0 overflow-hidden whitespace-nowrap opacity-0 transition-all duration-300 group-hover:me-1 group-hover:max-w-25 group-hover:opacity-100 group-focus:me-1 group-focus:max-w-25 group-focus:opacity-100">
                                                        View all
                                                      </span>
                                            <svg class="shrink-0 size-3" xmlns="http://www.w3.org/2000/svg" width="24"
                                                 height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M15 3h6v6"/>
                                                <path d="m21 3-7 7"/>
                                                <path d="m3 21 7-7"/>
                                                <path d="M9 21H3v-6"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <!-- End Button -->
                                </div>
                                <!-- End Progress Content -->

                                <!-- List Group -->
                                <ul class="mt-5 flex flex-col gap-y-3">
                                    <!-- List Item -->
                                    <li class="flex justify-between items-center gap-x-2">
                                        <div class="flex flex-col gap-y-1">
                                                      <span
                                                          class="block text-[13px] text-gray-500 dark:text-neutral-500">
                                                        Title/subject length
                                                      </span>
                                            <span class="block font-medium text-sm text-gray-800 dark:text-neutral-200">
                                                        50-60
                                                      </span>
                                        </div>
                                        <!-- End Col -->

                                        <div class="flex flex-col justify-end items-end gap-1">
                                            <div class="flex justify-end items-center gap-1">
                                                <span
                                                    class="shrink-0 w-1 h-3.5 inline-block bg-indigo-700 rounded-full dark:bg-indigo-400"></span>
                                                <span
                                                    class="shrink-0 w-1 h-3.5 inline-block bg-indigo-700 rounded-full dark:bg-indigo-400"></span>
                                                <span
                                                    class="shrink-0 w-1 h-3.5 inline-block bg-indigo-700 rounded-full dark:bg-indigo-400"></span>
                                            </div>

                                            <p class="text-[13px] text-indigo-700 dark:text-indigo-400">
                                                Good
                                            </p>
                                        </div>
                                        <!-- End Col -->
                                    </li>
                                    <!-- End List Item -->

                                    <!-- List Item -->
                                    <li class="flex justify-between items-center gap-x-2">
                                        <div class="flex flex-col gap-y-1">
                                                          <span
                                                              class="block text-[13px] text-gray-500 dark:text-neutral-500">
                                                            Body word count
                                                          </span>
                                            <span class="block font-medium text-sm text-gray-800 dark:text-neutral-200">
                                                        300–800
                                                      </span>
                                        </div>
                                        <!-- End Col -->

                                        <div class="flex flex-col justify-end items-end gap-1">
                                            <div class="flex justify-end items-center gap-1">
                                                <span
                                                    class="shrink-0 w-1 h-3.5 inline-block bg-indigo-700 rounded-full dark:bg-indigo-400"></span>
                                                <span
                                                    class="shrink-0 w-1 h-3.5 inline-block bg-indigo-700 rounded-full dark:bg-indigo-400"></span>
                                                <span
                                                    class="shrink-0 w-1 h-3.5 inline-block bg-indigo-700 rounded-full dark:bg-indigo-400"></span>
                                            </div>

                                            <p class="text-[13px] text-indigo-700 dark:text-indigo-400">
                                                Good
                                            </p>
                                        </div>
                                        <!-- End Col -->
                                    </li>
                                    <!-- End List Item -->

                                    <!-- List Item -->
                                    <li class="flex justify-between items-center gap-x-2">
                                        <div class="flex flex-col gap-y-1">
                                                      <span
                                                          class="block text-[13px] text-gray-500 dark:text-neutral-500">
                                                        Tags/keywords
                                                      </span>
                                            <span class="block font-medium text-sm text-gray-800 dark:text-neutral-200">
                                                        3-8
                                                      </span>
                                        </div>
                                        <!-- End Col -->

                                        <div class="flex flex-col justify-end items-end gap-1">
                                            <div class="flex justify-end items-center gap-1">
                                                <span
                                                    class="shrink-0 w-1 h-3.5 inline-block bg-indigo-700 rounded-full dark:bg-indigo-400"></span>
                                                <span
                                                    class="shrink-0 w-1 h-3.5 inline-block bg-indigo-700 rounded-full dark:bg-indigo-400"></span>
                                                <span
                                                    class="shrink-0 w-1 h-3.5 inline-block bg-indigo-700 rounded-full dark:bg-indigo-400"></span>
                                            </div>

                                            <p class="text-[13px] text-indigo-700 dark:text-indigo-400">
                                                Good
                                            </p>
                                        </div>
                                        <!-- End Col -->
                                    </li>
                                    <!-- End List Item -->

                                    <!-- List Item -->
                                    <li class="flex justify-between items-center gap-x-2">
                                        <div class="flex flex-col gap-y-1">
                                                          <span
                                                              class="block text-[13px] text-gray-500 dark:text-neutral-500">
                                                            Broken links
                                                          </span>
                                            <span class="block font-medium text-sm text-gray-800 dark:text-neutral-200">
                                                            2
                                                        </span>
                                        </div>
                                        <!-- End Col -->

                                        <div class="flex flex-col justify-end items-end gap-1">
                                            <div class="flex justify-end items-center gap-1">
                                                <span
                                                    class="shrink-0 w-1 h-3.5 inline-block bg-orange-500 rounded-full dark:bg-orange-400"></span>
                                                <span
                                                    class="shrink-0 w-1 h-3.5 inline-block bg-orange-500 rounded-full dark:bg-orange-400"></span>
                                                <span
                                                    class="shrink-0 w-1 h-3.5 inline-block bg-gray-300 rounded-full dark:bg-neutral-600"></span>
                                            </div>

                                            <p class="text-sm text-orange-500 dark:text-orange-400">
                                                Poor
                                            </p>
                                        </div>
                                        <!-- End Col -->
                                    </li>
                                    <!-- End List Item -->

                                    <!-- List Item -->
                                    <li class="flex justify-between items-center gap-x-2">
                                        <div class="flex flex-col gap-y-1">
                                                      <span
                                                          class="block text-[13px] text-gray-500 dark:text-neutral-500">
                                                        Spelling &amp; grammar
                                                      </span>
                                            <span class="block font-medium text-sm text-gray-800 dark:text-neutral-200">
                                                        100%
                                                      </span>
                                        </div>
                                        <!-- End Col -->

                                        <div class="flex flex-col justify-end items-end gap-1">
                                            <div class="flex justify-end items-center gap-1">
                                                <span
                                                    class="shrink-0 w-1 h-3.5 inline-block bg-indigo-700 rounded-full dark:bg-indigo-400"></span>
                                                <span
                                                    class="shrink-0 w-1 h-3.5 inline-block bg-indigo-700 rounded-full dark:bg-indigo-400"></span>
                                                <span
                                                    class="shrink-0 w-1 h-3.5 inline-block bg-indigo-700 rounded-full dark:bg-indigo-400"></span>
                                            </div>

                                            <p class="text-[13px] text-indigo-700 dark:text-indigo-400">
                                                Good
                                            </p>
                                        </div>
                                        <!-- End Col -->
                                    </li>
                                    <!-- End List Item -->
                                </ul>
                                <!-- End List Group -->
                            </div>
                            <!-- End Card -->
                        </div>
                        <!-- End Body -->
                    </div>
                    <!-- End Card Group -->

                    <div class="hidden lg:block border-t border-gray-200 dark:border-neutral-700">
                        <div class="p-4">
                            <!-- Card -->
                            <div class="p-4 flex flex-col bg-gray-100 rounded-lg dark:bg-neutral-700/30">
                                <h3 class="font-semibold text-sm text-gray-800 dark:text-neutral-200">
                                    Connect to your mailboxes
                                </h3>

                                <div class="mt-3">
                                    <p class="text-sm text-gray-500 dark:text-neutral-500">
                                        Connect to your favorite mailbox and recive updates to your inbox.
                                    </p>
                                </div>

                                <div class="mt-3 flex flex-wrap justify-between items-center gap-2">
                                    <a class="inline-flex items-center gap-x-0.5 text-[13px] text-indigo-700 underline underline-offset-2 hover:decoration-2 focus:outline-hidden focus:decoration-2 disabled:opacity-50 disabled:pointer-events-none dark:text-indigo-400"
                                       href="#">
                                        Discover more
                                    </a>

                                    <!-- Avatar Group -->
                                    <div class="flex gap-x-2">
                                        <div
                                            class="size-7 flex justify-center items-center bg-white shadow-xs rounded-md dark:bg-neutral-900">
                                            <svg class="shrink-0 size-4" width="32" height="32" viewBox="0 0 32 32"
                                                 fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M7.34318 0.00012207H24.6569C28.725 0.00012207 32 3.27516 32 7.34327V24.657C32 28.7251 28.725 32.0001 24.6569 32.0001H7.34318C3.27507 32.0001 2.52724e-05 28.7251 2.52724e-05 24.657V7.34327C2.52724e-05 3.27516 3.27507 0.00012207 7.34318 0.00012207Z"
                                                    fill="url(#paint0_linear_5465_1620)"></path>
                                                <path
                                                    d="M7.01113 9.1001C6.84252 9.1001 6.68368 9.12919 6.53335 9.18899L9.54446 12.289L12.5889 15.4446L12.6445 15.5112L12.8222 15.689L13 15.8779L15.6111 18.5557C15.6546 18.5827 15.7806 18.6994 15.879 18.7486C16.0058 18.812 16.1432 18.8704 16.2849 18.8754C16.4377 18.8809 16.594 18.8371 16.7315 18.7702C16.8345 18.7201 16.8803 18.6483 17 18.5557L20.0222 15.4334L26.0222 9.25566C25.8332 9.15324 25.6239 9.1001 25.4 9.1001H7.01113ZM6.08891 9.47788C5.7678 9.78214 5.56668 10.2395 5.56668 10.7557V20.9334C5.56668 21.3513 5.7009 21.731 5.92224 22.0223L6.34446 21.6223L9.48891 18.5668L12.2778 15.8668L12.2222 15.8001L9.16668 12.6557L6.11113 9.5001L6.08891 9.47788ZM26.4222 9.57788L20.4 15.8001L20.3445 15.8557L23.2445 18.6668L26.3889 21.7223L26.5778 21.9001C26.7471 21.6285 26.8445 21.2938 26.8445 20.9334V10.7557C26.8445 10.2955 26.685 9.87817 26.4222 9.57788ZM12.6333 16.2334L9.85557 18.9334L6.70002 21.989L6.30002 22.3779C6.5109 22.5137 6.75088 22.6001 7.01113 22.6001H25.4C25.7129 22.6001 25.9967 22.4797 26.2334 22.289L26.0334 22.089L22.8778 19.0334L19.9778 16.2334L17.3667 18.9223C17.2254 19.016 17.1309 19.1199 16.9929 19.1837C16.7709 19.2864 16.5275 19.3733 16.2828 19.3696C16.0375 19.3658 15.797 19.2698 15.5768 19.1615C15.4663 19.1072 15.4074 19.0531 15.2778 18.9446L12.6333 16.2334Z"
                                                    fill="white"></path>
                                                <defs>
                                                    <linearGradient id="paint0_linear_5465_1620" x1="16.2241"
                                                                    y1="31.8717" x2="16.2552" y2="0.386437"
                                                                    gradientUnits="userSpaceOnUse">
                                                        <stop stop-color="#70EFFF"></stop>
                                                        <stop offset="1" stop-color="#5770FF"></stop>
                                                    </linearGradient>
                                                </defs>
                                            </svg>
                                        </div>
                                        <div
                                            class="size-7 flex justify-center items-center bg-white shadow-xs rounded-md dark:bg-neutral-900">
                                            <svg class="shrink-0 size-4" width="33" height="32" viewBox="0 0 33 32"
                                                 fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_41)">
                                                    <path
                                                        d="M32.2566 16.36C32.2566 15.04 32.1567 14.08 31.9171 13.08H16.9166V19.02H25.7251C25.5454 20.5 24.5866 22.72 22.4494 24.22L22.4294 24.42L27.1633 28.1L27.4828 28.14C30.5189 25.34 32.2566 21.22 32.2566 16.36Z"
                                                        fill="#4285F4"></path>
                                                    <path
                                                        d="M16.9166 32C21.231 32 24.8463 30.58 27.5028 28.12L22.4694 24.2C21.1111 25.14 19.3135 25.8 16.9366 25.8C12.7021 25.8 9.12677 23 7.84844 19.16L7.66867 19.18L2.71513 23L2.65521 23.18C5.2718 28.4 10.6648 32 16.9166 32Z"
                                                        fill="#34A853"></path>
                                                    <path
                                                        d="M7.82845 19.16C7.48889 18.16 7.28915 17.1 7.28915 16C7.28915 14.9 7.48889 13.84 7.80848 12.84V12.62L2.81499 8.73999L2.6552 8.81999C1.55663 10.98 0.937439 13.42 0.937439 16C0.937439 18.58 1.55663 21.02 2.63522 23.18L7.82845 19.16Z"
                                                        fill="#FBBC05"></path>
                                                    <path
                                                        d="M16.9166 6.18C19.9127 6.18 21.9501 7.48 23.0886 8.56L27.6027 4.16C24.8263 1.58 21.231 0 16.9166 0C10.6648 0 5.27181 3.6 2.63525 8.82L7.80851 12.84C9.10681 8.98 12.6821 6.18 16.9166 6.18Z"
                                                        fill="#EB4335"></path>
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_41">
                                                        <rect width="32" height="32" fill="white"
                                                              transform="translate(0.937439)"></rect>
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </div>
                                        <div
                                            class="size-7 flex justify-center items-center bg-white shadow-xs rounded-md dark:bg-neutral-900">
                                            <svg class="shrink-0 size-4" width="17" height="16" viewBox="0 0 17 16"
                                                 fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M0.04 8C0.04 3.5816 3.6208 0 8.04 0C12.4576 0 16.04 3.5816 16.04 8C16.04 12.4184 12.4576 16 8.04 16C3.6208 16 0.04 12.4184 0.04 8Z"
                                                    fill="#FC3F1D"></path>
                                                <path
                                                    d="M9.064 4.5328H8.3248C6.9696 4.5328 6.2568 5.2192 6.2568 6.2312C6.2568 7.3752 6.7496 7.9112 7.7616 8.5984L8.5976 9.1616L6.1952 12.7512H4.4L6.556 9.54C5.316 8.6512 4.62 7.788 4.62 6.328C4.62 4.4976 5.896 3.248 8.316 3.248H10.7184V12.7424H9.064V4.5328Z"
                                                    fill="white"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <!-- End Avatar Group -->
                                </div>
                            </div>
                            <!-- End Card -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- End sidebar -->
        </div>
    </div>
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts/dist/apexcharts.css">
        <style>
            .apexcharts-tooltip.apexcharts-theme-light {
                background-color: transparent !important;
                border: none !important;
                box-shadow: none !important;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        </style>
    @endpush
    @push('scripts')
        <script>
            (function() {
                'use strict';

                // Configuration
                const config = {
                    autoRefreshInterval: 30000, // 30 seconds
                    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    routes: {
                        refresh: '{{ route("admin.refresh") }}',
                        getData: '{{ route("admin.analytics.data") }}'
                    }
                };

                // State
                let autoRefreshEnabled = {{ $autoRefresh ? 'true' : 'false' }};
                let refreshInterval = null;

                // DOM Elements
                const elements = {
                    dateRange: document.getElementById('dateRange'),
                    refreshBtn: document.getElementById('refresh-btn'),
                    liveIndicator: document.getElementById('live-indicator'),
                    lastUpdated: document.getElementById('last-updated'),
                    dashboardContainer: document.getElementById('dashboard-container')
                };

                /**
                 * Initialize dashboard
                 */
                function init() {
                    setupEventListeners();
                    if (autoRefreshEnabled) {
                        startAutoRefresh();
                        showLiveIndicator();
                    }
                }

                /**
                 * Setup event listeners
                 */
                function setupEventListeners() {
                    // Date range change
                    if (elements.dateRange) {
                        elements.dateRange.addEventListener('change', handleDateRangeChange);
                    }

                    // Manual refresh button
                    if (elements.refreshBtn) {
                        elements.refreshBtn.addEventListener('click', handleManualRefresh);
                    }

                    // Auto-refresh toggle (if you add a toggle button)
                    const autoRefreshToggle = document.getElementById('auto-refresh-toggle');
                    if (autoRefreshToggle) {
                        autoRefreshToggle.addEventListener('change', handleAutoRefreshToggle);
                    }
                }

                /**
                 * Handle date range change
                 */
                function handleDateRangeChange(e) {
                    const dateRange = e.target.value;
                    const url = new URL(window.location.href);
                    url.searchParams.set('dateRange', dateRange);
                    window.location.href = url.toString();
                }

                /**
                 * Handle manual refresh
                 */
                function handleManualRefresh(e) {
                    e.preventDefault();
                    refreshData();
                }

                /**
                 * Handle auto-refresh toggle
                 */
                function handleAutoRefreshToggle(e) {
                    autoRefreshEnabled = e.target.checked;

                    if (autoRefreshEnabled) {
                        startAutoRefresh();
                        showLiveIndicator();
                    } else {
                        stopAutoRefresh();
                        hideLiveIndicator();
                    }
                }

                /**
                 * Refresh analytics data
                 */
                function refreshData() {
                    // Show loading state
                    if (elements.refreshBtn) {
                        elements.refreshBtn.disabled = true;
                        const svg = elements.refreshBtn.querySelector('svg');
                        if (svg) {
                            svg.style.animation = 'spin 1s linear infinite';
                        }
                    }

                    // Make AJAX request
                    fetch(config.routes.refresh, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update last updated time
                                if (elements.lastUpdated && data.lastUpdated) {
                                    elements.lastUpdated.textContent = data.lastUpdated;
                                }

                                // Reload page to show fresh data
                                window.location.reload();
                            } else {
                                showNotification('Failed to refresh data', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Refresh error:', error);
                            showNotification('An error occurred while refreshing', 'error');
                        })
                        .finally(() => {
                            // Remove loading state
                            if (elements.refreshBtn) {
                                elements.refreshBtn.disabled = false;
                                const svg = elements.refreshBtn.querySelector('svg');
                                if (svg) {
                                    svg.style.animation = '';
                                }
                            }
                        });
                }

                /**
                 * Auto-refresh data (lightweight update without page reload)
                 */
                function autoRefreshData() {
                    if (!autoRefreshEnabled) return;

                    const dateRange = elements.dateRange ? elements.dateRange.value : '30';

                    fetch(`${config.routes.getData}?dateRange=${dateRange}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.data) {
                                // Update stats without full page reload
                                updateStats(data.data);

                                // Update last updated time
                                if (elements.lastUpdated && data.data.lastUpdated) {
                                    elements.lastUpdated.textContent = data.data.lastUpdated;
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Auto-refresh error:', error);
                        });
                }

                /**
                 * Update stats on the page
                 */
                function updateStats(data) {
                    // Update stat cards with animation
                    const stats = [
                        { selector: '[data-stat="totalPosts"]', value: data.totalPosts },
                        { selector: '[data-stat="totalViews"]', value: data.totalViews },
                        { selector: '[data-stat="totalUsers"]', value: data.totalUsers },
                        { selector: '[data-stat="activeSubscribers"]', value: data.activeSubscribers }
                    ];

                    stats.forEach(stat => {
                        const element = document.querySelector(stat.selector);
                        if (element && stat.value !== undefined) {
                            animateNumber(element, parseInt(element.textContent.replace(/,/g, '')), stat.value);
                        }
                    });
                }

                /**
                 * Animate number change
                 */
                function animateNumber(element, start, end) {
                    const duration = 1000; // 1 second
                    const startTime = performance.now();

                    function update(currentTime) {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);

                        const current = Math.floor(start + (end - start) * progress);
                        element.textContent = current.toLocaleString();

                        if (progress < 1) {
                            requestAnimationFrame(update);
                        }
                    }

                    requestAnimationFrame(update);
                }

                /**
                 * Start auto-refresh
                 */
                function startAutoRefresh() {
                    if (refreshInterval) {
                        clearInterval(refreshInterval);
                    }

                    refreshInterval = setInterval(autoRefreshData, config.autoRefreshInterval);
                }

                /**
                 * Stop auto-refresh
                 */
                function stopAutoRefresh() {
                    if (refreshInterval) {
                        clearInterval(refreshInterval);
                        refreshInterval = null;
                    }
                }

                /**
                 * Show live indicator
                 */
                function showLiveIndicator() {
                    if (elements.liveIndicator) {
                        elements.liveIndicator.style.display = 'inline-flex';
                    }
                }

                /**
                 * Hide live indicator
                 */
                function hideLiveIndicator() {
                    if (elements.liveIndicator) {
                        elements.liveIndicator.style.display = 'none';
                    }
                }

                /**
                 * Show notification
                 */
                function showNotification(message, type = 'info') {
                    // Simple notification - you can replace with your preferred notification library
                    const notification = document.createElement('div');
                    notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg ${
                        type === 'error' ? 'bg-red-500 text-white' :
                            type === 'success' ? 'bg-green-500 text-white' :
                                'bg-blue-500 text-white'
                    }`;
                    notification.textContent = message;

                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.style.opacity = '0';
                        notification.style.transition = 'opacity 0.3s';
                        setTimeout(() => notification.remove(), 300);
                    }, 3000);
                }

                /**
                 * Add CSS animation for spin
                 */
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes spin {
                        from { transform: rotate(0deg); }
                        to { transform: rotate(360deg); }
                    }
                `;
                document.head.appendChild(style);

                // Initialize when DOM is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    init();
                }

                // Cleanup on page unload
                window.addEventListener('beforeunload', () => {
                    stopAutoRefresh();
                });

            })();
        </script>
    @endpush
</x-app-layout>
