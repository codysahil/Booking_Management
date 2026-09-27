@extends('layouts.admin')

@section('header', 'Add Team Member')

@section('content')
    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ route('admin.team.store') }}" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-form.card title="Team Member">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-4a4 4 0 10-8 0 4 4 0 008 0zm6 4a4 4 0 10-8 0 4 4 0 008 0z"/></svg>
                </x-slot:icon>

                <div class="space-y-5">
                    <x-form.input name="name" label="Name" required />
                    <x-form.input name="email" label="Email" type="email" required />
                    <x-form.select name="role" label="Role" required>
                        @foreach (\App\Models\User::ROLES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-form.select>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <x-form.input name="password" label="Password" type="password" required />
                        <x-form.input name="password_confirmation" label="Confirm Password" type="password" required />
                    </div>
                </div>
            </x-form.card>

            <div class="mt-6 flex justify-end gap-3">
                <x-form.link-button :href="route('admin.team.index')">Cancel</x-form.link-button>
                <x-form.button label="Add Team Member" loading-label="Adding…" />
            </div>
        </form>
    </div>
@endsection
