<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Razorpay\Api\Api;

/**
 * Thin wrapper around the Razorpay SDK so controllers stay simple and tests
 * can swap it out without hitting the network.
 *
 * Two entirely separate concerns share this one class, and must never be
 * mixed up:
 *  - Platform billing (createPlan/createSubscription/fetchSubscription/
 *    cancelSubscription, and isConfigured()/api()) always uses THIS
 *    platform's own Razorpay account (config/services.php) — this is the
 *    money hostels pay US for their subscription.
 *  - Resident due-payments (createOrder/fetchOrder/verifySignature/
 *    fetchPayment, and residentPaymentsConfigured()/tenantApi()) uses the
 *    CURRENTLY-BOUND TENANT's own Razorpay account when the hostel has
 *    connected one (Settings → Payment Gateway), falling back to the
 *    platform account otherwise — this is rent residents pay THEIR hostel,
 *    which must land in that hostel's own bank account, not ours.
 */
class Razorpay
{
    private ?Api $api = null;
    private ?Api $tenantApiInstance = null;

    public function isConfigured(): bool
    {
        return filled(config('services.razorpay.key')) && filled(config('services.razorpay.secret'));
    }

    public function api(): Api
    {
        return $this->api ??= new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
    }

    /** The key id a hostel's resident-facing checkout should use — their own if connected, else the platform's. */
    public function tenantKeyId(): ?string
    {
        return Setting::get('razorpay_key_id') ?: config('services.razorpay.key');
    }

    private function tenantKeySecret(): ?string
    {
        $encrypted = Setting::get('razorpay_key_secret');

        if (filled($encrypted)) {
            try {
                return Crypt::decryptString($encrypted);
            } catch (\Exception) {
                // Ciphertext from a different APP_KEY, or corrupted — fall through to the platform default.
            }
        }

        return config('services.razorpay.secret');
    }

    public function residentPaymentsConfigured(): bool
    {
        return filled($this->tenantKeyId()) && filled($this->tenantKeySecret());
    }

    private function tenantApi(): Api
    {
        return $this->tenantApiInstance ??= new Api($this->tenantKeyId(), $this->tenantKeySecret());
    }

    /** @param array<string, scalar> $notes */
    public function createOrder(float $amountInRupees, string $receipt, array $notes = [])
    {
        return $this->tenantApi()->order->create([
            'receipt' => substr($receipt, 0, 40),
            'amount' => (int) round($amountInRupees * 100), // paise
            'currency' => 'INR',
            'notes' => $notes,
        ]);
    }

    public function fetchOrder(string $orderId)
    {
        return $this->tenantApi()->order->fetch($orderId);
    }

    /**
     * Creates the matching Plan on Razorpay's side for a locally-defined
     * Plan — a one-time setup step, done once when the super admin creates
     * or first attaches billing to a Plan. amountInRupees is per billing
     * cycle (e.g. the monthly price for a 'monthly' plan).
     */
    public function createPlan(string $name, float $amountInRupees, string $interval): object
    {
        return $this->api()->plan->create([
            'period' => $interval === 'yearly' ? 'yearly' : 'monthly',
            'interval' => 1,
            'item' => [
                'name' => $name,
                'amount' => (int) round($amountInRupees * 100), // paise
                'currency' => 'INR',
            ],
        ]);
    }

    /**
     * Starts a subscription mandate for one tenant against an already
     * Razorpay-linked Plan. total_count is a large but finite cycle count —
     * Razorpay Subscriptions requires one — since this bills indefinitely
     * until cancelled, not for a fixed term. Returns an entity carrying a
     * short_url: a hosted Razorpay page where the hostel owner authorizes
     * the recurring mandate (UPI Autopay / e-mandate / saved card).
     */
    public function createSubscription(string $razorpayPlanId, array $notes = []): object
    {
        return $this->api()->subscription->create([
            'plan_id' => $razorpayPlanId,
            'total_count' => 120, // 10 years of cycles; renews automatically until cancelled
            'quantity' => 1,
            'notes' => $notes,
        ]);
    }

    public function fetchSubscription(string $subscriptionId): object
    {
        return $this->api()->subscription->fetch($subscriptionId);
    }

    public function cancelSubscription(string $subscriptionId, bool $cancelAtCycleEnd = false): object
    {
        return $this->api()->subscription->fetch($subscriptionId)->cancel(['cancel_at_cycle_end' => $cancelAtCycleEnd ? 1 : 0]);
    }

    public function fetchPayment(string $paymentId)
    {
        return $this->tenantApi()->payment->fetch($paymentId);
    }

    /** @param array{razorpay_order_id: string, razorpay_payment_id: string, razorpay_signature: string} $attributes */
    public function verifySignature(array $attributes): void
    {
        $this->tenantApi()->utility->verifyPaymentSignature($attributes);
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
