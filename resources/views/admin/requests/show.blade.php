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

        <form method="POST" action="{{ route('admin.requests.update', $residentRequest) }}" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')
            <x-form.card title="Respond">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-6l-4 4v-4z"/></svg>
                </x-slot:icon>

                <div class="space-y-5">
                    <x-form.select name="status" label="Status">
                        @foreach (\App\Models\Request::STATUSES as $value => $label)
                            <option value="{{ $value }}" {{ $residentRequest->status == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-form.select>

                    <x-form.textarea name="admin_response" label="Response to resident" :rows="4" :value="$residentRequest->admin_response"
                        hint="Emailed to the resident if they have an email on file." />
                </div>

                <div class="mt-6 flex justify-end">
                    <x-form.button label="Save & Notify Resident" loading-label="Saving…" />
                </div>
            </x-form.card>
        </form>
    </div>
@endsection
