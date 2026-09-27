<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /** Read an owner-editable setting (see config/hostel.php for the keys). */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('money')) {
    /** Format an amount as Indian rupees, e.g. ₹12,500 or ₹12,500.50. */
    function money(float|int|string|null $amount): string
    {
        $amount = (float) $amount;
        $decimals = floor($amount) == $amount ? 0 : 2;

        return '₹' . number_format($amount, $decimals);
    }
}

if (! function_exists('whatsapp_link')) {
    /**
     * A wa.me deep link that opens WhatsApp with the given number and message
     * pre-filled — no WhatsApp Business API/account needed, just a click that
     * hands off to WhatsApp itself. Returns null if the phone number is blank,
     * so callers can hide the button rather than link to a broken chat.
     */
    function whatsapp_link(?string $phone, ?string $message = null): ?string
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        // Assume an unprefixed 10-digit number is Indian (this app's target market).
        if (strlen($digits) === 10) {
            $digits = '91' . $digits;
        }

        $url = "https://wa.me/{$digits}";

        return $message ? $url . '?text=' . rawurlencode($message) : $url;
    }
}
