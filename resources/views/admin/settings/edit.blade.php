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

            <x-form.card title="Payment Gateway" description="Connect your own Razorpay account so resident rent payments land directly in your bank account" :delay="180">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </x-slot:icon>

                <div class="mb-5 flex items-start gap-3 rounded-xl border-2 border-blue-100 bg-blue-50 p-4">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-blue-900">
                        <strong>Important:</strong> until you connect your own Razorpay account, any online rent payments residents make may be collected through the platform's own account instead of yours. Connect your keys from
                        <a href="https://dashboard.razorpay.com/app/keys" target="_blank" rel="noopener" class="underline font-medium">Settings → API Keys</a>
                        in your own Razorpay dashboard so payments land directly in your bank account.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-form.input name="razorpay_key_id" label="Key ID" :value="$settings['razorpay_key_id']" placeholder="rzp_live_••••••••••" />
                    <div>
                        <x-form.input name="razorpay_key_secret" label="Key Secret" type="password" placeholder="{{ $hasRazorpaySecret ? '•••••••••••••••• (configured)' : 'rzp_live_••••••••••' }}"
                            :hint="$hasRazorpaySecret ? 'A secret is already saved — leave blank to keep it, or type a new one to replace it.' : 'Never shown again after saving, for security — only whether one is set.'" />
                    </div>
                </div>
            </x-form.card>

            <x-form.card title="Receipts & Policies" :delay="240">
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
