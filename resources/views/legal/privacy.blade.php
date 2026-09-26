@extends('legal.layout')

@section('title', 'Privacy Policy')

@section('content')
<p>
    This Privacy Policy explains how {{ config('app.name') }} ("we", "us") handles data for hostel/PG businesses
    ("Customers") and the individuals whose information Customers store in the Service, such as residents and
    staff ("End Users").
</p>

<h2>1. What we collect</h2>
<ul>
    <li>Account information for a Customer's staff logins (name, email, role).</li>
    <li>Business records a Customer enters: branches, rooms, beds, residents, bookings, rent/payment history,
        expenses, and staff/employee records.</li>
    <li>Payment information processed through Razorpay for subscription billing and for residents' own rent/due
        payments. We do not store full card numbers or bank credentials — these are handled directly by Razorpay.</li>
    <li>Standard technical logs (IP address, browser, timestamps) for security and troubleshooting.</li>
</ul>

<h2>2. How we use it</h2>
<ul>
    <li>To operate the Service: authenticate logins, keep each Customer's data isolated, generate rent charges,
        record payments, and send in-app notifications.</li>
    <li>To bill Customers for their subscription and process residents' online payments.</li>
    <li>To provide support when a Customer contacts us.</li>
    <li>To maintain security, prevent abuse, and comply with legal obligations.</li>
</ul>

<h2>3. Who can see what</h2>
<p>
    A Customer's staff can see only their own hostel's data. Residents (End Users) can see only their own booking,
    payment, and request history through the resident portal. We do not sell or share Customer or End User data
    with third parties other than the service providers needed to run the Service (e.g. Razorpay for payments,
    our hosting provider, and, if configured, Cloudinary for file storage).
</p>

<h2>4. Data retention</h2>
<p>
    Data is retained for as long as a Customer's account is active. If a subscription is cancelled, data is
    retained for a reasonable period to allow reactivation or export before deletion; contact us to request earlier
    deletion or an export.
</p>

<h2>5. Security</h2>
<p>
    We use industry-standard measures (encrypted connections, hashed passwords, tenant-level data isolation) to
    protect data, but no system is completely secure, and we cannot guarantee absolute security.
</p>

<h2>6. Your rights</h2>
<p>
    A Customer or End User can request access to, correction of, or deletion of their personal data by contacting
    us through the details provided at onboarding, subject to any records we are legally required to retain.
</p>

<h2>7. Changes to this Policy</h2>
<p>
    We may update this Privacy Policy from time to time; continued use of the Service after an update constitutes
    acceptance of the revised Policy.
</p>

<p class="text-xs text-gray-400 mt-8 border-t pt-4">
    This is a template and has not been reviewed by a lawyer. In particular, review it against India's Digital
    Personal Data Protection Act (or the relevant law in your jurisdiction) before relying on it.
</p>
@endsection
