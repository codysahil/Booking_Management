@extends('layouts.super-admin')

@section('content')
    <div class="max-w-2xl">
        <h1 class="text-2xl font-display font-bold text-gray-900 mb-6">Onboard a hostel</h1>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('super-admin.tenants.store') }}" class="space-y-8">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <h2 class="font-bold text-gray-900">Hostel details</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hostel / business name *</label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                        class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Owner name</label>
                        <input type="text" name="owner_name" value="{{ old('owner_name') }}"
                            class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Owner email</label>
                        <input type="email" name="owner_email" value="{{ old('owner_email') }}"
                            class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Owner phone</label>
                        <input type="text" name="owner_phone" value="{{ old('owner_phone') }}"
                            class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trial length (days) *</label>
                    <input type="number" name="trial_days" min="0" max="365" required value="{{ old('trial_days', 14) }}"
                        class="w-full md:w-40 border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <h2 class="font-bold text-gray-900">First admin login</h2>
                <p class="text-sm text-gray-500">This is what the hostel owner will use to sign in to their own admin panel.</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" name="admin_name" required value="{{ old('admin_name') }}"
                        class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="admin_email" required value="{{ old('admin_email') }}"
                        class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Temporary password *</label>
                    <input type="text" name="admin_password" required minlength="8"
                        class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
                    <p class="text-xs text-gray-500 mt-1">Share this with the owner directly — they aren't emailed automatically.</p>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('super-admin.tenants.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="px-8 py-3 bg-slate-900 text-white rounded-lg font-bold hover:bg-slate-800 transition">
                    Create hostel account
                </button>
            </div>
        </form>
    </div>
@endsection
