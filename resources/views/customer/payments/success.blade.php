@extends('layouts.customer')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8 flex items-center justify-center">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8 text-center">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
            <p class="text-gray-600 mb-6">Your payment has been processed successfully.</p>
            
            @if(isset($paymentId))
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <p class="text-sm text-gray-600 mb-1">Transaction ID</p>
                <p class="font-mono text-sm font-medium text-gray-900">{{ $paymentId }}</p>
            </div>
            @endif
            
            <div class="space-y-3">
                <a href="{{ route('customer.dashboard') }}" 
                    class="block w-full bg-primary-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-primary-700 transition">
                    Go to Dashboard
                </a>
                <a href="{{ route('customer.payments.history') }}" 
                    class="block w-full bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-300 transition">
                    View Payment History
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
