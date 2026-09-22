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
        'hostel_name' => env('HOSTEL_NAME', 'Hostel Manager'),
        'tagline' => env('HOSTEL_TAGLINE', 'Safe, comfortable stays'),
        'logo_path' => null,

        // Contact (blank values are hidden on the site)
        'contact_phone' => env('HOSTEL_PHONE'),
        'contact_email' => env('HOSTEL_EMAIL'),
        'contact_whatsapp' => env('HOSTEL_WHATSAPP'),
        'contact_address' => env('HOSTEL_ADDRESS'),

        // Billing
        'min_advance' => 3000,
        'rent_due_day' => 5,
        'late_fee' => 0,
        'notice_period_days' => 30,

        // Receipts
        'gstin' => null,
        'receipt_footer' => 'This is a computer-generated receipt and does not need a signature.',

        // Policies
        'terms' => null,
    ],
];
