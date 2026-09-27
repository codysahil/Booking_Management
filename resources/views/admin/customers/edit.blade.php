@extends('layouts.admin')

@section('header', 'Edit Customer')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.customers.show', $customer) }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Customer Details
            </a>
        </div>

        @if ($errors->any())
            <div class="form-card-enter form-field-error mb-6 rounded-xl border-2 border-rose-200 bg-rose-50 p-4">
                <h3 class="font-bold text-rose-800 mb-2 text-sm">Please fix the following errors:</h3>
                <ul class="list-disc list-inside text-rose-700 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.customers.update', $customer) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')

            <x-form.card title="Personal Information">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </x-slot:icon>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-form.input name="name" label="Full Name" required :value="$customer->name" />
                    <x-form.input name="dob" label="Date of Birth" type="date" required :value="$customer->dob->format('Y-m-d')" />
                    <x-form.input name="phone" label="Phone Number" type="tel" required :value="$customer->phone" />
                    <x-form.input name="guardian_phone" label="Guardian Phone" type="tel" required :value="$customer->guardian_phone" />
                    <x-form.input name="email" label="Email" type="email" :value="$customer->email" />
                    <x-form.textarea name="work_details" label="Work/College Details" :rows="2" :value="$customer->work_details" />
                    <div class="md:col-span-2">
                        <x-form.textarea name="address" label="Address" :rows="3" required :value="$customer->address" />
                    </div>
                </div>
            </x-form.card>

            <x-form.card title="Documents" :delay="80">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </x-slot:icon>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        @if($customer->photo_path)
                            <div class="mb-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Current Photo</p>
                                <img src="{{ $customer->safe_photo_url }}" alt="Current Photo" class="w-full max-w-[10rem] rounded-xl border-2 border-gray-200 shadow-sm">
                            </div>
                        @endif
                        <x-form.file-input name="photo" label="Replace Photo" accept="image/*" hint="Max 2MB" />
                    </div>

                    <div>
                        @if($customer->id_proof_path)
                            <div class="mb-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Current ID Proof</p>
                                @if(str_ends_with($customer->id_proof_path, '.pdf'))
                                    <a href="{{ $customer->safe_id_proof_url }}" target="_blank"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-700 rounded-xl text-sm font-medium hover:bg-rose-100 transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path></svg>
                                        View Current PDF
                                    </a>
                                @else
                                    <img src="{{ $customer->safe_id_proof_url }}" alt="Current ID Proof" class="w-full max-w-[10rem] rounded-xl border-2 border-gray-200 shadow-sm">
                                @endif
                            </div>
                        @endif
                        <x-form.file-input name="id_proof" label="Replace ID Proof" accept="image/*,.pdf" hint="Max 2MB" />
                    </div>
                </div>
            </x-form.card>

            <div class="flex justify-end gap-3">
                <x-form.link-button :href="route('admin.customers.show', $customer)">Cancel</x-form.link-button>
                <x-form.button label="Update Customer" loading-label="Saving…" />
            </div>
        </form>
    </div>
@endsection
