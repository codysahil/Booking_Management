@extends('layouts.public')

@section('content')
    <div class="relative overflow-hidden bg-gradient-to-br from-teal-50 via-cyan-50 to-violet-50 py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-display font-bold text-gray-900 mb-4">Terms & Conditions</h1>
            <p class="text-lg text-gray-600">Please read these terms before booking a stay at {{ setting('hostel_name') }}.</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 md:p-12">
            @if(setting('terms'))
                <div class="prose max-w-none text-gray-700 whitespace-pre-line">{{ setting('terms') }}</div>
            @else
                <p class="text-gray-500">Terms & conditions have not been published yet. Please contact the office for details.</p>
            @endif
        </div>
    </div>
@endsection
