<?php

namespace App\Notifications;

use App\Models\Payment;

class PaymentReceived extends StaffAlert
{
    public function __construct(public Payment $payment)
    {
    }

    protected function kind(): string
    {
        return 'payment';
    }

    protected function title(): string
    {
        return 'Payment received: ' . money($this->payment->amount);
    }

    protected function message(): string
    {
        $customer = $this->payment->customer;

        return sprintf('%s (%s) paid %s via %s.',
            $customer?->name ?? 'A resident',
            $customer?->customer_code ?? '—',
            strtolower($this->payment->payment_type ?? 'charges'),
            $this->payment->payment_method);
    }

    protected function url(): string
    {
        return route('admin.payments.receipt', $this->payment);
    }
}
