@props(['name', 'show' => false])

<div id="{{ $name }}"
     class="hs-overlay {{ $show ? '' : 'hidden' }} size-full fixed top-0 start-0 z-80 overflow-x-hidden overflow-y-auto pointer-events-none"
     role="dialog"
     tabindex="-1"
     aria-labelledby="{{ $name }}-label">
    <div class="hs-overlay-animation-target hs-overlay-open:scale-100 hs-overlay-open:opacity-100 scale-95 opacity-0 ease-in-out transition-all duration-200 sm:max-w-lg sm:w-full m-3 sm:mx-auto min-h-[calc(100%-56px)] flex items-center">
        <div class="w-full flex flex-col bg-white border border-gray-400 shadow-2xs rounded-xl pointer-events-auto">
            {{ $slot }}
        </div>
    </div>
</div>
