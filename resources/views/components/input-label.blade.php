@props([
    'label' => '',
    'for' => '',
    'hidden' => false
])
<label for="{{ $for }}"
    {{ $attributes->class([
     'block mb-2 text-sm font-medium text-gray-800',
     'sr-only' => $hidden
 ]) }}
>
    {{$label}}
</label>
