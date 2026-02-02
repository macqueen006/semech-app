<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('meta')

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://api.fontshare.com/v2/css?f[]=switzer@200,300,400,500,600,800,900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{asset('favicon-96x96.png')}}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}" />
    <link rel="manifest" href="{{ asset('site.webmanifest') }}" />

    <link rel="preconnect" href="https://fonts.bunny.net">
    @stack('styles')
    <script>
        const html = document.querySelector('html');
        const isLightOrAuto = localStorage.getItem('hs_theme') === 'light' || (localStorage.getItem('hs_theme') === 'auto' && !window.matchMedia('(prefers-color-scheme: dark)').matches);
        const isDarkOrAuto = localStorage.getItem('hs_theme') === 'dark' || (localStorage.getItem('hs_theme') === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);

        if (isLightOrAuto && html.classList.contains('dark')) html.classList.remove('dark');
        else if (isDarkOrAuto && html.classList.contains('light')) html.classList.remove('light');
        else if (isDarkOrAuto && !html.classList.contains('dark')) html.classList.add('dark');
        else if (isLightOrAuto && !html.classList.contains('light')) html.classList.add('light');
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
<!-- ========== HEADER ========== -->
<header
    class="fixed top-0 inset-x-0 flex flex-wrap md:justify-start md:flex-nowrap z-48 lg:z-61 w-full bg-zinc-100 text-sm py-2.5 dark:bg-neutral-900">
    <nav class="px-4 sm:px-5.5 flex basis-full items-center w-full mx-auto">
        <div class="w-full flex items-center gap-x-1.5">
            <ul class="flex items-center gap-1.5">
                <li class="inline-flex items-center relative text-gray-200 pe-1.5 last:pe-0 last:after:hidden after:absolute after:top-1/2 after:end-0 after:inline-block after:w-px after:h-3.5 after:bg-gray-300 after:rounded-full after:-translate-y-1/2 after:rotate-12 dark:text-neutral-200 dark:after:bg-neutral-700">
                    <a class="shrink-0 inline-flex justify-center items-center bg-indigo-700 size-8 rounded-md text-xl inline-block font-semibold focus:outline-hidden focus:opacity-80"
                       href="index.html" aria-label="Preline">
                        <svg class="shrink-0 size-5" width="36" height="36" viewBox="0 0 36 36" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M18.0835 3.23358C9.88316 3.23358 3.23548 9.8771 3.23548 18.0723V35.5832H0.583496V18.0723C0.583496 8.41337 8.41851 0.583252 18.0835 0.583252C27.7485 0.583252 35.5835 8.41337 35.5835 18.0723C35.5835 27.7312 27.7485 35.5614 18.0835 35.5614H16.7357V32.911H18.0835C26.2838 32.911 32.9315 26.2675 32.9315 18.0723C32.9315 9.8771 26.2838 3.23358 18.0835 3.23358Z"
                                  class="fill-white" fill="currentColor"/>
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M18.0833 8.62162C12.8852 8.62162 8.62666 12.9245 8.62666 18.2879V35.5833H5.97468V18.2879C5.97468 11.5105 11.3713 5.97129 18.0833 5.97129C24.7954 5.97129 30.192 11.5105 30.192 18.2879C30.192 25.0653 24.7954 30.6045 18.0833 30.6045H16.7355V27.9542H18.0833C23.2815 27.9542 27.54 23.6513 27.54 18.2879C27.54 12.9245 23.2815 8.62162 18.0833 8.62162Z"
                                  class="fill-white" fill="currentColor"/>
                            <path
                                d="M24.8225 18.1012C24.8225 21.8208 21.8053 24.8361 18.0833 24.8361C14.3614 24.8361 11.3442 21.8208 11.3442 18.1012C11.3442 14.3815 14.3614 11.3662 18.0833 11.3662C21.8053 11.3662 24.8225 14.3815 24.8225 18.1012Z"
                                class="fill-white" fill="currentColor"/>
                        </svg>
                    </a>

                    <div class="hidden sm:block ms-1">
                        <!-- Templates Dropdown -->
                        <div class="hs-dropdown  relative  [--scope:window] [--auto-close:inside] inline-flex">
                            <button id="hs-dropdown-preview-navbar" type="button"
                                    class="hs-dropdown-toggle  group relative flex justify-center items-center size-8 text-xs rounded-lg text-gray-800 hover:bg-gray-200 focus:bg-gray-200 dark:text-neutral-200 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden"
                                    aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                              <span class="">
                                <svg class=" size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">
                                  <path d="m6 9 6 6 6-6"/>
                                </svg>
                              </span>

                                <span class="absolute -top-0.5 -end-0.5">
                                        <span class="relative flex">
                                          <span
                                              class="animate-ping absolute inline-flex size-full rounded-full bg-red-400 dark:bg-red-600 opacity-75"></span>
                                          <span class="relative inline-flex size-2 bg-red-500 rounded-full"></span>
                                          <span class="sr-only">Notification</span>
                                        </span>
                                      </span>
                            </button>

                            <!-- Dropdown -->
                            <div
                                class="hs-dropdown-menu hs-dropdown-open:opacity-100 w-full min-w-90 md:w-125 transition-[opacity,margin] duration opacity-0 hidden z-61 overflow-hidden border border-gray-200 bg-white rounded-xl shadow-xl dark:bg-neutral-800 dark:border-neutral-700"
                                role="menu" aria-orientation="vertical"
                                aria-labelledby="hs-dropdown-preview-navbar">
                                <!-- Tab -->
                                <div
                                    class="p-3 pb-0 flex flex-wrap justify-between items-center gap-3 border-b border-gray-200 dark:border-neutral-700">
                                    <!-- Nav Tab -->
                                    <nav class="flex gap-1" aria-label="Tabs" role="tablist"
                                         aria-orientation="horizontal">
                                        <button type="button"
                                                class="hs-tab-active:after:bg-gray-800 hs-tab-active:text-gray-800 px-2 py-1.5 mb-2 relative inline-flex justify-center items-center gap-x-2 hover:bg-gray-100 text-gray-500 hover:text-gray-800 text-sm rounded-lg disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 after:absolute after:-bottom-2 after:inset-x-2 after:z-10 after:h-0.5 after:pointer-events-none dark:hs-tab-active:text-neutral-200 dark:hs-tab-active:after:bg-neutral-400 dark:text-neutral-500 dark:hover:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden "
                                                id="hs-pmn-item-pro" aria-selected="false" data-hs-tab="#hs-pmn-pro"
                                                aria-controls="hs-pmn-pro" role="tab">
                                            Pro
                                        </button>
                                        <button type="button"
                                                class="hs-tab-active:after:bg-gray-800 hs-tab-active:text-gray-800 px-2 py-1.5 mb-2 relative inline-flex justify-center items-center gap-x-2 hover:bg-gray-100 text-gray-500 hover:text-gray-800 text-sm rounded-lg disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 after:absolute after:-bottom-2 after:inset-x-2 after:z-10 after:h-0.5 after:pointer-events-none dark:hs-tab-active:text-neutral-200 dark:hs-tab-active:after:bg-neutral-400 dark:text-neutral-500 dark:hover:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden active"
                                                id="hs-pmn-item-free" aria-selected="true"
                                                data-hs-tab="#hs-pmn-free" aria-controls="hs-pmn-free" role="tab">
                                            Free
                                        </button>
                                    </nav>
                                    <!-- End Nav Tab -->

                                    <!-- Switch/Toggle -->
                                    <div class="mb-2 flex items-center gap-x-0.5">
                                        <button type="button"
                                                class="hs-dark-mode hs-dark-mode-active:hidden flex shrink-0 justify-center items-center gap-x-1 text-xs text-gray-500 hover:text-gray-800 focus:outline-hidden focus:text-gray-800 dark:text-neutral-400 dark:hover:text-neutral-200 dark:focus:text-neutral-200"
                                                data-hs-theme-click-value="dark">
                                            <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg"
                                                 width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                 stroke-linejoin="round">
                                                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                                            </svg>
                                            Switch to Dark
                                        </button>
                                        <button type="button"
                                                class="hs-dark-mode hs-dark-mode-active:flex hidden shrink-0 justify-center items-center gap-x-1 text-xs text-gray-500 hover:text-gray-800 focus:outline-hidden focus:text-gray-800 dark:text-neutral-400 dark:hover:text-neutral-200 dark:focus:text-neutral-200"
                                                data-hs-theme-click-value="light">
                                            <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg"
                                                 width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                 stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="4"></circle>
                                                <path d="M12 2v2"></path>
                                                <path d="M12 20v2"></path>
                                                <path d="m4.93 4.93 1.41 1.41"></path>
                                                <path d="m17.66 17.66 1.41 1.41"></path>
                                                <path d="M2 12h2"></path>
                                                <path d="M20 12h2"></path>
                                                <path d="m6.34 17.66-1.41 1.41"></path>
                                                <path d="m19.07 4.93-1.41 1.41"></path>
                                            </svg>
                                            Switch to Light
                                        </button>
                                    </div>
                                    <!-- End Switch/Toggle -->
                                </div>
                                <!-- End Tab -->

                                <!-- Tab Content -->
                                <div id="hs-pmn-pro" class="hidden" role="tabpanel"
                                     aria-labelledby="hs-pmn-item-pro">
                                    <!-- Header -->
                                    <div class="p-3 flex flex-wrap justify-between items-center gap-3">

                                    </div>
                                    <!-- End Header -->

                                    <!-- Body -->
                                    <div
                                        class="px-3 max-h-64 sm:max-h-100 overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">
                                        <!-- Grid -->
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">

                                        </div>
                                        <!-- End Grid -->
                                    </div>
                                    <!-- Body -->

                                </div>
                                <!-- End Tab Content -->

                                <!-- Tab Content -->
                                <div id="hs-pmn-free" class="" role="tabpanel" aria-labelledby="hs-pmn-item-free">
                                    <!-- Header -->
                                    <div class="p-3 flex flex-wrap justify-between items-center gap-3">

                                    </div>
                                    <!-- End Header -->

                                    <!-- Body -->
                                    <div
                                        class="px-3 max-h-64 sm:max-h-100 overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">
                                        <!-- Grid -->
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">

                                        </div>
                                        <!-- End Grid -->
                                    </div>
                                    <!-- Body -->
                                </div>
                                <!-- End Tab Content -->
                            </div>
                            <!-- End Dropdown -->
                        </div>
                        <!-- End Templates Dropdown -->
                    </div>

                    <!-- Sidebar Toggle -->
                    <button type="button"
                            class="p-1.5 size-7.5 inline-flex items-center gap-x-1 text-xs rounded-md border border-transparent text-gray-500 hover:text-gray-800 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:text-gray-800 dark:text-neutral-500 dark:hover:text-neutral-400 dark:focus:text-neutral-400"
                            aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-pro-sidebar"
                            data-hs-overlay="#hs-pro-sidebar">
                        <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2"/>
                            <path d="M15 3v18"/>
                            <path d="m10 15-3-3 3-3"/>
                        </svg>
                        <span class="sr-only">Sidebar Toggle</span>
                    </button>
                    <!-- End Sidebar Toggle -->
                </li>

                <li class="inline-flex items-center relative text-gray-200 pe-1.5 last:pe-0 last:after:hidden after:absolute after:top-1/2 after:end-0 after:inline-block after:w-px after:h-3.5 after:bg-gray-300 after:rounded-full after:-translate-y-1/2 after:rotate-12 dark:text-neutral-200 dark:after:bg-neutral-700">
                    <!-- Project Dropdown -->
                    <div class="inline-flex justify-center w-full">
                        <div
                            class="hs-dropdown relative [--strategy:absolute] [--placement:bottom-left] inline-flex">
                            <!-- Project Button -->
                            <button id="hs-pro-anpjdi" type="button"
                                    class="py-1 px-2 min-h-8 flex items-center gap-x-1 font-medium text-sm text-gray-800 rounded-lg hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 dark:text-neutral-200 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800"
                                    aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                                <img class="shrink-0 size-6 rounded-full object-cover me-1"
                                     src="https://preline.co/assets/img/logo/hs.png" alt="Logo">
                                Htmlstream
                                <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24"
                                     height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m7 15 5 5 5-5"/>
                                    <path d="m7 9 5-5 5 5"/>
                                </svg>
                            </button>
                            <!-- End Project Button -->

                            <!-- Dropdown -->
                            <div
                                class="hs-dropdown-menu hs-dropdown-open:opacity-100 w-65 transition-[opacity,margin] duration opacity-0 hidden z-20 bg-white border border-gray-200 rounded-xl shadow-xl dark:bg-neutral-900 dark:border-neutral-700"
                                role="menu" aria-orientation="vertical" aria-labelledby="hs-pro-anpjdi">
                                <div class="p-1">
                    <span class="block pt-2 pb-2 ps-2.5 text-sm text-gray-500 dark:text-neutral-500">
                      Projects (2)
                    </span>

                                    <div class="flex flex-col gap-y-1">
                                        <!-- Item -->
                                        <label for="hs-pro-anpjdi1"
                                               class="py-2 px-2.5 group flex justify-start items-center gap-x-3 rounded-lg cursor-pointer text-[13px] text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-200 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                                            <input type="radio" class="hidden" id="hs-pro-anpjdi1"
                                                   name="hs-pro-anpjdi" checked>
                                            <svg class="shrink-0 size-4 opacity-0 group-has-checked:opacity-100"
                                                 xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 6 9 17l-5-5"/>
                                            </svg>
                                            <span class="grow">
                          <span class="block text-sm font-medium text-gray-800 dark:text-neutral-200">
                            Htmlstream
                          </span>
                        </span>
                                            <img class="shrink-0 size-5 rounded-full object-cover"
                                                 src="https://preline.co/assets/img/logo/hs.png" alt="Logo">
                                        </label>
                                        <!-- End Item -->

                                        <!-- Item -->
                                        <label for="hs-pro-anpjdi2"
                                               class="py-2 px-2.5 group flex justify-start items-center gap-x-3 rounded-lg cursor-pointer text-[13px] text-gray-800 hover:bg-gray-100 dark:text-neutral-200 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                                            <input type="radio" class="hidden" id="hs-pro-anpjdi2"
                                                   name="hs-pro-anpjdi">
                                            <svg class="shrink-0 size-4 opacity-0 group-has-checked:opacity-100"
                                                 xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 6 9 17l-5-5"/>
                                            </svg>
                                            <span class="grow">
                          <span class="block text-sm font-medium text-gray-800 dark:text-neutral-200">
                            Bloomark
                          </span>
                        </span>
                                            <img class="shrink-0 size-5 rounded-full object-cover"
                                                 src="https://preline.co/assets/img/logo/logo-short.png" alt="Logo">
                                        </label>
                                        <!-- End Item -->
                                    </div>
                                </div>

                                <div class="p-1 border-t border-gray-200 dark:border-neutral-700">
                                    <button type="button"
                                            class="group w-full flex items-center gap-x-3 py-2 px-2.5 rounded-lg text-sm text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                             height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/>
                                            <path d="M8 12h8"/>
                                            <path d="M12 8v8"/>
                                        </svg>
                                        Add project
                                    </button>

                                    <button type="button"
                                            class="w-full flex items-center gap-x-3 py-2 px-2.5 rounded-lg text-sm text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                             height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                        Manage projects
                                    </button>
                                </div>
                            </div>
                            <!-- End Dropdown -->
                        </div>
                    </div>
                    <!-- End Project Dropdown -->
                </li>

                <li class="inline-flex items-center relative text-gray-200 pe-1.5 last:pe-0 last:after:hidden after:absolute after:top-1/2 after:end-0 after:inline-block after:w-px after:h-3.5 after:bg-gray-300 after:rounded-full after:-translate-y-1/2 after:rotate-12 dark:text-neutral-200 dark:after:bg-neutral-700">
                    <!-- Teams Dropdown -->
                    <div class="inline-flex justify-center w-full">
                        <div
                            class="hs-dropdown relative [--strategy:absolute] [--placement:bottom-left] inline-flex w-full">
                            <!-- Teams Button -->
                            <button id="hs-pro-antmd" type="button"
                                    class="py-1 px-2 min-h-8 flex items-center gap-x-1 font-medium text-sm text-gray-800 rounded-lg hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 dark:text-neutral-200 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800"
                                    aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                                Marketing
                                <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24"
                                     height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m7 15 5 5 5-5"/>
                                    <path d="m7 9 5-5 5 5"/>
                                </svg>
                            </button>
                            <!-- End Teams Button -->

                            <!-- Dropdown -->
                            <div
                                class="hs-dropdown-menu hs-dropdown-open:opacity-100 w-65 transition-[opacity,margin] duration opacity-0 hidden z-20 bg-white border border-gray-200 rounded-xl shadow-xl dark:bg-neutral-900 dark:border-neutral-700"
                                role="menu" aria-orientation="vertical" aria-labelledby="hs-pro-antmd">
                                <div class="p-1">
                    <span class="block pt-2 pb-2 ps-2.5 text-sm text-gray-500 dark:text-neutral-500">
                      Teams (1)
                    </span>

                                    <div class="flex flex-col gap-y-1">
                                        <!-- Item -->
                                        <label for="hs-pro-antmdi1"
                                               class="py-2 px-2.5 group flex justify-start items-center gap-x-3 rounded-lg cursor-pointer text-[13px] text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-200 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                                            <input type="radio" class="hidden" id="hs-pro-antmdi1"
                                                   name="hs-pro-antmdi" checked>
                                            <svg class="shrink-0 size-4 opacity-0 group-has-checked:opacity-100"
                                                 xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 6 9 17l-5-5"/>
                                            </svg>
                                            <span class="grow">
                          <span class="block text-sm font-medium text-gray-800 dark:text-neutral-200">
                            Marketing
                          </span>
                        </span>
                                        </label>
                                        <!-- End Item -->
                                    </div>
                                </div>

                                <div class="p-1 border-t border-gray-200 dark:border-neutral-700">
                                    <button type="button"
                                            class="w-full flex items-center gap-x-3 py-2 px-2.5 rounded-lg text-sm text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                             height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/>
                                            <path d="M8 12h8"/>
                                            <path d="M12 8v8"/>
                                        </svg>
                                        Add team
                                    </button>
                                </div>
                            </div>
                            <!-- End Dropdown -->
                        </div>
                    </div>
                    <!-- End Teams Dropdown -->
                </li>
            </ul>

            <ul class="flex flex-row items-center gap-x-3 ms-auto">
                <li class="inline-flex items-center gap-1.5 relative text-gray-500 pe-3 last:pe-0 last:after:hidden after:absolute after:top-1/2 after:end-0 after:inline-block after:w-px after:h-3.5 after:bg-gray-300 after:rounded-full after:-translate-y-1/2 after:rotate-12 dark:text-neutral-200 dark:after:bg-neutral-700">
                    <div class="h-8">
                        <!-- Account Dropdown -->
                        <div
                            class="hs-dropdown inline-flex [--strategy:absolute] [--auto-close:inside] [--placement:bottom-right] relative text-start">
                            <button id="hs-dnad" type="button"
                                    class="p-0.5 inline-flex shrink-0 items-center gap-x-3 text-start rounded-full hover:bg-gray-200 focus:outline-hidden focus:bg-gray-200 dark:hover:bg-neutral-800 dark:hover:text-neutral-200 dark:focus:bg-neutral-800 dark:focus:text-neutral-200 dark:text-neutral-500"
                                    aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
                                <img class="shrink-0 size-7 rounded-full"
                                     src="{{auth()->user()->image_path}}"
                                     alt="Avatar">
                            </button>

                            <!-- Account Dropdown -->
                            <div
                                class="hs-dropdown-menu hs-dropdown-open:opacity-100 w-60 transition-[opacity,margin] duration opacity-0 hidden z-20 bg-white border border-gray-200 rounded-xl shadow-xl dark:bg-neutral-900 dark:border-neutral-700"
                                role="menu" aria-orientation="vertical" aria-labelledby="hs-dnad">
                                <div class="py-2 px-3.5">
                                        <span class="font-medium text-gray-800 dark:text-neutral-300">
                                          {{ auth()->user()->firstname }} {{ auth()->user()->lastname }}
                                        </span>
                                    <p class="text-sm text-gray-500 dark:text-neutral-500">
                                        {{ auth()->user()->email }}
                                    </p>
                                </div>
                                <div class="px-4 py-2 border-t border-gray-200 dark:border-neutral-800">
                                    <!-- Switch/Toggle -->
                                    <div class="flex flex-wrap justify-between items-center gap-2">
                                            <span
                                                class="flex-1 cursor-pointer text-sm text-gray-600 dark:text-neutral-400">Theme</span>
                                        <div
                                            class="p-0.5 inline-flex cursor-pointer bg-gray-100 rounded-full dark:bg-neutral-800">
                                            <button type="button"
                                                    class="size-7 flex justify-center items-center bg-white shadow-sm text-gray-800 rounded-full dark:text-neutral-200 hs-auto-mode-active:bg-transparent hs-auto-mode-active:shadow-none hs-dark-mode-active:bg-transparent hs-dark-mode-active:shadow-none"
                                                    data-hs-theme-click-value="default">
                                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                                     width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                     stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="4"/>
                                                    <path d="M12 3v1"/>
                                                    <path d="M12 20v1"/>
                                                    <path d="M3 12h1"/>
                                                    <path d="M20 12h1"/>
                                                    <path d="m18.364 5.636-.707.707"/>
                                                    <path d="m6.343 17.657-.707.707"/>
                                                    <path d="m5.636 5.636.707.707"/>
                                                    <path d="m17.657 17.657.707.707"/>
                                                </svg>
                                                <span class="sr-only">Default (Light)</span>
                                            </button>
                                            <button type="button"
                                                    class="size-7 flex justify-center items-center text-gray-800 rounded-full dark:text-neutral-200 hs-dark-mode-active:bg-white hs-dark-mode-active:shadow-sm hs-dark-mode-active:text-neutral-800"
                                                    data-hs-theme-click-value="dark">
                                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                                     width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                     stroke-linejoin="round">
                                                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                                                </svg>
                                                <span class="sr-only">Dark</span>
                                            </button>
                                            <button type="button"
                                                    class="size-7 flex justify-center items-center text-gray-800 rounded-full dark:text-neutral-200 hs-auto-light-mode-active:bg-white hs-auto-dark-mode-active:bg-red-800 hs-auto-mode-active:shadow-sm"
                                                    data-hs-theme-click-value="auto">
                                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                                     width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                     stroke-linejoin="round">
                                                    <rect width="20" height="14" x="2" y="3" rx="2"/>
                                                    <line x1="8" x2="16" y1="21" y2="21"/>
                                                    <line x1="12" x2="12" y1="17" y2="21"/>
                                                </svg>
                                                <span class="sr-only">Auto (System)</span>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- End Switch/Toggle -->
                                </div>
                                <div class="p-1 border-t border-gray-200 dark:border-neutral-800">
                                    <a href="{{route('admin.profile')}}"
                                       class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                                        <svg class="shrink-0 mt-0.5 size-4" xmlns="http://www.w3.org/2000/svg"
                                             width="24" height="24" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                             stroke-linejoin="round">
                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                            <circle cx="12" cy="7" r="4"/>
                                        </svg>
                                        Profile
                                    </a>
                                    <a class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800"
                                       href="#">
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                             height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                        Settings
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                                        @csrf
                                        <button type="submit" class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                                            <svg class="shrink-0 mt-0.5 size-4" xmlns="http://www.w3.org/2000/svg"
                                                 width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                 stroke-linejoin="round">
                                                <path d="m16 17 5-5-5-5"/>
                                                <path d="M21 12H9"/>
                                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                            </svg>
                                            Log out
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <!-- End Account Dropdown -->
                        </div>
                        <!-- End Account Dropdown -->
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</header>
<!-- ========== END HEADER ========== -->

<!-- ========== MAIN CONTENT ========== -->
<main class="lg:hs-overlay-layout-open:ps-60 transition-all duration-300 lg:fixed lg:inset-0 pt-13 px-3 pb-3">
    <!-- Sidebar -->
    <div id="hs-pro-sidebar" class="hs-overlay [--body-scroll:true] lg:[--overlay-backdrop:false] [--is-layout-affect:true] [--opened:lg] [--auto-close:lg]
            hs-overlay-open:translate-x-0 lg:hs-overlay-layout-open:translate-x-0 -translate-x-full transition-all duration-300 transform w-60 hidden
            fixed inset-y-0 z-60 start-0 bg-gray-100 lg:block lg:-translate-x-full lg:end-auto lg:bottom-0 dark:bg-neutral-900"
         role="dialog" tabindex="-1" aria-label="Sidebar">
        <div class="lg:pt-13 relative flex flex-col h-full max-h-full">
            <!-- Body -->
            <nav
                class="p-3 size-full flex flex-col overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-200 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">
                <div class="lg:hidden mb-2 flex items-center justify-between">
                    <button type="button"
                            class="flex items-center gap-x-1.5 py-2 px-2.5 font-medium text-xs bg-black text-white rounded-lg focus:outline-hidden disabled:opacity-50 disabled:pointer-events-none dark:bg-white dark:text-black">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                             fill="currentColor" viewBox="0 0 16 16">
                            <path
                                d="M7.657 6.247c.11-.33.576-.33.686 0l.645 1.937a2.89 2.89 0 0 0 1.829 1.828l1.936.645c.33.11.33.576 0 .686l-1.937.645a2.89 2.89 0 0 0-1.828 1.829l-.645 1.936a.361.361 0 0 1-.686 0l-.645-1.937a2.89 2.89 0 0 0-1.828-1.828l-1.937-.645a.361.361 0 0 1 0-.686l1.937-.645a2.89 2.89 0 0 0 1.828-1.828zM3.794 1.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387A1.73 1.73 0 0 0 4.593 5.69l-.387 1.162a.217.217 0 0 1-.412 0L3.407 5.69A1.73 1.73 0 0 0 2.31 4.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387A1.73 1.73 0 0 0 3.407 2.31zM10.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.16 1.16 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.16 1.16 0 0 0-.732-.732L9.1 2.137a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732z"/>
                        </svg>
                        Ask AI
                    </button>

                    <!-- Sidebar Toggle -->
                    <button type="button"
                            class="p-1.5 size-7.5 inline-flex items-center gap-x-1 text-xs rounded-md text-gray-500 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden dark:text-neutral-500"
                            aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-pro-sidebar"
                            data-hs-overlay="#hs-pro-sidebar">
                        <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"/>
                            <path d="m6 6 12 12"/>
                        </svg>
                        <span class="sr-only">Sidebar Toggle</span>
                    </button>
                    <!-- End Sidebar Toggle -->
                </div>

                <button type="button" class="p-1.5 ps-2.5 w-full inline-flex items-center gap-x-2 text-sm rounded-lg bg-white border border-gray-200 text-gray-600 shadow-xs hover:border-gray-300 focus:outline-hidden focus:border-gray-300 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:hover:border-neutral-600 dark:focus:border-neutral-600">
                    Quick actions
                    <span
                        class="ms-auto flex items-center gap-x-1 py-px px-1.5 border border-gray-200 rounded-md dark:border-neutral-700">
                          <svg class="shrink-0 size-2.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                               stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3"></path>
                          </svg>
                          <span class="text-[11px] uppercase">k</span>
                        </span>
                </button>
                <!-- Dashboard Section -->
                <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 first:border-t-0 first:pt-0 first:mt-0 dark:border-neutral-700">
                                <span class="block ps-2.5 mb-2 font-medium text-xs uppercase text-gray-800 dark:text-neutral-500">
                                    Dashboard
                                </span>
                    <ul class="flex flex-col gap-y-1">
                        <li>
                            <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden {{ request()->routeIs('admin.index') ? 'bg-gray-200 text-gray-800 dark:bg-neutral-800 dark:text-neutral-200' : 'text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:hover:text-neutral-200' }}"
                               href="{{ route('admin.index') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Home
                            </a>
                        </li>
                        @can('activity-log-view')
                            <li>
                                <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden {{ request()->routeIs('admin.activity.index') ? 'bg-gray-200 text-gray-800 dark:bg-neutral-800 dark:text-neutral-200' : 'text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:hover:text-neutral-200' }}"
                                   href="{{ route('admin.activity.index') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    Activity Log
                                </a>
                            </li>
                        @endcan
                    </ul>
                </div>

                <!-- Posts Section -->
                @if(auth()->user()->can('post-list') || auth()->user()->can('post-create'))
                    <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700">
        <span class="block ps-2.5 mb-2 font-medium text-xs uppercase text-gray-800 dark:text-neutral-500">
            Posts
        </span>
                        <ul class="flex flex-col gap-y-1">
                            @can('post-list')
                                <li>
                                    <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden {{ request()->routeIs('admin.posts.index') ? 'bg-gray-200 text-gray-800 dark:bg-neutral-800 dark:text-neutral-200' : 'text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:hover:text-neutral-200' }}"
                                       href="{{ route('admin.posts.index') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                        </svg>
                                        All Posts
                                    </a>
                                </li>
                            @endcan
                            @can('post-create')
                                <li>
                                    <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden {{ request()->routeIs('admin.posts.create') ? 'bg-gray-200 text-gray-800 dark:bg-neutral-800 dark:text-neutral-200' : 'text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:hover:text-neutral-200' }}"
                                       href="{{ route('admin.posts.create') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Create Post
                                    </a>
                                </li>
                            @endcan
                            @can('post-list')
                                <li>
                                    <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden {{ request()->routeIs('admin.posts-saved.index') ? 'bg-gray-200 text-gray-800 dark:bg-neutral-800 dark:text-neutral-200' : 'text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:hover:text-neutral-200' }}"
                                       href="{{ route('admin.posts-saved.index') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                        </svg>
                                        Saved Drafts
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                @endif

                <!-- Categories Section -->
                @can('category-list')
                    <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700">
        <span class="block ps-2.5 mb-2 font-medium text-xs uppercase text-gray-800 dark:text-neutral-500">
            Categories
        </span>
                        <ul class="flex flex-col gap-y-1">
                            <li>
                                <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden {{ request()->routeIs('admin.categories.index') ? 'bg-gray-200 text-gray-800 dark:bg-neutral-800 dark:text-neutral-200' : 'text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:hover:text-neutral-200' }}"
                                   href="{{ route('admin.categories.index') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    All Categories
                                </a>
                            </li>
                            @can('category-create')
                                <li>
                                    <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden {{ request()->routeIs('admin.categories.create') ? 'bg-gray-200 text-gray-800 dark:bg-neutral-800 dark:text-neutral-200' : 'text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:hover:text-neutral-200' }}"
                                       href="{{ route('admin.categories.create') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Add Category
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                @endcan

                <!-- Comments Section -->
                @can('comment-list')
                    <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700">
                                <span class="block ps-2.5 mb-2 font-medium text-xs uppercase text-gray-800 dark:text-neutral-500">
                                    Comments
                                </span>
                        <ul class="flex flex-col gap-y-1">
                            <li>
                                <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden {{ request()->routeIs('admin.comments.index') ? 'bg-gray-200 text-gray-800 dark:bg-neutral-800 dark:text-neutral-200' : 'text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:hover:text-neutral-200' }}"
                                   href="{{ route('admin.comments.index') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                    </svg>
                                    All Comments
                                </a>
                            </li>
                        </ul>
                    </div>
                @endcan

                <!-- Subscribers Section -->
                @can('subscriber-list')
                    <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700">
        <span class="block ps-2.5 mb-2 font-medium text-xs uppercase text-gray-800 dark:text-neutral-500">
            Subscribers
        </span>
                        <ul class="flex flex-col gap-y-1">
                            <li>
                                <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden {{ request()->routeIs('admin.subscribers.index') ? 'bg-gray-200 text-gray-800 dark:bg-neutral-800 dark:text-neutral-200' : 'text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:hover:text-neutral-200' }}"
                                   href="{{ route('admin.subscribers.index') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    All Subscribers
                                </a>
                            </li>
                        </ul>
                    </div>
                @endcan

                <!-- Contact Messages Section -->
                @can('contact-list')
                    <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700">
        <span class="block ps-2.5 mb-2 font-medium text-xs uppercase text-gray-800 dark:text-neutral-500">
            Messages
        </span>
                        <ul class="flex flex-col gap-y-1">
                            <li>
                                <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden {{ request()->routeIs('admin.contact.index') ? 'bg-gray-200 text-gray-800 dark:bg-neutral-800 dark:text-neutral-200' : 'text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:hover:text-neutral-200' }}"
                                   href="{{ route('admin.contact.index') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    Contact Messages
                                </a>
                            </li>
                        </ul>
                    </div>
                @endcan

                <!-- Media Section -->
                @can('image-list')
                    <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700">
        <span class="block ps-2.5 mb-2 font-medium text-xs uppercase text-gray-800 dark:text-neutral-500">
            Media
        </span>
                        <ul class="flex flex-col gap-y-1">
                            <li>
                                <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden {{ request()->routeIs('admin.images.index') ? 'bg-gray-200 text-gray-800 dark:bg-neutral-800 dark:text-neutral-200' : 'text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:hover:text-neutral-200' }}"
                                   href="{{ route('admin.images.index') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Image Manager
                                </a>
                            </li>
                        </ul>
                    </div>
                @endcan

                <!-- Advertisements Section -->
                @can('advertisement-list')
                    <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700">
                        <span class="block ps-2.5 mb-2 font-medium text-xs uppercase text-gray-800 dark:text-neutral-500">
                            Advertising
                        </span>
                        <ul class="flex flex-col gap-y-1">
                            <li>
                                <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden {{ request()->routeIs('admin.advertisements.index') || request()->routeIs('admin.advertisements.create') || request()->routeIs('admin.advertisements.edit') ? 'bg-gray-200 text-gray-800 dark:bg-neutral-800 dark:text-neutral-200' : 'text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:hover:text-neutral-200' }}"
                                   href="{{ route('admin.advertisements.index') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                                    </svg>
                                    Advertisements
                                </a>
                            </li>
                        </ul>
                    </div>
                @endcan

                <!-- Users & Roles Section -->
                @if(auth()->user()->can('user-list') || auth()->user()->can('role-list'))
                    <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700">
                                <span class="block ps-2.5 mb-2 font-medium text-xs uppercase text-gray-800 dark:text-neutral-500">
                                    Users & Permissions
                                </span>
                        <ul class="flex flex-col gap-y-1">
                            @can('user-list')
                                <li>
                                    <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden {{ request()->routeIs('admin.users.index') ? 'bg-gray-200 text-gray-800 dark:bg-neutral-800 dark:text-neutral-200' : 'text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:hover:text-neutral-200' }}"
                                       href="{{ route('admin.users.index') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        Users
                                    </a>
                                </li>
                            @endcan
                            @can('role-list')
                                <li>
                                    <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden {{ request()->routeIs('admin.roles.index') || request()->routeIs('admin.roles.show') || request()->routeIs('admin.roles.create') || request()->routeIs('admin.roles.edit') ? 'bg-gray-200 text-gray-800 dark:bg-neutral-800 dark:text-neutral-200' : 'text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:hover:text-neutral-200' }}"
                                       href="{{ route('admin.roles.index') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        Roles & Permissions
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                @endif

                <!-- Account Section -->
                <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700">
                            <span class="block ps-2.5 mb-2 font-medium text-xs uppercase text-gray-800 dark:text-neutral-500">
                                Account
                            </span>
                    <ul class="flex flex-col gap-y-1">
                        <li>
                            <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm rounded-lg focus:outline-hidden {{ request()->routeIs('admin.profile') ? 'bg-gray-200 text-gray-800 dark:bg-neutral-800 dark:text-neutral-200' : 'text-gray-500 hover:bg-gray-200 hover:text-gray-800 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:hover:text-neutral-200' }}"
                               href="{{ route('admin.profile') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Profile
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            <!-- End Body -->

            <!-- Footer -->
            <footer class="mt-auto p-3 flex flex-col">
                <!-- List -->
                <ul class="flex flex-col gap-y-1">
                    <li>
                        <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm text-gray-500 rounded-lg hover:bg-gray-200 hover:text-gray-800 focus:outline-hidden focus:bg-gray-200 focus:text-gray-800 dark:hover:bg-neutral-800 dark:hover:text-neutral-200 dark:focus:bg-neutral-800 dark:focus:text-neutral-500 dark:text-neutral-500"
                           href="#">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                            </svg>
                            What's new?
                        </a>
                    </li>
                    <li>
                        <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm text-gray-500 rounded-lg hover:bg-gray-200 hover:text-gray-800 focus:outline-hidden focus:bg-gray-200 focus:text-gray-800 dark:hover:bg-neutral-800 dark:hover:text-neutral-200 dark:focus:bg-neutral-800 dark:focus:text-neutral-500 dark:text-neutral-500"
                           href="#">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>
                            </svg>
                            Help &amp; support
                        </a>
                    </li>
                    <li class="lg:hidden">
                        <a class="w-full flex items-center gap-x-2 py-2 px-2.5 text-sm text-gray-500 rounded-lg hover:bg-gray-200 hover:text-gray-800 focus:outline-hidden focus:bg-gray-200 focus:text-gray-800 dark:hover:bg-neutral-800 dark:hover:text-neutral-200 dark:focus:bg-neutral-800 dark:focus:text-neutral-500 dark:text-neutral-500"
                           href="#">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 7v14"/>
                                <path
                                    d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>
                            </svg>
                            Knowledge Base
                        </a>
                    </li>
                </ul>
                <!-- End List -->
            </footer>
            <!-- End Footer -->
        </div>
    </div>
    <!-- End Sidebar -->

    <!-- Content -->
    <div
        class="h-[calc(100dvh-62px)] lg:h-full overflow-hidden flex flex-col bg-white border border-gray-200 shadow-xs rounded-lg dark:bg-neutral-800 dark:border-neutral-700">
        <!-- Header -->
        <div
            class="py-3 px-4 flex flex-wrap justify-between items-center gap-2 bg-white border-b border-gray-200 dark:bg-neutral-800 dark:border-neutral-700">
            <div>
                <h1 class="font-medium text-lg text-gray-800 dark:text-neutral-200">
                    Dashboard
                </h1>
            </div>

            <!-- Button Group -->
            <div class="flex items-center gap-x-5">
                    <span class="py-1.5 px-2 flex items-center justify-center gap-x-1 bg-indigo-500/10 border border-indigo-200 text-indigo-700 text-xs rounded-full py-1 hover:bg-indigo-500/20 focus:outline-hidden focus:bg-indigo-500/20 dark:text-indigo-400 dark:border-indigo-500/20">
                        {{ strip_tags(Illuminate\Foundation\Inspiring::quote()) }}
                    </span>
            </div>
            <!-- End Button Group -->
        </div>
        <!-- End Header -->

        <!-- Body -->
        <div
            class="flex-1 flex flex-col overflow-hidden overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">
            {{ $slot }}
        </div>
        <!-- End Body -->
    </div>
    <!-- End Content -->
</main>
<!-- ========== END MAIN CONTENT ========== -->
@stack('modal')
@stack('scripts')
</body>
</html>

