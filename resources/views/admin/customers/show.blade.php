@extends('layouts.admin')

@section('header', 'Customer Details')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.customers.index') }}" class="text-gray-500 hover:text-gray-700 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Back to Customers
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-8">
            <div class="md:flex">
                <div class="md:w-1/3 bg-gray-50 p-8 border-r border-gray-100 flex flex-col items-center text-center">
                    <div class="relative mb-4">
                        @if($customer->photo_path)
                            <img class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-md"
                                src="{{ Storage::url($customer->photo_path) }}" alt="{{ $customer->name }}">
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
                        <a href="#"
                            class="block w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none">
                            Edit Profile
                        </a>
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
                                    {{ $booking->check_in_date->format('d M, Y') }}</p>
                            </div>
                        @else
                            <div class="col-span-2 text-red-500 font-medium">No active booking found.</div>
                        @endif
                    </div>

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
                                <a href="{{ Storage::url($customer->id_proof_path) }}" target="_blank"
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
    </div>
@endsection