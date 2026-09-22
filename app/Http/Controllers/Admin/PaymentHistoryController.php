<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentHistoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $method = $request->get('method');
        $branchId = $request->get('branch_id');

        $query = Payment::with(['customer', 'recorder'])
            ->successful()
            ->when($search, function ($q) use ($search) {
                $q->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('customer_code', 'like', "%{$search}%");
                });
            })
            ->when($method, fn ($q) => $q->where('payment_method', $method))
            ->forBranch($branchId);

        $payments = $query->clone()->latest('paid_at')->latest('id')->paginate(20)->withQueryString();

        $summary = [
            'total_payments' => $query->clone()->sum('amount'),
            'total_count' => $query->clone()->count(),
            'online_count' => (clone $query)->where('payment_method', '!=', 'cash')->whereNotNull('razorpay_payment_id')->count(),
            'cash_count' => (clone $query)->where('payment_method', 'cash')->count(),
        ];

        $branches = Branch::all();

        return view('admin.payments.history', compact('payments', 'summary', 'search', 'method', 'branchId', 'branches'));
    }

    public function receipt(Payment $payment)
    {
        abort_unless($payment->isPaid(), 404);

        $payment->load(['customer', 'booking.bed.room.branch']);

        return view('receipts.payment', [
            'payment' => $payment,
            'backUrl' => route('admin.payments.history'),
        ]);
    }
}
