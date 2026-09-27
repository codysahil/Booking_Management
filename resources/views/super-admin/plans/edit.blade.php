@extends('layouts.super-admin')

@section('content')
    <div class="max-w-xl">
        <h1 class="text-2xl font-display font-bold text-gray-900 mb-1">{{ $plan->name }}</h1>
        <p class="text-gray-500 text-sm mb-6">
            ₹{{ number_format($plan->price) }} / {{ $plan->billing_interval === 'yearly' ? 'yr' : 'mo' }} —
            price and billing interval can't be changed once created; make a new plan instead.
        </p>

        @if ($errors->any())
            <div class="form-card-enter form-field-error mb-6 bg-red-50 border-2 border-red-200 rounded-xl p-4 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('super-admin.plans.update', $plan) }}" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')

            <x-form.card accent="slate">
                <div class="space-y-4">
                    <x-form.input name="name" label="Plan name" required :value="$plan->name" accent="slate" />

                    <div class="grid grid-cols-3 gap-4">
                        <x-form.input name="max_branches" label="Max branches" type="number" min="1" :value="$plan->max_branches" accent="slate" />
                        <x-form.input name="max_beds" label="Max beds" type="number" min="1" :value="$plan->max_beds" accent="slate" />
                        <x-form.input name="max_staff" label="Max staff" type="number" min="1" :value="$plan->max_staff" accent="slate" />
                    </div>

                    <x-form.toggle name="is_active" label="Active" hint="Offered to new subscriptions" :checked="$plan->is_active" />
                </div>
            </x-form.card>

            <div class="mt-6 flex justify-end gap-3">
                <x-form.link-button :href="route('super-admin.plans.index')">Cancel</x-form.link-button>
                <x-form.button label="Save" loading-label="Saving…" accent="slate" />
            </div>
        </form>
    </div>
@endsection
