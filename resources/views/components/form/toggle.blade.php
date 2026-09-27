@props(['name', 'label' => null, 'checked' => false, 'hint' => null])

@php
    $isChecked = old($name, $checked ? '1' : null) == '1' || old($name) === 'on';
@endphp

<label for="{{ $name }}" class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border-2 border-gray-200 px-4 py-3 transition-colors hover:border-gray-300">
    <span>
        @if ($label)
            <span class="block text-sm font-semibold text-gray-700">{{ $label }}</span>
        @endif
        @if ($hint)
            <span class="block text-xs text-gray-500 mt-0.5">{{ $hint }}</span>
        @endif
    </span>

    <span class="relative inline-flex flex-shrink-0">
        <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="1" {{ $isChecked ? 'checked' : '' }}
            {{ $attributes->class(['peer sr-only']) }}>
        <span class="h-6 w-11 rounded-full bg-gray-200 transition-colors duration-200 ease-out peer-checked:bg-teal-500 peer-focus-visible:ring-4 peer-focus-visible:ring-teal-500/20"></span>
        <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200 ease-out peer-checked:translate-x-5"></span>
    </span>
</label>
