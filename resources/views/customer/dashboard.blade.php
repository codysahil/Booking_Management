@extends('layouts.customer')

@section('content')
    @php
        $bookings = Auth::guard('customer')->user()->bookings()->with('bed.room.branch')->latest()->get();
    @endphp
    
    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-display font-bold text-gray-900">Welcome, {{ Auth::guard('customer')->user()->name }}!</h1>
                    <p class="text-gray-600 mt-1">Customer ID: <span class="font-bold text-primary-600">{{ Auth::guard('customer')->user()->customer_code }}</span></p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Active Bookings</p>
                            <p class="text-3xl font-bold text-primary-600">{{ $bookings->where('status', 'active')->count() }}</p>
                        </div>
                        <div class="w-14 h-14 bg-gradient-to-br from-primary-100 to-secondary-100 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Pending Dues</p>
                            @php
                                $totalDues = 0;
                                foreach($bookings->where('status', 'active') as $booking) {
                                    $checkInDate = \Carbon\Carbon::parse($booking->check_in_date);
                                    $now = now();
                                    
                                    // Calculate months stayed (rounded up)
                                    $monthsStayed = (int) ceil($checkInDate->diffInMonths($now, true));
                                    if ($monthsStayed < 1) {
                                        $monthsStayed = 1; // Minimum 1 month
                                    }
                                    
                                    // Calculate total rent due (advance is separate security deposit)
                                    $totalRentDue = ($booking->bed->monthly_rent ?? 0) * $monthsStayed;
                                    
                                    $totalDues += $totalRentDue;
                                }
                            @endphp
                            <p class="text-3xl font-bold text-amber-600">₹{{ number_format($totalDues) }}</p>
                        </div>
                        <div class="w-14 h-14 bg-gradient-to-br from-amber-100 to-yellow-100 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Total Payments</p>
                            @php
                                $totalPayments = $bookings->sum('advance_paid');
                            @endphp
                            <p class="text-3xl font-bold text-green-600">₹{{ number_format($totalPayments) }}</p>
                        </div>
                        <div class="w-14 h-14 bg-gradient-to-br from-green-100 to-emerald-100 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Bookings -->
            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6 mb-8">
                <h2 class="text-2xl font-display font-bold text-gray-900 mb-6">My Bookings</h2>

                @if($bookings->count() > 0)
                    <div class="space-y-4">
                        @foreach($bookings as $booking)
                            <div class="border-2 border-gray-100 rounded-xl p-6 hover:border-primary-200 transition">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-3">
                                            <h3 class="text-lg font-bold text-gray-900">{{ $booking->bed->room->branch->name }}</h3>
                                            @php
                                                $statusColors = [
                                                    'active' => 'bg-green-100 text-green-800',
                                                    'paid' => 'bg-blue-100 text-blue-800',
                                                    'cancelled' => 'bg-red-100 text-red-800',
                                                ];
                                            @endphp
                                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <p class="text-gray-500">Booking Reference</p>
                                                <p class="font-bold text-gray-900">{{ $booking->booking_reference }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Room & Bed</p>
                                                <p class="font-bold text-gray-900">{{ $booking->bed->room->room_number }} • {{ $booking->bed->bed_number }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Check-in Date</p>
                                                <p class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d M, Y') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Advance Paid</p>
                                                <p class="font-bold text-green-600">₹{{ number_format($booking->advance_paid) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <a href="{{ route('customer.bookings.show', $booking) }}" class="px-4 py-2 bg-primary-50 text-primary-600 rounded-lg hover:bg-primary-100 transition text-sm font-medium text-center">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500">No bookings found</p>
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-primary-500 to-secondary-500 rounded-2xl p-6 text-white">
                    <h3 class="text-xl font-bold mb-2">Need to Pay Dues?</h3>
                    <p class="text-white/90 mb-4 text-sm">View and pay your pending rent, EB bills, and fines online</p>
                    <button onclick="alert('Payment feature coming soon! Please contact the hostel office for now.')" class="bg-white text-primary-600 px-6 py-2 rounded-lg font-bold hover:bg-gray-50 transition">
                        View Dues
                    </button>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl p-6 text-white">
                    <h3 class="text-xl font-bold mb-2">Raise a Request</h3>
                    <p class="text-white/90 mb-4 text-sm">Room swap, vacation notice, maintenance, or refund requests</p>
                    <button onclick="alert('Request feature coming soon! Please contact the hostel office for now.')" class="bg-white text-purple-600 px-6 py-2 rounded-lg font-bold hover:bg-gray-50 transition">
                        New Request
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
