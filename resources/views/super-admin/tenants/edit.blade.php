@extends('layouts.super-admin')

@section('content')
    <div class="max-w-2xl">
        <h1 class="text-2xl font-display font-bold text-gray-900 mb-6">Edit {{ $tenant->name }}</h1>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('super-admin.tenants.update', $tenant) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hostel / business name *</label>
                <input type="text" name="name" required value="{{ old('name', $tenant->name) }}"
                    class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                <select name="status" class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
                    <option value="active" {{ $tenant->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ $tenant->status === 'suspended' ? 'selected' : '' }}>Suspended (cannot log in)</option>
                    <option value="cancelled" {{ $tenant->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">This is separate from billing status — it controls whether the account can log in at all.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Owner name</label>
                    <input type="text" name="owner_name" value="{{ old('owner_name', $tenant->owner_name) }}"
                        class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Owner email</label>
                    <input type="email" name="owner_email" value="{{ old('owner_email', $tenant->owner_email) }}"
                        class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Owner phone</label>
                    <input type="text" name="owner_phone" value="{{ old('owner_phone', $tenant->owner_phone) }}"
                        class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3"
                    class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">{{ old('notes', $tenant->notes) }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="px-8 py-3 bg-slate-900 text-white rounded-lg font-bold hover:bg-slate-800 transition">
                    Save changes
                </button>
            </div>
        </form>
    </div>
@endsection
