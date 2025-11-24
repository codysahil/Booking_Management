<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Hostel Management') }} - Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600&family=Outfit:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50">
    <div class="min-h-screen flex">
        <!-- Mobile Menu Overlay -->
        <div id="mobileMenuOverlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="w-56 bg-white/80 backdrop-blur-sm border-r border-rose-100 shadow-lg fixed h-screen overflow-y-auto z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
            <div class="p-4 border-b border-rose-100">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-rose-500 to-pink-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-base font-display font-bold text-gray-800">SereneStay</div>
                        <div class="text-xs text-gray-500">Admin</div>
                    </div>
                </a>
            </div>
            <nav class="p-3 space-y-1">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gradient-to-r hover:from-rose-50 hover:to-pink-50 hover:text-rose-600 transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-rose-50 to-pink-50 text-rose-600 font-semibold' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.branches.index') }}"
                    class="flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gradient-to-r hover:from-rose-50 hover:to-pink-50 hover:text-rose-600 transition-all duration-200 {{ request()->routeIs('admin.branches.*') ? 'bg-gradient-to-r from-rose-50 to-pink-50 text-rose-600 font-semibold' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Branches</span>
                </a>
                <a href="{{ route('admin.rooms.index') }}"
                    class="flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gradient-to-r hover:from-rose-50 hover:to-pink-50 hover:text-rose-600 transition-all duration-200 {{ request()->routeIs('admin.rooms.*') ? 'bg-gradient-to-r from-rose-50 to-pink-50 text-rose-600 font-semibold' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z"></path>
                    </svg>
                    <span>Rooms</span>
                </a>
                <a href="{{ route('admin.bookings.index') }}"
                    class="flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gradient-to-r hover:from-rose-50 hover:to-pink-50 hover:text-rose-600 transition-all duration-200 {{ request()->routeIs('admin.bookings.*') ? 'bg-gradient-to-r from-rose-50 to-pink-50 text-rose-600 font-semibold' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span>Bookings</span>
                </a>
                <a href="{{ route('admin.customers.index') }}"
                    class="flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gradient-to-r hover:from-rose-50 hover:to-pink-50 hover:text-rose-600 transition-all duration-200 {{ request()->routeIs('admin.customers.*') ? 'bg-gradient-to-r from-rose-50 to-pink-50 text-rose-600 font-semibold' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>Customers</span>
                </a>
                <a href="{{ route('admin.employees.index') }}"
                    class="flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gradient-to-r hover:from-rose-50 hover:to-pink-50 hover:text-rose-600 transition-all duration-200 {{ request()->routeIs('admin.employees.*') ? 'bg-gradient-to-r from-rose-50 to-pink-50 text-rose-600 font-semibold' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span>Employees</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 w-full md:ml-56 overflow-y-auto">
            <header class="bg-white/80 backdrop-blur-sm border-b border-rose-100 px-4 md:px-6 py-4 sticky top-0 z-30">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <!-- Mobile Menu Button -->
                        <button id="mobileMenuBtn" class="md:hidden p-2 rounded-lg hover:bg-rose-50 transition-colors">
                            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <h1 class="text-xl md:text-2xl font-display font-bold text-gray-800">@yield('header')</h1>
                    </div>
                    <div class="flex items-center space-x-2 md:space-x-3">
                        <a href="{{ route('home') }}" target="_blank" class="hidden sm:flex text-xs text-gray-600 hover:text-rose-600 transition items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            <span class="hidden md:inline">View Site</span>
                        </a>
                        <div class="flex items-center space-x-2 px-2 lg:px-3 py-1.5 bg-gradient-to-r from-rose-50 to-pink-50 rounded-full">
                            <div class="w-6 h-6 bg-gradient-to-br from-rose-400 to-pink-400 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                A
                            </div>
                            <span class="hidden sm:inline text-xs font-medium text-gray-700">Admin</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="p-4 md:p-6">
                @if(session('success'))
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-4 flex items-center text-sm shadow-sm"
                        role="alert">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-4 flex items-center text-sm shadow-sm"
                        role="alert">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileMenuOverlay');

        function toggleMobileMenu() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        }

        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
        overlay.addEventListener('click', toggleMobileMenu);

        // Close mobile menu when clicking a link
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    toggleMobileMenu();
                }
            });
        });

        // Close mobile menu on window resize to desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        });
    </script>
</body>

</html>