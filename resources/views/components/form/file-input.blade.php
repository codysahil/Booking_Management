@props(['name', 'label' => null, 'hint' => null, 'multiple' => false, 'accept' => 'image/*'])

@php
    $hasError = $errors->has($name);
@endphp

<div x-data="{ files: [] }">
    @if ($label)
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ $label }}</label>
    @endif

    <label for="{{ $name }}"
        class="group flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-4 py-6 text-center transition-all duration-200"
        :class="files.length ? 'border-teal-300 bg-teal-50/50' : '{{ $hasError ? 'border-rose-300 bg-rose-50/30' : 'border-gray-200 hover:border-teal-300 hover:bg-teal-50/30' }}'"
    >
        <svg class="h-8 w-8 text-gray-400 transition-transform duration-200 group-hover:scale-110 group-hover:text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M12 12v9m0-9l-3 3m3-3l3 3"></path>
        </svg>
        <span class="text-sm font-medium text-gray-600">
            <span class="text-teal-600" x-show="!files.length">Click to upload</span>
            <template x-if="files.length">
                <span x-text="files.length + (files.length === 1 ? ' file selected' : ' files selected')"></span>
            </template>
            <span x-show="!files.length"> or drag and drop</span>
        </span>
        <input type="file" name="{{ $name }}" id="{{ $name }}" accept="{{ $accept }}" @if($multiple) multiple @endif
            {{ $attributes->class(['sr-only']) }}
            @change="files = Array.from($event.target.files).map(f => f.name)"
        >
    </label>

    <template x-if="files.length">
        <ul class="mt-2 space-y-1">
            <template x-for="name in files" :key="name">
                <li class="flex items-center gap-1.5 text-xs text-gray-600">
                    <svg class="h-3.5 w-3.5 flex-shrink-0 text-teal-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
                    <span x-text="name" class="truncate"></span>
                </li>
            </template>
        </ul>
    </template>

    @if ($hint && !$hasError)
        <p class="mt-1.5 text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="form-field-error mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
    @enderror
</div>
