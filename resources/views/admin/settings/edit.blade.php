@extends('layouts.admin')

@section('header', 'Settings')

@section('content')
    <div class="max-w-3xl mx-auto">
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Branding</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Hostel Name *</label>
                        <input type="text" name="hostel_name" required value="{{ old('hostel_name', $settings['hostel_name']) }}"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tagline</label>
                        <input type="text" name="tagline" value="{{ old('tagline', $settings['tagline']) }}"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Logo</label>
                        @if($settings['logo_path'] ?? null)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($settings['logo_path']) }}" class="h-12 mb-2 rounded">
                        @endif
                        <input type="file" name="logo" accept="image/*" class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Contact</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">WhatsApp</label>
                        <input type="text" name="contact_whatsapp" value="{{ old('contact_whatsapp', $settings['contact_whatsapp']) }}"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
                        <input type="text" name="contact_address" value="{{ old('contact_address', $settings['contact_address']) }}"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Billing Rules</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Minimum Advance (₹) *</label>
                        <input type="number" step="0.01" name="min_advance" required value="{{ old('min_advance', $settings['min_advance']) }}"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Rent Due Day (of month) *</label>
                        <input type="number" min="1" max="28" name="rent_due_day" required value="{{ old('rent_due_day', $settings['rent_due_day']) }}"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Late Fee (₹)</label>
                        <input type="number" step="0.01" name="late_fee" value="{{ old('late_fee', $settings['late_fee']) }}"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Vacation Notice Period (days) *</label>
                        <input type="number" min="0" name="notice_period_days" required value="{{ old('notice_period_days', $settings['notice_period_days']) }}"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Receipts & Policies</h3>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">GSTIN</label>
                        <input type="text" name="gstin" value="{{ old('gstin', $settings['gstin']) }}"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Receipt Footer</label>
                        <input type="text" name="receipt_footer" value="{{ old('receipt_footer', $settings['receipt_footer']) }}"
                            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Terms & Conditions</label>
                        <textarea name="terms" rows="8" class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">{{ old('terms', $settings['terms']) }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Shown on the public /terms page and required at checkout.</p>
                    </div>
                </div>
            </div>

            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-xl hover:from-teal-600 hover:to-cyan-600 transition shadow-lg">
                Save Settings
            </button>
        </form>
    </div>
@endsection
