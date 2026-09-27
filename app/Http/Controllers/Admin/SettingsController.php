<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = Setting::allValues();
        $hasRazorpaySecret = filled(Setting::get('razorpay_key_secret'));

        return view('admin.settings.edit', compact('settings', 'hasRazorpaySecret'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'hostel_name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'contact_whatsapp' => 'nullable|string|max:20',
            'contact_address' => 'nullable|string',
            'rent_due_day' => 'required|integer|min:1|max:28',
            'late_fee' => 'required|numeric|min:0',
            'notice_period_days' => 'required|integer|min:0',
            'gstin' => 'nullable|string|max:20',
            'receipt_footer' => 'nullable|string',
            'terms' => 'nullable|string',
            'razorpay_key_id' => 'nullable|string|max:255',
            'razorpay_key_secret' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('logo')) {
            $oldLogo = Setting::get('logo_path');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $validated['logo_path'] = $request->file('logo')->store('branding', 'public');
        }
        unset($validated['logo']);

        // Leave-blank-to-keep, same as a password field: only touch the stored
        // secret when a new one was actually typed, and always encrypt it —
        // this is the one setting in this table that isn't safe to keep in
        // plain text (see App\Services\Razorpay for where it's decrypted).
        if (filled($validated['razorpay_key_secret'] ?? null)) {
            $validated['razorpay_key_secret'] = Crypt::encryptString($validated['razorpay_key_secret']);
        } else {
            unset($validated['razorpay_key_secret']);
        }

        Setting::putMany($validated);

        return back()->with('success', 'Settings saved.');
    }
}
