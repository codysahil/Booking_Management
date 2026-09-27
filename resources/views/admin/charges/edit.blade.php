@extends('layouts.admin')

@section('header', 'Edit Monthly Charge')

@section('content')
<div class="max-w-2xl mx-auto">
    <p class="text-gray-500 -mt-2 mb-6">{{ $charge->customer->name }} · {{ \Carbon\Carbon::parse($charge->month_year)->format('F Y') }}</p>

    <form method="POST" action="{{ route('admin.charges.update', $charge) }}" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        @method('PUT')

        <x-form.card title="Charge Details">
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3v-6m-3 6v-9m-2 9h10a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </x-slot:icon>

            <div class="mb-6 grid grid-cols-2 gap-3 rounded-xl bg-gray-50 p-4 text-sm">
                <div><span class="text-gray-400">Customer</span><p class="font-semibold text-gray-900">{{ $charge->customer->name }}</p></div>
                <div><span class="text-gray-400">Room</span><p class="font-semibold text-gray-900">{{ $charge->booking->bed->room->room_number }}</p></div>
                <div><span class="text-gray-400">Bed</span><p class="font-semibold text-gray-900">Bed {{ $charge->booking->bed->bed_number }}</p></div>
                <div><span class="text-gray-400">Month</span><p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($charge->month_year)->format('F Y') }}</p></div>
            </div>

            @if($charge->is_prorated)
                <div class="mb-6 rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                    <span class="font-semibold">Prorated charge.</span> This resident checked in on {{ $charge->booking->check_in_date->format('d M, Y') }}, so the rent below only covers the remaining days of that month — not a mistake to correct to the full rent.
                </div>
            @endif

            <div class="space-y-5">
                <x-form.input name="rent_amount" label="Rent Amount" type="number" step="0.01" required :value="$charge->rent_amount" />
                <x-form.input name="eb_amount" label="Electricity Bill (EB) Amount" type="number" step="0.01" required :value="$charge->eb_amount"
                    hint="Enter the electricity bill amount for this month." />
                <x-form.input name="other_charges" label="Other Charges (optional)" type="number" step="0.01" :value="$charge->other_charges" />
                <x-form.textarea name="other_charges_description" label="Other Charges Description" :rows="2" :value="$charge->other_charges_description" />
                <x-form.input name="due_date" label="Due Date" type="date" required :value="$charge->due_date->format('Y-m-d')" />
            </div>

            <div class="mt-6 flex items-center justify-between rounded-xl bg-primary-50 p-4">
                <span class="text-sm font-semibold text-gray-700">Total Amount</span>
                <span class="text-2xl font-bold text-primary-600 transition-all" id="totalAmount">
                    ₹{{ number_format($charge->total_amount, 2) }}
                </span>
            </div>
        </x-form.card>

        <div class="mt-6 flex justify-end gap-3">
            <x-form.link-button :href="route('admin.charges.index', ['month' => $charge->month_year])">Cancel</x-form.link-button>
            <x-form.button label="Update Charge" loading-label="Saving…" />
        </div>
    </form>
</div>

<script>
document.querySelectorAll('input[name="rent_amount"], input[name="eb_amount"], input[name="other_charges"]').forEach(input => {
    input.addEventListener('input', calculateTotal);
});

function calculateTotal() {
    const rent = parseFloat(document.querySelector('input[name="rent_amount"]').value) || 0;
    const eb = parseFloat(document.querySelector('input[name="eb_amount"]').value) || 0;
    const other = parseFloat(document.querySelector('input[name="other_charges"]').value) || 0;
    const total = rent + eb + other;
    const el = document.getElementById('totalAmount');
    el.textContent = '₹' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    el.classList.add('scale-110');
    setTimeout(() => el.classList.remove('scale-110'), 150);
}
</script>
@endsection
