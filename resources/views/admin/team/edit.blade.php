@extends('layouts.admin')

@section('header', 'Edit Team Member')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8">
        <form method="POST" action="{{ route('admin.team.update', $member) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Name *</label>
                <input type="text" name="name" required value="{{ old('name', $member->name) }}"
                    class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                <input type="email" name="email" required value="{{ old('email', $member->email) }}"
                    class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Role *</label>
                <select name="role" required class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    @foreach (\App\Models\User::ROLES as $value => $label)
                        <option value="{{ $value }}" {{ $member->role == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ $member->is_active ? 'checked' : '' }}
                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span class="text-sm text-gray-700">Account active (can sign in)</span>
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                    <input type="password" name="password"
                        class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    <p class="text-xs text-gray-500 mt-1">Leave blank to keep the current password.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password" name="password_confirmation"
                        class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-xl hover:from-teal-600 hover:to-cyan-600 transition shadow-lg">
                    Save Changes
                </button>
                <a href="{{ route('admin.team.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-50 transition">Cancel</a>
            </div>
        </form>
    </div>
@endsection
