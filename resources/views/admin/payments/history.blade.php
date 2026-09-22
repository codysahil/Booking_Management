@extends('layouts.admin')

@section('header', 'Payment History')

@section('content')
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-green-100">
            <div class="text-sm text-gray-600 mb-1">Total Collected</div>
            <div class="text-2xl font-bold text-green-600">{{ money($summary['total_payments']) }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-gray-100">
            <div class="text-sm text-gray-600 mb-1">Total Transactions</div>
            <div class="text-2xl font-bold text-gray-800">{{ $summary['total_count'] }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-blue-100">
            <div class="text-sm text-gray-600 mb-1">Online Payments</div>
            <div class="text-2xl font-bold text-blue-600">{{ $summary['online_count'] }}</div>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-amber-100">
            <div class="text-sm text-gray-600 mb-1">Cash Payments</div>
            <div class="text-2xl font-bold text-amber-600">{{ $summary['cash_count'] }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by customer name or code..."
                    class="w-full border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <select name="branch_id" class="border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (string) $branchId === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="method" class="border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All Methods</option>
                    <option value="cash" {{ $method === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="upi" {{ $method === 'upi' ? 'selected' : '' }}>UPI</option>
                    <option value="bank_transfer" {{ $method === 'bank_transfer' ? 'selected' : '' }}>Bank transfer</option>
                    <option value="card" {{ $method === 'card' ? 'selected' : '' }}>Card</option>
                </select>
            </div>
            <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700">
                Filter
            </button>
            @if($search || $method || $branchId)
                <a href="{{ route('admin.payments.history') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-rose-50 to-pink-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Receipt</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($payments as $payment)
                    <tr class="hover:bg-rose-50/50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ ($payment->paid_at ?? $payment->created_at)->format('d M, Y') }}
                            <br><span class="text-xs text-gray-500">{{ ($payment->paid_at ?? $payment->created_at)->format('h:i A') }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="font-medium text-gray-900">{{ $payment->customer->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $payment->customer->customer_code ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="font-medium">{{ $payment->payment_type }}</div>
                            @if($payment->recorder)<div class="text-xs text-gray-500">by {{ $payment->recorder->name }}</div>@endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-lg font-bold text-green-600">{{ money($payment->amount) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($payment->razorpay_payment_id)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Online</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">{{ ucfirst($payment->payment_method) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.payments.receipt', $payment) }}" target="_blank" class="text-primary-600 hover:underline">View</a>
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
@endsection
