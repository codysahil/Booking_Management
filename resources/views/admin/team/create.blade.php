@extends('layouts.admin')

@section('header', 'Add Team Member')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8">
        <form method="POST" action="{{ route('admin.team.store') }}" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Name *</label>
                <input type="text" name="name" required value="{{ old('name') }}"
                    class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                <input type="email" name="email" required value="{{ old('email') }}"
                    class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Role *</label>
                <select name="role" required class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    @foreach (\App\Models\User::ROLES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password *</label>
                    <input type="password" name="password" required
                        class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                    @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password *</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-rose-500 to-pink-500 text-white font-bold rounded-xl hover:from-rose-600 hover:to-pink-600 transition shadow-lg">
                    Add Team Member
                </button>
                <a href="{{ route('admin.team.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-50 transition">Cancel</a>
            </div>
        </form>
    </div>
@endsection
