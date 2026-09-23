@extends('layouts.super-admin')

@section('content')
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-2xl font-display font-bold text-gray-900">{{ $tenant->name }}</h1>
            <p class="text-gray-500 text-sm mt-1">{{ $tenant->slug }}</p>
        </div>
        <a href="{{ route('super-admin.tenants.edit', $tenant) }}"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm">
            Edit
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="font-bold text-gray-900 mb-2">Admin login link</h2>
        @if (config('app.tenant_domain'))
            <p class="text-sm text-gray-700">
                <a href="http://{{ $tenant->slug }}.{{ config('app.tenant_domain') }}/admin/login" class="text-slate-900 underline" target="_blank">
                    {{ $tenant->slug }}.{{ config('app.tenant_domain') }}/admin/login
                </a>
            </p>
            <p class="text-xs text-gray-500 mt-1">Share this exact link with the owner — logging in anywhere else won't work for their account.</p>
        @else
            <p class="text-sm text-gray-700">
                <a href="{{ route('admin.login') }}" class="text-slate-900 underline" target="_blank">{{ route('admin.login') }}</a>
            </p>
            <p class="text-xs text-gray-500 mt-1">Every hostel shares this same link for now — set <code class="bg-gray-100 px-1 rounded">TENANT_DOMAIN</code> once you have a domain to give each hostel its own URL instead.</p>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="font-bold text-gray-900 mb-4">Account</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Status</dt>
                    <dd class="font-medium">
                        <span class="text-xs px-2 py-1 rounded-full {{ $tenant->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($tenant->status) }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Owner</dt>
                    <dd class="font-medium text-gray-900">{{ $tenant->owner_name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Owner email</dt>
                    <dd class="font-medium text-gray-900">{{ $tenant->owner_email ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Owner phone</dt>
                    <dd class="font-medium text-gray-900">{{ $tenant->owner_phone ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Trial ends</dt>
                    <dd class="font-medium text-gray-900">{{ $tenant->trial_ends_at?->format('d M, Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Onboarded</dt>
                    <dd class="font-medium text-gray-900">{{ $tenant->created_at->format('d M, Y') }}</dd>
                </div>
            </dl>
            @if ($tenant->notes)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-gray-500 text-sm mb-1">Notes</p>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $tenant->notes }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="font-bold text-gray-900 mb-4">Staff logins</h2>
            <div class="divide-y divide-gray-100">
                @forelse ($tenant->users as $user)
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <p class="font-medium text-gray-900 text-sm">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">{{ $user->role_label }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No staff yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
