<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Hostel Management') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600&family=Outfit:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-800 bg-primary-50">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-primary-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="font-display font-bold text-xl sm:text-2xl text-primary-600">
                        Serene<span class="text-gray-800">Stay</span>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ route('about') }}" class="text-gray-600 hover:text-primary-600 font-medium transition whitespace-nowrap">About Us</a>
                    <a href="{{ route('gallery') }}" class="text-gray-600 hover:text-primary-600 font-medium transition whitespace-nowrap">Gallery</a>
                    <a href="{{ route('contact') }}" class="text-gray-600 hover:text-primary-600 font-medium transition whitespace-nowrap">Contact</a>
                    <a href="{{ route('customer.login') }}"
                        class="bg-gradient-to-r from-rose-500 to-pink-500 text-white px-6 py-2.5 rounded-full font-medium hover:from-rose-600 hover:to-pink-600 transition shadow-lg shadow-rose-200 whitespace-nowrap">
                        Login
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" type="button" class="text-gray-600 hover:text-primary-600 focus:outline-none focus:text-primary-600 transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path id="menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path id="close-icon" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-primary-100 bg-white">
            <div class="px-4 pt-2 pb-4 space-y-2">
                <a href="{{ route('about') }}" class="block px-4 py-3 text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg font-medium transition">About Us</a>
                <a href="{{ route('gallery') }}" class="block px-4 py-3 text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg font-medium transition">Gallery</a>
                <a href="{{ route('contact') }}" class="block px-4 py-3 text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg font-medium transition">Contact</a>
                <a href="{{ route('customer.login') }}"
                    class="block text-center bg-gradient-to-r from-rose-500 to-pink-500 text-white px-5 py-3 rounded-full font-medium hover:from-rose-600 hover:to-pink-600 transition shadow-lg shadow-rose-200">
                    Login
                </a>
            </div>
        </div>
    </nav>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const menuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('menu-icon');
            const closeIcon = document.getElementById('close-icon');

            menuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
                menuIcon.classList.toggle('hidden');
                closeIcon.classList.toggle('hidden');
            });

            // Close menu when clicking on a link
            const mobileLinks = mobileMenu.querySelectorAll('a');
            mobileLinks.forEach(link => {
                link.addEventListener('click', function() {
                    mobileMenu.classList.add('hidden');
                    menuIcon.classList.remove('hidden');
                    closeIcon.classList.add('hidden');
                });
            });
        });
    </script>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-primary-100 mt-12">
        <div class="max-w-7xl mx-auto py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 sm:gap-0">
                <div class="text-center sm:text-left">
                    <span class="font-display font-bold text-lg sm:text-xl text-gray-800">SereneStay</span>
                    <p class="text-gray-500 text-sm mt-1">Premium Women's Hostel</p>
                </div>
                <div class="text-gray-400 text-xs sm:text-sm text-center sm:text-right">
                    &copy; {{ date('Y') }} SereneStay. All rights reserved.
                </div>
            </div>
        </div>
    </footer>
</body>

</html>