@extends('layouts.admin')

@section('header', 'Application Details')

@section('content')
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('admin.intake-applications.index') }}" class="text-gray-500 hover:text-gray-700 flex items-center mb-6">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Applications
        </a>

        <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8 mb-6">
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center gap-4">
                    @if($intakeApplication->safe_photo_url)
                        <img src="{{ $intakeApplication->safe_photo_url }}" class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-md">
                    @else
                        <div class="w-16 h-16 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold text-xl">
                            {{ substr($intakeApplication->name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $intakeApplication->name }}</h2>
                        <p class="text-sm text-gray-500">Submitted {{ $intakeApplication->created_at->format('d M, Y h:i A') }}</p>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $intakeApplication->status_color }}">{{ $intakeApplication->status_label }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-100 pt-6">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $intakeApplication->phone }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Email</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $intakeApplication->email ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Date of Birth</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $intakeApplication->dob->format('d M, Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Guardian Phone</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $intakeApplication->guardian_phone }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Preferred Branch</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $intakeApplication->branch->name ?? 'No preference' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Preferred Move-in</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $intakeApplication->preferred_move_in_date?->format('d M, Y') ?? '—' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Permanent Address</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $intakeApplication->address }}</p>
                </div>
                @if($intakeApplication->work_details)
                    <div class="md:col-span-2">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Work/Study Details</p>
                        <p class="text-sm text-gray-900 mt-1">{{ $intakeApplication->work_details }}</p>
                    </div>
                @endif
                @if($intakeApplication->safe_id_proof_url)
                    <div class="md:col-span-2">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">ID Proof</p>
                        <a href="{{ $intakeApplication->safe_id_proof_url }}" target="_blank" class="text-primary-600 hover:underline text-sm">View Document</a>
                    </div>
                @endif
            </div>

            @if($intakeApplication->status === \App\Models\IntakeApplication::STATUS_CONVERTED && $intakeApplication->customer)
                <div class="mt-6 bg-green-50 border border-green-200 rounded-xl p-4">
                    <p class="text-sm text-green-900">
                        Converted to resident —
                        <a href="{{ route('admin.customers.show', $intakeApplication->customer) }}" class="font-semibold underline">{{ $intakeApplication->customer->name }}</a>
                    </p>
                </div>
            @endif

            @if($intakeApplication->admin_notes)
                <div class="mt-6 bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <p class="text-sm font-semibold text-gray-700 mb-1">Notes</p>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $intakeApplication->admin_notes }}</p>
                </div>
            @endif
        </div>

        @if($intakeApplication->isPending())
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('admin.customers.create', ['intake' => $intakeApplication->id]) }}"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-primary-600 text-white text-sm font-bold rounded-xl hover:bg-primary-700 transition">
                    Start Check-In With These Details
                </a>
                <form method="POST" action="{{ route('admin.intake-applications.reject', $intakeApplication) }}" class="flex-1"
                    onsubmit="return confirm('Mark this application as rejected?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full px-5 py-3 border-2 border-gray-200 text-gray-600 text-sm font-bold rounded-xl hover:bg-gray-50 transition">
                        Reject Application
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection
