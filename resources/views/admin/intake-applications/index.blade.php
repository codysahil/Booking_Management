@extends('layouts.admin')

@section('header', 'Intake Applications')

@section('content')
    @if($intakeLink)
        <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6 mb-6" x-data="{ copied: false }">
            <label class="block text-xs font-medium text-gray-500 mb-1">Your shareable self-registration link</label>
            <p class="text-sm text-gray-500 mb-3">Share this with prospects — they fill their own details, and it lands here for you to review.</p>
            <div class="flex flex-col gap-2 sm:flex-row">
                <input type="text" readonly value="{{ $intakeLink }}" onclick="this.select()"
                    class="flex-1 text-sm border-gray-300 rounded-lg bg-gray-50 text-gray-700 focus:ring-primary-500 focus:border-primary-500">
                <button type="button"
                    @click="navigator.clipboard.writeText('{{ $intakeLink }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition whitespace-nowrap">
                    <span x-show="!copied">Copy Link</span>
                    <span x-show="copied" x-cloak>Copied!</span>
                </button>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6 mb-6">
        <form method="GET" class="flex gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full border-gray-300 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All statuses</option>
                    @foreach (\App\Models\IntakeApplication::STATUSES as $value => $label)
                        <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-700 transition">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-teal-50 to-cyan-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Applicant</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Branch Preference</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Submitted</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($applications as $application)
                    <tr class="hover:bg-teal-50/50">
                        <td class="px-6 py-4 text-sm">
                            <div class="font-medium text-gray-900">{{ $application->name }}</div>
                            <div class="text-xs text-gray-500">{{ $application->phone }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $application->branch->name ?? 'No preference' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $application->created_at->format('d M, Y') }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $application->status_color }}">
                                {{ $application->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('admin.intake-applications.show', $application) }}" class="text-primary-600 hover:text-primary-800 font-medium">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No applications found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($applications->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $applications->links() }}</div>
        @endif
    </div>
@endsection
