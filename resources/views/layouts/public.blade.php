<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Hostel Management') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased text-gray-800 bg-primary-50">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-primary-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="font-display font-bold text-xl sm:text-2xl text-primary-600">
                        Honeybees<span class="text-gray-800"> Hostel</span>
                    </a>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ route('about') }}" class="text-gray-600 hover:text-primary-600 font-medium transition whitespace-nowrap">About Us</a>
                    <a href="{{ route('gallery') }}" class="text-gray-600 hover:text-primary-600 font-medium transition whitespace-nowrap">Gallery</a>
                    <a href="{{ route('contact') }}" class="text-gray-600 hover:text-primary-600 font-medium transition whitespace-nowrap">Contact</a>
                    <a href="{{ route('customer.login') }}" class="bg-gradient-to-r from-rose-500 to-pink-500 text-white px-6 py-2.5 rounded-full font-medium hover:from-rose-600 hover:to-pink-600 transition shadow-lg shadow-rose-200 whitespace-nowrap">Login</a>
                </div>
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" type="button" class="text-gray-600 hover:text-primary-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 md:hidden" style="z-index: 99998;"></div>
    
    <!-- Mobile Menu Sidebar (Guest Only) -->
    <div id="mobile-menu" class="fixed top-0 right-0 h-full w-80 max-w-[85vw] bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out md:hidden" style="transform: translateX(100%); z-index: 99999;">
        <div class="flex flex-col h-full">
            <!-- Header with Gradient -->
            <div class="relative bg-gradient-to-br from-rose-500 via-pink-500 to-purple-600 p-6 pb-8">
                <button id="close-menu-btn" type="button" class="absolute top-3 right-3 text-white/80 hover:text-white hover:bg-white/10 rounded-full p-1.5 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <div class="flex items-center gap-3 mt-2">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-display font-bold text-white">Honeybees</h2>
                        <p class="text-white/80 text-xs">Your Safe Haven Awaits</p>
                    </div>
                </div>
            </div>
            
            <!-- Menu Items with Cards -->
            <div class="flex-1 overflow-y-auto p-6 -mt-6">
                <div class="bg-white rounded-2xl shadow-lg p-2 mb-6">
                    <a href="{{ route('about') }}" class="flex items-center gap-4 px-4 py-4 text-gray-700 hover:bg-gradient-to-r hover:from-rose-50 hover:to-pink-50 rounded-xl font-medium transition-all group">
                        <div class="w-10 h-10 bg-gradient-to-br from-rose-100 to-pink-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <span class="font-semibold">About Us</span>
                            <p class="text-xs text-gray-500">Learn our story</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-rose-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    
                    <a href="{{ route('gallery') }}" class="flex items-center gap-4 px-4 py-4 text-gray-700 hover:bg-gradient-to-r hover:from-rose-50 hover:to-pink-50 rounded-xl font-medium transition-all group">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-100 to-pink-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <span class="font-semibold">Gallery</span>
                            <p class="text-xs text-gray-500">View our spaces</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-rose-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    
                    <a href="{{ route('contact') }}" class="flex items-center gap-4 px-4 py-4 text-gray-700 hover:bg-gradient-to-r hover:from-rose-50 hover:to-pink-50 rounded-xl font-medium transition-all group">
                        <div class="w-10 h-10 bg-gradient-to-br from-pink-100 to-rose-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <span class="font-semibold">Contact</span>
                            <p class="text-xs text-gray-500">Get in touch</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-rose-500 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                
                <!-- CTA Card -->
                <div class="bg-gradient-to-br from-rose-500 via-pink-500 to-purple-600 rounded-2xl p-6 text-white shadow-xl">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Already a Member?</h3>
                            <p class="text-white/80 text-sm">Access your account</p>
                        </div>
                    </div>
                    <a href="{{ route('customer.login') }}" class="block text-center bg-white text-rose-600 px-6 py-3 rounded-xl font-bold hover:bg-rose-50 transition shadow-lg mt-4">
                        Login to Your Account
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const mobileMenuBtn = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const overlay = document.getElementById('mobile-menu-overlay');
        const closeMenuBtn = document.getElementById('close-menu-btn');

        function toggleMobileMenu() {
            const isOpen = mobileMenu.style.transform === 'translateX(0px)' || mobileMenu.style.transform === 'translateX(0%)';
            
            if (isOpen) {
                // Close menu
                mobileMenu.style.transform = 'translateX(100%)';
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            } else {
                // Open menu
                mobileMenu.style.transform = 'translateX(0)';
                overlay.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', toggleMobileMenu);
        }
        if (overlay) {
            overlay.addEventListener('click', toggleMobileMenu);
        }
        if (closeMenuBtn) {
            closeMenuBtn.addEventListener('click', toggleMobileMenu);
        }

        // Close menu when clicking any link
        if (mobileMenu) {
            const menuLinks = mobileMenu.querySelectorAll('a');
            menuLinks.forEach(link => {
                link.addEventListener('click', () => {
                    const isOpen = mobileMenu.style.transform === 'translateX(0px)' || mobileMenu.style.transform === 'translateX(0%)';
                    if (isOpen) {
                        setTimeout(toggleMobileMenu, 100);
                    }
                });
            });
        }

        // Close on window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                mobileMenu.style.transform = 'translateX(100%)';
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }
        });

        // Ensure menu starts hidden
        window.addEventListener('DOMContentLoaded', () => {
            mobileMenu.style.transform = 'translateX(100%)';
            overlay.style.display = 'none';
        });
    </script>

    <main>
        @yield('content')
    </main>

    <footer class="bg-white border-t border-primary-100 mt-12">
        <div class="max-w-7xl mx-auto py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 sm:gap-0">
                <div class="text-center sm:text-left">
                    <span class="font-display font-bold text-lg sm:text-xl text-gray-800">Honeybees Hostel</span>
                    <p class="text-gray-500 text-sm mt-1">Premium Women's Hostel</p>
                </div>
                <div class="text-gray-400 text-xs sm:text-sm text-center sm:text-right">
                    &copy; {{ date('Y') }} Honeybees Hostel. All rights reserved.
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
