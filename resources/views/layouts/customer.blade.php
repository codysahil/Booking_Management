<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ setting('hostel_name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600&family=Outfit:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="font-sans antialiased text-gray-800 bg-primary-50">
    <!-- Customer Navigation -->
    <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-primary-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('customer.dashboard') }}"
                        class="font-display font-bold text-xl sm:text-2xl text-primary-600">
                        {{ setting('hostel_name') }}
                    </a>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center gap-2 text-gray-700 hover:text-primary-600 transition">
                            @if(Auth::guard('customer')->user()->photo_path)
                                <img src="{{ Auth::guard('customer')->user()->safe_photo_url }}" alt="Profile"
                                    class="w-8 h-8 rounded-full object-cover border-2 border-primary-200">
                            @else
                                <div
                                    class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold text-sm border-2 border-primary-200">
                                    {{ substr(Auth::guard('customer')->user()->name, 0, 1) }}
                                </div>
                            @endif
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-2">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::guard('customer')->user()->name }}
                                </p>
                                <p class="text-xs text-gray-500">{{ Auth::guard('customer')->user()->customer_code }}
                                </p>
                            </div>
                            <a href="{{ route('customer.dashboard') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                    </path>
                                </svg>
                                Dashboard
                            </a>
                            <a href="{{ route('customer.payments.history') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                    </path>
                                </svg>
                                Payment History
                            </a>
                            <a href="{{ route('customer.requests.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-6l-4 4v-4z">
                                    </path>
                                </svg>
                                My Requests
                            </a>
                            <a href="{{ route('customer.announcements.index') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z">
                                    </path>
                                </svg>
                                Announcements
                            </a>
                            <form method="POST" action="{{ route('customer.logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                        </path>
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" type="button" class="text-gray-600 hover:text-primary-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 md:hidden" style="z-index: 99998;">
    </div>

    <!-- Mobile Menu Sidebar (Customer) -->
    <div id="mobile-menu"
        class="fixed top-0 right-0 h-full w-80 max-w-[85vw] bg-gradient-to-br from-teal-50 via-cyan-50 to-violet-50 shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out md:hidden"
        style="transform: translateX(100%); z-index: 99999;">
        <div class="flex flex-col h-full">
            <!-- Header with Close Button -->
            <div class="bg-gradient-to-r from-teal-500 to-cyan-500 p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="font-display font-bold text-xl text-white">{{ setting('hostel_name') }}</span>
                    <button id="close-menu-btn" type="button"
                        class="text-white hover:bg-white/20 rounded-full p-2 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- User Profile Card -->
                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-4">
                    <div class="flex items-center gap-3">
                        @if(Auth::guard('customer')->user()->photo_path)
                            <img src="{{ Auth::guard('customer')->user()->safe_photo_url }}" alt="Profile"
                                class="w-14 h-14 rounded-full object-cover border-3 border-white shadow-lg">
                        @else
                            <div
                                class="w-14 h-14 rounded-full bg-white flex items-center justify-center text-primary-600 font-bold text-xl border-3 border-white shadow-lg">
                                {{ substr(Auth::guard('customer')->user()->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="flex-1">
                            <p class="text-sm font-bold text-white">{{ Auth::guard('customer')->user()->name }}</p>
                            <p class="text-xs text-white/80">{{ Auth::guard('customer')->user()->customer_code }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu Items -->
            <div class="flex-1 overflow-y-auto p-4 space-y-1">
                <a href="{{ route('customer.dashboard') }}"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-white rounded-xl font-medium transition group">
                    <svg class="w-5 h-5 text-primary-500 group-hover:scale-110 transition" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('customer.payments.history') }}"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-white rounded-xl font-medium transition group">
                    <svg class="w-5 h-5 text-primary-500 group-hover:scale-110 transition" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                        </path>
                    </svg>
                    <span>Payment History</span>
                </a>
                <a href="{{ route('customer.requests.index') }}"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-white rounded-xl font-medium transition group">
                    <svg class="w-5 h-5 text-primary-500 group-hover:scale-110 transition" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-6l-4 4v-4z">
                        </path>
                    </svg>
                    <span>My Requests</span>
                </a>
                <a href="{{ route('customer.announcements.index') }}"
                    class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-white rounded-xl font-medium transition group">
                    <svg class="w-5 h-5 text-primary-500 group-hover:scale-110 transition" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z">
                        </path>
                    </svg>
                    <span>Announcements</span>
                </a>
            </div>

            <!-- Logout Button -->
            <div class="p-4">
                <form method="POST" action="{{ route('customer.logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white text-red-600 hover:bg-red-50 rounded-xl font-bold transition shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
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
                mobileMenu.style.transform = 'translateX(100%)';
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            } else {
                mobileMenu.style.transform = 'translateX(0)';
                overlay.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        }

        if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', toggleMobileMenu);
        if (overlay) overlay.addEventListener('click', toggleMobileMenu);
        if (closeMenuBtn) closeMenuBtn.addEventListener('click', toggleMobileMenu);

        if (mobileMenu) {
            const menuLinks = mobileMenu.querySelectorAll('a');
            menuLinks.forEach(link => {
                link.addEventListener('click', () => {
                    const isOpen = mobileMenu.style.transform === 'translateX(0px)' || mobileMenu.style.transform === 'translateX(0%)';
                    if (isOpen) setTimeout(toggleMobileMenu, 100);
                });
            });
        }

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                mobileMenu.style.transform = 'translateX(100%)';
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }
        });

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
                    <span class="font-display font-bold text-lg sm:text-xl text-gray-800">{{ setting('hostel_name') }}</span>
                    <p class="text-gray-500 text-sm mt-1">{{ setting('tagline') }}</p>
                </div>
                <div class="text-gray-400 text-xs sm:text-sm text-center sm:text-right">
                    &copy; {{ date('Y') }} {{ setting('hostel_name') }}. All rights reserved.
                </div>
            </div>
        </div>
    </footer>
</body>

</html>