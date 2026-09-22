<?php

/*
|--------------------------------------------------------------------------
| Hostel defaults
|--------------------------------------------------------------------------
|
| Every value here can be changed by the owner from Admin → Settings; these
| are only the starting values used until something is saved there.
|
*/

return [
    'defaults' => [
        // Branding
        'hostel_name' => env('HOSTEL_NAME', 'Nestay PG'),
        'tagline' => env('HOSTEL_TAGLINE', 'Comfortable PG stays in Greater Noida'),
        'logo_path' => null,

        // Contact (blank values are hidden on the site)
        'contact_phone' => env('HOSTEL_PHONE', '+91 98765 43210'),
        'contact_email' => env('HOSTEL_EMAIL', 'contact@nestaypg.in'),
        'contact_whatsapp' => env('HOSTEL_WHATSAPP'),
        'contact_address' => env('HOSTEL_ADDRESS', 'Knowledge Park II & Pari Chowk, Greater Noida, Uttar Pradesh'),

        // Billing
        'min_advance' => 3000,
        'rent_due_day' => 5,
        'late_fee' => 0,
        'notice_period_days' => 30,
        // How long an online booking's bed hold lasts before it's released unpaid.
        // Only applies once Razorpay is configured — see Public\BookingController@processPayment.
        'online_payment_hold_minutes' => 30,

        // Receipts
        'gstin' => null,
        'receipt_footer' => 'This is a computer-generated receipt and does not need a signature.',

        // Policies
        'terms' => null,
    ],
];
