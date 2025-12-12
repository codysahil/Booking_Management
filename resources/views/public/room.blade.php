@extends('layouts.public')

@section('content')
    <!-- Hero Header with Gradient -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #be185d 0%, #ec4899 50%, #a855f7 100%);">
        <!-- Decorative circles -->
        <div class="absolute top-0 left-0 w-72 h-72 rounded-full opacity-20" style="background: white; transform: translate(-50%, -50%);"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 rounded-full opacity-10" style="background: white; transform: translate(30%, 50%);"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            <!-- Breadcrumb -->
            <nav class="flex items-center space-x-2 text-sm mb-6" style="color: rgba(255,255,255,0.8);">
                <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <a href="{{ route('booking.branch', $branch) }}" class="hover:text-white transition">{{ $branch->name }}</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-white font-medium">Room {{ $room->room_number }}</span>
            </nav>

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div>
                    <!-- Availability Badge -->
                    <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold mb-4" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); color: white;">
                        <span class="w-2 h-2 rounded-full mr-2 animate-pulse" style="background: #4ade80;"></span>
                        {{ $room->beds->where('status', 'vacant')->count() }} Beds Available
                    </div>
                    
                    <h1 class="text-4xl md:text-5xl font-bold text-white mb-3">Room {{ $room->room_number }}</h1>
                    
                    <div class="flex flex-wrap items-center gap-4 text-white" style="opacity: 0.9;">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            {{ $room->type }} Room
                        </span>
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $room->capacity }} Sharing
                        </span>
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Women Only
                        </span>
                    </div>
                </div>

                <!-- Price Card -->
                <div class="rounded-2xl p-6" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2);">
                    <div class="text-white text-sm" style="opacity: 0.8;">Starting from</div>
                    <div class="text-3xl font-bold text-white">₹{{ number_format($room->beds->min('monthly_rent')) }}<span class="text-lg font-normal" style="opacity: 0.8;">/month</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div style="background: linear-gradient(180deg, #fdf2f8 0%, #ffffff 100%);" class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Bed Selection Area -->
                <div class="lg:col-span-2">
                    <form action="{{ route('booking.select-beds') }}" method="POST" id="bed-selection-form">
                        @csrf
                        
                        <!-- Bed Selection Card -->
                        <div class="bg-white rounded-3xl shadow-xl overflow-hidden" style="border: 1px solid rgba(0,0,0,0.05);">
                            <!-- Card Header -->
                            <div class="px-6 py-5 border-b" style="background: linear-gradient(90deg, #fdf2f8 0%, #fce7f3 100%);">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-900">Choose Your Bed</h2>
                                        <p class="text-sm text-gray-500 mt-1">Select one or more beds to proceed</p>
                                    </div>
                                    <div class="flex items-center gap-4 text-sm">
                                        <span class="flex items-center">
                                            <span class="w-3 h-3 rounded-full mr-2" style="background: linear-gradient(135deg, #10b981, #34d399);"></span>
                                            Available
                                        </span>
                                        <span class="flex items-center">
                                            <span class="w-3 h-3 rounded-full mr-2 bg-gray-300"></span>
                                            Occupied
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Beds Grid -->
                            <div class="p-6">
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    @foreach($room->beds as $bed)
                                        @php
                                            $isAvailable = $bed->status === 'vacant' && (!$bed->reserved_until || $bed->reserved_until < now());
                                        @endphp
                                        
                                        @if($isAvailable)
                                            <label class="cursor-pointer group">
                                                <input type="checkbox" name="bed_ids[]" value="{{ $bed->id }}" 
                                                    class="peer sr-only bed-checkbox" data-rent="{{ $bed->monthly_rent }}">
                                                <div class="relative rounded-2xl p-5 text-center transition-all duration-300 border-2 border-gray-100
                                                    peer-checked:border-pink-500 peer-checked:shadow-lg peer-checked:shadow-pink-100
                                                    hover:border-pink-200 hover:shadow-md group-hover:-translate-y-1"
                                                    style="background: linear-gradient(180deg, #ffffff 0%, #fdf2f8 100%);">
                                                    
                                                    <!-- Selection Circle -->
                                                    <div class="absolute top-3 right-3 w-6 h-6 rounded-full border-2 border-gray-200 flex items-center justify-center
                                                        peer-checked:border-pink-500 transition-all duration-300"
                                                        style="background: white;">
                                                        <div class="w-3 h-3 rounded-full scale-0 peer-checked:scale-100 transition-transform duration-300" style="background: linear-gradient(135deg, #ec4899, #be185d);"></div>
                                                    </div>
                                                    
                                                    <!-- Bed Icon -->
                                                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110"
                                                        style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);">
                                                        <svg class="w-8 h-8" style="color: #059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6a2 2 0 00-2-2"></path>
                                                        </svg>
                                                    </div>
                                                    
                                                    <h3 class="font-bold text-gray-900 text-lg">Bed {{ $bed->bed_number }}</h3>
                                                    
                                                    <div class="mt-2 mb-3">
                                                        <span class="text-2xl font-bold" style="color: #be185d;">₹{{ number_format($bed->monthly_rent) }}</span>
                                                        <span class="text-gray-500 text-sm">/month</span>
                                                    </div>
                                                    
                                                    <div class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold"
                                                        style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #047857;">
                                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 animate-pulse" style="background: #10b981;"></span>
                                                        Available
                                                    </div>
                                                </div>
                                            </label>
                                        @else
                                            <div class="rounded-2xl p-5 text-center border-2 border-gray-100 opacity-50" style="background: #f9fafb;">
                                                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center bg-gray-200">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6a2 2 0 00-2-2"></path>
                                                    </svg>
                                                </div>
                                                <h3 class="font-bold text-gray-400 text-lg">Bed {{ $bed->bed_number }}</h3>
                                                <div class="mt-2 mb-3">
                                                    <span class="text-2xl font-bold text-gray-400">₹{{ number_format($bed->monthly_rent) }}</span>
                                                    <span class="text-gray-400 text-sm">/month</span>
                                                </div>
                                                <div class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-500">
                                                    {{ ucfirst($bed->status) }}
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                @error('bed_ids')
                                    <div class="mt-4 p-4 rounded-xl" style="background: #fef2f2; border: 1px solid #fecaca;">
                                        <p class="text-sm" style="color: #dc2626;">{{ $message }}</p>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Checkout Bar -->
                        <div class="mt-6 bg-white rounded-3xl shadow-xl p-6" style="border: 1px solid rgba(0,0,0,0.05);">
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div class="flex items-center gap-5">
                                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);">
                                        <span id="selected-count" class="text-3xl font-bold" style="color: #be185d;">0</span>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">Beds Selected</div>
                                        <div class="text-2xl font-bold text-gray-900">₹<span id="total-rent">0</span><span class="text-base font-normal text-gray-500">/month</span></div>
                                    </div>
                                </div>
                                
                                <button type="submit" id="proceed-btn" disabled
                                    class="w-full sm:w-auto px-10 py-4 rounded-xl font-bold text-white transition-all duration-300 flex items-center justify-center gap-2
                                    disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-xl hover:-translate-y-0.5"
                                    style="background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);">
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
                        
                        <!-- Booking Summary Card -->
                        <div class="bg-white rounded-3xl shadow-xl overflow-hidden" style="border: 1px solid rgba(0,0,0,0.05);">
                            <div class="px-5 py-4" style="background: linear-gradient(135deg, #be185d 0%, #ec4899 100%);">
                                <h3 class="font-bold text-white text-lg">Booking Summary</h3>
                            </div>
                            <div class="p-5 space-y-4">
                                <!-- Branch - Stacked layout for long names -->
                                <div class="py-3 border-b border-gray-100">
                                    <div class="flex items-center text-gray-500 text-sm mb-1">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        </svg>
                                        Branch
                                    </div>
                                    <div class="font-semibold text-gray-900 pl-6">{{ $branch->name }}</div>
                                </div>
                                
                                <!-- Room Type & Capacity - Side by side -->
                                <div class="grid grid-cols-2 gap-4 py-2">
                                    <div>
                                        <div class="flex items-center text-gray-500 text-sm mb-1">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            Type
                                        </div>
                                        <div class="font-semibold text-gray-900 pl-6">{{ $room->type }}</div>
                                    </div>
                                    <div>
                                        <div class="flex items-center text-gray-500 text-sm mb-1">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            Sharing
                                        </div>
                                        <div class="font-semibold text-gray-900 pl-6">{{ $room->capacity }} Persons</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Amenities Card -->
                        <div class="bg-white rounded-3xl shadow-xl p-6" style="border: 1px solid rgba(0,0,0,0.05);">
                            <h3 class="font-bold text-gray-900 text-lg mb-5 flex items-center">
                                <span class="w-8 h-8 rounded-lg mr-3 flex items-center justify-center" style="background: linear-gradient(135deg, #fce7f3, #fbcfe8);">
                                    <svg class="w-4 h-4" style="color: #be185d;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                    </svg>
                                </span>
                                Amenities Included
                            </h3>
                            <div class="space-y-3">
                                @php
                                    $amenities = [
                                        ['icon' => 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0', 'name' => 'High-Speed Wi-Fi', 'color' => '#3b82f6'],
                                        ['icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'name' => '24/7 Security', 'color' => '#10b981'],
                                        ['icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16', 'name' => 'Daily Housekeeping', 'color' => '#8b5cf6'],
                                        ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'name' => 'Power Backup', 'color' => '#f59e0b'],
                                        ['icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'name' => 'Attached Bathroom', 'color' => '#ec4899'],
                                    ];
                                @endphp
                                @foreach($amenities as $amenity)
                                    <div class="flex items-center p-3 rounded-xl" style="background: #fdf2f8;">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-3" style="background: {{ $amenity['color'] }}20;">
                                            <svg class="w-5 h-5" style="color: {{ $amenity['color'] }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $amenity['icon'] }}"></path>
                                            </svg>
                                        </div>
                                        <span class="font-medium text-gray-700">{{ $amenity['name'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Help Card -->
                        <div class="rounded-3xl p-6" style="background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%); border: 1px solid #fbcfe8;">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #ec4899, #be185d);">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 mb-1">Need Help?</h4>
                                    <p class="text-sm text-gray-600 mb-3">Our team is here to assist you with your booking.</p>
                                    <a href="{{ route('contact') }}" class="inline-flex items-center font-semibold text-sm transition-colors" style="color: #be185d;">
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
                checked.forEach(cb => total += parseInt(cb.dataset.rent));
                
                selectedCount.textContent = checked.length;
                totalRent.textContent = total.toLocaleString('en-IN');
                proceedBtn.disabled = checked.length === 0;
                
                // Animate the count
                selectedCount.style.transform = 'scale(1.2)';
                setTimeout(() => selectedCount.style.transform = 'scale(1)', 150);
            }

            checkboxes.forEach(cb => cb.addEventListener('change', updateSelection));
        });
    </script>
@endsection
