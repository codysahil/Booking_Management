@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Payment History</h1>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-600 mb-1">Total Collected</div>
            <div class="text-2xl font-bold text-green-600">₹{{ number_format($summary['total_payments'], 2) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-600 mb-1">Total Transactions</div>
            <div class="text-2xl font-bold text-gray-800">{{ $summary['total_count'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-600 mb-1">Online Payments</div>
            <div class="text-2xl font-bold text-blue-600">{{ $summary['razorpay_count'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm text-gray-600 mb-1">Cash Payments</div>
            <div class="text-2xl font-bold text-amber-600">{{ $summary['cash_count'] }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by customer name or code..."
                    class="w-full border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <select name="filter" class="border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Payments</option>
                    <option value="charges" {{ $filter === 'charges' ? 'selected' : '' }}>Monthly Charges</option>
                    <option value="dues" {{ $filter === 'dues' ? 'selected' : '' }}>Other Dues</option>
                </select>
            </div>
            <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700">
                Filter
            </button>
            @if($search || $filter !== 'all')
            <a href="{{ route('admin.payments.history') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">
                Clear
            </a>
            @endif
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction ID</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($payments as $payment)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $payment['date']->format('d M, Y') }}
                        <br><span class="text-xs text-gray-500">{{ $payment['date']->format('h:i A') }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <div class="font-medium text-gray-900">{{ $payment['customer']->name }}</div>
                        <div class="text-xs text-gray-500">{{ $payment['customer']->customer_code }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <div class="font-medium">{{ $payment['description'] }}</div>
                        <div class="text-xs text-gray-500">{{ $payment['details'] }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-lg font-bold text-green-600">₹{{ number_format($payment['amount']) }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($payment['method'] === 'razorpay')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                Online
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                                {{ ucfirst($payment['method']) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 font-mono">
                        {{ $payment['transaction_id'] ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        No payment history found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $payments->links() }}
    </div>
</div>
@endsection
