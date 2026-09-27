@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'required' => false,
    'hint' => null,
    'icon' => null,
    'id' => null,
    'accent' => 'primary',
])

@php
    $hasError = $errors->has($name);
    $current = old($name, $value);
    $fieldId = $id ?? $name;
    $focusRing = $accent === 'slate'
        ? 'border-gray-200 focus:border-slate-500 focus:ring-4 focus:ring-slate-500/10 hover:border-gray-300'
        : 'border-gray-200 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 hover:border-gray-300';
@endphp

<div>
    @if ($label)
        <label for="{{ $fieldId }}" class="block text-sm font-semibold text-gray-700 mb-1.5">
            {{ $label }} @if($required)<span class="text-rose-500">*</span>@endif
        </label>
    @endif

    <div class="relative">
        @if ($icon)
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                {{ $icon }}
            </div>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $fieldId }}"
            value="{{ $current }}"
            @if($required) required @endif
            {{ $attributes->class([
                'block w-full rounded-xl border-2 bg-white text-gray-900 shadow-sm transition-all duration-200 sm:text-sm',
                'py-2.5' => !$icon,
                'py-2.5 pl-10' => $icon,
                'px-4' => !$icon,
                'pr-4' => $icon,
                'border-rose-300 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10' => $hasError,
                $focusRing => !$hasError,
            ]) }}
        >
    </div>

    @if ($hint && !$hasError)
        <p class="mt-1.5 text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="form-field-error mt-1.5 text-xs font-medium text-rose-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
