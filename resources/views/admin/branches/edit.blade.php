@extends('layouts.admin')

@section('header', 'Edit Branch')

@section('content')
    <div class="max-w-2xl mx-auto">
        <form action="{{ route('admin.branches.update', $branch) }}" method="POST" class="space-y-6"
            x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')

            <x-form.card title="Branch Details">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2M5 21h2m0 0h10M5 21v-4a1 1 0 011-1h1m8 5v-4a1 1 0 00-1-1h-1m-4 5v-3"/></svg>
                </x-slot:icon>

                <div class="space-y-5">
                    <x-form.input name="name" label="Branch Name" required :value="$branch->name" />
                    <x-form.textarea name="address" label="Address" :rows="3" required :value="$branch->address" />
                    <x-form.input name="google_map_url" label="Google Map URL" type="url" :value="$branch->google_map_url" />
                </div>
            </x-form.card>

            <div class="flex justify-end gap-3">
                <x-form.link-button :href="route('admin.branches.index')">Cancel</x-form.link-button>
                <x-form.button label="Update Branch" loading-label="Saving…" />
            </div>
        </form>
    </div>
@endsection
