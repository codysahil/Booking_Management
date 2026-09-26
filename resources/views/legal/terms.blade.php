@extends('legal.layout')

@section('title', 'Terms of Service')

@section('content')
<p>
    These Terms of Service ("Terms") govern access to and use of {{ config('app.name') }} (the "Service"), a
    hostel/PG management platform operated by us ("we", "us", "our"). By creating an account or using the Service,
    a hostel or PG business ("Customer", "you") agrees to these Terms.
</p>

<h2>1. The Service</h2>
<p>
    The Service lets a Customer manage their own hostel/PG business — rooms, beds, residents, rent collection,
    staff, and related records — on a subscription basis. Each Customer's account and data are kept separate from
    every other Customer's.
</p>

<h2>2. Accounts</h2>
<p>
    Accounts are created for a Customer by us directly; there is no public self-signup. A Customer is responsible
    for the accuracy of information provided, for all activity under their staff logins, and for keeping login
    credentials confidential.
</p>

<h2>3. Subscription and billing</h2>
<p>
    Access to the Service is billed on a recurring basis (monthly or yearly, per the plan selected) through our
    payment partner, Razorpay. By authorizing a subscription, a Customer authorizes recurring charges until the
    subscription is cancelled. See our <a href="{{ route('legal.refund-policy') }}" class="underline">Refund
    Policy</a> for cancellation and refund terms.
</p>

<h2>4. What happens if a subscription lapses</h2>
<p>
    If a payment fails and is not resolved within the grace period shown at checkout or in-app, the account moves
    to a read-only state: existing data remains visible, but new records can no longer be created, edited, or
    deleted until the subscription is renewed.
</p>

<h2>5. Customer's own data and residents</h2>
<p>
    A Customer is solely responsible for their relationship with, and obligations to, their own residents/tenants
    — including rent agreements, deposits, refunds, and any payments a resident makes through the Service. We are
    not a party to that relationship; the Service is a record-keeping and payment-processing tool for the
    Customer's own business.
</p>

<h2>6. Acceptable use</h2>
<p>
    The Service may not be used for any unlawful purpose, to store data you do not have the right to store, or to
    attempt to access another Customer's account or data.
</p>

<h2>7. Termination</h2>
<p>
    Either party may terminate a subscription as described in the Refund Policy. We may suspend or terminate an
    account for non-payment, breach of these Terms, or unlawful use.
</p>

<h2>8. Disclaimer and liability</h2>
<p>
    The Service is provided "as is". To the fullest extent permitted by law, we are not liable for indirect,
    incidental, or consequential damages arising from use of the Service.
</p>

<h2>9. Changes to these Terms</h2>
<p>
    We may update these Terms from time to time; continued use of the Service after an update constitutes
    acceptance of the revised Terms.
</p>

<h2>10. Contact</h2>
<p>
    Questions about these Terms can be sent to the contact details provided at onboarding.
</p>

<p class="text-xs text-gray-400 mt-8 border-t pt-4">
    This is a template and has not been reviewed by a lawyer. Have it reviewed for your jurisdiction and business
    before relying on it.
</p>
@endsection
