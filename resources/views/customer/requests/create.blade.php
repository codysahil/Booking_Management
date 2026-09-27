@extends('layouts.customer')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-teal-50 via-cyan-50 to-violet-50 py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-display font-bold text-gray-900 mb-8">New Request</h1>

            <form method="POST" action="{{ route('customer.requests.store') }}" enctype="multipart/form-data"
                x-data="{ type: '{{ old('type', 'service') }}', submitting: false }" @submit="submitting = true">
                @csrf

                <div class="form-card-enter rounded-2xl border-2 border-gray-100 bg-white p-8 shadow-lg">
                    <div class="space-y-5">
                        <x-form.select name="type" label="Request Type" required x-model="type">
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-form.select>

                        <x-form.input name="subject" label="Subject" required />

                        {{-- Both fields share the name "preferred_date" — only one may ever be
                             enabled at a time, or the disabled one's empty value would still be
                             submitted and silently overwrite the other (a CSS-hidden input is
                             not excluded from form submission; a disabled one is). --}}
                        <div x-show="type === 'vacation'" x-cloak>
                            <x-form.input name="preferred_date" id="preferred_date_vacation" label="Move-out Date" type="date"
                                x-bind:disabled="type !== 'vacation'"
                                :hint="'At least ' . setting('notice_period_days', 30) . ' days\' notice is required.'" />
                        </div>
                        <div x-show="type !== 'vacation'" x-cloak>
                            <x-form.input name="preferred_date" id="preferred_date_general" label="Preferred Date" type="date"
                                x-bind:disabled="type === 'vacation'" />
                        </div>

                        <x-form.textarea name="description" label="Description" :rows="4" />
                        <x-form.file-input name="attachment" label="Attachment" accept=".pdf,.jpg,.jpeg,.png" />
                    </div>

                    <div class="mt-8 flex gap-3">
                        <x-form.button label="Submit Request" loading-label="Submitting…" />
                        <x-form.link-button :href="route('customer.requests.index')">Cancel</x-form.link-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
