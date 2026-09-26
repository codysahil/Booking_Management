@extends('legal.layout')

@section('title', 'Refund Policy')

@section('content')
<p>
    This Refund Policy covers two separate kinds of payment made through {{ config('app.name') }}: a hostel/PG
    business's own subscription to the platform, and a resident's rent/due payment made to their hostel.
</p>

<h2>1. Subscription billing (hostel/PG business → platform)</h2>
<p>
    Subscriptions renew automatically each billing cycle until cancelled. A subscription can be cancelled at any
    time; cancellation takes effect at the end of the current billing period, and access continues (read-write)
    until then. We do not provide prorated refunds for a partial billing period already paid for, except where
    required by law or at our discretion for a documented billing error.
</p>
<p>
    If a scheduled charge fails, a short grace period applies during which the account keeps full read-write
    access while payment is retried. If payment is not resolved within that window, the account moves to a
    read-only state until the subscription is renewed or cancelled.
</p>

<h2>2. Resident payments (resident → their hostel)</h2>
<p>
    Rent, deposits, and other dues a resident pays through the Service are payments to their own hostel/PG
    business, not to us. Refunds for these payments (e.g. a deposit refund on move-out, a billing correction) are
    the hostel's own responsibility and are governed by the hostel's own policy and their agreement with the
    resident. We are not a party to that transaction beyond processing the payment itself.
</p>

<h2>3. Payment processing errors</h2>
<p>
    If a payment is charged in error due to a technical fault in the Service itself (e.g. a duplicate charge), we
    will investigate and issue a refund for the erroneous charge once confirmed.
</p>

<h2>4. How to request a refund</h2>
<p>
    Contact us or your hostel (as applicable, per the sections above) using the contact details provided at
    onboarding. Include the payment reference/receipt number and a description of the issue.
</p>

<p class="text-xs text-gray-400 mt-8 border-t pt-4">
    This is a template and has not been reviewed by a lawyer. Confirm it matches your actual cancellation/refund
    practice and applicable consumer-protection law before relying on it.
</p>
@endsection
