<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Due;
use App\Models\MonthlyCharge;
use App\Models\Payment;
use App\Services\PaymentRecorder;
use App\Services\Razorpay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(private PaymentRecorder $recorder, private Razorpay $razorpay)
    {
    }

    // Show payment page
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        $pendingCharges = $customer->monthlyCharges()
            ->unpaid()
            ->orderBy('month_year', 'asc')
            ->get();

        $pendingDues = $customer->dues()
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->get();

        $onlinePaymentsEnabled = $this->razorpay->isConfigured();

        return view('customer.payments.index', compact('pendingCharges', 'pendingDues', 'onlinePaymentsEnabled'));
    }

    // Create Razorpay order
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'charge_ids' => 'nullable|array',
            'charge_ids.*' => 'integer',
            'due_ids' => 'nullable|array',
            'due_ids.*' => 'integer',
        ]);

        $customer = Auth::guard('customer')->user();

        if (! $this->razorpay->isConfigured()) {
            return back()->withErrors(['error' => 'Online payments are not available right now. Please pay at the office.']);
        }

        // Only this resident's unpaid items count, whatever ids were posted.
        $charges = MonthlyCharge::whereIn('id', $validated['charge_ids'] ?? [])
            ->where('customer_id', $customer->id)
            ->unpaid()
            ->get();

        $dues = Due::whereIn('id', $validated['due_ids'] ?? [])
            ->where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->get();

        $items = $charges->map(fn ($c) => [
            'type' => 'charge',
            'id' => $c->id,
            'description' => 'Rent for ' . $c->period_label,
            'amount' => (float) $c->total_amount,
        ])->concat($dues->map(fn ($d) => [
            'type' => 'due',
            'id' => $d->id,
            'description' => $d->title,
            'amount' => (float) $d->amount,
        ]))->values()->all();

        $totalAmount = round(collect($items)->sum('amount'), 2);

        if ($totalAmount <= 0) {
            return back()->withErrors(['error' => 'Please select at least one item to pay.']);
        }

        try {
            $razorpayOrder = $this->razorpay->createOrder($totalAmount, 'rcpt_' . $customer->id . '_' . time(), [
                'purpose' => 'dues',
                'customer_id' => $customer->id,
                'customer_code' => $customer->customer_code,
                // Razorpay notes are limited to 256 chars each, so keep only type/id pairs.
                'items' => json_encode(array_map(fn ($i) => ['type' => $i['type'], 'id' => $i['id']], $items)),
            ]);

            return view('customer.payments.checkout', [
                'order' => $razorpayOrder,
                'customer' => $customer,
                'totalAmount' => $totalAmount,
                'items' => $items,
                'razorpayKey' => config('services.razorpay.key'),
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed', [
                'error' => $e->getMessage(),
                'customer_id' => $customer->id,
                'amount' => $totalAmount,
            ]);

            return back()->withErrors(['error' => 'Failed to create payment order. Please try again.']);
        }
    }

    // Verify payment and update status
    public function verifyPayment(Request $request)
    {
        $validated = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $customer = Auth::guard('customer')->user();

        try {
            $this->razorpay->verifySignature($validated);

            $order = $this->razorpay->fetchOrder($validated['razorpay_order_id']);
            $paymentData = $this->razorpay->fetchPayment($validated['razorpay_payment_id']);

            if ((int) ($order->notes->customer_id ?? 0) !== $customer->id) {
                throw new \RuntimeException('Order does not belong to this customer.');
            }

            $items = json_decode($order->notes->items ?? '[]', true) ?: [];

            $payment = $this->recorder->settle(
                $customer,
                MonthlyCharge::whereIn('id', collect($items)->where('type', 'charge')->pluck('id'))->get(),
                Due::whereIn('id', collect($items)->where('type', 'due')->pluck('id'))->get(),
                Razorpay::describeMethod($paymentData->toArray()),
                $validated['razorpay_payment_id'],
                [
                    'razorpay_payment_id' => $validated['razorpay_payment_id'],
                    'razorpay_order_id' => $validated['razorpay_order_id'],
                ],
            );

            Log::info('Payment successful', [
                'customer_id' => $customer->id,
                'payment_id' => $validated['razorpay_payment_id'],
                'amount' => $order->amount / 100,
            ]);

            return redirect()->route('customer.payments.success', [
                'payment_id' => $validated['razorpay_payment_id'],
                'receipt' => $payment?->id,
            ]);
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            Log::error('Payment signature verification failed', [
                'error' => $e->getMessage(),
                'payment_id' => $validated['razorpay_payment_id'] ?? null,
            ]);

            return redirect()->route('customer.payments.failed')
                ->with('error', 'Payment verification failed. Please contact support.');
        } catch (\Exception $e) {
            Log::error('Payment verification error', ['error' => $e->getMessage()]);

            return redirect()->route('customer.payments.failed')
                ->with('error', 'Payment processing failed. If money was deducted, it will be updated automatically within a few minutes.');
        }
    }

    // Payment success page
    public function success(Request $request)
    {
        $paymentId = $request->get('payment_id');
        $customer = Auth::guard('customer')->user();
        $payment = $request->filled('receipt')
            ? $customer->payments()->find($request->get('receipt'))
            : null;

        return view('customer.payments.success', compact('paymentId', 'payment'));
    }

    // Payment failed page
    public function failed()
    {
        return view('customer.payments.failed');
    }

    // Payment history (ledger rows with downloadable receipts)
    public function history()
    {
        $customer = Auth::guard('customer')->user();

        $payments = $customer->payments()
            ->successful()
            ->latest('paid_at')
            ->latest('id')
            ->paginate(20);

        // Kept for the current history view until it is switched to ledger rows.
        $paidCharges = $customer->monthlyCharges()
            ->where('status', 'paid')
            ->orderBy('paid_date', 'desc')
            ->paginate(20);

        return view('customer.payments.history', compact('payments', 'paidCharges'));
    }

    public function receipt(Payment $payment)
    {
        $customer = Auth::guard('customer')->user();
        abort_unless($payment->customer_id === $customer->id && $payment->isPaid(), 404);

        $payment->load(['customer', 'booking.bed.room.branch']);

        return view('receipts.payment', [
            'payment' => $payment,
            'backUrl' => route('customer.payments.history'),
        ]);
    }
}
