@extends('layouts.customer')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Back Button -->
            <div class="mb-6">
                <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center text-gray-600 hover:text-primary-600 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Dashboard
                </a>
            </div>

            <!-- Booking Header -->
            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-display font-bold text-gray-900 mb-2">{{ $booking->bed->room->branch->name }}</h1>
                        <p class="text-gray-600">Booking Reference: <span class="font-bold text-primary-600">{{ $booking->booking_reference }}</span></p>
                    </div>
                    <div>
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'paid' => 'bg-blue-100 text-blue-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <span class="px-4 py-2 rounded-full text-sm font-bold {{ $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Accommodation Details -->
                <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Accommodation Details
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm text-gray-500">Branch</label>
                            <p class="font-semibold text-gray-900">{{ $booking->bed->room->branch->name }}</p>
                            <p class="text-sm text-gray-600">{{ $booking->bed->room->branch->address }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-500">Room Number</label>
                                <p class="font-semibold text-gray-900">{{ $booking->bed->room->room_number }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Room Type</label>
                                <p class="font-semibold text-gray-900">{{ $booking->bed->room->type }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Bed Number</label>
                            <p class="font-semibold text-gray-900">Bed {{ $booking->bed->bed_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Payment Details
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm text-gray-500">Monthly Rent</label>
                            <p class="text-2xl font-bold text-primary-600">₹{{ number_format($booking->bed->monthly_rent) }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Advance Paid</label>
                            <p class="text-xl font-bold text-green-600">₹{{ number_format($booking->advance_paid) }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Payment Status</label>
                            @php
                                $paymentStatusColors = [
                                    'paid' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'partial' => 'bg-orange-100 text-orange-800',
                                ];
                            @endphp
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold {{ $paymentStatusColors[$booking->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($booking->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Timeline -->
            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Booking Timeline
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="text-sm text-gray-500">Booking Date</label>
                        <p class="font-semibold text-gray-900">{{ $booking->created_at->format('d M, Y') }}</p>
                        <p class="text-xs text-gray-500">{{ $booking->created_at->format('h:i A') }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Check-in Date</label>
                        <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d M, Y') }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Duration</label>
                        @php
                            $checkInDate = \Carbon\Carbon::parse($booking->check_in_date);
                            $now = now();
                            
                            if ($checkInDate->isFuture()) {
                                $daysUntil = (int) $checkInDate->diffInDays($now);
                                $duration = 'Starts in ' . $daysUntil . ' day' . ($daysUntil != 1 ? 's' : '');
                            } else {
                                $totalDays = (int) $checkInDate->diffInDays($now);
                                $months = (int) floor($totalDays / 30);
                                $days = $totalDays % 30;
                                
                                if ($months > 0) {
                                    $duration = $months . ' month' . ($months > 1 ? 's' : '');
                                    if ($days > 0) {
                                        $duration .= ', ' . $days . ' day' . ($days > 1 ? 's' : '');
                                    }
                                } else {
                                    $duration = $totalDays . ' day' . ($totalDays != 1 ? 's' : '');
                                }
                            }
                        @endphp
                        <p class="font-semibold text-gray-900">{{ $duration }}</p>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    Hostel Contact
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-500">Phone</label>
                        <p class="font-semibold text-gray-900">+91 9840 999 888</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Email</label>
                        <p class="font-semibold text-gray-900">honeybees.hostel@gmail.com</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm text-gray-500">Address</label>
                        <p class="font-semibold text-gray-900">{{ $booking->bed->room->branch->address }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row gap-4">
                <button onclick="window.print()" class="flex-1 px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-lg hover:border-primary-500 hover:text-primary-600 transition font-medium">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Details
                </button>
                <button onclick="alert('Support feature coming soon! Please contact the hostel office.')" class="flex-1 px-6 py-3 bg-gradient-to-r from-rose-500 to-pink-500 text-white rounded-lg hover:from-rose-600 hover:to-pink-600 transition font-medium shadow-lg">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Need Help?
                </button>
            </div>
        </div>
    </div>
@endsection
