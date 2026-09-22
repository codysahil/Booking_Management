<?php

namespace App\Services;

use Razorpay\Api\Api;

/**
 * Thin wrapper around the Razorpay SDK so controllers stay simple and tests
 * can swap it out without hitting the network.
 */
class Razorpay
{
    private ?Api $api = null;

    public function isConfigured(): bool
    {
        return filled(config('services.razorpay.key')) && filled(config('services.razorpay.secret'));
    }

    public function api(): Api
    {
        return $this->api ??= new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
    }

    /** @param array<string, scalar> $notes */
    public function createOrder(float $amountInRupees, string $receipt, array $notes = [])
    {
        return $this->api()->order->create([
            'receipt' => substr($receipt, 0, 40),
            'amount' => (int) round($amountInRupees * 100), // paise
            'currency' => 'INR',
            'notes' => $notes,
        ]);
    }

    public function fetchOrder(string $orderId)
    {
        return $this->api()->order->fetch($orderId);
    }

    public function fetchPayment(string $paymentId)
    {
        return $this->api()->payment->fetch($paymentId);
    }

    /** @param array{razorpay_order_id: string, razorpay_payment_id: string, razorpay_signature: string} $attributes */
    public function verifySignature(array $attributes): void
    {
        $this->api()->utility->verifyPaymentSignature($attributes);
    }

    /** Human-friendly method name from a Razorpay payment entity (array form). */
    public static function describeMethod(array $payment): string
    {
        $method = $payment['method'] ?? 'razorpay';

        switch ($method) {
            case 'upi':
                $vpa = $payment['vpa'] ?? ($payment['upi']['vpa'] ?? '');
                return match (true) {
                    str_contains($vpa, '@okaxis'), str_contains($vpa, '@okhdfcbank'),
                    str_contains($vpa, '@okicici'), str_contains($vpa, '@oksbi') => 'GPay',
                    str_contains($vpa, '@paytm') => 'Paytm',
                    str_contains($vpa, '@ybl'), str_contains($vpa, '@ibl'), str_contains($vpa, '@axl') => 'PhonePe',
                    str_contains($vpa, '@apl') => 'Amazon Pay',
                    default => 'UPI',
                };

            case 'card':
                $card = $payment['card'] ?? [];
                $network = $card['network'] ?? '';
                $type = match ($card['type'] ?? null) {
                    'credit' => 'Credit Card',
                    'debit' => 'Debit Card',
                    default => 'Card',
                };
                return $type . ($network ? " ($network)" : '');

            case 'netbanking':
                $bank = $payment['bank'] ?? '';
                return 'Net Banking' . ($bank ? " ($bank)" : '');

            case 'wallet':
                return [
                    'paytm' => 'Paytm Wallet',
                    'phonepe' => 'PhonePe Wallet',
                    'amazonpay' => 'Amazon Pay',
                    'freecharge' => 'Freecharge',
                    'mobikwik' => 'MobiKwik',
                ][$payment['wallet'] ?? ''] ?? 'Wallet';

            default:
                return ucfirst($method);
        }
    }
}
