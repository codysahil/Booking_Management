@extends('layouts.customer')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-teal-50 via-cyan-50 to-violet-50 py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('customer.requests.index') }}" class="text-gray-500 hover:text-gray-700 flex items-center mb-6">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to My Requests
            </a>

            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8">
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'approved' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800',
                        'completed' => 'bg-blue-100 text-blue-800',
                    ];
                @endphp
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $residentRequest->subject }}</h2>
                        <p class="text-sm text-gray-500">{{ $residentRequest->type_label }} · Submitted {{ $residentRequest->created_at->format('d M, Y') }}</p>
                    </div>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusColors[$residentRequest->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $residentRequest->status_label }}
                    </span>
                </div>

                @if($residentRequest->preferred_date)
                    <div class="mb-4">
                        <p class="text-sm font-semibold text-gray-700 mb-1">Preferred / Move-out Date</p>
                        <p class="text-sm text-gray-900">{{ $residentRequest->preferred_date->format('d M, Y') }}</p>
                    </div>
                @endif

                <div class="mb-4">
                    <p class="text-sm font-semibold text-gray-700 mb-1">Description</p>
                    <p class="text-sm text-gray-900 whitespace-pre-line">{{ $residentRequest->description ?: '—' }}</p>
                </div>

                @if($residentRequest->attachment_url)
                    <div class="mb-4">
                        <a href="{{ $residentRequest->attachment_url }}" target="_blank" class="text-primary-600 hover:underline text-sm">View attachment</a>
                    </div>
                @endif

                @if($residentRequest->admin_response)
                    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <p class="text-sm font-semibold text-blue-900 mb-1">Response from the office</p>
                        <p class="text-sm text-blue-900 whitespace-pre-line">{{ $residentRequest->admin_response }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
