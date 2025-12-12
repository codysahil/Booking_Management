<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyCharge;
use App\Models\Due;
use Illuminate\Http\Request;

class PaymentHistoryController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all'); // all, charges, dues
        $search = $request->get('search');
        
        $query = collect();
        
        if ($filter === 'all' || $filter === 'charges') {
            $charges = MonthlyCharge::with('customer')
                ->where('status', 'paid')
                ->when($search, function($q) use ($search) {
                    $q->whereHas('customer', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('customer_code', 'like', "%{$search}%");
                    });
                })
                ->get()
                ->map(function($charge) {
                    return [
                        'type' => 'charge',
                        'id' => $charge->id,
                        'date' => $charge->updated_at,
                        'customer' => $charge->customer,
                        'description' => \Carbon\Carbon::parse($charge->month_year)->format('F Y') . ' - Monthly Charges',
                        'details' => "Rent: ₹" . number_format($charge->rent_amount) . " • EB: ₹" . number_format($charge->eb_amount),
                        'amount' => $charge->total_amount,
                        'method' => $charge->payment_method,
                        'transaction_id' => $charge->transaction_id,
                    ];
                });
            
            $query = $query->merge($charges);
        }
        
        if ($filter === 'all' || $filter === 'dues') {
            $dues = Due::with('customer')
                ->where('status', 'paid')
                ->when($search, function($q) use ($search) {
                    $q->whereHas('customer', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('customer_code', 'like', "%{$search}%");
                    });
                })
                ->get()
                ->map(function($due) {
                    return [
                        'type' => 'due',
                        'id' => $due->id,
                        'date' => $due->updated_at,
                        'customer' => $due->customer,
                        'description' => $due->title,
                        'details' => $due->description,
                        'amount' => $due->amount,
                        'method' => $due->payment_method,
                        'transaction_id' => $due->transaction_id,
                    ];
                });
            
            $query = $query->merge($dues);
        }
        
        // Sort by date descending
        $payments = $query->sortByDesc('date')->values();
        
        // Manual pagination
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $total = $payments->count();
        $payments = $payments->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $payments,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        $summary = [
            'total_payments' => $query->sum('amount'),
            'total_count' => $query->count(),
            'razorpay_count' => $query->where('method', 'razorpay')->count(),
            'cash_count' => $query->where('method', 'cash')->count(),
        ];
        
        return view('admin.payments.history', [
            'payments' => $paginator,
            'summary' => $summary,
            'filter' => $filter,
            'search' => $search,
        ]);
    }
}
