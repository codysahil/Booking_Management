@extends('layouts.customer')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8 flex items-center justify-center">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8 text-center">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Failed</h1>
            <p class="text-gray-600 mb-4">
                @if(session('error'))
                    {{ session('error') }}
                @elseif(request('error'))
                    {{ request('error') }}
                @else
                    Your payment could not be processed. Please try again.
                @endif
            </p>
            
            @if(request('code'))
            <p class="text-xs text-gray-400 mb-6">Error Code: {{ request('code') }}</p>
            @endif
            
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6 text-left">
                <p class="text-sm text-amber-800">
                    <strong>Note:</strong> If money was deducted from your account, it will be automatically refunded within 5-7 business days. 
                    If the payment was successful but you see this page, please check your payment history or contact support.
                </p>
            </div>
            
            <div class="space-y-3">
                <a href="{{ route('customer.payments.index') }}" 
                    class="block w-full bg-primary-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-primary-700 transition">
                    Try Again
                </a>
                <a href="{{ route('customer.dashboard') }}" 
                    class="block w-full bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-300 transition">
                    Go to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
