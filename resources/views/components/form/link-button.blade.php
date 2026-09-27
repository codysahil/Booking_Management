@props(['href'])

<a href="{{ $href }}"
    {{ $attributes->class([
        'inline-flex items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-white px-6 py-2.5 text-sm font-bold text-gray-600',
        'transition-all duration-200 ease-out hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900',
    ]) }}
>
    {{ $slot }}
</a>
