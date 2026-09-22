<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Due;
use App\Models\MonthlyCharge;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentReceived;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * The single place where money gets recorded. Marks the settled charges/dues as
 * paid and writes one ledger row (with receipt number) in `payments`, so online
 * payments, webhook retries and cash entries all show up the same way.
 */
class PaymentRecorder
{
    /**
     * @param  Collection<int, MonthlyCharge>  $charges
     * @param  Collection<int, Due>  $dues
     * @param  array{razorpay_payment_id?: string, razorpay_order_id?: string, recorded_by?: int|null, notes?: string|null}  $extra
     */
    public function settle(
        Customer $customer,
        Collection $charges,
        Collection $dues,
        string $method,
        ?string $reference = null,
        array $extra = [],
    ): ?Payment {
        // Webhook and browser callback can both arrive for the same Razorpay payment.
        if (! empty($extra['razorpay_payment_id'])) {
            $existing = Payment::where('razorpay_payment_id', $extra['razorpay_payment_id'])
                ->orWhere('transaction_ref', $extra['razorpay_payment_id'])
                ->first();
            if ($existing) {
                return $existing;
            }
        }

        $charges = $charges->filter(fn ($c) => $c->customer_id === $customer->id && $c->status !== 'paid')->values();
        $dues = $dues->filter(fn ($d) => $d->customer_id === $customer->id && $d->status !== 'paid')->values();

        if ($charges->isEmpty() && $dues->isEmpty()) {
            return null;
        }

        $now = now();
        $items = [];

        foreach ($charges as $charge) {
            $charge->update([
                'status' => 'paid',
                'paid_date' => $now,
                'payment_method' => $method,
                'transaction_id' => $reference,
            ]);
            $items[] = [
                'type' => 'charge',
                'id' => $charge->id,
                'description' => 'Rent & charges – ' . Carbon::parse($charge->month_year . '-01')->format('F Y'),
                'amount' => (float) $charge->total_amount,
            ];
        }

        foreach ($dues as $due) {
            $due->update([
                'status' => 'paid',
                'paid_date' => $now,
                'payment_method' => $method,
                'transaction_id' => $reference,
            ]);
            $items[] = [
                'type' => 'due',
                'id' => $due->id,
                'description' => $due->title,
                'amount' => (float) $due->amount,
            ];
        }

        $payment = $customer->payments()->create([
            'booking_id' => $charges->first()?->booking_id ?? $customer->bookings()->where('status', 'active')->value('id'),
            'monthly_charge_id' => $charges->count() === 1 && $dues->isEmpty() ? $charges->first()->id : null,
            'due_id' => $dues->count() === 1 && $charges->isEmpty() ? $dues->first()->id : null,
            'amount' => collect($items)->sum('amount'),
            'payment_type' => $this->describeType($charges, $dues),
            'payment_method' => $method,
            'transaction_ref' => $reference ?: 'CASH-' . $now->format('YmdHis'),
            'razorpay_payment_id' => $extra['razorpay_payment_id'] ?? null,
            'razorpay_order_id' => $extra['razorpay_order_id'] ?? null,
            'status' => Payment::STATUS_PAID,
            'paid_at' => $now,
            'items' => $items,
            'notes' => $extra['notes'] ?? null,
            'recorded_by' => $extra['recorded_by'] ?? null,
        ]);

        $this->notifyStaff($payment);

        return $payment;
    }

    /** Record a one-off amount that is not tied to a charge or due (e.g. advance at check-in). */
    public function recordDirect(Customer $customer, float $amount, string $type, string $method, array $attributes = []): Payment
    {
        $payment = $customer->payments()->create(array_merge([
            'amount' => $amount,
            'payment_type' => $type,
            'payment_method' => $method,
            'transaction_ref' => strtoupper(substr($type, 0, 3)) . '-' . now()->format('YmdHis'),
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
            'items' => [['type' => strtolower($type), 'description' => $type, 'amount' => $amount]],
        ], $attributes));

        $this->notifyStaff($payment);

        return $payment;
    }

    private function describeType(Collection $charges, Collection $dues): string
    {
        if ($dues->isEmpty() && $charges->isNotEmpty()) {
            return 'Rent';
        }
        if ($charges->isEmpty() && $dues->count() === 1) {
            return Due::TYPES[$dues->first()->due_type] ?? 'Due';
        }

        return 'Monthly Charges';
    }

    private function notifyStaff(Payment $payment): void
    {
        try {
            // Only ping staff for online payments; cash entries are made by staff themselves.
            if ($payment->recorded_by) {
                return;
            }
            Notification::send(User::query()->where('is_active', true)->get(), new PaymentReceived($payment));
        } catch (\Throwable $e) {
            Log::warning('Could not send payment notification', ['error' => $e->getMessage()]);
        }
    }
}
