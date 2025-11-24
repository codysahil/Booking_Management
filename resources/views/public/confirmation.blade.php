@extends('layouts.public')

@section('content')
    <div class="bg-gradient-to-r from-green-500 via-emerald-500 to-teal-500 py-16 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Success Icon -->
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full mb-6 animate-bounce">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h1 class="text-4xl md:text-5xl font-display font-bold mb-4">Booking Confirmed!</h1>
            <p class="text-xl text-white/90 mb-2">Your reservation has been successfully confirmed</p>
            <p class="text-white/80">Booking Reference: <span class="font-bold">{{ $booking->booking_reference }}</span></p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Customer Details Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-display font-bold text-gray-900">Booking Details</h2>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-green-100 text-green-800">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Confirmed
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Customer Info -->
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Customer ID</label>
                        <p class="text-lg font-bold text-primary-600">{{ $booking->customer->customer_code }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Name</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $booking->customer->name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Phone Number</label>
                        <p class="text-lg text-gray-900">{{ $booking->customer->phone }}</p>
                    </div>
                    @if($booking->customer->email)
                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="text-lg text-gray-900">{{ $booking->customer->email }}</p>
                        </div>
                    @endif
                </div>

                <!-- Booking Info -->
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Booking Reference</label>
                        <p class="text-lg font-bold text-gray-900">{{ $booking->booking_reference }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Check-in Date</label>
                        <p class="text-lg text-gray-900">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d M, Y') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Branch</label>
                        <p class="text-lg text-gray-900">{{ $booking->bed->room->branch->name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Total Beds</label>
                        <p class="text-lg font-bold text-gray-900">{{ $relatedBookings->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bed Details -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 mb-8">
            <h3 class="text-xl font-display font-bold text-gray-900 mb-6">Your Bed(s)</h3>
            
            <div class="space-y-4">
                @foreach($relatedBookings as $relatedBooking)
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-primary-50 to-secondary-50 rounded-xl border border-primary-100">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">{{ $relatedBooking->bed->bed_number }}</p>
                                <p class="text-sm text-gray-600">{{ $relatedBooking->bed->room->room_number }} • {{ $relatedBooking->bed->room->type }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-900">₹{{ number_format($relatedBooking->bed->monthly_rent) }}</p>
                            <p class="text-xs text-gray-500">per month</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-bold text-gray-900">Advance Paid:</span>
                    <span class="text-3xl font-bold text-green-600">₹{{ number_format($booking->customer->payments->first()->amount ?? 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Important Information -->
        <div class="bg-gradient-to-r from-amber-50 to-yellow-50 rounded-2xl border-2 border-amber-200 p-8 mb-8">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-amber-900 mb-3">Important Information</h3>
                    <ul class="space-y-2 text-sm text-amber-800">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Please save your <strong>Customer ID ({{ $booking->customer->customer_code }})</strong> for future reference and portal login.</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Bring valid ID proof and photo on check-in day for verification.</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>A confirmation SMS has been sent to your registered mobile number.</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>For any queries, contact us at the branch or call our helpline.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('customer.login') }}" 
                class="flex-1 inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-bold rounded-xl hover:from-primary-700 hover:to-secondary-700 transition shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                Login to Customer Portal
            </a>
            <a href="{{ route('home') }}" 
                class="flex-1 inline-flex items-center justify-center px-8 py-4 bg-white border-2 border-gray-200 text-gray-700 font-bold rounded-xl hover:border-primary-500 hover:text-primary-600 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Back to Home
            </a>
        </div>
    </div>
@endsection
