@props(['label' => 'Save', 'loadingLabel' => 'Saving…', 'accent' => 'primary'])

@php
    $colors = $accent === 'slate'
        ? ['bg-slate-900 shadow-slate-900/20', 'hover:bg-slate-800 hover:shadow-lg hover:shadow-slate-900/30 hover:-translate-y-0.5']
        : ['bg-gradient-to-r from-teal-500 via-cyan-500 to-teal-600 shadow-md shadow-teal-500/20', 'hover:shadow-lg hover:shadow-teal-500/30 hover:-translate-y-0.5'];
@endphp

<button
    type="submit"
    :disabled="typeof submitting !== 'undefined' && submitting"
    {{ $attributes->class([
        'group relative inline-flex items-center justify-center gap-2 rounded-xl px-6 py-2.5 text-sm font-bold text-white',
        $colors[0],
        'transition-all duration-200 ease-out',
        $colors[1],
        'active:translate-y-0 active:shadow-sm',
        'disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-md',
    ]) }}
>
    <svg x-show="typeof submitting !== 'undefined' && submitting" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span x-text="(typeof submitting !== 'undefined' && submitting) ? '{{ $loadingLabel }}' : '{{ $label }}'">{{ $label }}</span>
</button>
