@extends('layouts.admin')

@section('header', 'Generate Monthly Charges')

@section('content')
<div class="max-w-2xl mx-auto">
    <form method="POST" action="{{ route('admin.charges.generate') }}" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <x-form.card title="Generate Charges" description="Creates rent charges for all active customers, taken from each resident's bed's monthly rent. Add EB or other charges later by editing individual charges.">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </x-slot:icon>

            <x-form.input name="month" label="Select Month" type="month" :value="$month" required hint="Charges will be generated for this month." />

            <div class="mt-5 flex items-start gap-3 rounded-xl border-2 border-amber-200 bg-amber-50 p-4">
                <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-amber-800">Note</p>
                    <p class="text-sm text-amber-700 mt-0.5">If charges already exist for this month, they'll be skipped.</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-form.link-button :href="route('admin.charges.index')">Cancel</x-form.link-button>
                <x-form.button label="Generate Charges" loading-label="Generating…" />
            </div>
        </x-form.card>
    </form>
</div>
@endsection
