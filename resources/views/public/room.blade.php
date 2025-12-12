@extends('layouts.public')

@section('content')
    <!-- Elegant Header with Room Images -->
    <div class="relative bg-gradient-to-br from-rose-600 via-pink-600 to-purple-700 overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-64 h-64 bg-white rounded-full translate-x-1/3 translate-y-1/3"></div>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            <!-- Breadcrumb -->
            <nav class="flex items-center space-x-2 text-white/70 text-sm mb-6">
                <a href="{{ route('home') }}" class="hover:text-white transition flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Home
                </a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <a href="{{ route('booking.branch', $branch) }}" class="hover:text-white transition">{{ $branch->name }}</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-white font-medium">Room {{ $room->room_number }}</span>
            </nav>

            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                <div>
                    <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-white text-sm font-medium mb-4">
                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                        {{ $room->beds->where('status', 'vacant')->count() }} Beds Available
                    </div>
                    <h1 class="text-4xl md:text-5xl font-display font-bold text-white mb-3">Room {{ $room->room_number }}</h1>
                    <div class="flex flex-wrap items-center gap-4 text-white/90">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            {{ $room->type }}
                        </span>
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            {{ $room->capacity }} Sharing
                        </span>
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Women Only
                        </span>
                    </div>
                </div>
                
                <!-- Price Badge -->
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                    <div class="text-white/70 text-sm mb-1">Starting from</div>
                    <div class="text-3xl font-bold text-white">₹{{ number_format($room->beds->min('monthly_rent')) }}<span class="text-lg font-normal text-white/70">/month</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Bed Selection Area -->
            <div class="lg:col-span-2">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-display font-bold text-gray-900">Choose Your Bed</h2>
                    <div class="flex items-center text-sm text-gray-500">
                        <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span> Available
                        <span class="w-3 h-3 bg-gray-300 rounded-full ml-4 mr-2"></span> Occupied
                    </div>
                </div>
                
                <form action="{{ route('booking.select-beds') }}" method="POST" id="bed-selection-form">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($room->beds as $bed)
                            @php
                                $isAvailable = $bed->status === 'vacant' && (!$bed->reserved_until || $bed->reserved_until < now());
                            @endphp
                            <div class="relative group">
                                @if($isAvailable)
                                    <label class="cursor-pointer block h-full">
                                        <input type="checkbox" name="bed_ids[]" value="{{ $bed->id }}" 
                                            class="peer sr-only bed-checkbox" 
                                            data-rent="{{ $bed->monthly_rent }}">
                                        <div class="h-full bg-white border-2 border-gray-100 rounded-2xl p-5 
                                            peer-checked:border-rose-500 peer-checked:bg-gradient-to-br peer-checked:from-rose-50 peer-checked:to-pink-50
                                            hover:border-rose-200 hover:shadow-lg hover:shadow-rose-100/50
                                            transition-all duration-300 transform hover:-translate-y-1">
                                            
                                            <!-- Selection Indicator -->
                                            <div class="absolute top-4 right-4 w-6 h-6 rounded-full border-2 border-gray-200 
                                                peer-checked:border-rose-500 peer-checked:bg-rose-500 
                                                flex items-center justify-center transition-all duration-300">
                                                <svg class="w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                            
                                            <!-- Bed Visual -->
                                            <div class="flex flex-col items-center text-center">
                                                <div class="w-16 h-16 bg-gradient-to-br from-rose-100 to-pink-100 rounded-2xl flex items-center justify-center mb-4 
                                                    group-hover:scale-110 transition-transform duration-300">
                                                    <svg class="w-8 h-8 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                                    </svg>
                                                </div>
                                                
                                                <h3 class="text-lg font-bold text-gray-900 mb-1">Bed {{ $bed->bed_number }}</h3>
                                                
                                                <div class="flex items-baseline justify-center mb-3">
                                                    <span class="text-2xl font-bold text-gray-900">₹{{ number_format($bed->monthly_rent) }}</span>
                                                    <span class="text-sm text-gray-500 ml-1">/mo</span>
                                                </div>
                                                
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                                                    Available Now
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                @else
                                    <div class="h-full bg-gray-50 border-2 border-gray-100 rounded-2xl p-5 opacity-60">
                                        <div class="flex flex-col items-center text-center">
                                            <div class="w-16 h-16 bg-gray-200 rounded-2xl flex items-center justify-center mb-4">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                                </svg>
                                            </div>
                                            
                                            <h3 class="text-lg font-bold text-gray-500 mb-1">Bed {{ $bed->bed_number }}</h3>
                                            
                                            <div class="flex items-baseline justify-center mb-3">
                                                <span class="text-2xl font-bold text-gray-400">₹{{ number_format($bed->monthly_rent) }}</span>
                                                <span class="text-sm text-gray-400 ml-1">/mo</span>
                                            </div>
                                            
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-600">
                                                {{ ucfirst($bed->status) }}
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @error('bed_ids')
                        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                            <p class="text-red-600 text-sm flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        </div>
                    @enderror

                    <!-- Selection Summary Bar -->
                    <div class="mt-8 bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-6 border border-gray-200">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center">
                                    <span id="selected-count" class="text-2xl font-bold text-rose-600">0</span>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Beds Selected</div>
                                    <div class="text-lg font-bold text-gray-900">Total: ₹<span id="total-rent">0</span>/month</div>
                                </div>
                            </div>
                            <button type="submit" id="proceed-btn" disabled
                                class="w-full sm:w-auto bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500 text-white px-8 py-4 rounded-xl font-bold 
                                hover:from-rose-600 hover:via-pink-600 hover:to-purple-600 
                                transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-rose-200/50
                                disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-lg
                                flex items-center justify-center space-x-2">
                                <span>Proceed to Checkout</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 space-y-6">
                    <!-- Room Info Card -->
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-rose-500 to-pink-500 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">Room Details</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-600 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    </svg>
                                    Branch
                                </span>
                                <span class="font-semibold text-gray-900">{{ $branch->name }}</span>
                            </div>
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="text-gray-600 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    Room Type
                                </span>
                                <span class="font-semibold text-gray-900">{{ $room->type }}</span>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <span class="text-gray-600 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Capacity
                                </span>
                                <span class="font-semibold text-gray-900">{{ $room->capacity }} Persons</span>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities Card -->
                    <div class="bg-gradient-to-br from-rose-50 to-pink-50 rounded-2xl p-6 border border-rose-100">
                        <h4 class="font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                            </svg>
                            Amenities Included
                        </h4>
                        <div class="grid grid-cols-1 gap-3">
                            <div class="flex items-center bg-white rounded-lg px-4 py-3 shadow-sm">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700">High-Speed Wi-Fi</span>
                            </div>
                            <div class="flex items-center bg-white rounded-lg px-4 py-3 shadow-sm">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700">24/7 Security</span>
                            </div>
                            <div class="flex items-center bg-white rounded-lg px-4 py-3 shadow-sm">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Daily Housekeeping</span>
                            </div>
                            <div class="flex items-center bg-white rounded-lg px-4 py-3 shadow-sm">
                                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Power Backup</span>
                            </div>
                        </div>
                    </div>

                    <!-- Help Card -->
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-rose-100 to-pink-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 mb-1">Need Help?</h4>
                                <p class="text-sm text-gray-600 mb-3">Our team is here to assist you with your booking.</p>
                                <a href="{{ route('contact') }}" class="text-sm font-semibold text-rose-600 hover:text-rose-700 flex items-center">
                                    Contact Us
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.bed-checkbox');
            const selectedCount = document.getElementById('selected-count');
            const totalRent = document.getElementById('total-rent');
            const proceedBtn = document.getElementById('proceed-btn');

            function updateSelection() {
                const checked = document.querySelectorAll('.bed-checkbox:checked');
                let total = 0;
                
                checked.forEach(cb => {
                    total += parseInt(cb.dataset.rent);
                });
                
                selectedCount.textContent = checked.length;
                totalRent.textContent = total.toLocaleString('en-IN');
                proceedBtn.disabled = checked.length === 0;
                
                // Add animation to count
                selectedCount.classList.add('scale-125');
                setTimeout(() => selectedCount.classList.remove('scale-125'), 150);
            }

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelection);
            });
        });
    </script>
@endsection
