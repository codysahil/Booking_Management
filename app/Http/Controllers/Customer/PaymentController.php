<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\MonthlyCharge;
use App\Models\Due;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    // Show payment page
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        
        // Get all pending charges
        $pendingCharges = $customer->monthlyCharges()
            ->where('status', 'pending')
            ->orderBy('month_year', 'asc')
            ->get();
        
        // Get all pending dues
        $pendingDues = $customer->dues()
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->get();
        
        return view('customer.payments.index', compact('pendingCharges', 'pendingDues'));
    }

    // Create Razorpay order
    public function createOrder(Request $request)
    {
        \Log::info('Payment order creation started', $request->all());
        
        $validated = $request->validate([
            'charge_ids' => 'nullable|array',
            'charge_ids.*' => 'exists:monthly_charges,id',
            'due_ids' => 'nullable|array',
            'due_ids.*' => 'exists:dues,id',
        ]);

        $customer = Auth::guard('customer')->user();
        $totalAmount = 0;
        $items = [];
        
        \Log::info('Validated data', $validated);

        // Calculate total from selected charges
        if (!empty($validated['charge_ids'])) {
            $charges = MonthlyCharge::whereIn('id', $validated['charge_ids'])
                ->where('customer_id', $customer->id)
                ->where('status', 'pending')
                ->get();
            
            foreach ($charges as $charge) {
                $totalAmount += $charge->total_amount;
                $items[] = [
                    'type' => 'charge',
                    'id' => $charge->id,
                    'description' => 'Rent for ' . \Carbon\Carbon::parse($charge->month_year)->format('F Y'),
                    'amount' => $charge->total_amount,
                ];
            }
        }

        // Calculate total from selected dues
        if (!empty($validated['due_ids'])) {
            $dues = Due::whereIn('id', $validated['due_ids'])
                ->where('customer_id', $customer->id)
                ->where('status', 'pending')
                ->get();
            
            foreach ($dues as $due) {
                $totalAmount += $due->amount;
                $items[] = [
                    'type' => 'due',
                    'id' => $due->id,
                    'description' => $due->title,
                    'amount' => $due->amount,
                ];
            }
        }

        if ($totalAmount <= 0) {
            return back()->withErrors(['error' => 'Please select at least one item to pay.']);
        }

        // Create Razorpay order
        try {
            $api = new \Razorpay\Api\Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $orderData = [
                'receipt' => 'rcpt_' . time(),
                'amount' => $totalAmount * 100, // Amount in paise
                'currency' => 'INR',
                'notes' => [
                    'customer_id' => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'items' => json_encode($items),
                ]
            ];

            $razorpayOrder = $api->order->create($orderData);

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

        try {
            $api = new \Razorpay\Api\Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            // Verify signature
            $attributes = [
                'razorpay_order_id' => $validated['razorpay_order_id'],
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
            ];

            $api->utility->verifyPaymentSignature($attributes);

            // Fetch order details
            $order = $api->order->fetch($validated['razorpay_order_id']);
            $payment = $api->payment->fetch($validated['razorpay_payment_id']);

            // Get items from order notes
            $items = json_decode($order->notes->items, true);
            $customer = Auth::guard('customer')->user();

            \DB::beginTransaction();
            try {
                // Update charges and dues
                foreach ($items as $item) {
                    if ($item['type'] === 'charge') {
                        $charge = MonthlyCharge::find($item['id']);
                        if ($charge && $charge->customer_id === $customer->id) {
                            $charge->update([
                                'status' => 'paid',
                                'paid_date' => now(),
                                'payment_method' => 'razorpay',
                                'transaction_id' => $validated['razorpay_payment_id'],
                            ]);
                        }
                    } elseif ($item['type'] === 'due') {
                        $due = Due::find($item['id']);
                        if ($due && $due->customer_id === $customer->id) {
                            $due->update([
                                'status' => 'paid',
                                'paid_date' => now(),
                                'payment_method' => 'razorpay',
                                'transaction_id' => $validated['razorpay_payment_id'],
                            ]);
                        }
                    }
                }

                // Create payment record
                $customer->payments()->create([
                    'amount' => $order->amount / 100,
                    'payment_type' => 'Monthly Charges',
                    'payment_method' => 'razorpay',
                    'transaction_ref' => $validated['razorpay_payment_id'],
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                \DB::commit();

                Log::info('Payment successful', [
                    'customer_id' => $customer->id,
                    'payment_id' => $validated['razorpay_payment_id'],
                    'amount' => $order->amount / 100,
                ]);

                return redirect()->route('customer.payments.success', ['payment_id' => $validated['razorpay_payment_id']]);

            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }

        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            Log::error('Payment signature verification failed', [
                'error' => $e->getMessage(),
                'payment_id' => $validated['razorpay_payment_id'] ?? null,
            ]);
            
            return redirect()->route('customer.payments.failed')
                ->with('error', 'Payment verification failed. Please contact support.');
        } catch (\Exception $e) {
            Log::error('Payment verification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('customer.payments.failed')
                ->with('error', 'Payment processing failed. Please contact support.');
        }
    }

    // Payment success page
    public function success(Request $request)
    {
        $paymentId = $request->get('payment_id');
        return view('customer.payments.success', compact('paymentId'));
    }

    // Payment failed page
    public function failed()
    {
        return view('customer.payments.failed');
    }

    // Payment history
    public function history()
    {
        $customer = Auth::guard('customer')->user();
        
        $paidCharges = $customer->monthlyCharges()
            ->where('status', 'paid')
            ->orderBy('paid_date', 'desc')
            ->paginate(20);
        
        return view('customer.payments.history', compact('paidCharges'));
    }
}
