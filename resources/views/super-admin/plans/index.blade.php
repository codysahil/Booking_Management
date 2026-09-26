@extends('layouts.super-admin')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-display font-bold text-gray-900">Plans</h1>
        <a href="{{ route('super-admin.plans.create') }}"
            class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 transition">
            + New plan
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Razorpay</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($plans as $plan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $plan->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            ₹{{ number_format($plan->price) }} / {{ $plan->billing_interval === 'yearly' ? 'yr' : 'mo' }}
                        </td>
                        <td class="px-6 py-4">
                            @if ($plan->hasRazorpayPlan())
                                <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-800">Linked</span>
                            @else
                                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">Not linked</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs px-2 py-1 rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $plan->is_active ? 'Active' : 'Retired' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('super-admin.plans.edit', $plan) }}" class="text-slate-900 underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">No plans yet — create one to start billing hostels.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
