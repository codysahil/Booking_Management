@extends('layouts.admin')

@section('header', 'Edit Team Member')

@section('content')
    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ route('admin.team.update', $member) }}" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')
            <x-form.card title="Team Member">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-4a4 4 0 10-8 0 4 4 0 008 0zm6 4a4 4 0 10-8 0 4 4 0 008 0z"/></svg>
                </x-slot:icon>

                <div class="space-y-5">
                    <x-form.input name="name" label="Name" required :value="$member->name" />
                    <x-form.input name="email" label="Email" type="email" required :value="$member->email" />
                    <x-form.select name="role" label="Role" required>
                        @foreach (\App\Models\User::ROLES as $value => $label)
                            <option value="{{ $value }}" {{ $member->role == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-form.select>

                    <x-form.toggle name="is_active" label="Account active" hint="Can sign in to the admin panel" :checked="$member->is_active" />

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 pt-2 border-t border-gray-100">
                        <x-form.input name="password" label="New Password" type="password" hint="Leave blank to keep the current password." />
                        <x-form.input name="password_confirmation" label="Confirm New Password" type="password" />
                    </div>
                </div>
            </x-form.card>

            <div class="mt-6 flex justify-end gap-3">
                <x-form.link-button :href="route('admin.team.index')">Cancel</x-form.link-button>
                <x-form.button label="Save Changes" loading-label="Saving…" />
            </div>
        </form>
    </div>
@endsection
