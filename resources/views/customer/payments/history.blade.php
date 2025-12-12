@extends('layouts.customer')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-display font-bold text-gray-900">Payment History</h1>
            <a href="{{ route('customer.dashboard') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                ← Back to Dashboard
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($paidCharges as $charge)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>{{ $charge->updated_at->format('d M, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $charge->updated_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="font-medium">{{ \Carbon\Carbon::parse($charge->month_year)->format('F Y') }} - Monthly Charges</div>
                                <div class="text-xs text-gray-500">
                                    Rent: ₹{{ number_format($charge->rent_amount) }} • 
                                    EB: ₹{{ number_format($charge->eb_amount) }}
                                    @if($charge->other_charges > 0)
                                        • Other: ₹{{ number_format($charge->other_charges) }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-lg font-bold text-green-600">₹{{ number_format($charge->total_amount) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @php
                                    $method = $charge->payment_method ?? 'razorpay';
                                    $methodColors = [
                                        'GPay' => 'bg-blue-100 text-blue-800',
                                        'PhonePe' => 'bg-purple-100 text-purple-800',
                                        'Paytm' => 'bg-sky-100 text-sky-800',
                                        'UPI' => 'bg-green-100 text-green-800',
                                        'Credit Card' => 'bg-amber-100 text-amber-800',
                                        'Debit Card' => 'bg-orange-100 text-orange-800',
                                        'Net Banking' => 'bg-indigo-100 text-indigo-800',
                                        'default' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $colorClass = $methodColors[$method] ?? $methodColors['default'];
                                    // Check for partial matches
                                    if (str_contains($method, 'Credit Card')) $colorClass = $methodColors['Credit Card'];
                                    elseif (str_contains($method, 'Debit Card')) $colorClass = $methodColors['Debit Card'];
                                    elseif (str_contains($method, 'Net Banking')) $colorClass = $methodColors['Net Banking'];
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $colorClass }}">
                                    {{ $method }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">
                                {{ $charge->transaction_id ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-500">No payment history found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $paidCharges->links() }}
        </div>
    </div>
</div>
@endsection
