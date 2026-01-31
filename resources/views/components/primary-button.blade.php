<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-secondary border border-gray-200 text-white hover:bg-secondary focus:outline-hidden focus:bg-primary-focus disabled:opacity-50 disabled:pointer-events-none'
]) }}>
    {{ $slot }}
</button>
