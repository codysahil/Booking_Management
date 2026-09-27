@extends('layouts.super-admin')

@section('content')
    <div class="max-w-2xl">
        <h1 class="text-2xl font-display font-bold text-gray-900 mb-6">Edit {{ $tenant->name }}</h1>

        @if ($errors->any())
            <div class="form-card-enter form-field-error mb-6 bg-red-50 border-2 border-red-200 rounded-xl p-4 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('super-admin.tenants.update', $tenant) }}"
            x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')

            <x-form.card accent="slate">
                <div class="space-y-4">
                    <x-form.input name="name" label="Hostel / business name" required :value="$tenant->name" accent="slate" />

                    <x-form.select name="status" label="Status" accent="slate">
                        <option value="active" {{ $tenant->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ $tenant->status === 'suspended' ? 'selected' : '' }}>Suspended (cannot log in)</option>
                        <option value="cancelled" {{ $tenant->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </x-form.select>
                    <p class="-mt-3 text-xs text-gray-500">This is separate from billing status — it controls whether the account can log in at all.</p>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <x-form.input name="owner_name" label="Owner name" :value="$tenant->owner_name" accent="slate" />
                        <x-form.input name="owner_email" label="Owner email" type="email" :value="$tenant->owner_email" accent="slate" />
                        <x-form.input name="owner_phone" label="Owner phone" :value="$tenant->owner_phone" accent="slate" />
                    </div>

                    <x-form.textarea name="notes" label="Notes" :rows="3" :value="$tenant->notes" accent="slate" />
                </div>
            </x-form.card>

            <div class="mt-6 flex justify-end gap-3">
                <x-form.link-button :href="route('super-admin.tenants.show', $tenant)">Cancel</x-form.link-button>
                <x-form.button label="Save changes" loading-label="Saving…" accent="slate" />
            </div>
        </form>
    </div>
@endsection
