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
            <h1 class="text-4xl font-display font-bold mb-3">Select Your Beds</h1>
            <p class="text-white/90 text-lg">{{ $room->type }} • {{ $room->capacity }} Sharing • Women Only</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Bed Grid (Theater Style) -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                    <div class="mb-6">
                        <h2 class="text-2xl font-display font-bold text-gray-900 mb-2">Available Beds</h2>
                        <p class="text-sm text-gray-500">Click on beds to select (you can select multiple)</p>
                    </div>

                    <form id="bed-selection-form" action="{{ route('booking.select-beds') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
                            @foreach($room->beds as $bed)
                                @php
                                    $isAvailable = $bed->status === 'vacant' && 
                                                   (is_null($bed->reserved_until) || $bed->reserved_until < now());
                                @endphp
                                <div class="bed-item {{ $isAvailable ? 'available' : 'unavailable' }}" 
                                     data-bed-id="{{ $bed->id }}" 
                                     data-bed-price="{{ $bed->monthly_rent }}"
                                     data-bed-number="{{ $bed->bed_number }}">
                                    <input type="checkbox" 
                                           name="bed_ids[]" 
                                           value="{{ $bed->id }}" 
                                           id="bed-{{ $bed->id }}"
                                           class="hidden bed-checkbox"
                                           {{ !$isAvailable ? 'disabled' : '' }}>
                                    <label for="bed-{{ $bed->id }}" 
                                           class="block cursor-pointer border-2 rounded-xl p-4 transition duration-200 text-center
                                                  {{ $isAvailable ? 'border-gray-200 hover:border-primary-500 hover:bg-primary-50' : 'border-gray-100 bg-gray-50 opacity-60 cursor-not-allowed' }}">
                                        <!-- Bed Icon -->
                                        <div class="w-12 h-12 mx-auto mb-3 rounded-full flex items-center justify-center
                                                    {{ $isAvailable ? 'bg-gray-100' : 'bg-gray-200' }}">
                                            <svg class="w-6 h-6 {{ $isAvailable ? 'text-gray-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                                            </svg>
                                        </div>
                                        <div class="font-bold text-sm {{ $isAvailable ? 'text-gray-900' : 'text-gray-500' }}">
                                            {{ $bed->bed_number }}
                                        </div>
                                        <div class="text-xs {{ $isAvailable ? 'text-gray-600' : 'text-gray-400' }} mt-1">
                                            ₹{{ number_format($bed->monthly_rent) }}/mo
                                        </div>
                                        <div class="mt-2">
                                            @if($isAvailable)
                                                <span class="inline-block px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Available
                                                </span>
                                            @else
                                                <span class="inline-block px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    {{ ucfirst($bed->status) }}
                                                </span>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" 
                                id="proceed-btn"
                                class="w-full bg-primary-600 text-white py-3 px-6 rounded-xl font-bold text-lg hover:bg-primary-700 transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                            Proceed to Checkout
                        </button>
                    </form>
                </div>
            </div>

            <!-- Selection Summary Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 sticky top-24">
                    <h3 class="text-xl font-display font-bold text-gray-900 mb-4">Selection Summary</h3>
                    
                    <div id="selected-beds-list" class="space-y-2 mb-6 min-h-[100px]">
                        <p class="text-gray-500 text-sm text-center py-8">No beds selected yet</p>
                    </div>

                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Total Beds:</span>
                            <span id="total-beds" class="font-bold text-gray-900">0</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Monthly Rent:</span>
                            <span id="total-rent" class="font-bold text-gray-900">₹0</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t">
                            <span class="font-bold text-gray-900">Advance Required:</span>
                            <span id="advance-amount" class="font-bold text-2xl text-primary-600">₹0</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">*Minimum ₹3,000 or 1 month rent</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bed-item.available label.selected {
            border-color: #ec4899;
            background-color: #fdf2f8;
        }
        .bed-item.available label.selected .bg-gray-100 {
            background-color: #fbcfe8;
        }
        .bed-item.available label.selected svg {
            color: #ec4899;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('bed-selection-form');
            const proceedBtn = document.getElementById('proceed-btn');
            const selectedBedsList = document.getElementById('selected-beds-list');
            const totalBedsEl = document.getElementById('total-beds');
            const totalRentEl = document.getElementById('total-rent');
            const advanceAmountEl = document.getElementById('advance-amount');

            const bedCheckboxes = document.querySelectorAll('.bed-checkbox:not([disabled])');

            function updateSummary() {
                const selectedBeds = Array.from(bedCheckboxes).filter(cb => cb.checked);
                const count = selectedBeds.length;

                if (count === 0) {
                    selectedBedsList.innerHTML = '<p class="text-gray-500 text-sm text-center py-8">No beds selected yet</p>';
                    proceedBtn.disabled = true;
                    totalBedsEl.textContent = '0';
                    totalRentEl.textContent = '₹0';
                    advanceAmountEl.textContent = '₹0';
                    return;
                }

                // Build selected beds list
                let html = '';
                let totalRent = 0;
                selectedBeds.forEach(cb => {
                    const bedItem = cb.closest('.bed-item');
                    const bedNumber = bedItem.dataset.bedNumber;
                    const bedPrice = parseInt(bedItem.dataset.bedPrice);
                    totalRent += bedPrice;

                    html += `
                        <div class="flex justify-between items-center text-sm p-2 bg-primary-50 rounded-lg">
                            <span class="font-medium text-primary-900">${bedNumber}</span>
                            <span class="text-primary-700">₹${bedPrice.toLocaleString()}</span>
                        </div>
                    `;
                });
                selectedBedsList.innerHTML = html;

                // Calculate advance (max of total rent or 3000)
                const advance = Math.max(totalRent, 3000);

                totalBedsEl.textContent = count;
                totalRentEl.textContent = `₹${totalRent.toLocaleString()}`;
                advanceAmountEl.textContent = `₹${advance.toLocaleString()}`;
                proceedBtn.disabled = false;
            }

            bedCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const label = this.nextElementSibling;
                    if (this.checked) {
                        label.classList.add('selected');
                    } else {
                        label.classList.remove('selected');
                    }
                    updateSummary();
                });
            });
        });
    </script>
@endsection
