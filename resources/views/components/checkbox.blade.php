@props([
    'id' => '',
    'name' => '',
    'value' => '1',
    'checked' => false,
    'label' => '',
    'disabled' => false,
])

<label
    @if($id) for="{{ $id }}" @endif
    {{ $attributes->merge(['class' => 'flex items-center w-full text-sm']) }}
>
    <input
        type="checkbox"
        @if($id) id="{{ $id }}" @endif
        @if($name) name="{{ $name }}" @endif
        value="{{ $value }}"
        @if($checked || old($name)) checked @endif
        @if($disabled) disabled @endif
        class="shrink-0 size-4 bg-transparent border-line-3 rounded-sm shadow-2xs text-primary focus:ring-0 focus:ring-offset-0 checked:bg-secondary checked:border-secondary disabled:opacity-50 disabled:pointer-events-none"
    >
    <span class="text-sm ms-3 text-muted-foreground-1">{{ $label }}</span>
</label>
