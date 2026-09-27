@extends('layouts.super-admin')

@section('content')
    <div class="max-w-2xl">
        <h1 class="text-2xl font-display font-bold text-gray-900 mb-6">Onboard a hostel</h1>

        @if ($errors->any())
            <div class="form-card-enter form-field-error mb-6 bg-red-50 border-2 border-red-200 rounded-xl p-4 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('super-admin.tenants.store') }}" class="space-y-6"
            x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <x-form.card title="Hostel details" accent="slate">
                <div class="space-y-4">
                    <x-form.input name="name" label="Hostel / business name" required accent="slate" />
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <x-form.input name="owner_name" label="Owner name" accent="slate" />
                        <x-form.input name="owner_email" label="Owner email" type="email" accent="slate" />
                        <x-form.input name="owner_phone" label="Owner phone" accent="slate" />
                    </div>
                    <x-form.input name="trial_days" label="Trial length (days)" type="number" min="0" max="365" required :value="old('trial_days', 14)" accent="slate" class="md:w-40" />
                </div>
            </x-form.card>

            <x-form.card title="First admin login" description="This is what the hostel owner will use to sign in to their own admin panel." accent="slate" :delay="80">
                <div class="space-y-4">
                    <x-form.input name="admin_name" label="Name" required accent="slate" />
                    <x-form.input name="admin_email" label="Email" type="email" required accent="slate" />
                    <x-form.input name="admin_password" label="Temporary password" required minlength="8" accent="slate"
                        hint="Share this with the owner directly — they aren't emailed automatically." />
                </div>
            </x-form.card>

            <div class="flex justify-end gap-3">
                <x-form.link-button :href="route('super-admin.tenants.index')">Cancel</x-form.link-button>
                <x-form.button label="Create hostel account" loading-label="Creating…" accent="slate" />
            </div>
        </form>
    </div>
@endsection
