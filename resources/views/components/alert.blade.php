@props([
    'type' => 'success',
    'dismissible' => true,
    'id' => 'alert-' . uniqid(),
    'title' => '',
    'icon' => true,
])

@php
    $styles = match($type) {
        'success' => [
            'bg' => 'bg-teal-50 dark:bg-teal-500/20',
            'border' => 'border-teal-200 dark:border-teal-900',
            'text' => 'text-teal-800 dark:text-teal-400',
            'icon_color' => 'text-teal-500',
            'button_bg' => 'bg-teal-50 dark:bg-transparent',
            'button_hover' => 'hover:bg-teal-100 dark:hover:bg-teal-800/50',
            'button_focus' => 'focus:bg-teal-100 dark:focus:bg-teal-800/50',
            'icon' => '<svg class="shrink-0 size-4 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>',
        ],
        'error' => [
            'bg' => 'bg-red-50 dark:bg-red-500/20',
            'border' => 'border-red-200 dark:border-red-900',
            'text' => 'text-red-800 dark:text-red-400',
            'icon_color' => 'text-red-500',
            'button_bg' => 'bg-red-50 dark:bg-transparent',
            'button_hover' => 'hover:bg-red-100 dark:hover:bg-red-800/50',
            'button_focus' => 'focus:bg-red-100 dark:focus:bg-red-800/50',
            'icon' => '<svg class="shrink-0 size-4 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>',
        ],
        'warning' => [
            'bg' => 'bg-yellow-50 dark:bg-yellow-500/20',
            'border' => 'border-yellow-200 dark:border-yellow-900',
            'text' => 'text-yellow-800 dark:text-yellow-400',
            'icon_color' => 'text-yellow-500',
            'button_bg' => 'bg-yellow-50 dark:bg-transparent',
            'button_hover' => 'hover:bg-yellow-100 dark:hover:bg-yellow-800/50',
            'button_focus' => 'focus:bg-yellow-100 dark:focus:bg-yellow-800/50',
            'icon' => '<svg class="shrink-0 size-4 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>',
        ],
        'info' => [
            'bg' => 'bg-blue-50 dark:bg-blue-500/20',
            'border' => 'border-blue-200 dark:border-blue-900',
            'text' => 'text-blue-800 dark:text-blue-400',
            'icon_color' => 'text-blue-500',
            'button_bg' => 'bg-blue-50 dark:bg-transparent',
            'button_hover' => 'hover:bg-blue-100 dark:hover:bg-blue-800/50',
            'button_focus' => 'focus:bg-blue-100 dark:focus:bg-blue-800/50',
            'icon' => '<svg class="shrink-0 size-4 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
        ],
        default => [
            'bg' => 'bg-gray-50 dark:bg-gray-500/20',
            'border' => 'border-gray-200 dark:border-gray-900',
            'text' => 'text-gray-800 dark:text-gray-400',
            'icon_color' => 'text-gray-500',
            'button_bg' => 'bg-gray-50 dark:bg-transparent',
            'button_hover' => 'hover:bg-gray-100 dark:hover:bg-gray-800/50',
            'button_focus' => 'focus:bg-gray-100 dark:focus:bg-gray-800/50',
            'icon' => '<svg class="shrink-0 size-4 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
        ],
    };
@endphp

<div
    id="{{ $id }}"
    class="hs-removing:translate-x-5 hs-removing:opacity-0 transition duration-300 {{ $styles['bg'] }} border {{ $styles['border'] }} text-sm {{ $styles['text'] }} rounded-lg p-4"
    role="alert"
    tabindex="-1"
>
    <div class="flex">
        @if($icon)
            <div class="shrink-0">
                {!! $styles['icon'] !!}
            </div>
        @endif

        <div class="ms-2">
            @if($title)
                <h3 class="text-sm font-medium">
                    {{ $title }}
                </h3>
            @endif
            @if($slot->isNotEmpty())
                <div class="{{ $title ? 'mt-1' : '' }} text-sm">
                    {{ $slot }}
                </div>
            @endif
        </div>

        @if($dismissible)
            <div class="ps-3 ms-auto">
                <div class="-mx-1.5 -my-1.5">
                    <button
                        type="button"
                        class="inline-flex {{ $styles['button_bg'] }} rounded-lg p-1.5 {{ $styles['icon_color'] }} {{ $styles['button_hover'] }} focus:outline-hidden {{ $styles['button_focus'] }}"
                        data-hs-remove-element="#{{ $id }}"
                    >
                        <span class="sr-only">Dismiss</span>
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"/>
                            <path d="m6 6 12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
