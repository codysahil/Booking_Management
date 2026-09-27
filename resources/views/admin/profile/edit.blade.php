@extends('layouts.admin')

@section('header', 'Profile Settings')

@section('content')
<div>
    <p class="text-gray-500 -mt-2 mb-6">Manage your account details and password</p>

    @if (session('success'))
        <div class="form-card-enter mb-6 bg-green-50 border-2 border-green-200 rounded-xl p-4">
            <p class="text-green-800 text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('admin.profile.update') }}" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')

            <x-form.card title="Profile Information">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </x-slot:icon>

                <div class="space-y-4">
                    <x-form.input name="name" label="Full Name" required :value="$user->name" />
                    <x-form.input name="email" label="Email Address" type="email" required :value="$user->email" />
                    <x-form.button label="Update Profile" loading-label="Saving…" class="w-full justify-center" />
                </div>
            </x-form.card>
        </form>

        <form method="POST" action="{{ route('admin.profile.password') }}" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')

            <x-form.card title="Change Password" :delay="60">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </x-slot:icon>

                <div class="space-y-4">
                    <x-form.input name="current_password" label="Current Password" type="password" required />
                    <x-form.input name="password" label="New Password" type="password" required hint="Minimum 8 characters" />
                    <x-form.input name="password_confirmation" label="Confirm New Password" type="password" required />
                    <x-form.button label="Update Password" loading-label="Saving…" class="w-full justify-center" />
                </div>
            </x-form.card>
        </form>
    </div>
</div>
@endsection
