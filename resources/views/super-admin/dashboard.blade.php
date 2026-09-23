@extends('layouts.super-admin')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-display font-bold text-gray-900">Dashboard</h1>
        <a href="{{ route('super-admin.tenants.create') }}"
            class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 transition">
            + Onboard a hostel
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-500">Total hostels</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_tenants'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-500">Active</p>
            <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['active_tenants'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-500">On trial</p>
            <p class="text-3xl font-bold text-amber-600 mt-1">{{ $stats['on_trial'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-100 font-bold text-gray-900">Recently onboarded</div>
        <div class="divide-y divide-gray-100">
            @forelse ($recentTenants as $tenant)
                <a href="{{ route('super-admin.tenants.show', $tenant) }}"
                    class="flex items-center justify-between p-4 hover:bg-gray-50 transition">
                    <div>
                        <p class="font-medium text-gray-900">{{ $tenant->name }}</p>
                        <p class="text-xs text-gray-500">{{ $tenant->owner_email ?? 'No contact on file' }}</p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full {{ $tenant->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($tenant->status) }}
                    </span>
                </a>
            @empty
                <p class="p-6 text-sm text-gray-500 text-center">No hostels onboarded yet.</p>
            @endforelse
        </div>
    </div>
@endsection
