@extends('layouts.admin')

@section('header', 'Dashboard')

@section('content')
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500 rounded-3xl p-8 mb-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-display font-bold mb-2">Welcome back! 👋</h2>
                <p class="text-white/90">Here's what's happening with your hostels today.</p>
            </div>
            <svg class="w-24 h-24 opacity-20" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
            </svg>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-rose-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-rose-100 to-pink-100 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-rose-600 bg-rose-50 px-3 py-1 rounded-full">Active</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Total Branches</p>
                <p class="text-3xl font-bold text-gray-900">{{ $stats['branches'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-pink-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-pink-100 to-purple-100 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z"></path>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-pink-600 bg-pink-50 px-3 py-1 rounded-full">Managed</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Total Rooms</p>
                <p class="text-3xl font-bold text-gray-900">{{ $stats['rooms'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-purple-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-3 py-1 rounded-full">Total</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Total Beds</p>
                <p class="text-3xl font-bold text-gray-900">{{ $stats['beds'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-green-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-green-100 to-emerald-100 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-green-600 bg-green-50 px-3 py-1 rounded-full">Available</span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Vacant Beds</p>
                <p class="text-3xl font-bold text-gray-900">{{ $stats['vacant_beds'] }}</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-2xl shadow-lg p-8 border-2 border-gray-100">
        <h3 class="text-2xl font-display font-bold text-gray-900 mb-6 flex items-center">
            <svg class="w-6 h-6 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            Quick Actions
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('admin.branches.create') }}"
                class="group flex items-center justify-center px-6 py-4 bg-gradient-to-r from-rose-500 to-pink-500 text-white font-bold rounded-xl hover:from-rose-600 hover:to-pink-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add New Branch
            </a>
            <a href="{{ route('admin.rooms.create') }}"
                class="group flex items-center justify-center px-6 py-4 bg-gradient-to-r from-pink-500 to-purple-500 text-white font-bold rounded-xl hover:from-pink-600 hover:to-purple-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add New Room
            </a>
            <a href="{{ route('admin.customers.index') }}"
                class="group flex items-center justify-center px-6 py-4 bg-gradient-to-r from-purple-500 to-indigo-500 text-white font-bold rounded-xl hover:from-purple-600 hover:to-indigo-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                View Customers
            </a>
        </div>
    </div>
@endsection