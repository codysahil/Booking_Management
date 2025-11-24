@extends('layouts.public')

@section('content')
    <div class="bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500 py-16 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-center mb-4">
                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-display font-bold text-center">Complete Your Booking</h1>
            <p class="mt-3 text-white/90 text-center text-lg">Just a few more details to confirm your reservation</p>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Checkout Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100">
                    <h2 class="text-2xl font-display font-bold text-gray-900 mb-6">Your Details</h2>

                    <div class="bg-primary-50 rounded-xl p-6 mb-6">
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-primary-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-sm text-primary-800">
                                <p class="font-bold mb-1">Quick Booking Process</p>
                                <p>We only need your mobile number to reserve your bed. Complete details and documents will be collected when you check-in at the hostel.</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('booking.process-payment') }}" method="POST">
                        @csrf

                        <div class="space-y-6">
                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Mobile Number *
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </div>
                                    <input type="tel" name="phone" id="phone" required value="{{ old('phone') }}"
                                        placeholder="Enter your 10-digit mobile number"
                                        pattern="[0-9]{10}"
                                        maxlength="10"
                                        class="w-full pl-12 pr-4 py-4 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-lg">
                                </div>
                                @error('phone')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-2">You'll receive booking confirmation on this number</p>
                            </div>

                            <!-- Name (Optional for now) -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Full Name (Optional)
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}"
                                    placeholder="Enter your full name"
                                    class="w-full px-4 py-4 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-lg">
                                @error('name')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Check-in Date -->
                            <div>
                                <label for="check_in_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Expected Check-in Date *
                                </label>
                                <input type="date" name="check_in_date" id="check_in_date" required
                                    min="{{ date('Y-m-d') }}" value="{{ old('check_in_date', date('Y-m-d')) }}"
                                    class="w-full px-4 py-4 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-lg">
                                @error('check_in_date')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Hidden fields with default values -->
                            <input type="hidden" name="email" value="">
                            <input type="hidden" name="address" value="To be collected on check-in">
                            <input type="hidden" name="payment_method" value="pending">

                            <div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-4">
                                <div class="flex items-start space-x-3">
                                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="text-sm text-amber-800">
                                        <p class="font-bold mb-1">Payment will be collected at check-in</p>
                                        <p>Advance amount: ₹{{ number_format($advance) }} (1 month rent or ₹3,000 minimum)</p>
                                    </div>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500 text-white py-4 px-6 rounded-xl font-bold text-lg hover:from-rose-600 hover:via-pink-600 hover:to-purple-600 transition shadow-lg">
                                Confirm Booking
                            </button>

                            <p class="text-xs text-center text-gray-500">
                                By confirming, you agree to bring valid ID proof and photo on check-in day
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Booking Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 sticky top-24">
                    <h3 class="text-xl font-display font-bold text-gray-900 mb-4">Booking Summary</h3>

                    <div class="space-y-3 mb-6">
                        @foreach($beds as $bed)
                            <div class="flex justify-between items-center text-sm p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $bed->bed_number }}</p>
                                    <p class="text-xs text-gray-500">{{ $bed->room->room_number }} •
                                        {{ $bed->room->branch->name }}</p>
                                </div>
                                <span class="text-gray-700 font-medium">₹{{ number_format($bed->monthly_rent) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total Beds:</span>
                            <span class="font-medium">{{ $beds->count() }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Monthly Rent:</span>
                            <span class="font-medium">₹{{ number_format($beds->sum('monthly_rent')) }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t">
                            <span class="font-bold text-gray-900">Advance Payment:</span>
                            <span class="font-bold text-2xl text-primary-600">₹{{ number_format($advance) }}</span>
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-primary-50 rounded-lg">
                        <p class="text-xs text-primary-800">
                            <strong>Note:</strong> Your selected beds are reserved for 10 minutes. Please complete the
                            payment to confirm your booking.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection