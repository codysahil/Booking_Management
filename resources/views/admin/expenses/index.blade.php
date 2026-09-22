@extends('layouts.admin')

@section('header', 'Expenses')

@section('content')
    <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Branch</label>
                <select name="branch_id" class="w-full border-gray-300 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                <select name="category" class="w-full border-gray-300 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-primary-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-primary-700 transition">Filter</button>
                <a href="{{ route('admin.expenses.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition">Reset</a>
            </div>
        </form>
    </div>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-4">
            <p class="text-xs text-gray-500">Total for this filter</p>
            <p class="text-2xl font-bold text-rose-600">{{ money($totalForFilter) }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.expenses.export', request()->query()) }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl hover:bg-gray-50 transition text-sm font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Export CSV
            </a>
            <a href="{{ route('admin.expenses.create') }}"
                class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-rose-500 to-pink-500 text-white font-bold rounded-xl hover:from-rose-600 hover:to-pink-600 transition shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Record Expense
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-rose-50 to-pink-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Receipt</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse ($expenses as $expense)
                        <tr class="hover:bg-rose-50/50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $expense->date->format('d M, Y') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $expense->branch->name ?? 'All branches' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $expense->category }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $expense->vendor ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ money($expense->amount) }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($expense->receipt_url)
                                    <a href="{{ $expense->receipt_url }}" target="_blank" class="text-primary-600 hover:underline">View</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <a href="{{ route('admin.expenses.edit', $expense) }}" class="text-primary-600 hover:text-primary-800 mr-3">Edit</a>
                                <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="inline" onsubmit="return confirm('Delete this expense?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No expenses recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $expenses->links() }}</div>
        @endif
    </div>
@endsection
