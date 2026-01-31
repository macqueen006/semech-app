@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600']) }}>
        {{ $status }}
    </div>
@endif

@props([
    'type' => 'success',
])

@php
    $classes = match($type) {
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'error' => 'bg-red-50 border-red-200 text-red-800',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'info' => 'bg-blue-50 border-blue-200 text-blue-800',
        default => 'bg-gray-50 border-gray-200 text-gray-800',
    };
@endphp

<div {{ $attributes->merge(['class' => "border rounded-lg p-4 text-sm {$classes}"]) }}>
    {{ $slot }}
</div>
