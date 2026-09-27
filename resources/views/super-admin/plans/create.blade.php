@extends('layouts.super-admin')

@section('content')
    <div class="max-w-xl">
        <h1 class="text-2xl font-display font-bold text-gray-900 mb-6">New plan</h1>

        @if ($errors->any())
            <div class="form-card-enter form-field-error mb-6 bg-red-50 border-2 border-red-200 rounded-xl p-4 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('super-admin.plans.store') }}" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <x-form.card accent="slate">
                <div class="space-y-4">
                    <x-form.input name="name" label="Plan name" required placeholder="e.g. Standard" accent="slate" />

                    <div class="grid grid-cols-2 gap-4">
                        <x-form.input name="price" label="Price (₹)" type="number" min="1" step="1" required accent="slate" />
                        <x-form.select name="billing_interval" label="Billing interval" required accent="slate">
                            @foreach (\App\Models\Plan::INTERVALS as $value => $label)
                                <option value="{{ $value }}" @selected(old('billing_interval') === $value)>{{ $label }}</option>
                            @endforeach
                        </x-form.select>
                    </div>

                    <p class="text-xs text-gray-500">Limits below are optional — leave blank for unlimited.</p>
                    <div class="grid grid-cols-3 gap-4">
                        <x-form.input name="max_branches" label="Max branches" type="number" min="1" accent="slate" />
                        <x-form.input name="max_beds" label="Max beds" type="number" min="1" accent="slate" />
                        <x-form.input name="max_staff" label="Max staff" type="number" min="1" accent="slate" />
                    </div>
                </div>
            </x-form.card>

            <div class="mt-6 flex justify-end gap-3">
                <x-form.link-button :href="route('super-admin.plans.index')">Cancel</x-form.link-button>
                <x-form.button label="Create plan" loading-label="Creating…" accent="slate" />
            </div>
        </form>
    </div>
@endsection
