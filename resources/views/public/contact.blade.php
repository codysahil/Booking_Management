@extends('layouts.public')

@section('content')
    <!-- Hero Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-5xl md:text-6xl font-display font-bold text-gray-900 mb-6">
                    Get in
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500">
                        Touch
                    </span>
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                    We'd love to hear from you! Reach out with any questions or to schedule a visit
                </p>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16">
                <!-- Contact Form -->
                <div>
                    <h2 class="text-3xl font-display font-bold text-gray-900 mb-8">Send Us a Message</h2>
                    <form class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Your Name</label>
                            <input type="text" id="name" name="name" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-rose-500 focus:ring-0 transition-colors" placeholder="Enter your name">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="email" name="email" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-rose-500 focus:ring-0 transition-colors" placeholder="your@email.com">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-rose-500 focus:ring-0 transition-colors" placeholder="+1 (555) 000-0000">
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">Subject</label>
                            <select id="subject" name="subject" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-rose-500 focus:ring-0 transition-colors">
                                <option>General Inquiry</option>
                                <option>Room Booking</option>
                                <option>Schedule a Visit</option>
                                <option>Partnership</option>
                                <option>Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
                            <textarea id="message" name="message" rows="5" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-rose-500 focus:ring-0 transition-colors" placeholder="Tell us how we can help you..."></textarea>
                        </div>

                        <button type="submit" class="w-full inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500 text-white text-lg font-bold rounded-full hover:from-rose-600 hover:via-pink-600 hover:to-purple-600 transition-all duration-300 shadow-lg hover:shadow-xl">
                            Send Message
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- Contact Information -->
                <div>
                    <h2 class="text-3xl font-display font-bold text-gray-900 mb-8">Contact Information</h2>
                    
                    <div class="space-y-6 mb-12">
                        <!-- Phone -->
                        <div class="flex items-start space-x-4">
                            <div class="w-14 h-14 bg-gradient-to-br from-rose-100 to-pink-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-1">Phone</h3>
                                <a href="tel:+919944159321" class="text-gray-600 hover:text-rose-600 transition">+91 99441 59321</a>
                                <p class="text-sm text-gray-500 mt-1">Mon-Sat, 9AM-8PM</p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex items-start space-x-4">
                            <div class="w-14 h-14 bg-gradient-to-br from-pink-100 to-purple-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-7 h-7 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-1">Email</h3>
                                <a href="mailto:senthiltogether@gmail.com" class="text-gray-600 hover:text-rose-600 transition">senthiltogether@gmail.com</a>
                                <p class="text-sm text-gray-500 mt-1">We'll respond within 24 hours</p>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="flex items-start space-x-4">
                            <div class="w-14 h-14 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-1">Locations</h3>
                                <p class="text-gray-600 mb-3">
                                    <strong>Branch 1:</strong><br>
                                    50, Annai Indira Nagar 1st Main Road,<br>
                                    Thoraipakkam, Chennai - 600097<br>
                                    <span class="text-sm text-gray-500">(Near Tansq Jewellery)</span>
                                </p>
                                <p class="text-gray-600">
                                    <strong>Branch 2:</strong><br>
                                    Nethaji 1st Cross Street,<br>
                                    Muttukkaranchavadi, Thoraipakkam,<br>
                                    Chennai - 600097<br>
                                    <span class="text-sm text-gray-500">(Near Tansq Jewellery)</span>
                                </p>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="flex items-start space-x-4">
                            <div class="w-14 h-14 bg-gradient-to-br from-rose-100 to-orange-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-7 h-7 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-2">Follow Us</h3>
                                <div class="flex space-x-3">
                                    <a href="#" class="w-10 h-10 bg-rose-100 rounded-full flex items-center justify-center hover:bg-rose-200 transition-colors">
                                        <span class="text-rose-600 font-bold">f</span>
                                    </a>
                                    <a href="#" class="w-10 h-10 bg-pink-100 rounded-full flex items-center justify-center hover:bg-pink-200 transition-colors">
                                        <span class="text-pink-600 font-bold">in</span>
                                    </a>
                                    <a href="#" class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center hover:bg-purple-200 transition-colors">
                                        <span class="text-purple-600 font-bold">ig</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="bg-gradient-to-br from-rose-50 to-pink-50 rounded-3xl p-8 border-2 border-rose-100">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="{{ route('home') }}#locations" class="flex items-center justify-between p-4 bg-white rounded-xl hover:shadow-md transition-all duration-300 group">
                                <span class="font-semibold text-gray-700">Book a Room</span>
                                <svg class="w-5 h-5 text-rose-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                            <a href="{{ route('gallery') }}" class="flex items-center justify-between p-4 bg-white rounded-xl hover:shadow-md transition-all duration-300 group">
                                <span class="font-semibold text-gray-700">View Gallery</span>
                                <svg class="w-5 h-5 text-pink-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                            <a href="{{ route('about') }}" class="flex items-center justify-between p-4 bg-white rounded-xl hover:shadow-md transition-all duration-300 group">
                                <span class="font-semibold text-gray-700">About Us</span>
                                <svg class="w-5 h-5 text-purple-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Section (Placeholder) -->
    <div class="py-20 bg-gradient-to-b from-rose-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-display font-bold text-gray-900 mb-8 text-center">Find Us</h2>
            <div class="aspect-[16/9] bg-gradient-to-br from-rose-200 via-pink-200 to-purple-200 rounded-3xl shadow-xl flex items-center justify-center">
                <div class="text-center text-white">
                    <svg class="w-24 h-24 mx-auto mb-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <p class="text-xl font-semibold">Map Integration Coming Soon</p>
                </div>
            </div>
        </div>
    </div>
@endsection
