@extends('layouts.admin')

@section('header', 'Customer Details')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('admin.customers.index') }}" class="text-gray-500 hover:text-gray-700 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Back to Customers
            </a>
            <div class="flex gap-3">
                <a href="{{ route('admin.customers.edit', $customer) }}"
                    class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                    Edit Customer
                </a>
                @if($customer->is_active)
                @php $activeBooking = $customer->bookings->firstWhere('status', 'active'); @endphp
                <div x-data="{
                    open: {{ $errors->hasAny(['deposit_deduction_amount', 'deposit_deduction_reason']) ? 'true' : 'false' }},
                    deposit: {{ (float) ($activeBooking->advance_paid ?? 0) }},
                    outstanding: {{ (float) $pendingAmount }},
                    deduction: {{ (float) old('deposit_deduction_amount', 0) }},
                    get refund() { return Math.max(this.deposit - this.outstanding - (parseFloat(this.deduction) || 0), 0) },
                }">
                    <button type="button" @click="open = true"
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        Vacate Customer
                    </button>

                    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-transition.opacity>
                        <div @click.outside="open = false" x-show="open" x-transition
                            class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                            <h3 class="font-display text-lg font-bold text-gray-900">Vacate {{ $customer->name }}</h3>
                            <p class="mt-1 text-sm text-gray-500">This frees their bed and disables their login. Settle their security deposit below — this cannot be undone.</p>

                            <form method="POST" action="{{ route('admin.customers.deactivate', $customer) }}" class="mt-5 space-y-4">
                                @csrf
                                @method('PATCH')

                                <div class="space-y-2 rounded-xl bg-gray-50 p-4 text-sm">
                                    <div class="flex justify-between"><span class="text-gray-500">Deposit paid</span><span class="font-semibold text-gray-900">₹<span x-text="deposit.toLocaleString('en-IN')"></span></span></div>
                                    <div class="flex justify-between"><span class="text-gray-500">Outstanding dues &amp; charges</span><span class="font-semibold text-rose-600">− ₹<span x-text="outstanding.toLocaleString('en-IN')"></span></span></div>
                                </div>

                                <x-form.input name="deposit_deduction_amount" label="Deduction (damage, cleaning, etc.)" type="number" min="0" step="0.01" x-model="deduction" placeholder="0" />
                                <x-form.textarea name="deposit_deduction_reason" label="Deduction reason" :rows="2" placeholder="Only needed if deducting something" />

                                <div class="flex items-center justify-between rounded-xl bg-teal-50 p-4">
                                    <span class="text-sm font-semibold text-gray-700">Refund due to resident</span>
                                    <span class="text-xl font-bold text-teal-700">₹<span x-text="refund.toLocaleString('en-IN')"></span></span>
                                </div>

                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" @click="open = false" class="rounded-xl border-2 border-gray-200 bg-white px-5 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-50">Cancel</button>
                                    <button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700">Confirm &amp; Vacate</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <span class="inline-flex items-center px-4 py-2 bg-gray-400 text-white rounded-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                        </path>
                    </svg>
                    Deactivated
                </span>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-8">
            <div class="md:flex">
                <div class="md:w-1/3 bg-gray-50 p-8 border-r border-gray-100 flex flex-col items-center text-center">
                    <div class="relative mb-4">
                        @if($customer->photo_path)
                            <img class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-md"
                                src="{{ $customer->safe_photo_url }}" alt="{{ $customer->name }}">
                        @else
                            <div
                                class="w-32 h-32 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold text-4xl border-4 border-white shadow-md">
                                {{ substr($customer->name, 0, 1) }}
                            </div>
                        @endif
                        <span
                            class="absolute bottom-1 right-1 bg-green-500 w-5 h-5 rounded-full border-2 border-white"></span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h2>
                    <p class="text-gray-500 font-medium">{{ $customer->customer_code }}</p>

                    <div class="mt-6 w-full space-y-3">
                        <div class="flex items-center text-sm text-gray-600 justify-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                            {{ $customer->phone }}
                        </div>
                        <div class="flex items-center text-sm text-gray-600 justify-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            {{ $customer->email }}
                        </div>
                    </div>

                    @php
                        $reminderMessage = $pendingAmount > 0
                            ? "Hi {$customer->name}, this is a reminder that ₹" . number_format($pendingAmount) . " is due for your stay at " . setting('hostel_name') . ". Please clear it at your earliest convenience. Thank you!"
                            : "Hi {$customer->name}, this is " . setting('hostel_name') . ".";
                        $customerWaLink = whatsapp_link($customer->phone, $reminderMessage);
                    @endphp
                    @if ($customerWaLink)
                        <div class="mt-8 w-full">
                            <a href="{{ $customerWaLink }}" target="_blank" rel="noopener"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#25D366] text-white rounded-lg font-medium text-sm hover:opacity-90 transition">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.9 9.9 0 004.74 1.21h.005c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.87 9.87 0 0012.04 2zm5.8 14.05c-.24.68-1.4 1.3-1.93 1.38-.5.08-1.12.11-1.8-.11-.42-.13-.96-.31-1.65-.6-2.9-1.25-4.79-4.17-4.94-4.36-.14-.2-1.18-1.57-1.18-3 0-1.42.75-2.12 1.02-2.41.27-.29.58-.36.78-.36.2 0 .39 0 .56.01.18.01.42-.07.66.5.24.58.83 2 .9 2.14.07.15.12.32.02.52-.1.2-.15.32-.3.49-.14.17-.3.38-.43.5-.15.15-.3.31-.13.6.17.3.75 1.24 1.62 2.01 1.11.99 2.05 1.3 2.35 1.44.3.15.47.13.65-.07.18-.2.75-.87.95-1.17.2-.3.4-.25.66-.15.27.1 1.7.8 1.99.95.29.15.48.22.55.34.07.13.07.72-.17 1.4z"/>
                                </svg>
                                {{ $pendingAmount > 0 ? 'Send Rent Reminder' : 'Message on WhatsApp' }}
                            </a>
                        </div>
                    @endif
                </div>

                <div class="md:w-2/3 p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Accommodation Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        @if($customer->bookings->isNotEmpty())
                            @php $booking = $customer->bookings->first(); @endphp
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</label>
                                <p class="mt-1 text-sm font-semibold text-gray-900">{{ $booking->bed->room->branch->name }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Room</label>
                                <p class="mt-1 text-sm font-semibold text-gray-900">Room {{ $booking->bed->room->room_number }}
                                    ({{ $booking->bed->room->type }})</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Bed</label>
                                <p class="mt-1 text-sm font-semibold text-gray-900">Bed {{ $booking->bed->bed_number }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Monthly
                                    Rent</label>
                                <p class="mt-1 text-sm font-semibold text-primary-600">
                                    ₹{{ number_format($booking->bed->monthly_rent) }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in
                                    Date</label>
                                <p class="mt-1 text-sm font-semibold text-gray-900">
                                    {{ $booking->check_in_date->format('d M, Y') }}
                                </p>
                            </div>
                        @else
                            <div class="col-span-2 text-red-500 font-medium">No active booking found.</div>
                        @endif
                    </div>

                    @if(($booking ?? null)?->settled_at)
                        <div class="mb-8 rounded-xl border-2 border-gray-100 bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Deposit Settlement — {{ $booking->settled_at->format('d M, Y') }}</p>
                            <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                                <div><span class="text-gray-500">Deposit</span><p class="font-semibold text-gray-900">₹{{ number_format($booking->advance_paid) }}</p></div>
                                <div><span class="text-gray-500">Deducted</span><p class="font-semibold text-rose-600">₹{{ number_format($booking->deposit_deduction_amount ?? 0) }}</p></div>
                                <div class="col-span-2 sm:col-span-1"><span class="text-gray-500">Refunded</span><p class="font-semibold text-teal-700">₹{{ number_format($booking->deposit_refund_amount ?? 0) }}</p></div>
                                @if($booking->deposit_deduction_reason)
                                    <div class="col-span-2 sm:col-span-4"><span class="text-gray-500">Reason</span><p class="text-gray-700">{{ $booking->deposit_deduction_reason }}</p></div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($customer->bookings->isNotEmpty())
                        <!-- Rent Increase Section -->
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-8">
                            <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                                Update Rent Amount
                            </h4>
                            <form method="POST" action="{{ route('admin.customers.update-rent', $customer) }}"
                                class="space-y-3">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">New Rent Amount</label>
                                        <input type="number" name="new_rent" step="0.01" required
                                            value="{{ $booking->bed->monthly_rent }}"
                                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Effective From
                                            (Month)</label>
                                        <input type="month" name="effective_from" required
                                            value="{{ now()->addMonth()->format('Y-m') }}"
                                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Reason (Optional)</label>
                                        <input type="text" name="reason" placeholder="e.g., Annual increase"
                                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                                    </div>
                                </div>
                                <button type="submit"
                                    class="w-full md:w-auto px-4 py-2 bg-amber-600 text-white text-sm rounded-lg hover:bg-amber-700 transition">
                                    Update Rent
                                </button>
                            </form>
                        </div>

                        <!-- View Charges Link -->
                        <div class="mb-8">
                            <a href="{{ route('admin.customers.charges', $customer) }}"
                                class="inline-flex items-center px-4 py-2 bg-primary-100 text-primary-700 rounded-lg hover:bg-primary-200 transition text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                    </path>
                                </svg>
                                View All Monthly Charges & Payment History
                            </a>
                        </div>
                    @endif

                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Date of
                                Birth</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $customer->dob->format('d M, Y') }}</p>
                        </div>
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Work/College</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $customer->work_details ?? 'N/A' }}</p>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Permanent
                                Address</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $customer->address }}</p>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Guardian Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Guardian
                                Phone</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $customer->guardian_phone }}</p>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">ID Proof &amp; Police Verification</h3>
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded Document</label>
                        @if($customer->id_proof_path)
                            <a href="{{ $customer->safe_id_proof_url }}" target="_blank"
                                class="mt-2 inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                                View Document
                            </a>
                        @else
                            <span class="text-gray-400 text-sm block mt-1">Not Uploaded</span>
                        @endif
                    </div>

                    <div class="rounded-xl border-2 border-gray-100 bg-gray-50 p-4">
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $customer->verification_status_color }}">
                                {{ $customer->verification_status_label }}
                            </span>
                            <a href="{{ route('admin.customers.police-verification.print', $customer) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 hover:text-primary-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 00-1 1v4a1 1 0 001 1zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Verification Form
                            </a>
                        </div>

                        <form method="POST" action="{{ route('admin.customers.police-verification.update', $customer) }}" class="space-y-4">
                            @csrf
                            @method('PATCH')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-form.select name="id_proof_type" label="ID Proof Type">
                                    <option value="">Select type</option>
                                    @foreach (\App\Models\Customer::ID_PROOF_TYPES as $type)
                                        <option value="{{ $type }}" @selected(old('id_proof_type', $customer->id_proof_type) === $type)>{{ $type }}</option>
                                    @endforeach
                                </x-form.select>
                                <x-form.input name="id_proof_number" label="ID Proof Number" :value="$customer->id_proof_number" placeholder="e.g. XXXX-XXXX-XXXX" />
                            </div>
                            <x-form.select name="police_verification_status" label="Verification Status" required>
                                @foreach (\App\Models\Customer::VERIFICATION_STATUSES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('police_verification_status', $customer->police_verification_status) === $value)>{{ $label }}</option>
                                @endforeach
                            </x-form.select>
                            <x-form.textarea name="police_verification_notes" label="Notes" :rows="2" placeholder="e.g. Station name, form reference number">{{ old('police_verification_notes', $customer->police_verification_notes) }}</x-form.textarea>
                            <x-form.button label="Save Verification Details" loading-label="Saving…" />
                        </form>

                        @if($customer->police_verification_submitted_at || $customer->police_verification_verified_at)
                            <p class="text-xs text-gray-400 mt-4 pt-4 border-t border-gray-200">
                                @if($customer->police_verification_submitted_at)
                                    Submitted {{ $customer->police_verification_submitted_at->format('d M, Y') }}.
                                @endif
                                @if($customer->police_verification_verified_at)
                                    Verified {{ $customer->police_verification_verified_at->format('d M, Y') }}.
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Dues (fines, EB, damage, other) -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Dues</h3>

            <form method="POST" action="{{ route('admin.dues.store', $customer) }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Type</label>
                    <select name="due_type" required class="w-full text-sm border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                        @foreach (\App\Models\Due::TYPES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" required placeholder="e.g. May EB bill"
                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Amount (₹)</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required
                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Due Date</label>
                    <input type="date" name="due_date" required value="{{ now()->addDays(7)->format('Y-m-d') }}"
                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 transition">
                    Add Due
                </button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Title</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Due Date</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Amount</th>
                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-2 text-right text-xs font-bold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($customer->dues as $due)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $due->title }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $due->type_label }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $due->due_date->format('d M, Y') }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">₹{{ number_format($due->amount, 2) }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $due->isPaid() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($due->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm space-x-2">
                                    @if($due->isPending())
                                        <form action="{{ route('admin.dues.mark-paid', $due) }}" method="POST" class="inline-flex items-center gap-2">
                                            @csrf @method('PATCH')
                                            <select name="payment_method" class="text-xs border-gray-300 rounded-lg">
                                                <option value="cash">Cash</option>
                                                <option value="upi">UPI</option>
                                                <option value="bank_transfer">Bank transfer</option>
                                                <option value="card">Card</option>
                                            </select>
                                            <button type="submit" class="text-green-600 hover:text-green-800 font-medium">Mark Paid</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.dues.destroy', $due) }}" method="POST" class="inline" onsubmit="return confirm('Delete this due?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500 text-sm">No dues on this account.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection