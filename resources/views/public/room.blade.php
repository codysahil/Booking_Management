@extends('layouts.public')

@section('content')
    <!-- Clean Header -->
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Breadcrumb -->
            <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                <a href="{{ route('home') }}" class="hover:text-rose-600 transition">Home</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <a href="{{ route('booking.branch', $branch) }}" class="hover:text-rose-600 transition">{{ $branch->name }}</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-gray-900 font-medium">Room {{ $room->room_number }}</span>
            </nav>
            
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Room {{ $room->room_number }}</h1>
                    <div class="flex items-center gap-4 mt-2 text-gray-600">
                        <span class="flex items-center">
                            <span class="w-2 h-2 bg-rose-500 rounded-full mr-2"></span>
                            {{ $room->type }}
                        </span>
                        <span>{{ $room->capacity }} Sharing</span>
                        <span class="text-rose-600 font-medium">{{ $room->beds->where('status', 'vacant')->count() }} beds available</span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Starting from</div>
                    <div class="text-2xl font-bold text-gray-900">₹{{ number_format($room->beds->min('monthly_rent')) }}<span class="text-base font-normal text-gray-500">/month</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Bed Selection -->
                <div class="lg:col-span-2">
                    <form action="{{ route('booking.select-beds') }}" method="POST" id="bed-selection-form">
                        @csrf
                        
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-bold text-gray-900">Select Your Bed</h2>
                                <div class="flex items-center gap-4 text-sm">
                                    <span class="flex items-center"><span class="w-3 h-3 bg-emerald-500 rounded-full mr-2"></span>Available</span>
                                    <span class="flex items-center"><span class="w-3 h-3 bg-gray-300 rounded-full mr-2"></span>Occupied</span>
                                </div>
                            </div>

                            <!-- Beds Grid -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                @foreach($room->beds as $bed)
                                    @php
                                        $isAvailable = $bed->status === 'vacant' && (!$bed->reserved_until || $bed->reserved_until < now());
                                    @endphp
                                    
                                    @if($isAvailable)
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="bed_ids[]" value="{{ $bed->id }}" 
                                                class="peer sr-only bed-checkbox" data-rent="{{ $bed->monthly_rent }}">
                                            <div class="relative bg-white border-2 border-gray-200 rounded-xl p-4 text-center
                                                peer-checked:border-rose-500 peer-checked:bg-rose-50
                                                hover:border-rose-300 hover:bg-rose-50/50 transition-all">
                                                
                                                <!-- Checkbox indicator -->
                                                <div class="absolute top-3 right-3 w-5 h-5 rounded-full border-2 border-gray-300
                                                    peer-checked:border-rose-500 peer-checked:bg-rose-500 flex items-center justify-center">
                                                    <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </div>
                                                
                                                <!-- Bed icon -->
                                                <div class="w-12 h-12 mx-auto mb-3 bg-emerald-100 rounded-xl flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6a2 2 0 00-2-2"></path>
                                                    </svg>
                                                </div>
                                                
                                                <div class="font-semibold text-gray-900">Bed {{ $bed->bed_number }}</div>
                                                <div class="text-lg font-bold text-gray-900 mt-1">₹{{ number_format($bed->monthly_rent) }}<span class="text-sm font-normal text-gray-500">/mo</span></div>
                                                <div class="mt-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                                    Available
                                                </div>
                                            </div>
                                        </label>
                                    @else
                                        <div class="bg-gray-100 border-2 border-gray-200 rounded-xl p-4 text-center opacity-60">
                                            <div class="w-12 h-12 mx-auto mb-3 bg-gray-200 rounded-xl flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6a2 2 0 00-2-2"></path>
                                                </svg>
                                            </div>
                                            <div class="font-semibold text-gray-500">Bed {{ $bed->bed_number }}</div>
                                            <div class="text-lg font-bold text-gray-400 mt-1">₹{{ number_format($bed->monthly_rent) }}<span class="text-sm font-normal">/mo</span></div>
                                            <div class="mt-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-200 text-gray-600">
                                                {{ ucfirst($bed->status) }}
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            @error('bed_ids')
                                <p class="text-red-600 text-sm mt-4">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Bottom Action Bar -->
                        <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 bg-rose-100 rounded-xl flex items-center justify-center">
                                        <span id="selected-count" class="text-2xl font-bold text-rose-600">0</span>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">Beds Selected</div>
                                        <div class="text-xl font-bold text-gray-900">₹<span id="total-rent">0</span>/month</div>
                                    </div>
                                </div>
                                <button type="submit" id="proceed-btn" disabled
                                    class="w-full sm:w-auto px-8 py-4 bg-rose-600 text-white font-semibold rounded-xl
                                    hover:bg-rose-700 transition disabled:bg-gray-300 disabled:cursor-not-allowed
                                    flex items-center justify-center gap-2">
                                    Proceed to Checkout
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
                        <!-- Room Info -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-rose-600 px-5 py-4">
                                <h3 class="font-semibold text-white">Booking Summary</h3>
                            </div>
                            <div class="p-5 space-y-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Branch</span>
                                    <span class="font-medium text-gray-900">{{ $branch->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Room</span>
                                    <span class="font-medium text-gray-900">{{ $room->room_number }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Type</span>
                                    <span class="font-medium text-gray-900">{{ $room->type }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Sharing</span>
                                    <span class="font-medium text-gray-900">{{ $room->capacity }} Persons</span>
                                </div>
                            </div>
                        </div>

                        <!-- Amenities -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
                            <h3 class="font-semibold text-gray-900 mb-4">Amenities Included</h3>
                            <ul class="space-y-3">
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-emerald-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    High-Speed Wi-Fi
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-emerald-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    24/7 Security
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-emerald-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Daily Housekeeping
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-emerald-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Power Backup
                                </li>
                                <li class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-emerald-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Attached Bathroom
                                </li>
                            </ul>
                        </div>

                        <!-- Help -->
                        <div class="bg-rose-50 rounded-2xl p-5 border border-rose-100">
                            <h3 class="font-semibold text-gray-900 mb-2">Need Help?</h3>
                            <p class="text-sm text-gray-600 mb-3">Contact us for any queries about booking.</p>
                            <a href="{{ route('contact') }}" class="text-rose-600 font-medium text-sm hover:text-rose-700">
                                Contact Us →
                            </a>
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
            }

            checkboxes.forEach(cb => cb.addEventListener('change', updateSelection));
        });
    </script>
@endsection
