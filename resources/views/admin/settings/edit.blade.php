@extends('layouts.admin')

@section('header', 'Settings')

@section('content')
    <div class="max-w-3xl mx-auto">
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data"
            class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')

            <x-form.card title="Branding" description="How your hostel appears across the app">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485"/></svg>
                </x-slot:icon>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-form.input name="hostel_name" label="Hostel Name" required :value="$settings['hostel_name']" />
                    <x-form.input name="tagline" label="Tagline" :value="$settings['tagline']" />
                    <div class="md:col-span-2">
                        @if($settings['logo_path'] ?? null)
                            <div class="mb-2">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($settings['logo_path']) }}" class="h-12 rounded-lg border border-gray-200 p-1">
                            </div>
                        @endif
                        <x-form.file-input name="logo" label="Logo" accept="image/*" />
                    </div>
                </div>
            </x-form.card>

            <x-form.card title="Contact" :delay="60">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </x-slot:icon>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-form.input name="contact_phone" label="Phone" :value="$settings['contact_phone']" />
                    <x-form.input name="contact_email" label="Email" type="email" :value="$settings['contact_email']" />
                    <x-form.input name="contact_whatsapp" label="WhatsApp" :value="$settings['contact_whatsapp']"
                        hint="Shown as a chat button to residents and used for rent-reminder links." />
                    <x-form.input name="contact_address" label="Address" :value="$settings['contact_address']" />
                </div>
            </x-form.card>

            <x-form.card title="Billing Rules" :delay="120">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </x-slot:icon>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-form.input name="rent_due_day" label="Rent Due Day (of month)" type="number" min="1" max="28" required :value="$settings['rent_due_day']" />
                    <x-form.input name="late_fee" label="Late Fee (₹)" type="number" step="0.01" :value="$settings['late_fee']" />
                    <x-form.input name="notice_period_days" label="Vacation Notice Period (days)" type="number" min="0" required :value="$settings['notice_period_days']" />
                </div>
            </x-form.card>

            <x-form.card title="Receipts & Policies" :delay="180">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </x-slot:icon>

                <div class="space-y-5">
                    <x-form.input name="gstin" label="GSTIN" :value="$settings['gstin']" />
                    <x-form.input name="receipt_footer" label="Receipt Footer" :value="$settings['receipt_footer']" />
                    <x-form.textarea name="terms" label="Terms & Conditions" :rows="8" :value="$settings['terms']"
                        hint="Shown on the public /terms page and required at checkout." />
                </div>
            </x-form.card>

            <div class="flex justify-end">
                <x-form.button label="Save Settings" loading-label="Saving…" />
            </div>
        </form>
    </div>
@endsection
