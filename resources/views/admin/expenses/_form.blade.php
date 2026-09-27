@csrf

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <x-form.select name="branch_id" label="Branch">
        <option value="">All branches</option>
        @foreach ($branches as $branch)
            <option value="{{ $branch->id }}" {{ old('branch_id', $expense->branch_id ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
        @endforeach
    </x-form.select>

    <div>
        <x-form.input name="category" label="Category" required list="category-options" :value="$expense->category ?? ''" />
        <datalist id="category-options">
            @foreach ($categories as $category)
                <option value="{{ $category }}"></option>
            @endforeach
        </datalist>
    </div>

    <x-form.input name="amount" label="Amount (₹)" type="number" step="0.01" min="0.01" required :value="$expense->amount ?? ''" />

    <x-form.input name="date" label="Date" type="date" required
        :value="old('date', isset($expense) ? $expense->date->format('Y-m-d') : now()->format('Y-m-d'))" />

    <x-form.input name="vendor" label="Vendor" :value="$expense->vendor ?? ''" />

    <x-form.select name="payment_method" label="Payment Method" required>
        @foreach (\App\Models\Expense::PAYMENT_METHODS as $value => $label)
            <option value="{{ $value }}" {{ old('payment_method', $expense->payment_method ?? 'cash') == $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </x-form.select>

    <x-form.input name="reference" label="Reference / Invoice #" :value="$expense->reference ?? ''" />

    <div>
        <x-form.file-input name="receipt" label="Receipt (PDF/Image)" accept=".pdf,.jpg,.jpeg,.png" />
        @if(isset($expense) && $expense->receipt_url)
            <a href="{{ $expense->receipt_url }}" target="_blank" class="mt-1.5 inline-block text-xs font-medium text-primary-600 hover:underline">View current receipt</a>
        @endif
    </div>

    <div class="md:col-span-2">
        <x-form.textarea name="description" label="Description" :rows="3" :value="$expense->description ?? ''" />
    </div>
</div>

<div class="mt-8 flex justify-end gap-3">
    <x-form.link-button :href="route('admin.expenses.index')">Cancel</x-form.link-button>
    <x-form.button :label="isset($expense) ? 'Update Expense' : 'Save Expense'" loading-label="Saving…" />
</div>
