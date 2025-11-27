@extends('layouts.public')

@section('content')
    <div class="bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500 py-16 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-display font-bold mb-3">{{ $branch->name }}</h1>
            <p class="text-white/90 flex items-center justify-center text-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                {{ $branch->address }}
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-2xl font-display font-bold text-gray-900 mb-6">Available Rooms</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
            @foreach($branch->rooms as $room)
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition group flex flex-col h-full">
                    <!-- Room Image -->
                    <div class="relative h-48 bg-gray-100 overflow-hidden">
                        @if($room->images->count() > 0)
                            <img id="main-image-{{ $room->id }}" src="{{ $room->images->first()->safe_url }}" alt="{{ $room->room_number }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-rose-100 via-pink-100 to-purple-100 flex items-center justify-center">
                                <div class="text-center">
                                    <svg class="w-20 h-20 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="text-sm text-gray-400 font-medium">No Image Available</p>
                                </div>
                            </div>
                        @endif
                        <div class="absolute top-3 right-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/90 backdrop-blur-sm text-gray-800 shadow-md">
                                {{ $room->type }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-6 flex-1 flex flex-col">
                        <!-- Thumbnail Gallery - Fixed height for consistency -->
                        <div class="mb-4 -mt-2 h-20">
                            @if($room->images->count() > 1)
                                <div class="flex gap-2 overflow-x-auto pb-2">
                                    @foreach($room->images as $index => $image)
                                        <button type="button" onclick="changeRoomImage({{ $room->id }}, '{{ $image->safe_url }}', this)" 
                                            class="thumb-{{ $room->id }} flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 {{ $index === 0 ? 'border-primary-500' : 'border-gray-200' }} hover:border-primary-400 transition">
                                            <img src="{{ $image->safe_url }}" alt="View {{ $index + 1 }}" class="w-full h-full object-cover">
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $room->room_number }}</h3>
                            </div>
                            <div class="text-right">
                                <span class="block text-2xl font-bold text-primary-600">
                                    {{ $room->beds_count }}
                                </span>
                                <span class="text-xs text-gray-500 uppercase tracking-wide">Beds Left</span>
                            </div>
                        </div>

                        <div class="space-y-2 mb-6 flex-1">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                                {{ $room->capacity }} Sharing
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Women Only
                            </div>
                        </div>

                        <a href="{{ route('booking.room', [$branch, $room]) }}"
                            class="block w-full text-center bg-gray-50 border border-gray-200 text-gray-700 font-medium py-2 rounded-lg hover:bg-primary-50 hover:text-primary-700 hover:border-primary-200 transition">
                            Select Room
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        function changeRoomImage(roomId, imageSrc, clickedThumb) {
            console.log('Changing image for room', roomId, 'to', imageSrc);
            
            // Change main image
            const mainImage = document.getElementById('main-image-' + roomId);
            if (mainImage) {
                mainImage.src = imageSrc;
                console.log('Image changed successfully');
            } else {
                console.error('Main image element not found for room', roomId);
            }
            
            // Update thumbnail borders
            const thumbnails = document.querySelectorAll('.thumb-' + roomId);
            console.log('Found', thumbnails.length, 'thumbnails');
            
            thumbnails.forEach(thumb => {
                thumb.classList.remove('border-primary-500');
                thumb.classList.add('border-gray-200');
            });
            
            clickedThumb.classList.remove('border-gray-200');
            clickedThumb.classList.add('border-primary-500');
        }
    </script>
@endsection