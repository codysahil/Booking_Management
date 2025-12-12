<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\MonthlyCharge;
use App\Models\Due;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    /**
     * Handle Razorpay webhook events
     * This handles edge cases like:
     * - Internet disconnection after payment
     * - Browser closed before callback
     * - Payment timeout on client side
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        
        // Verify webhook signature
        $webhookSecret = config('services.razorpay.webhook_secret');
        
        if ($webhookSecret) {
            $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
            
            if (!hash_equals($expectedSignature, $signature ?? '')) {
                Log::warning('Razorpay webhook signature verification failed');
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }
        
        $event = json_decode($payload, true);
        
        Log::info('Razorpay webhook received', [
            'event' => $event['event'] ?? 'unknown',
            'payload' => $event,
        ]);
        
        $eventType = $event['event'] ?? '';
        
        switch ($eventType) {
            case 'payment.captured':
                return $this->handlePaymentCaptured($event);
                
            case 'payment.failed':
                return $this->handlePaymentFailed($event);
                
            case 'order.paid':
                return $this->handleOrderPaid($event);
                
            default:
                Log::info('Unhandled Razorpay webhook event', ['event' => $eventType]);
                return response()->json(['status' => 'ignored']);
        }
    }
    
    /**
     * Handle successful payment capture
     */
    private function handlePaymentCaptured($event)
    {
        $paymentData = $event['payload']['payment']['entity'] ?? [];
        $orderId = $paymentData['order_id'] ?? null;
        $paymentId = $paymentData['id'] ?? null;
        
        if (!$orderId || !$paymentId) {
            Log::error('Missing order_id or payment_id in webhook');
            return response()->json(['error' => 'Missing data'], 400);
        }
        
        // Check if payment already processed
        $existingPayment = Payment::where('transaction_ref', $paymentId)->first();
        if ($existingPayment) {
            Log::info('Payment already processed', ['payment_id' => $paymentId]);
            return response()->json(['status' => 'already_processed']);
        }
        
        try {
            $api = new \Razorpay\Api\Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );
            
            // Fetch order to get items
            $order = $api->order->fetch($orderId);
            $items = json_decode($order->notes->items ?? '[]', true);
            $customerId = $order->notes->customer_id ?? null;
            
            if (!$customerId || empty($items)) {
                Log::error('Missing customer_id or items in order notes', [
                    'order_id' => $orderId,
                ]);
                return response()->json(['error' => 'Invalid order data'], 400);
            }
            
            $customer = Customer::find($customerId);
            if (!$customer) {
                Log::error('Customer not found', ['customer_id' => $customerId]);
                return response()->json(['error' => 'Customer not found'], 404);
            }
            
            // Get payment method details
            $paymentMethod = $this->getPaymentMethodDetails($paymentData);
            
            \DB::beginTransaction();
            try {
                // Update charges and dues
                foreach ($items as $item) {
                    if ($item['type'] === 'charge') {
                        $charge = MonthlyCharge::find($item['id']);
                        if ($charge && $charge->customer_id == $customerId && $charge->status === 'pending') {
                            $charge->update([
                                'status' => 'paid',
                                'paid_date' => now(),
                                'payment_method' => $paymentMethod,
                                'transaction_id' => $paymentId,
                            ]);
                        }
                    } elseif ($item['type'] === 'due') {
                        $due = Due::find($item['id']);
                        if ($due && $due->customer_id == $customerId && $due->status === 'pending') {
                            $due->update([
                                'status' => 'paid',
                                'paid_date' => now(),
                                'payment_method' => $paymentMethod,
                                'transaction_id' => $paymentId,
                            ]);
                        }
                    }
                }
                
                // Create payment record
                $customer->payments()->create([
                    'amount' => $paymentData['amount'] / 100,
                    'payment_type' => 'Monthly Charges',
                    'payment_method' => $paymentMethod,
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_order_id' => $orderId,
                    'transaction_ref' => $paymentId,
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                
                \DB::commit();
                
                Log::info('Webhook: Payment processed successfully', [
                    'customer_id' => $customerId,
                    'payment_id' => $paymentId,
                    'amount' => $paymentData['amount'] / 100,
                ]);
                
                return response()->json(['status' => 'success']);
                
            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Webhook payment processing failed', [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId,
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }
    
    /**
     * Handle failed payment
     */
    private function handlePaymentFailed($event)
    {
        $paymentData = $event['payload']['payment']['entity'] ?? [];
        $paymentId = $paymentData['id'] ?? null;
        $errorCode = $paymentData['error_code'] ?? 'unknown';
        $errorDescription = $paymentData['error_description'] ?? 'Payment failed';
        
        Log::warning('Payment failed via webhook', [
            'payment_id' => $paymentId,
            'error_code' => $errorCode,
            'error_description' => $errorDescription,
        ]);
        
        return response()->json(['status' => 'logged']);
    }
    
    /**
     * Handle order paid event (backup for payment.captured)
     */
    private function handleOrderPaid($event)
    {
        // This is a backup - order.paid fires when all payments for an order are captured
        $orderData = $event['payload']['order']['entity'] ?? [];
        $paymentData = $event['payload']['payment']['entity'] ?? [];
        
        Log::info('Order paid webhook received', [
            'order_id' => $orderData['id'] ?? null,
            'payment_id' => $paymentData['id'] ?? null,
        ]);
        
        // The payment.captured handler should have already processed this
        // But we can use this as a fallback
        if (!empty($paymentData)) {
            return $this->handlePaymentCaptured([
                'payload' => ['payment' => ['entity' => $paymentData]]
            ]);
        }
        
        return response()->json(['status' => 'ok']);
    }
    
    /**
     * Extract detailed payment method from payment data
     */
    private function getPaymentMethodDetails($paymentData)
    {
        $method = $paymentData['method'] ?? 'razorpay';
        
        switch ($method) {
            case 'upi':
                $vpa = $paymentData['vpa'] ?? '';
                if (str_contains($vpa, '@okaxis') || str_contains($vpa, '@okhdfcbank')) {
                    return 'GPay';
                } elseif (str_contains($vpa, '@paytm')) {
                    return 'Paytm';
                } elseif (str_contains($vpa, '@ybl') || str_contains($vpa, '@ibl')) {
                    return 'PhonePe';
                } elseif (str_contains($vpa, '@apl')) {
                    return 'Amazon Pay';
                }
                return 'UPI';
                
            case 'card':
                $card = $paymentData['card'] ?? [];
                $cardType = $card['type'] ?? 'card';
                $network = $card['network'] ?? '';
                if ($cardType === 'credit') {
                    return 'Credit Card' . ($network ? " ($network)" : '');
                } elseif ($cardType === 'debit') {
                    return 'Debit Card' . ($network ? " ($network)" : '');
                }
                return 'Card';
                
            case 'netbanking':
                $bank = $paymentData['bank'] ?? '';
                return 'Net Banking' . ($bank ? " ($bank)" : '');
                
            case 'wallet':
                $wallet = $paymentData['wallet'] ?? '';
                $walletNames = [
                    'paytm' => 'Paytm Wallet',
                    'phonepe' => 'PhonePe Wallet',
                    'amazonpay' => 'Amazon Pay',
                    'freecharge' => 'Freecharge',
                    'mobikwik' => 'MobiKwik',
                ];
                return $walletNames[$wallet] ?? 'Wallet';
                
            default:
                return ucfirst($method);
        }
    }
}
