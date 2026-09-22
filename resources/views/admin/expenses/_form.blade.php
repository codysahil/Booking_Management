@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Branch</label>
        <select name="branch_id" class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
            <option value="">All branches</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" {{ old('branch_id', $expense->branch_id ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Category *</label>
        <input type="text" name="category" list="category-options" required
            value="{{ old('category', $expense->category ?? '') }}"
            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
        <datalist id="category-options">
            @foreach ($categories as $category)
                <option value="{{ $category }}"></option>
            @endforeach
        </datalist>
        @error('category') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Amount (₹) *</label>
        <input type="number" step="0.01" min="0.01" name="amount" required
            value="{{ old('amount', $expense->amount ?? '') }}"
            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
        @error('amount') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Date *</label>
        <input type="date" name="date" required
            value="{{ old('date', isset($expense) ? $expense->date->format('Y-m-d') : now()->format('Y-m-d')) }}"
            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
        @error('date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Vendor</label>
        <input type="text" name="vendor" value="{{ old('vendor', $expense->vendor ?? '') }}"
            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Payment Method *</label>
        <select name="payment_method" required class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
            @foreach (\App\Models\Expense::PAYMENT_METHODS as $value => $label)
                <option value="{{ $value }}" {{ old('payment_method', $expense->payment_method ?? 'cash') == $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Reference / Invoice #</label>
        <input type="text" name="reference" value="{{ old('reference', $expense->reference ?? '') }}"
            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Receipt (PDF/Image)</label>
        <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png"
            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
        @if(isset($expense) && $expense->receipt_url)
            <a href="{{ $expense->receipt_url }}" target="_blank" class="text-xs text-primary-600 hover:underline">View current receipt</a>
        @endif
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
        <textarea name="description" rows="3" class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">{{ old('description', $expense->description ?? '') }}</textarea>
    </div>
</div>

<div class="mt-8 flex gap-3">
    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-rose-500 to-pink-500 text-white font-bold rounded-xl hover:from-rose-600 hover:to-pink-600 transition shadow-lg">
        {{ isset($expense) ? 'Update Expense' : 'Save Expense' }}
    </button>
    <a href="{{ route('admin.expenses.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-50 transition">Cancel</a>
</div>
