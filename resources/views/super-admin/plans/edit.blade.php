@extends('layouts.super-admin')

@section('content')
    <div class="max-w-xl">
        <h1 class="text-2xl font-display font-bold text-gray-900 mb-1">{{ $plan->name }}</h1>
        <p class="text-gray-500 text-sm mb-6">
            ₹{{ number_format($plan->price) }} / {{ $plan->billing_interval === 'yearly' ? 'yr' : 'mo' }} —
            price and billing interval can't be changed once created; make a new plan instead.
        </p>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('super-admin.plans.update', $plan) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Plan name *</label>
                <input type="text" name="name" required value="{{ old('name', $plan->name) }}"
                    class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max branches</label>
                    <input type="number" name="max_branches" min="1" value="{{ old('max_branches', $plan->max_branches) }}"
                        class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max beds</label>
                    <input type="number" name="max_beds" min="1" value="{{ old('max_beds', $plan->max_beds) }}"
                        class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max staff</label>
                    <input type="number" name="max_staff" min="1" value="{{ old('max_staff', $plan->max_staff) }}"
                        class="w-full border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500">
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active)) class="rounded border-gray-300 text-slate-900 focus:ring-slate-500">
                Active — offered to new subscriptions
            </label>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('super-admin.plans.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="px-8 py-3 bg-slate-900 text-white rounded-lg font-bold hover:bg-slate-800 transition">
                    Save
                </button>
            </div>
        </form>
    </div>
@endsection
