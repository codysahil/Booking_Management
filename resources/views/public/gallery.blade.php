@extends('layouts.public')

@section('content')
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-5xl md:text-6xl font-display font-bold text-gray-900 mb-6">
                    Our
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500">
                        Gallery
                    </span>
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    Take a virtual tour of our beautiful spaces and vibrant community
                </p>
            </div>
        </div>
    </div>

    <!-- Gallery Grid -->
    <div class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Rooms Section -->
            <div class="mb-20">
                <h2 class="text-3xl font-display font-bold text-gray-900 mb-8 text-center">
                    <span class="text-rose-500">🏠</span> Our Rooms
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @php
                    $roomImages = [
                        'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=800&q=80',
                        'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=800&q=80',
                        'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&q=80',
                        'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=800&q=80',
                        'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?w=800&q=80',
                        'https://images.unsplash.com/photo-1616594039964-ae9021a400a0?w=800&q=80',
                    ];
                    @endphp

                    @foreach($roomImages as $index => $image)
                        <div class="group relative aspect-[4/3] rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 leading-none">
                            <img src="{{ $image }}" alt="Room {{ $index + 1 }}" class="block absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" style="display: block;">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                <p class="text-lg font-bold">Cozy Room {{ $index + 1 }}</p>
                                <p class="text-sm text-white/90">Comfortable & Well-furnished</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Common Areas Section -->
            <div class="mb-20">
                <h2 class="text-3xl font-display font-bold text-gray-900 mb-8 text-center">
                    <span class="text-pink-500">✨</span> Common Areas
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @php
                    $areas = [
                        ['name' => 'Lounge Area', 'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800&q=80'],
                        ['name' => 'Kitchen', 'image' => 'https://images.unsplash.com/photo-1556911220-bff31c812dba?w=800&q=80'],
                        ['name' => 'Study Room', 'image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&q=80'],
                        ['name' => 'Dining Area', 'image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&q=80'],
                        ['name' => 'Terrace', 'image' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&q=80'],
                        ['name' => 'Recreation', 'image' => 'https://images.unsplash.com/photo-1574643156929-51fa098b0394?w=800&q=80'],
                    ];
                    @endphp

                    @foreach($areas as $area)
                        <div class="group relative aspect-[4/3] rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 leading-none">
                            <img src="{{ $area['image'] }}" alt="{{ $area['name'] }}" class="block absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" style="display: block;">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                <p class="text-lg font-bold">{{ $area['name'] }}</p>
                                <p class="text-sm text-white/90">Shared spaces for everyone</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Community Events Section -->
            <div>
                <h2 class="text-3xl font-display font-bold text-gray-900 mb-8 text-center">
                    <span class="text-purple-500">💕</span> Community Events
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @php
                    $events = [
                        ['name' => 'Yoga Sessions', 'image' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800&q=80'],
                        ['name' => 'Movie Nights', 'image' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=800&q=80'],
                        ['name' => 'Cooking Classes', 'image' => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=800&q=80'],
                        ['name' => 'Game Nights', 'image' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=800&q=80'],
                    ];
                    @endphp

                    @foreach($events as $event)
                        <div class="group relative aspect-square rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 leading-none">
                            <img src="{{ $event['image'] }}" alt="{{ $event['name'] }}" class="block absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" style="display: block;">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                                <p class="text-base font-bold">{{ $event['name'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="py-20 bg-gradient-to-br from-rose-500 via-pink-500 to-purple-500">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl md:text-5xl font-display font-bold text-white mb-6">
                Love What You See?
            </h2>
            <p class="text-xl text-white/90 mb-10">
                Schedule a visit to experience it in person
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('home') }}#locations" class="inline-flex items-center justify-center px-12 py-5 bg-white text-rose-600 text-lg font-bold rounded-full hover:bg-gray-50 transition-all duration-300 shadow-2xl hover:shadow-white/50 transform hover:scale-105">
                    Book Your Room
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center px-12 py-5 bg-white/10 backdrop-blur-sm border-2 border-white text-white text-lg font-bold rounded-full hover:bg-white/20 transition-all duration-300">
                    Contact Us
                </a>
            </div>
        </div>
    </div>
@endsection
