@extends('layouts.public')

@section('content')
    <!-- Hero Slider Section - Compact & Beautiful with Ken Burns Effect -->
    <style>
        @keyframes kenburns-1 {
            0% { transform: scale(1) translate(0, 0); }
            100% { transform: scale(1.05) translate(-1%, 0); }
        }
        @keyframes kenburns-2 {
            0% { transform: scale(1.03) translate(0, 0); }
            100% { transform: scale(1) translate(1%, 0); }
        }
        @keyframes kenburns-3 {
            0% { transform: scale(1) translate(0, 0); }
            100% { transform: scale(1.04) translate(0, -1%); }
        }
        @keyframes fadeSlideUp {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeSlideRight {
            0% { opacity: 0; transform: translateX(-30px); }
            100% { opacity: 1; transform: translateX(0); }
        }
        .slider-item.active .kenburns-img { animation: kenburns-1 8s ease-out forwards; }
        .slider-item[data-slide="1"].active .kenburns-img { animation: kenburns-2 8s ease-out forwards; }
        .slider-item[data-slide="2"].active .kenburns-img { animation: kenburns-3 8s ease-out forwards; }
        .slider-item.active .slide-title { animation: fadeSlideUp 0.8s ease-out 0.2s forwards; opacity: 0; }
        .slider-item.active .slide-desc { animation: fadeSlideRight 0.8s ease-out 0.4s forwards; opacity: 0; }
    </style>
    
    @if(isset($sliders) && $sliders->count() > 0)
    <div class="px-4 md:px-8 lg:px-16 py-4 md:py-6 bg-gradient-to-b from-rose-50 to-white">
        <div class="relative w-full aspect-[4/3] sm:aspect-[16/9] md:aspect-[21/9] max-h-[500px] overflow-hidden rounded-xl sm:rounded-2xl md:rounded-3xl shadow-2xl ring-1 ring-black/5">
        <div class="slider-container relative w-full h-full">
            @foreach($sliders as $index => $slider)
            <div class="slider-item absolute inset-0 transition-opacity duration-1000 ease-in-out {{ $index === 0 ? 'opacity-100 active' : 'opacity-0' }}" data-slide="{{ $index }}">
                <!-- Background Image -->
                <div class="absolute inset-0 kenburns-img" style="background-image: url('{{ $slider->image_url }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
                <!-- Elegant Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-r from-black/40 via-transparent to-black/20"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
                
                @if($slider->title || $slider->description)
                <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4 md:p-8">
                    <div class="max-w-4xl">
                        @if($slider->title)
                        <h2 class="slide-title text-base sm:text-xl md:text-3xl font-bold text-white mb-1 drop-shadow-lg line-clamp-2">
                            {{ $slider->title }}
                        </h2>
                        @endif
                        @if($slider->description)
                        <p class="slide-desc text-xs sm:text-sm md:text-base text-white/90 max-w-md md:max-w-xl drop-shadow-md line-clamp-2 hidden sm:block">
                            {{ $slider->description }}
                        </p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Elegant Navigation Arrows -->
        @if($sliders->count() > 1)
        <button onclick="prevSlide()" class="absolute left-3 md:left-6 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/40 backdrop-blur-sm text-white p-2 md:p-3 rounded-full transition-all duration-300 hover:scale-110 z-10 group">
            <svg class="w-5 h-5 md:w-6 md:h-6 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button onclick="nextSlide()" class="absolute right-3 md:right-6 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/40 backdrop-blur-sm text-white p-2 md:p-3 rounded-full transition-all duration-300 hover:scale-110 z-10 group">
            <svg class="w-5 h-5 md:w-6 md:h-6 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>

        <!-- Modern Dot Indicators -->
        <div class="absolute bottom-4 md:bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-2 z-10">
            @foreach($sliders as $index => $slider)
            <button onclick="goToSlide({{ $index }})" class="slider-dot transition-all duration-300 {{ $index === 0 ? 'w-8 bg-white' : 'w-2 bg-white/50 hover:bg-white/70' }} h-2 rounded-full" data-dot="{{ $index }}"></button>
            @endforeach
        </div>
        @endif

        <!-- Progress Bar -->
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-white/20 rounded-b-3xl overflow-hidden">
            <div id="slider-progress" class="h-full bg-gradient-to-r from-rose-500 to-pink-500 transition-all duration-100" style="width: 0%"></div>
        </div>
        </div>
    </div>

    <script>
        let currentSlide = 0;
        const totalSlides = {{ $sliders->count() }};
        let slideInterval;
        let progressInterval;
        let progress = 0;
        const slideDuration = 5000;

        function showSlide(index) {
            const slides = document.querySelectorAll('.slider-item');
            const dots = document.querySelectorAll('.slider-dot');
            
            slides.forEach((slide, i) => {
                if (i === index) {
                    slide.classList.remove('opacity-0');
                    slide.classList.add('opacity-100', 'active');
                } else {
                    slide.classList.remove('opacity-100', 'active');
                    slide.classList.add('opacity-0');
                }
            });
            
            dots.forEach((dot, i) => {
                if (i === index) {
                    dot.classList.remove('w-2', 'bg-white/50');
                    dot.classList.add('w-8', 'bg-white');
                } else {
                    dot.classList.remove('w-8', 'bg-white');
                    dot.classList.add('w-2', 'bg-white/50');
                }
            });
            
            currentSlide = index;
            resetProgress();
        }

        function resetProgress() {
            progress = 0;
            const progressBar = document.getElementById('slider-progress');
            if (progressBar) progressBar.style.width = '0%';
        }

        function updateProgress() {
            progress += 100 / (slideDuration / 50);
            const progressBar = document.getElementById('slider-progress');
            if (progressBar) progressBar.style.width = Math.min(progress, 100) + '%';
        }

        function nextSlide() {
            showSlide((currentSlide + 1) % totalSlides);
            resetInterval();
        }

        function prevSlide() {
            showSlide((currentSlide - 1 + totalSlides) % totalSlides);
            resetInterval();
        }

        function goToSlide(index) {
            showSlide(index);
            resetInterval();
        }

        function resetInterval() {
            clearInterval(slideInterval);
            clearInterval(progressInterval);
            resetProgress();
            slideInterval = setInterval(nextSlide, slideDuration);
            progressInterval = setInterval(updateProgress, 50);
        }

        // Auto-advance slides
        if (totalSlides > 1) {
            slideInterval = setInterval(nextSlide, slideDuration);
            progressInterval = setInterval(updateProgress, 50);
        }

        // Pause on hover
        const sliderContainer = document.querySelector('.slider-container');
        if (sliderContainer) {
            sliderContainer.parentElement.addEventListener('mouseenter', () => {
                clearInterval(slideInterval);
                clearInterval(progressInterval);
            });
            sliderContainer.parentElement.addEventListener('mouseleave', () => {
                resetInterval();
            });
        }
    </script>
    @endif

    <!-- Hero Section - Feminine & Elegant Design -->
    <div class="relative overflow-hidden bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50">
        <!-- Decorative Floral Elements -->
        <div class="absolute inset-0 overflow-hidden opacity-30">
            <div class="absolute top-20 right-20 w-64 h-64 bg-rose-200 rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
            <div class="absolute bottom-20 left-20 w-72 h-72 bg-pink-200 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-2000"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-4000"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-12 lg:pt-16 pb-16 sm:pb-20 lg:pb-28">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center">
                <!-- Left Content -->
                <div class="text-left space-y-6 sm:space-y-8">
                    <!-- Badge with Heart -->
                    <div class="inline-flex items-center space-x-2 bg-white/90 backdrop-blur-sm px-4 sm:px-5 py-2 sm:py-2.5 rounded-full shadow-lg border-2 border-rose-100">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-rose-500 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-xs sm:text-sm font-semibold text-gray-700">Women's Hostel in Chennai</span>
                    </div>

                    <!-- Main Heading -->
                    <div>
                        <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-display font-bold leading-tight mb-4 sm:mb-6">
                            <span class="text-gray-800">Where</span>
                            <span class="block bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500 bg-clip-text text-transparent">
                                Dreams Bloom
                            </span>
                        </h1>
                        <p class="text-base sm:text-lg lg:text-xl text-gray-600 leading-relaxed max-w-xl">
                            A nurturing sanctuary designed exclusively for women. Experience comfort, safety, and sisterhood in every corner.
                        </p>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                        <a href="#locations" class="group inline-flex items-center justify-center px-6 sm:px-8 lg:px-10 py-4 sm:py-5 bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500 text-white text-base sm:text-lg font-bold rounded-full shadow-2xl hover:shadow-rose-300/50 hover:scale-105 transition-all duration-300">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Book Your Room</span>
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                        <a href="#features" class="inline-flex items-center justify-center px-6 sm:px-8 lg:px-10 py-4 sm:py-5 bg-white text-gray-700 text-base sm:text-lg font-bold rounded-full shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 border-2 border-rose-100">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                            </svg>
                            Explore Amenities
                        </a>
                    </div>

                    <!-- Quick Stats with Icons -->
                    <div class="grid grid-cols-3 gap-3 sm:gap-4 lg:gap-6 pt-6 sm:pt-8">
                        <div class="text-center">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-rose-100 to-pink-100 rounded-full flex items-center justify-center mx-auto mb-1 sm:mb-2">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                </svg>
                            </div>
                            <div class="text-2xl sm:text-3xl font-bold text-rose-600">{{ $branches->count() }}+</div>
                            <div class="text-xs sm:text-sm text-gray-600 mt-0.5 sm:mt-1">Prime Locations</div>
                        </div>
                        <div class="text-center">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-pink-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-1 sm:mb-2">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                            </div>
                            <div class="text-2xl sm:text-3xl font-bold text-pink-600">{{ $branches->sum('rooms_count') }}+</div>
                            <div class="text-xs sm:text-sm text-gray-600 mt-0.5 sm:mt-1">Cozy Rooms</div>
                        </div>
                        <div class="text-center">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-1 sm:mb-2">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <div class="text-2xl sm:text-3xl font-bold text-purple-600">24/7</div>
                            <div class="text-xs sm:text-sm text-gray-600 mt-0.5 sm:mt-1">Safe & Secure</div>
                        </div>
                    </div>
                </div>

                <!-- Right Visual - Elegant Illustration -->
                <div class="relative lg:block hidden">
                    <div class="relative">
                        <!-- Main Image Card -->
                        <div class="relative z-10 rounded-3xl overflow-hidden shadow-2xl border-4 border-white">
                            <div class="aspect-[4/5] bg-gradient-to-br from-rose-300 via-pink-300 to-purple-300 flex items-center justify-center p-12">
                                <div class="text-center text-white">
                                    <!-- Decorative Icon -->
                                    <div class="w-32 h-32 mx-auto mb-6 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                                        <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-2xl font-bold mb-2">Your Home Away From Home</h3>
                                    <p class="text-white/90">Where comfort meets community</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating Feature Cards -->
                        <div class="absolute -bottom-6 -left-6 bg-white rounded-2xl shadow-2xl p-5 border-2 border-rose-100 animate-float">
                            <div class="flex items-center space-x-3">
                                <div class="w-14 h-14 bg-gradient-to-br from-green-100 to-emerald-100 rounded-2xl flex items-center justify-center">
                                    <svg class="w-7 h-7 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-900">100% Women Safe</div>
                                    <div class="text-xs text-gray-500">Verified & Secure</div>
                                </div>
                            </div>
                        </div>

                        <div class="absolute -top-6 -right-6 bg-white rounded-2xl shadow-2xl p-5 border-2 border-purple-100 animate-float animation-delay-2000">
                            <div class="flex items-center space-x-3">
                                <div class="w-14 h-14 bg-gradient-to-br from-purple-100 to-pink-100 rounded-2xl flex items-center justify-center">
                                    <svg class="w-7 h-7 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-900">Community Events</div>
                                    <div class="text-xs text-gray-500">Connect & Grow</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section - Soft & Welcoming -->
    <div id="features" class="py-12 sm:py-16 lg:py-24 bg-gradient-to-b from-white to-rose-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-10 sm:mb-12 lg:mb-16">
                <div class="inline-flex items-center px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-gradient-to-r from-rose-100 via-pink-100 to-purple-100 text-rose-700 text-xs sm:text-sm font-bold mb-4 sm:mb-6 border-2 border-rose-200">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1.5 sm:mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    THOUGHTFUL AMENITIES
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-display font-bold text-gray-900 mb-3 sm:mb-4 px-4">
                    Designed With
                    <span class="block text-transparent bg-clip-text bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500">
                        You in Mind
                    </span>
                </h2>
                <p class="text-base sm:text-lg text-gray-600 max-w-2xl mx-auto px-4">
                    Every detail crafted to make you feel at home, safe, and empowered
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                @php
                $features = [
                    ['icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'title' => '24/7 Security', 'desc' => 'Your safety is our priority with CCTV, biometric access, and trained female security staff', 'color' => 'rose', 'emoji' => '🛡️'],
                    ['icon' => 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0', 'title' => 'High-Speed WiFi', 'desc' => 'Stay connected with unlimited fiber internet perfect for work and study', 'color' => 'purple', 'emoji' => '📶'],
                    ['icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'title' => 'Fully Furnished', 'desc' => 'Move in hassle-free with premium furniture, AC, wardrobe, and cozy bedding', 'color' => 'pink', 'emoji' => '🏠'],
                    ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'Daily Housekeeping', 'desc' => 'Relax while our professional team maintains a clean and hygienic environment', 'color' => 'green', 'emoji' => '✨'],
                    ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'title' => 'Sisterhood Community', 'desc' => 'Join workshops, events, and make lifelong friendships with amazing women', 'color' => 'amber', 'emoji' => '💕'],
                    ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'No Curfews', 'desc' => 'Live freely with 24/7 access and smart card entry - your schedule, your rules', 'color' => 'indigo', 'emoji' => '🔑'],
                ];
                @endphp

                @foreach($features as $feature)
                    <div class="group relative bg-white p-6 sm:p-8 rounded-2xl sm:rounded-3xl border-2 border-{{ $feature['color'] }}-100 hover:border-{{ $feature['color'] }}-300 hover:shadow-2xl hover:shadow-{{ $feature['color'] }}-100/50 transition-all duration-300 hover:-translate-y-2">
                        <div class="relative">
                            <!-- Emoji Badge -->
                            <div class="text-3xl sm:text-4xl mb-3 sm:mb-4">{{ $feature['emoji'] }}</div>
                            
                            <!-- Icon Circle -->
                            <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-{{ $feature['color'] }}-100 to-{{ $feature['color'] }}-200 rounded-xl sm:rounded-2xl flex items-center justify-center mb-4 sm:mb-5 group-hover:scale-110 transition-transform duration-300 shadow-md">
                                <svg class="w-7 h-7 sm:w-8 sm:h-8 text-{{ $feature['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"></path>
                                </svg>
                            </div>
                            
                            <!-- Content -->
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-2 sm:mb-3">{{ $feature['title'] }}</h3>
                            <p class="text-sm sm:text-base text-gray-600 leading-relaxed">{{ $feature['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Locations Section - Modern Cards -->
    <div id="locations" class="py-12 sm:py-16 lg:py-24 bg-gradient-to-b from-gray-50 via-white to-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-10 sm:mb-12 lg:mb-16">
                <span class="inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-secondary-100 to-pink-100 text-secondary-700 text-xs sm:text-sm font-bold mb-3 sm:mb-4">
                    📍 OUR LOCATIONS
                </span>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-display font-bold text-gray-900 mb-3 sm:mb-4 px-4">
                    Find Your Perfect
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-secondary-600">Location</span>
                </h2>
                <p class="text-base sm:text-lg text-gray-600 max-w-2xl mx-auto px-4">
                    Premium hostels strategically located in the heart of the city
                </p>
            </div>

            <!-- Locations Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                @foreach($branches as $branch)
                    <div class="group relative bg-white rounded-2xl sm:rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 border-2 border-gray-100 hover:border-primary-200">
                        <!-- Image Section with Hostel Background -->
                        <div class="relative h-64 sm:h-72 overflow-hidden">
                            <!-- Hostel Image Background -->
                            <div class="absolute inset-0 bg-cover bg-center group-hover:scale-110 transition-transform duration-700" 
                                style="background-image: url('https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=800&q=80');">
                            </div>
                            
                            <!-- Gradient Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20"></div>

                            <!-- Room Count Badge -->
                            <div class="absolute top-4 right-4 bg-white/95 backdrop-blur-sm rounded-full px-3 sm:px-4 py-2 shadow-lg border border-white/20">
                                <div class="flex items-center space-x-1.5 sm:space-x-2">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    <span class="text-xs sm:text-sm font-bold text-gray-900">{{ $branch->rooms_count }} Rooms</span>
                                </div>
                            </div>

                            <!-- Location Name Overlay -->
                            <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-6">
                                <h3 class="text-xl sm:text-2xl font-display font-bold text-white mb-2">{{ $branch->name }}</h3>
                                <div class="flex items-start text-white/90 text-xs sm:text-sm mb-4">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5 sm:mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    </svg>
                                    <span class="line-clamp-2">{{ $branch->address }}</span>
                                </div>
                                
                                <!-- Amenities Tags on Image -->
                                <div class="flex flex-wrap gap-1.5 sm:gap-2 mb-4">
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-lg text-xs font-bold bg-white/20 backdrop-blur-sm text-white border border-white/30">
                                        <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 mr-1 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                                        </svg>
                                        WiFi
                                    </span>
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-lg text-xs font-bold bg-white/20 backdrop-blur-sm text-white border border-white/30">
                                        <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 mr-1 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        AC
                                    </span>
                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-lg text-xs font-bold bg-white/20 backdrop-blur-sm text-white border border-white/30">
                                        <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 mr-1 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        24/7
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Content Section -->
                        <div class="p-4 sm:p-6">
                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                                @if($branch->google_map_url)
                                    <a href="{{ $branch->google_map_url }}" target="_blank" class="flex-1 inline-flex items-center justify-center px-3 sm:px-4 py-2.5 sm:py-3 border-2 border-gray-200 text-xs sm:text-sm font-bold rounded-lg sm:rounded-xl text-gray-700 hover:border-primary-500 hover:text-primary-600 hover:bg-primary-50 transition-all duration-300">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        </svg>
                                        View Map
                                    </a>
                                @endif
                                <a href="{{ route('booking.branch', $branch) }}" class="flex-1 inline-flex items-center justify-center px-3 sm:px-4 py-2.5 sm:py-3 bg-gradient-to-r from-primary-600 to-secondary-600 text-xs sm:text-sm font-bold rounded-lg sm:rounded-xl text-white hover:from-primary-700 hover:to-secondary-700 transition-all duration-300 shadow-lg hover:shadow-xl group-hover:scale-105">
                                    Book Now
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 ml-1 sm:ml-1.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Testimonials Section - Warm & Personal -->
    <div class="py-12 sm:py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 sm:mb-12 lg:mb-16">
                <div class="inline-flex items-center px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-gradient-to-r from-amber-100 to-yellow-100 text-amber-700 text-xs sm:text-sm font-bold mb-4 sm:mb-6 border-2 border-amber-200">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1.5 sm:mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    STORIES FROM OUR FAMILY
                </div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-display font-bold text-gray-900 mb-3 sm:mb-4 px-4">
                    Hear From Our
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-500 to-pink-500">Sisters</span>
                </h2>
                <p class="text-base sm:text-lg text-gray-600 max-w-2xl mx-auto px-4">
                    Real experiences from women who call this place home
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                @php
                $testimonials = [
                    ['name' => 'Priya Sharma', 'role' => 'Software Engineer', 'text' => 'Best decision I made! The community here is amazing and I feel completely safe. The amenities are top-notch and the sisterhood is real.', 'color' => 'rose'],
                    ['name' => 'Ananya Reddy', 'role' => 'Medical Student', 'text' => 'Perfect for students like me. High-speed WiFi, quiet study spaces, and supportive roommates. This place feels like a second home!', 'color' => 'pink'],
                    ['name' => 'Meera Patel', 'role' => 'Marketing Professional', 'text' => 'The location is perfect for my office commute. The staff treats us like family and the rooms are always spotless. Highly recommend!', 'color' => 'purple'],
                ];
                @endphp

                @foreach($testimonials as $testimonial)
                    <div class="group relative bg-gradient-to-br from-{{ $testimonial['color'] }}-50 to-white p-6 sm:p-8 rounded-2xl sm:rounded-3xl border-2 border-{{ $testimonial['color'] }}-100 hover:border-{{ $testimonial['color'] }}-300 hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                        <!-- Quote Icon -->
                        <div class="absolute top-4 right-4 sm:top-6 sm:right-6 text-{{ $testimonial['color'] }}-200 opacity-50">
                            <svg class="w-10 h-10 sm:w-12 sm:h-12" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                            </svg>
                        </div>

                        <!-- Stars -->
                        <div class="flex items-center mb-3 sm:mb-4">
                            @for($i = 0; $i < 5; $i++)
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            @endfor
                        </div>

                        <!-- Testimonial Text -->
                        <p class="text-sm sm:text-base text-gray-700 mb-5 sm:mb-6 leading-relaxed relative z-10">"{{ $testimonial['text'] }}"</p>

                        <!-- Author -->
                        <div class="flex items-center">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-{{ $testimonial['color'] }}-400 to-{{ $testimonial['color'] }}-500 rounded-full flex items-center justify-center text-white font-bold text-lg sm:text-xl shadow-lg">
                                {{ substr($testimonial['name'], 0, 1) }}
                            </div>
                            <div class="ml-3 sm:ml-4">
                                <div class="font-bold text-sm sm:text-base text-gray-900">{{ $testimonial['name'] }}</div>
                                <div class="text-xs sm:text-sm text-gray-500">{{ $testimonial['role'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Final CTA Section - Warm & Inviting -->
    <div class="relative py-16 sm:py-20 lg:py-28 overflow-hidden bg-gradient-to-br from-rose-500 via-pink-500 to-purple-500">
        <!-- Animated Background -->
        <div class="absolute inset-0 opacity-20">
            <div class="absolute top-0 left-0 w-64 sm:w-96 h-64 sm:h-96 bg-white rounded-full mix-blend-overlay filter blur-3xl animate-blob"></div>
            <div class="absolute bottom-0 right-0 w-64 sm:w-96 h-64 sm:h-96 bg-white rounded-full mix-blend-overlay filter blur-3xl animate-blob animation-delay-2000"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 sm:w-96 h-64 sm:h-96 bg-white rounded-full mix-blend-overlay filter blur-3xl animate-blob animation-delay-4000"></div>
        </div>

        <!-- Decorative Hearts -->
        <div class="absolute inset-0 overflow-hidden opacity-10 hidden sm:block">
            <svg class="absolute top-20 left-20 w-12 sm:w-16 h-12 sm:h-16 text-white animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
            </svg>
            <svg class="absolute bottom-32 right-32 w-10 sm:w-12 h-10 sm:h-12 text-white animate-pulse animation-delay-2000" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
            </svg>
        </div>
        
        <div class="relative max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <!-- Heart Icon -->
            <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 bg-white/20 backdrop-blur-sm rounded-full mb-6 sm:mb-8">
                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                </svg>
            </div>

            <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-display font-bold text-white mb-4 sm:mb-6 leading-tight">
                Your Journey to<br class="hidden sm:block"/>
                <span class="inline-block bg-white/20 backdrop-blur-sm px-4 sm:px-6 py-1.5 sm:py-2 rounded-xl sm:rounded-2xl mt-2">Empowerment Starts Here</span>
            </h2>
            <p class="text-base sm:text-lg lg:text-xl text-white/95 mb-8 sm:mb-12 max-w-2xl mx-auto leading-relaxed">
                Join a community of inspiring women who support, uplift, and celebrate each other every day
            </p>
            
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center items-center mb-10 sm:mb-16">
                <a href="#locations" class="w-full sm:w-auto group inline-flex items-center justify-center px-8 sm:px-10 lg:px-12 py-4 sm:py-5 bg-white text-rose-600 text-base sm:text-lg font-bold rounded-full hover:bg-gray-50 transition-all duration-300 shadow-2xl hover:shadow-white/50 transform hover:scale-105">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Book Your Room Today
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
                <a href="#" class="w-full sm:w-auto inline-flex items-center justify-center px-8 sm:px-10 lg:px-12 py-4 sm:py-5 bg-white/10 backdrop-blur-sm border-2 border-white text-white text-base sm:text-lg font-bold rounded-full hover:bg-white/20 transition-all duration-300">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    Schedule a Visit
                </a>
            </div>

            <!-- Trust Indicators with Icons -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 lg:gap-8 max-w-3xl mx-auto">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl sm:rounded-2xl p-5 sm:p-6 border-2 border-white/20">
                    <div class="text-3xl sm:text-4xl font-bold text-white mb-1 sm:mb-2">500+</div>
                    <div class="text-white/90 text-sm font-medium">Happy Residents</div>
                    <div class="text-white/70 text-xs mt-0.5 sm:mt-1">Living their best lives</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl sm:rounded-2xl p-5 sm:p-6 border-2 border-white/20">
                    <div class="flex items-center justify-center mb-1 sm:mb-2">
                        <span class="text-3xl sm:text-4xl font-bold text-white">4.9</span>
                        <svg class="w-7 h-7 sm:w-8 sm:h-8 text-amber-300 ml-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    </div>
                    <div class="text-white/90 text-sm font-medium">Average Rating</div>
                    <div class="text-white/70 text-xs mt-0.5 sm:mt-1">From verified residents</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl sm:rounded-2xl p-5 sm:p-6 border-2 border-white/20">
                    <div class="text-3xl sm:text-4xl font-bold text-white mb-1 sm:mb-2">100%</div>
                    <div class="text-white/90 text-sm font-medium">Women Safe</div>
                    <div class="text-white/70 text-xs mt-0.5 sm:mt-1">Your safety, our priority</div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(20px, -50px) scale(1.1); }
            50% { transform: translate(-20px, 20px) scale(0.9); }
            75% { transform: translate(50px, 50px) scale(1.05); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
    </style>
@endsection