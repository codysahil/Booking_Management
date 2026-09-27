@extends('layouts.admin')

@section('header', 'New Customer Entry')

@section('content')
    <div class="mb-2 -mt-2 flex items-start justify-between gap-4">
        <p class="text-gray-500">Add a walk-in customer with full details</p>
        <a href="{{ route('admin.customers.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border-2 border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition-all hover:border-gray-300 hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <form action="{{ route('admin.customers.store') }}" method="POST" enctype="multipart/form-data"
        class="space-y-6 mt-6" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        @if ($errors->any())
            <div class="form-card-enter form-field-error rounded-xl border-2 border-rose-200 bg-rose-50 p-4">
                <h3 class="font-bold text-rose-800 mb-2 text-sm">Please fix the following errors:</h3>
                <ul class="list-disc list-inside text-rose-700 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <x-form.card title="Personal Information">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </x-slot:icon>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <x-form.input name="name" label="Full Name" required />
                <x-form.input name="dob" label="Date of Birth" type="date" required />
                <x-form.input name="phone" label="Phone Number" type="tel" required pattern="[0-9]{10}" maxlength="10" />
                <x-form.input name="guardian_phone" label="Guardian/Parent Phone" type="tel" required pattern="[0-9]{10}" maxlength="10" />
                <x-form.input name="email" label="Email (optional)" type="email" />
                <div class="md:col-span-2">
                    <x-form.textarea name="address" label="Permanent Address" :rows="3" required />
                </div>
                <div class="md:col-span-2">
                    <x-form.textarea name="work_details" label="Work/Study Details (optional)" :rows="2" placeholder="e.g. Software Engineer at ABC Company, Student at XYZ College" />
                </div>
            </div>
        </x-form.card>

        <x-form.card title="Document Uploads" :delay="80">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </x-slot:icon>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-form.file-input name="photo" label="Customer Photo *" accept="image/*" hint="A clear passport-size photo (max 2MB)" />
                <x-form.file-input name="id_proof" label="ID Proof *" accept=".pdf,.jpg,.jpeg,.png" hint="Aadhar/PAN/Driving License (max 2MB)" />
            </div>
        </x-form.card>

        <x-form.card title="Booking & Payment Details" :delay="160">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </x-slot:icon>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <x-form.select name="branch_id" id="branch_select" label="Select Branch" required>
                    <option value="">Choose a branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-form.select>

                <x-form.select name="bed_id" id="bed_select" label="Select Bed" required>
                    <option value="">First select a branch</option>
                </x-form.select>

                <x-form.input name="check_in_date" label="Check-in Date" type="date" required :value="old('check_in_date', date('Y-m-d'))" />

                <x-form.select name="stay_type" label="Stay Type" required>
                    <option value="permanent">Permanent</option>
                    <option value="day_basis">Day Basis</option>
                </x-form.select>

                <x-form.input name="advance_amount" label="Advance Amount (₹)" type="number" min="0" step="0.01" required :value="old('advance_amount', 3000)" />

                <x-form.select name="payment_method" label="Payment Method" required>
                    <option value="cash">Cash</option>
                    <option value="upi">UPI</option>
                    <option value="card">Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </x-form.select>
            </div>
        </x-form.card>

        <div class="flex justify-end gap-3">
            <x-form.link-button :href="route('admin.customers.index')">Cancel</x-form.link-button>
            <x-form.button label="Add Customer & Assign Bed" loading-label="Saving…" />
        </div>
    </form>

    <script>
        // Branch and Bed Selection for Walk-ins
        const branches = @json($branches);
        const branchSelect = document.getElementById('branch_select');
        const bedSelect = document.getElementById('bed_select');

        branchSelect.addEventListener('change', function() {
            const branchId = this.value;
            bedSelect.innerHTML = '<option value="">Select a bed</option>';

            if (branchId) {
                const branch = branches.find(b => b.id == branchId);
                if (branch && branch.rooms) {
                    branch.rooms.forEach(room => {
                        if (room.beds && room.beds.length > 0) {
                            room.beds.forEach(bed => {
                                const option = document.createElement('option');
                                option.value = bed.id;
                                option.textContent =
                                    `${room.room_number} - ${bed.bed_number} (₹${bed.monthly_rent}/month)`;
                                bedSelect.appendChild(option);
                            });
                        }
                    });
                }
            }
        });
    </script>
@endsection
