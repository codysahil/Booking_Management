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
                <form method="POST" action="{{ route('admin.customers.deactivate', $customer) }}" 
                    onsubmit="return confirm('Are you sure you want to deactivate this customer? This will:\n- Mark customer as vacated\n- Free up their bed\n- Disable their login\n\nThis action cannot be undone.')">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        Vacate Customer
                    </button>
                </form>
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

                    <div class="mt-8 w-full">

                    </div>
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

                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Documents</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">ID Proof</label>
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
                                <span class="text-gray-400 text-sm">Not Uploaded</span>
                            @endif
                        </div>
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