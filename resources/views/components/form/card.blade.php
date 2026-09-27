@props(['title' => null, 'description' => null, 'icon' => null, 'delay' => 0, 'accent' => 'primary'])

@php
    $iconColors = $accent === 'slate' ? 'bg-slate-100 text-slate-700' : 'bg-primary-50 text-primary-600';
@endphp

<div
    {{ $attributes->class(['form-card-enter rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8']) }}
    style="animation-delay: {{ $delay }}ms"
>
    @if ($title)
        <div class="mb-6 flex items-start gap-3">
            @if ($icon)
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl {{ $iconColors }}">
                    {{ $icon }}
                </div>
            @endif
            <div>
                <h2 class="font-display text-lg font-bold text-gray-900">{{ $title }}</h2>
                @if ($description)
                    <p class="mt-0.5 text-sm text-gray-500">{{ $description }}</p>
                @endif
            </div>
        </div>
    @endif

    {{ $slot }}
</div>
