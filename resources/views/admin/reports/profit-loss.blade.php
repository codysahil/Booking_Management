@extends('layouts.admin')

@section('header', 'Profit & Loss')

@section('content')
    <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Month</label>
                <input type="month" name="month" value="{{ $month }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Branch</label>
                <select name="branch_id" class="w-full border-gray-300 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (string) $branchId === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-700 transition">Run Report</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-green-100">
            <p class="text-sm text-gray-500 mb-1">Income</p>
            <p class="text-3xl font-bold text-green-600">{{ money($income) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-red-100">
            <p class="text-sm text-gray-500 mb-1">Expenses</p>
            <p class="text-3xl font-bold text-red-600">{{ money($totalExpenses) }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 {{ $net >= 0 ? 'border-primary-100' : 'border-amber-200' }}">
            <p class="text-sm text-gray-500 mb-1">Net</p>
            <p class="text-3xl font-bold {{ $net >= 0 ? 'text-primary-600' : 'text-amber-600' }}">{{ money($net) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-900">Expenses by Category</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Category</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Amount</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">% of Expenses</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($expensesByCategory as $row)
                    <tr>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $row->category }}</td>
                        <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">{{ money($row->total) }}</td>
                        <td class="px-6 py-3 text-sm text-right text-gray-500">
                            {{ $totalExpenses > 0 ? number_format(($row->total / $totalExpenses) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">No expenses in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
