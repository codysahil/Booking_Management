@extends('layouts.customer')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-display font-bold text-gray-900 mb-8">Pay Your Dues</h1>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('customer.payments.create-order') }}" id="paymentForm">
            @csrf

            <!-- Monthly Charges -->
            @if($pendingCharges->count() > 0)
            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Monthly Charges (Rent + EB)</h2>
                <div class="space-y-3">
                    @foreach($pendingCharges as $charge)
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg hover:border-primary-300 cursor-pointer transition">
                        <input type="checkbox" name="charge_ids[]" value="{{ $charge->id }}" 
                            class="w-5 h-5 text-primary-600 rounded focus:ring-primary-500 charge-checkbox"
                            data-amount="{{ $charge->total_amount }}">
                        <div class="ml-4 flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($charge->month_year)->format('F Y') }}</h3>
                                    <div class="text-sm text-gray-600 mt-1">
                                        Rent: ₹{{ number_format($charge->rent_amount) }} • 
                                        EB: ₹{{ number_format($charge->eb_amount) }}
                                        @if($charge->other_charges > 0)
                                            • Other: ₹{{ number_format($charge->other_charges) }}
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Due: {{ $charge->due_date->format('d M, Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xl font-bold text-primary-600">₹{{ number_format($charge->total_amount) }}</p>
                                    @if($charge->isOverdue())
                                        <span class="text-xs text-red-600 font-medium">Overdue</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Other Dues -->
            @if($pendingDues->count() > 0)
            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Other Dues</h2>
                <div class="space-y-3">
                    @foreach($pendingDues as $due)
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg hover:border-red-300 cursor-pointer transition">
                        <input type="checkbox" name="due_ids[]" value="{{ $due->id }}" 
                            class="w-5 h-5 text-red-600 rounded focus:ring-red-500 due-checkbox"
                            data-amount="{{ $due->amount }}">
                        <div class="ml-4 flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-gray-900">{{ $due->title }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $due->description }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Type: {{ ucfirst($due->due_type) }} • Due: {{ $due->due_date->format('d M, Y') }}</p>
                                </div>
                                <p class="text-xl font-bold text-red-600">₹{{ number_format($due->amount) }}</p>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            @if($pendingCharges->count() === 0 && $pendingDues->count() === 0)
            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-12 text-center">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">All Paid Up!</h3>
                <p class="text-gray-600">You have no pending dues at the moment.</p>
            </div>
            @else
            <!-- Payment Summary -->
            <div class="bg-gradient-to-br from-primary-500 to-secondary-500 rounded-2xl shadow-lg p-6 text-white">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-lg font-medium">Total Amount to Pay:</span>
                    <span class="text-3xl font-bold" id="totalAmount">₹0</span>
                </div>
                <button type="submit" id="payButton" disabled
                    class="w-full bg-white text-primary-600 px-6 py-3 rounded-lg font-bold hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Proceed to Payment
                </button>
                <p class="text-sm text-white/80 mt-3 text-center">Secure payment powered by Razorpay</p>
            </div>
            @endif
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chargeCheckboxes = document.querySelectorAll('.charge-checkbox');
    const dueCheckboxes = document.querySelectorAll('.due-checkbox');
    const totalAmountEl = document.getElementById('totalAmount');
    const payButton = document.getElementById('payButton');

    function updateTotal() {
        let total = 0;
        
        chargeCheckboxes.forEach(cb => {
            if (cb.checked) {
                total += parseFloat(cb.dataset.amount);
            }
        });
        
        dueCheckboxes.forEach(cb => {
            if (cb.checked) {
                total += parseFloat(cb.dataset.amount);
            }
        });
        
        totalAmountEl.textContent = '₹' + total.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        payButton.disabled = total === 0;
    }

    chargeCheckboxes.forEach(cb => cb.addEventListener('change', updateTotal));
    dueCheckboxes.forEach(cb => cb.addEventListener('change', updateTotal));
});
</script>
@endsection
