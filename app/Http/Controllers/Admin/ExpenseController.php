<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['branch', 'creator']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $expenses = $query->latest('date')->latest('id')->paginate(20)->withQueryString();

        $totalForFilter = (clone $query)->sum('amount');

        $branches = Branch::all();
        $categories = Expense::categoryOptions();

        return view('admin.expenses.index', compact('expenses', 'branches', 'categories', 'totalForFilter'));
    }

    public function create()
    {
        $branches = Branch::all();
        $categories = Expense::CATEGORIES;

        return view('admin.expenses.create', compact('branches', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('receipt')) {
            $validated['receipt_path'] = $request->file('receipt')->store('expenses/receipts', 'public');
        }

        $validated['created_by'] = Auth::id();

        Expense::create($validated);

        return redirect()->route('admin.expenses.index')->with('success', 'Expense recorded successfully!');
    }

    public function edit(Expense $expense)
    {
        $branches = Branch::all();
        $categories = Expense::CATEGORIES;

        return view('admin.expenses.edit', compact('expense', 'branches', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $validated['receipt_path'] = $request->file('receipt')->store('expenses/receipts', 'public');
        }

        $expense->update($validated);

        return redirect()->route('admin.expenses.index')->with('success', 'Expense updated successfully!');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        $expense->delete();

        return back()->with('success', 'Expense deleted successfully!');
    }

    public function export(Request $request)
    {
        $query = Expense::with(['branch', 'creator']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $expenses = $query->orderBy('date')->get();

        $filename = 'expenses_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($expenses) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Branch', 'Category', 'Vendor', 'Payment Method', 'Reference', 'Amount', 'Description', 'Recorded By']);

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->date->format('Y-m-d'),
                    $expense->branch->name ?? 'All branches',
                    $expense->category,
                    $expense->vendor,
                    $expense->payment_method,
                    $expense->reference,
                    $expense->amount,
                    $expense->description,
                    $expense->creator->name ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'payment_method' => 'required|string|in:' . implode(',', array_keys(Expense::PAYMENT_METHODS)),
            'reference' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);
    }
}
