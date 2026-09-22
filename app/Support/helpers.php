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
