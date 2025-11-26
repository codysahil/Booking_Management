@extends('layouts.public')

@section('content')
    <div class="bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500 py-16 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="flex items-center justify-center space-x-2 text-white/80 text-sm mb-4">
                <a href="{{ route('home') }}" class="hover:text-white">Home</a>
                <span>/</span>
                <a href="{{ route('booking.branch', $branch) }}" class="hover:text-white">{{ $branch->name }}</a>
                <span>/</span>
                <span class="text-white">Room {{ $room->room_number }}</span>
            </div>
            <h1 class="text-4xl font-display font-bold mb-3">Room {{ $room->room_number }}</h1>
            <p class="text-white/90 text-lg">{{ $room->type }} • {{ $room->capacity }} Sharing • Women Only</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">Room-15

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Bed Selection Area -->
            <div class="lg:col-span-2">
                <h2 class="text-2xl font-display font-bold text-gray-900 mb-6">Select Your Bed(s)</h2>
                
                <form action="{{ route('booking.select-beds') }}" method="POST" id="bed-selection-form">
                    @csrf
                    <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                        <div class="grid grid-cols-2 gap-6">
                            @foreach($room->beds as $bed)
                                <div class="relative">
                                    @if($bed->status === 'vacant' && (!$bed->reserved_until || $bed->reserved_until < now()))
                                        <label class="cursor-pointer block">
                                            <input type="checkbox" name="bed_ids[]" value="{{ $bed->id }}" 
                                                class="peer sr-only bed-checkbox" 
                                                data-rent="{{ $bed->monthly_rent }}">
                                            <div class="border-2 border-gray-200 rounded-xl p-6 peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-primary-300 transition duration-300 flex flex-col items-center relative">
                                                <!-- Checkmark Circle - Inside the card -->
                                                <div class="absolute top-3 right-3 w-6 h-6 rounded-full border-2 border-gray-300 peer-checked:border-primary-600 peer-checked:bg-primary-600 flex items-center justify-center transition">
                                                    <svg class="w-4 h-4 text-white opacity-0 peer-checked:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </div>
                                                
                                                <!-- Bed Icon -->
                                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 peer-checked:bg-primary-100 transition">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                                    </svg>
                                                </div>
                                                <span class="text-lg font-bold text-gray-900">Bed {{ $bed->bed_number }}</span>
                                                <span class="text-sm text-gray-500 mt-1">₹{{ number_format($bed->monthly_rent) }}/month</span>
                                                <span class="mt-4 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Available
                                                </span>
                                            </div>
                                        </label>
                                    @else
                                        <div class="border-2 border-gray-100 rounded-xl p-6 bg-gray-50 opacity-60 cursor-not-allowed flex flex-col items-center">
                                            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-4">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                                </svg>
                                            </div>
                                            <span class="text-lg font-bold text-gray-500">Bed {{ $bed->bed_number }}</span>
                                            <span class="text-sm text-gray-400 mt-1">₹{{ number_format($bed->monthly_rent) }}/month</span>
                                            <span class="mt-4 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-200 text-gray-600">
                                                {{ ucfirst($bed->status) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @error('bed_ids')
                            <p class="text-red-600 text-sm mt-4">{{ $message }}</p>
                        @enderror

                        <div class="mt-8 flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                <span id="selected-count" class="font-bold text-primary-600">0</span> bed(s) selected
                            </div>
                            <button type="submit" id="proceed-btn" disabled
                                class="bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500 text-white px-8 py-3 rounded-full font-bold hover:from-rose-600 hover:via-pink-600 hover:to-purple-600 transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                                Proceed to Checkout
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <script>
                // Bed selection
                document.addEventListener('DOMContentLoaded', function() {
                    const checkboxes = document.querySelectorAll('.bed-checkbox');
                    const selectedCount = document.getElementById('selected-count');
                    const proceedBtn = document.getElementById('proceed-btn');

                    function updateSelection() {
                        const checked = document.querySelectorAll('.bed-checkbox:checked');
                        selectedCount.textContent = checked.length;
                        proceedBtn.disabled = checked.length === 0;
                    }

                    checkboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', updateSelection);
                    });
                });
            </script>

            <!-- Summary / Info Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 sticky top-24">
                    <h3 class="text-xl font-display font-bold text-gray-900 mb-4">Booking Summary</h3>
                    <div class="space-y-4 text-sm border-b border-gray-100 pb-6 mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Branch</span>
                            <span class="font-medium text-gray-900">{{ $branch->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Room Type</span>
                            <span class="font-medium text-gray-900">{{ $room->type }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Sharing</span>
                            <span class="font-medium text-gray-900">{{ $room->capacity }} Persons</span>
                        </div>
                    </div>
                    
                    <div class="bg-primary-50 rounded-lg p-4 mb-6">
                        <h4 class="font-bold text-primary-800 mb-2">Amenities Included</h4>
                        <ul class="space-y-2 text-sm text-primary-700">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                High-Speed Wi-Fi
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                24/7 Security
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Daily Housekeeping
                            </li>
                        </ul>
                    </div>

                    <div class="text-xs text-gray-500 text-center">
                        Select a vacant bed to proceed with booking.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
