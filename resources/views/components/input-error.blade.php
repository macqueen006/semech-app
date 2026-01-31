@props([
    'id' => '',
    'message' => '',
    'for' => '',
])

@if($message)
    <p
        @if($id) id="{{ $id }}" @endif
        @if($for) data-for="{{ $for }}" @endif
        {{ $attributes->class([
            'text-xs text-red-600 mt-2'
        ]) }}
    >
        {{ $message }}
    </p>
@endif
