@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Monthly Charge</h1>
        <p class="text-gray-600">{{ $charge->customer->name }} - {{ \Carbon\Carbon::parse($charge->month_year)->format('F Y') }}</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.charges.update', $charge) }}">
            @csrf
            @method('PUT')

            <!-- Customer Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Customer:</span>
                        <span class="font-medium">{{ $charge->customer->name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Room:</span>
                        <span class="font-medium">{{ $charge->booking->bed->room->room_number }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Bed:</span>
                        <span class="font-medium">Bed {{ $charge->booking->bed->bed_number }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Month:</span>
                        <span class="font-medium">{{ \Carbon\Carbon::parse($charge->month_year)->format('F Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Rent Amount -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Rent Amount</label>
                <input type="number" name="rent_amount" value="{{ old('rent_amount', $charge->rent_amount) }}" step="0.01" required
                    class="w-full border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                @error('rent_amount')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- EB Amount -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Electricity Bill (EB) Amount</label>
                <input type="number" name="eb_amount" value="{{ old('eb_amount', $charge->eb_amount) }}" step="0.01" required
                    class="w-full border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                @error('eb_amount')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Enter the electricity bill amount for this month</p>
            </div>

            <!-- Other Charges -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Other Charges (Optional)</label>
                <input type="number" name="other_charges" value="{{ old('other_charges', $charge->other_charges) }}" step="0.01"
                    class="w-full border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                @error('other_charges')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Other Charges Description -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Other Charges Description</label>
                <textarea name="other_charges_description" rows="2"
                    class="w-full border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">{{ old('other_charges_description', $charge->other_charges_description) }}</textarea>
            </div>

            <!-- Due Date -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                <input type="date" name="due_date" value="{{ old('due_date', $charge->due_date->format('Y-m-d')) }}" required
                    class="w-full border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                @error('due_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Total Preview -->
            <div class="bg-primary-50 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-medium text-gray-700">Total Amount:</span>
                    <span class="text-2xl font-bold text-primary-600" id="totalAmount">
                        ₹{{ number_format($charge->total_amount, 2) }}
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 font-medium">
                    Update Charge
                </button>
                <a href="{{ route('admin.charges.index', ['month' => $charge->month_year]) }}" 
                    class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 font-medium text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-calculate total
document.querySelectorAll('input[name="rent_amount"], input[name="eb_amount"], input[name="other_charges"]').forEach(input => {
    input.addEventListener('input', calculateTotal);
});

function calculateTotal() {
    const rent = parseFloat(document.querySelector('input[name="rent_amount"]').value) || 0;
    const eb = parseFloat(document.querySelector('input[name="eb_amount"]').value) || 0;
    const other = parseFloat(document.querySelector('input[name="other_charges"]').value) || 0;
    const total = rent + eb + other;
    document.getElementById('totalAmount').textContent = '₹' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
</script>
@endsection
