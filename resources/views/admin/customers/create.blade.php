@extends('layouts.admin')

@section('content')
    <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $booking ? 'Complete Check-In' : 'New Customer Entry' }}
                </h1>
                <p class="text-gray-600 mt-1">
                    {{ $booking ? 'Complete customer details and collect documents' : 'Add walk-in customer with full details' }}
                </p>
            </div>
            <a href="{{ route('admin.customers.index') }}"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Back
            </a>
        </div>

        @if ($booking)
            <!-- Booking Info Card -->
            <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6 mb-6">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-blue-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-blue-900 mb-2">Online Booking Details</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <p class="text-blue-600 font-medium">Booking Ref</p>
                                <p class="text-blue-900 font-bold">{{ $booking->booking_reference }}</p>
                            </div>
                            <div>
                                <p class="text-blue-600 font-medium">Customer ID</p>
                                <p class="text-blue-900 font-bold">{{ $booking->customer->customer_code }}</p>
                            </div>
                            <div>
                                <p class="text-blue-600 font-medium">Branch</p>
                                <p class="text-blue-900 font-bold">{{ $booking->bed->room->branch->name }}</p>
                            </div>
                            <div>
                                <p class="text-blue-600 font-medium">Bed</p>
                                <p class="text-blue-900 font-bold">{{ $booking->bed->room->room_number }} •
                                    {{ $booking->bed->bed_number }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Customer Entry Form -->
        <form action="{{ route('admin.customers.store') }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">
            @csrf

            @if ($booking)
                <input type="hidden" name="customer_id" value="{{ $booking->customer_id }}">
                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                <input type="hidden" name="bed_id" value="{{ $booking->bed_id }}">
            @endif

            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Personal Information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Full Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="name" required
                            value="{{ old('name', $booking->customer->name ?? '') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                        <input type="date" name="dob" required value="{{ old('dob') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('dob')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                        <input type="tel" name="phone" required pattern="[0-9]{10}" maxlength="10"
                            value="{{ old('phone', $booking->customer->phone ?? '') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('phone')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Guardian Phone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Guardian/Parent Phone *</label>
                        <input type="tel" name="guardian_phone" required pattern="[0-9]{10}" maxlength="10"
                            value="{{ old('guardian_phone') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('guardian_phone')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email (Optional)</label>
                        <input type="email" name="email" value="{{ old('email', $booking->customer->email ?? '') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('email')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Permanent Address *</label>
                        <textarea name="address" rows="3" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('address', $booking->customer->address ?? '') }}</textarea>
                        @error('address')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Work Details -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Work/Study Details (Optional)</label>
                        <textarea name="work_details" rows="2" placeholder="e.g., Software Engineer at ABC Company, Student at XYZ College"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('work_details') }}</textarea>
                        @error('work_details')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Document Uploads -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                        </path>
                    </svg>
                    Document Uploads
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Photo Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer Photo *</label>
                        <input type="file" name="photo" required accept="image/*"
                            class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-xs text-gray-500 mt-2">Upload a clear passport-size photo (Max: 2MB)</p>
                        @error('photo')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- ID Proof Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Proof *</label>
                        <input type="file" name="id_proof" required accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p class="text-xs text-gray-500 mt-2">Aadhar/PAN/Driving License (Max: 2MB)</p>
                        @error('id_proof')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                    Booking & Payment Details
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if (!$booking)
                        <!-- Branch Selection (only for walk-ins) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Branch *</label>
                            <select name="branch_id" id="branch_select" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Choose a branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bed Selection (only for walk-ins) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Bed *</label>
                            <select name="bed_id" id="bed_select" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">First select a branch</option>
                            </select>
                        </div>
                    @endif

                    <!-- Check-in Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date *</label>
                        <input type="date" name="check_in_date" required
                            value="{{ old('check_in_date', $booking->check_in_date ?? date('Y-m-d')) }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('check_in_date')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Stay Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Stay Type *</label>
                        <select name="stay_type" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="permanent">Permanent</option>
                            <option value="day_basis">Day Basis</option>
                        </select>
                        @error('stay_type')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Advance Amount -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Advance Amount (₹) *</label>
                        <input type="number" name="advance_amount" required min="0" step="0.01"
                            value="{{ old('advance_amount', 3000) }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('advance_amount')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                        <select name="payment_method" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('admin.customers.index') }}"
                    class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-primary-600 to-secondary-600 text-white rounded-lg font-bold hover:from-primary-700 hover:to-secondary-700 transition shadow-lg">
                    {{ $booking ? 'Complete Check-In' : 'Add Customer & Assign Bed' }}
                </button>
            </div>
        </form>
    </div>

    @if (!$booking)
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
    @endif
@endsection
