@extends('layouts.admin')

@section('header', 'Request Details')

@section('content')
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('admin.requests.index') }}" class="text-gray-500 hover:text-gray-700 flex items-center mb-6">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Requests
        </a>

        <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8 mb-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $residentRequest->subject }}</h2>
                    <p class="text-sm text-gray-500">{{ $residentRequest->type_label }} · Submitted {{ $residentRequest->created_at->format('d M, Y') }}</p>
                </div>
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ $residentRequest->status_label }}</span>
            </div>

            <div class="border-t border-gray-100 pt-4 mb-4">
                <p class="text-sm font-semibold text-gray-700 mb-1">Resident</p>
                <p class="text-sm text-gray-900">{{ $residentRequest->customer->name ?? 'Unknown' }} ({{ $residentRequest->customer->customer_code ?? '—' }})</p>
                <p class="text-sm text-gray-600">{{ $residentRequest->customer->phone ?? '' }}</p>
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
                    <p class="text-sm font-semibold text-gray-700 mb-1">Attachment</p>
                    <a href="{{ $residentRequest->attachment_url }}" target="_blank" class="text-primary-600 hover:underline text-sm">View attachment</a>
                </div>
            @endif

            @if($residentRequest->admin_response)
                <div class="mb-4 bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <p class="text-sm font-semibold text-blue-900 mb-1">Office response ({{ $residentRequest->handler->name ?? 'Staff' }})</p>
                    <p class="text-sm text-blue-900 whitespace-pre-line">{{ $residentRequest->admin_response }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Respond</h3>
            <form method="POST" action="{{ route('admin.requests.update', $residentRequest) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                        @foreach (\App\Models\Request::STATUSES as $value => $label)
                            <option value="{{ $value }}" {{ $residentRequest->status == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Response to resident (emailed if they have an email on file)</label>
                    <textarea name="admin_response" rows="4" class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">{{ old('admin_response', $residentRequest->admin_response) }}</textarea>
                </div>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-xl hover:from-teal-600 hover:to-cyan-600 transition shadow-lg">
                    Save & Notify Resident
                </button>
            </form>
        </div>
    </div>
@endsection
