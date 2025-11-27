@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="{{ route('admin.customers.show', $customer) }}" class="text-gray-500 hover:text-gray-700 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Customer
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Monthly Charges</h1>
                <p class="text-gray-600 mt-1">{{ $customer->name }} ({{ $customer->customer_code }})</p>
            </div>
            @if($activeBooking)
            <div class="text-right">
                <p class="text-sm text-gray-600">Current Rent</p>
                <p class="text-2xl font-bold text-primary-600">₹{{ number_format($activeBooking->bed->monthly_rent) }}/month</p>
                <p class="text-xs text-gray-500 mt-1">Room {{ $activeBooking->bed->room->room_number }} • Bed {{ $activeBooking->bed->bed_number }}</p>
            </div>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Charges List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">EB</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Other</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($charges as $charge)
                <tr class="{{ $charge->isOverdue() ? 'bg-red-50' : '' }}">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ \Carbon\Carbon::parse($charge->month_year)->format('F Y') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">₹{{ number_format($charge->rent_amount, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₹{{ number_format($charge->eb_amount, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        @if($charge->other_charges > 0)
                            ₹{{ number_format($charge->other_charges, 2) }}
                            @if($charge->other_charges_description)
                                <br><span class="text-xs text-gray-500">{{ $charge->other_charges_description }}</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">₹{{ number_format($charge->total_amount, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $charge->due_date->format('d M, Y') }}
                    </td>
                    <td class="px-6 py-4">
                        @if($charge->status === 'paid')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Paid
                            </span>
                            @if($charge->paid_date)
                                <br><span class="text-xs text-gray-500">{{ $charge->paid_date->format('d M, Y') }}</span>
                            @endif
                        @elseif($charge->isOverdue())
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                Overdue
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('admin.charges.edit', $charge) }}" class="text-primary-600 hover:text-primary-900 mr-3">Edit</a>
                        @if($charge->status !== 'paid')
                        <form method="POST" action="{{ route('admin.charges.mark-paid', $charge) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="payment_method" value="cash">
                            <button type="submit" class="text-green-600 hover:text-green-900" onclick="return confirm('Mark as paid?')">Mark Paid</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                        No charges found for this customer.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $charges->links() }}
    </div>
</div>
@endsection
