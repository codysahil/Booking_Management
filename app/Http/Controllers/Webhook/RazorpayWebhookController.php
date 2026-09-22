<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Due;
use App\Models\MonthlyCharge;
use App\Models\Payment;
use App\Services\PaymentRecorder;
use App\Services\Razorpay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Server-to-server backstop for online payments: catches the case where the
 * customer's browser never comes back to /payments/verify (closed tab, dropped
 * connection, timeout) by settling the same charges/dues through PaymentRecorder,
 * which de-dupes on razorpay_payment_id so this never double-counts a payment
 * already recorded by the browser callback.
 */
class RazorpayWebhookController extends Controller
{
    public function __construct(private PaymentRecorder $recorder)
    {
    }

    public function handle(Request $request)
    {
        $webhookSecret = config('services.razorpay.webhook_secret');

        if (! $webhookSecret) {
            Log::error('Razorpay webhook secret is not configured; refusing webhook.');

            return response()->json(['error' => 'Webhook not configured'], 500);
        }

        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        if (! $signature || ! hash_equals($expectedSignature, $signature)) {
            Log::warning('Razorpay webhook signature verification failed');

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);
        $eventType = $event['event'] ?? '';

        Log::info('Razorpay webhook received', ['event' => $eventType]);

        return match ($eventType) {
            'payment.captured', 'order.paid' => $this->handlePaymentCaptured($event),
            'payment.failed' => $this->handlePaymentFailed($event),
            default => response()->json(['status' => 'ignored']),
        };
    }

    private function handlePaymentCaptured(array $event)
    {
        $paymentData = $event['payload']['payment']['entity'] ?? [];
        $orderId = $paymentData['order_id'] ?? null;
        $paymentId = $paymentData['id'] ?? null;

        if (! $orderId || ! $paymentId) {
            Log::error('Missing order_id or payment_id in webhook');

            return response()->json(['error' => 'Missing data'], 400);
        }

        $existing = Payment::where('razorpay_payment_id', $paymentId)
            ->orWhere('transaction_ref', $paymentId)
            ->first();

        if ($existing) {
            Log::info('Payment already processed', ['payment_id' => $paymentId]);

            return response()->json(['status' => 'already_processed']);
        }

        try {
            $razorpay = app(Razorpay::class);
            $order = $razorpay->fetchOrder($orderId);
            $items = json_decode($order->notes->items ?? '[]', true) ?: [];
            $customerId = $order->notes->customer_id ?? null;

            if (! $customerId || empty($items)) {
                Log::error('Missing customer_id or items in order notes', ['order_id' => $orderId]);

                return response()->json(['error' => 'Invalid order data'], 400);
            }

            $customer = Customer::find($customerId);

            if (! $customer) {
                Log::error('Customer not found', ['customer_id' => $customerId]);

                return response()->json(['error' => 'Customer not found'], 404);
            }

            $charges = MonthlyCharge::whereIn('id', collect($items)->where('type', 'charge')->pluck('id'))->get();
            $dues = Due::whereIn('id', collect($items)->where('type', 'due')->pluck('id'))->get();

            $payment = $this->recorder->settle(
                $customer,
                $charges,
                $dues,
                Razorpay::describeMethod($paymentData),
                $paymentId,
                [
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_order_id' => $orderId,
                    'notes' => 'Recorded via webhook',
                ],
            );

            Log::info('Webhook: payment processed successfully', [
                'customer_id' => $customerId,
                'payment_id' => $paymentId,
                'recorded_payment_id' => $payment?->id,
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Webhook payment processing failed', [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId,
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    private function handlePaymentFailed(array $event)
    {
        $paymentData = $event['payload']['payment']['entity'] ?? [];

        Log::warning('Payment failed via webhook', [
            'payment_id' => $paymentData['id'] ?? null,
            'error_code' => $paymentData['error_code'] ?? 'unknown',
            'error_description' => $paymentData['error_description'] ?? 'Payment failed',
        ]);

        return response()->json(['status' => 'logged']);
    }
}
