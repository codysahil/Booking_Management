@extends('layouts.public')

@section('content')
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-5xl md:text-6xl font-display font-bold text-gray-900 mb-6">
                    About
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500">
                        Honeybees Hostel
                    </span>
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    Creating safe, comfortable, and empowering spaces for women to thrive
                </p>
            </div>
        </div>
    </div>

    <!-- Our Story -->
    <div class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div>
                    <h2 class="text-4xl font-display font-bold text-gray-900 mb-6">Our Story</h2>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>
                            Honeybees Hostel was born from a simple yet powerful vision: to create a home away from home where women can feel safe, supported, and inspired to pursue their dreams.
                        </p>
                        <p>
                            We understand the challenges women face when moving to a new city - finding safe accommodation, building a support network, and maintaining work-life balance. That's why we've created more than just a hostel; we've built a community.
                        </p>
                        <p>
                            Every aspect of Honeybees Hostel is designed with women's needs in mind, from our 24/7 security to our community events that foster meaningful connections and personal growth.
                        </p>
                    </div>
                </div>
                <div class="relative">
                    <div class="aspect-square bg-gradient-to-br from-rose-300 via-pink-300 to-purple-300 rounded-3xl shadow-2xl flex items-center justify-center">
                        <svg class="w-32 h-32 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Our Values -->
    <div class="py-20 bg-gradient-to-b from-rose-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-display font-bold text-gray-900 mb-4">Our Core Values</h2>
                <p class="text-lg text-gray-600">The principles that guide everything we do</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-3xl border-2 border-rose-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-rose-100 to-pink-100 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Safety First</h3>
                    <p class="text-gray-600 leading-relaxed">Your security and peace of mind are our top priorities. We maintain the highest safety standards.</p>
                </div>

                <div class="bg-white p-8 rounded-3xl border-2 border-pink-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-100 to-purple-100 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-pink-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Community</h3>
                    <p class="text-gray-600 leading-relaxed">We foster a supportive sisterhood where women uplift, inspire, and celebrate each other.</p>
                </div>

                <div class="bg-white p-8 rounded-3xl border-2 border-purple-100 hover:shadow-xl transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Empowerment</h3>
                    <p class="text-gray-600 leading-relaxed">We provide resources and opportunities for personal and professional growth.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="py-20 bg-gradient-to-br from-rose-500 via-pink-500 to-purple-500">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl md:text-5xl font-display font-bold text-white mb-6">
                Ready to Join Our Community?
            </h2>
            <p class="text-xl text-white/90 mb-10">
                Experience the Honeybees Hostel difference for yourself
            </p>
            <a href="{{ route('home') }}#locations" class="inline-flex items-center justify-center px-12 py-5 bg-white text-rose-600 text-lg font-bold rounded-full hover:bg-gray-50 transition-all duration-300 shadow-2xl hover:shadow-white/50 transform hover:scale-105">
                Book Your Room
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
        </div>
    </div>
@endsection
