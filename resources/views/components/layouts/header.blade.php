<div>
    @php
        $categories = \App\Models\Category::withCount(['posts' => function($q) {
            $q->isLive()->notExpired();
        }])
        ->withSum(['posts' => function($q) {
            $q->isLive()
              ->notExpired()
              ->where('created_at', '>=', now()->subDays(30));
        }], 'view_count')
        ->get()
        ->filter(fn($category) => $category->posts_count > 0)
        ->sortByDesc('posts_sum_view_count')
        ->sortByDesc('posts_count')
        ->take(6);
        $currentRoute = request()->route()->getName();
    @endphp

    <div role="list" class="topbar">
        @foreach($categories as $category)
            <div role="listitem">
                <a href="{{ route('category.show', $category->slug) }}"
                   class="top-menu">
                    {{ $category->name }}
                </a>
            </div>
        @endforeach
    </div>

    <div class="navbar">
        <!-- ========== HEADER ========== -->
        <header class="flex flex-wrap md:justify-start md:flex-nowrap w-full">
            <nav class="relative w-full md:flex md:items-center md:justify-between md:gap-3 mx-auto py-2">
                <!-- Logo w/ Collapse Button -->
                <div class="flex items-center justify-between">
                    <a class="flex-none font-normal text-xl text-body focus:outline-hidden focus:opacity-80 uppercase"
                       href="{{ route('home.index') }}" aria-label="Brand">semech</a>

                    <!-- Collapse Button -->
                    <div class="md:hidden flex gap-4">
                        <button type="button"
                                aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-scale-animation-modal" data-hs-overlay="#hs-scale-animation-modal"
                                class="flex cursor-pointer items-center justify-center rounded-full text-body d05xb focus:outline-hidden">
                            <svg class="block text-body size-5" xmlns="http://www.w3.org/2000/svg" width="24"
                                 height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                            </svg>
                        </button>

                        <button type="button"
                                class="hs-collapse-toggle relative size-9 flex justify-center items-center text-sm font-semibold rounded-lg border border-gray-200 text-body hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none"
                                id="hs-header-classic-collapse" aria-expanded="false" aria-controls="hs-header-classic"
                                aria-label="Toggle navigation" data-hs-collapse="#hs-header-classic">
                            <svg class="hs-collapse-open:hidden size-4" xmlns="http://www.w3.org/2000/svg"
                                 width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="3" x2="21" y1="6" y2="6" />
                                <line x1="3" x2="21" y1="12" y2="12" />
                                <line x1="3" x2="21" y1="18" y2="18" />
                            </svg>
                            <svg class="hs-collapse-open:block shrink-0 hidden size-4"
                                 xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                            <span class="sr-only">Toggle navigation</span>
                        </button>

                    </div>
                    <!-- End Collapse Button -->
                </div>
                <!-- End Logo w/ Collapse Button -->

                <!-- Collapse -->
                <div id="hs-header-classic"
                     class="hs-collapse hidden overflow-hidden transition-all duration-300 basis-full grow md:block"
                     aria-labelledby="hs-header-classic-collapse">
                    <div class="overflow-hidden overflow-y-auto max-h-[75vh] [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">
                        <div class="py-2 md:py-0 flex flex-col md:flex-row md:items-center md:justify-end gap-0.5 md:gap-1">
                            <a class="p-2 flex items-center text-sm {{ $currentRoute === 'home.index' ? 'text-secondary focus:text-secondary': '' }}  focus:outline-hidden"
                               href="{{ route('home.index') }}" aria-current="page">
                                <svg class="shrink-0 size-4 me-3 md:me-2 block md:hidden"
                                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
                                    <path
                                        d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                </svg>
                                Home
                            </a>

                            <a class="p-2 flex items-center text-sm {{ $currentRoute === 'about' ? 'text-secondary focus:text-secondary': '' }} text-body hover:text-gray-500 focus:outline-hidden focus:text-gray-500"
                               href="{{route('about')}}">
                                <svg class="shrink-0 size-4 me-3 md:me-2 block md:hidden"
                                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                                About us
                            </a>

                            <a class="p-2 flex items-center {{ $currentRoute === 'articles' ? 'text-secondary focus:text-secondary': '' }} text-sm text-body hover:text-gray-500 focus:outline-hidden focus:text-gray-500"
                               href="{{route('articles')}}">
                                <svg class="shrink-0 size-4 me-3 md:me-2 block md:hidden"
                                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <path d="M12 12h.01" />
                                    <path d="M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                                    <path d="M22 13a18.15 18.15 0 0 1-20 0" />
                                    <rect width="20" height="14" x="2" y="6" rx="2" />
                                </svg>
                                Blog
                            </a>

                            <a class="p-2 flex items-center uppercase text-sm {{ $currentRoute === 'south.west' ? 'text-secondary focus:text-secondary': '' }} text-body hover:text-gray-500 focus:outline-hidden focus:text-gray-500"
                               href="{{route('south.west')}}">
                                <svg class="shrink-0 size-4 me-3 md:me-2 block md:hidden"
                                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <path d="M12 12h.01" />
                                    <path d="M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                                    <path d="M22 13a18.15 18.15 0 0 1-20 0" />
                                    <rect width="20" height="14" x="2" y="6" rx="2" />
                                </svg>
                                Swgdi
                            </a>

                            <a class="p-2 flex items-center {{ $currentRoute === 'contact' ? 'text-secondary focus:text-secondary': '' }} text-sm text-body hover:text-gray-500 focus:outline-hidden focus:text-gray-500"
                               href="{{ route('contact') }}">
                                <svg class="shrink-0 size-4 me-3 md:me-2 block md:hidden"
                                     xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <path
                                        d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2" />
                                    <path d="M18 14h-8" />
                                    <path d="M15 18h-5" />
                                    <path d="M10 6h8v4h-8V6Z" />
                                </svg>
                                Contact
                            </a>

                            <!-- Button Group -->
                            <div class="relative flex items-center gap-x-1.5 md:ps-2.5 mt-1 md:mt-0 md:ms-1.5 before:block before:absolute before:top-1/2 before:-start-px before:w-px before:h-4 before:bg-gray-300 before:-translate-y-1/2 dark:before:bg-neutral-700">
                                <button
                                    aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-scale-animation-modal" data-hs-overlay="#hs-scale-animation-modal"
                                    type="button"
                                        aria-label="Open search"
                                        class="hidden md:flex cursor-pointer items-center justify-center rounded-full text-body d05xb focus:outline-hidden r17tr">
                                    <svg class="block text-body size-4" xmlns="http://www.w3.org/2000/svg"
                                         width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.3-4.3"></path>
                                    </svg>
                                </button>

                                @push('modal')
                                    <!-- Search Modal -->
                                    <div id="hs-scale-animation-modal"
                                         class="hs-overlay hidden size-full fixed top-0 start-0 z-[80] overflow-x-hidden overflow-y-auto pointer-events-none"
                                         role="dialog"
                                         tabindex="-1"
                                         aria-labelledby="hs-scale-animation-modal-label">
                                        <div class="hs-overlay-animation-target hs-overlay-open:scale-100 hs-overlay-open:opacity-100 scale-95 opacity-0 ease-in-out transition-all duration-200 sm:max-w-2xl sm:w-full m-3 sm:mx-auto min-h-[calc(100%-56px)] flex items-center">
                                            <div class="w-full flex flex-col bg-white border border-gray-200 shadow-2xs rounded-xl relative max-h-[100%] pb-2 pointer-events-auto">

                                                <!-- Header -->
                                                <div class="p-4 border-b border-gray-200">
                                                    <!-- Input -->
                                                    <div class="relative">
                                                        <div class="ps-3.5 flex items-center z-20 start-0 inset-y-0 absolute pointer-events-none">
                                                            <svg class="text-gray-400 shrink-0 size-4 block" xmlns="http://www.w3.org/2000/svg"
                                                                 width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <circle cx="11" cy="11" r="8"></circle>
                                                                <path d="m21 21-4.3-4.3"></path>
                                                            </svg>
                                                        </div>

                                                        <div class="flex items-center gap-2">
                                                            <div class="grow">
                                                                <div class="w-full relative">
                                                                    <input type="text"
                                                                           class="py-2 sm:py-3 pe-20 ps-10 px-4 block w-full text-body border-gray-200 sm:text-sm focus:border-gray-300 focus:ring-transparent placeholder:opacity-[1] disabled:opacity-50 disabled:pointer-events-none border rounded-lg mb-0"
                                                                           placeholder="Search or type a command"
                                                                           autocomplete="off">

                                                                    <div class="absolute pe-2 z-20 flex items-center end-0 inset-y-0">
                                                                        <div class="flex items-center gap-1">
                                                                            <button type="button"
                                                                                    class="text-white font-medium text-sm bg-secondary/90 rounded-full flex justify-center shrink-0 size-7 cursor-pointer items-center disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden ukj8s">
                                                                                <svg class="shrink-0 size-3.5 block" xmlns="http://www.w3.org/2000/svg"
                                                                                     width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                                                     stroke-linejoin="round">
                                                                                    <path d="M5 12h14"></path>
                                                                                    <path d="m12 5 7 7-7 7"></path>
                                                                                </svg>
                                                                                <span class="sr-only">Search</span>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- End Input -->

                                                    <!-- Button Group -->
                                                    <div class="mt-3 flex flex-wrap justify-between gap-2 items-center">
                                                        <div class="flex flex-wrap gap-2"></div>
                                                    </div>
                                                    <!-- End Button Group -->
                                                </div>
                                                <!-- End Header -->

                                                <!-- Body / Results Container -->
                                                <div class="h-[calc(20rem*1.25)] px-4 py-1.5 overflow-y-auto overflow-hidden"></div>
                                                <!-- End Body -->

                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Search Modal -->
                                @endpush

                                @auth
                                    <!-- User Avatar Dropdown -->
                                    <a href="{{route('admin.index')}}"
                                       type="button"
                                       class="p-0.5 pr-2.5 inline-flex shrink-0 items-center gap-x-3 text-start rounded-full hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 dark:hover:bg-neutral-800 dark:hover:text-neutral-200 dark:focus:bg-neutral-800 dark:focus:text-neutral-200 dark:text-neutral-500"
                                       aria-label="User Menu">
                                        @if(auth()->user()->image_path)
                                            <img class="shrink-0 size-7 rounded-full object-cover"
                                                 src="{{ asset(auth()->user()->image_path) }}"
                                                 alt="{{ auth()->user()->firstname }}">
                                        @else
                                            <div class="shrink-0 size-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-normal">
                                                {{ strtoupper(substr(auth()->user()->firstname, 0, 1)) }}{{ strtoupper(substr(auth()->user()->lastname, 0, 1)) }}
                                            </div>
                                        @endif
                                    </a>
                                @else
                                    <!-- Login Link -->
                                    <a href="{{ route('login') }}"
                                       class="nav-link pr-2.5 {{ $currentRoute === 'login' ? 'nav-link-active' : '' }}">
                                        Login
                                    </a>
                                @endauth
                                <a class="p-2 flex items-center text-sm bg-secondary py-2.5 px-4 text-white rounded-sm hover:text-gray-20 focus:outline-hidden"
                                   href="https://www.oncrowdr.com/explore/c/6970cac96769c0fceb54485e" target="_blank">
                                    <svg class="shrink-0 size-4 me-3 md:me-2 block md:hidden"
                                         xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                         stroke-linejoin="round">
                                        <path
                                            d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2" />
                                        <path d="M18 14h-8" />
                                        <path d="M15 18h-5" />
                                        <path d="M10 6h8v4h-8V6Z" />
                                    </svg>
                                    Donate
                                </a>
                            </div>
                            <!-- End Button Group -->
                        </div>
                    </div>
                </div>
                <!-- End Collapse -->
            </nav>
        </header>
        <!-- ========== END HEADER ========== -->
    </div>
</div>

