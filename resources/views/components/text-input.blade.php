@props([
    'id' => '',
    'type' => 'text',
    'placeholder' => '',
    'value' => '',
    'name' => '',
    'required' => false,
    'disabled' => false,
])

<input
    @if($id) id="{{ $id }}" @endif
    @if($name) name="{{ $name }}" @endif
    type="{{ $type }}"
    value="{{ old($name, $value) }}"
    placeholder="{{ $placeholder }}"
    @if($required) required @endif
    @if($disabled) disabled @endif
    {{ $attributes->class([
        'py-2.5 sm:py-3 px-4 rounded-lg block w-full bg-layer border-gray-200 sm:text-sm text-foreground placeholder:text-muted-foreground-1 focus:border-primary-focus focus:ring-primary-focus disabled:opacity-50 disabled:pointer-events-none'
    ]) }}
>
