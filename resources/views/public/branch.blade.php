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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($branch->rooms as $room)
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Room {{ $room->room_number }}</h3>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-800 mt-1">
                                    {{ $room->type }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="block text-2xl font-bold text-primary-600">
                                    {{ $room->beds_count }}
                                </span>
                                <span class="text-xs text-gray-500 uppercase tracking-wide">Beds Left</span>
                            </div>
                        </div>

                        <div class="space-y-2 mb-6">
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
@endsection