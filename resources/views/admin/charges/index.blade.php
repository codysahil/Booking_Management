@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Monthly Charges</h1>
        <a href="{{ route('admin.charges.generate') }}" class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700">
            Generate Charges
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Month Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Month</label>
                <input type="month" name="month" value="{{ $month }}" 
                    class="w-full border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
            </div>
            <button type="submit" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700">
                Filter
            </button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-600 mb-1">Total Charges</div>
            <div class="text-2xl font-bold text-gray-800">₹{{ number_format($summary['total_charges'], 2) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-600 mb-1">Paid</div>
            <div class="text-2xl font-bold text-green-600">₹{{ number_format($summary['paid'], 2) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-600 mb-1">Pending</div>
            <div class="text-2xl font-bold text-orange-600">₹{{ number_format($summary['pending'], 2) }}</div>
        </div>
    </div>

    <!-- Charges Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">EB</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Other</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($charges as $charge)
                <tr>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $charge->customer->name }}</div>
                        <div class="text-sm text-gray-500">{{ $charge->customer->customer_code }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ \Carbon\Carbon::parse($charge->month_year)->format('M Y') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">₹{{ number_format($charge->rent_amount, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₹{{ number_format($charge->eb_amount, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">₹{{ number_format($charge->other_charges, 2) }}</td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">₹{{ number_format($charge->total_amount, 2) }}</td>
                    <td class="px-6 py-4">
                        @if($charge->status === 'paid')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                        @elseif($charge->isOverdue())
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
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
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                        No charges found for this month. <a href="{{ route('admin.charges.generate') }}" class="text-primary-600 hover:underline">Generate charges</a>
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
