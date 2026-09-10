<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class PaymentGatewaySettingController extends Controller
{
    /**
     * Display the payment gateway settings page with tabs for SSLCommerz and bKash.
     */
    public function index()
    {
        $keys = [
            // SSLCommerz General
            'sslcommerz_enabled',
            'sslcommerz_mode',
            'sslcommerz_currency',
            // SSLCommerz Sandbox
            'sslcommerz_sandbox_store_id',
            'sslcommerz_sandbox_store_passwd',
            'sslcommerz_sandbox_url',
            // SSLCommerz Live
            'sslcommerz_live_store_id',
            'sslcommerz_live_store_passwd',
            'sslcommerz_live_url',

            // bKash General
            'bkash_enabled',
            'bkash_mode',
            'bkash_currency',
            // bKash Sandbox
            'bkash_sandbox_app_key',
            'bkash_sandbox_app_secret',
            'bkash_sandbox_username',
            'bkash_sandbox_password',
            'bkash_sandbox_base_url',
            // bKash Live
            'bkash_live_app_key',
            'bkash_live_app_secret',
            'bkash_live_username',
            'bkash_live_password',
            'bkash_live_base_url',
        ];

        $settings = Setting::whereIn('key', $keys)->pluck('value', 'key')->toArray();

        // Sensible defaults if not previously saved
        $defaults = [
            'sslcommerz_enabled' => '0',
            'sslcommerz_mode' => 'sandbox',
            'sslcommerz_currency' => 'BDT',
            'sslcommerz_sandbox_url' => 'https://sandbox.sslcommerz.com',
            'sslcommerz_live_url' => 'https://securepay.sslcommerz.com',

            'bkash_enabled' => '0',
            'bkash_mode' => 'sandbox',
            'bkash_currency' => 'BDT',
            'bkash_sandbox_base_url' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta',
            'bkash_live_base_url' => 'https://tokenized.pay.bka.sh/v1.2.0-beta',
        ];

        foreach ($defaults as $k => $v) {
            if (!isset($settings[$k]) || $settings[$k] === '') {
                $settings[$k] = $v;
            }
        }

        return view('admin.settings.payment_gateways', compact('settings'));
    }

    /**
     * Update SSLCommerz configuration.
     */
    public function updateSslcommerz(Request $request)
    {
        $enabled = $request->boolean('sslcommerz_enabled') ? '1' : '0';
        $mode = $request->input('sslcommerz_mode', 'sandbox');
        if (!in_array($mode, ['sandbox', 'live'])) {
            $mode = 'sandbox';
        }

        Setting::updateOrCreate(['key' => 'sslcommerz_enabled'], ['value' => $enabled]);
        Setting::updateOrCreate(['key' => 'sslcommerz_mode'], ['value' => $mode]);
        Setting::updateOrCreate(['key' => 'sslcommerz_currency'], ['value' => $request->input('sslcommerz_currency', 'BDT')]);

        // Sandbox credentials
        Setting::updateOrCreate(['key' => 'sslcommerz_sandbox_store_id'], ['value' => trim((string) $request->input('sslcommerz_sandbox_store_id', ''))]);
        Setting::updateOrCreate(['key' => 'sslcommerz_sandbox_url'], ['value' => trim((string) $request->input('sslcommerz_sandbox_url', 'https://sandbox.sslcommerz.com'))]);
        if ($request->filled('sslcommerz_sandbox_store_passwd')) {
            Setting::updateOrCreate(['key' => 'sslcommerz_sandbox_store_passwd'], ['value' => (string) $request->input('sslcommerz_sandbox_store_passwd')]);
        }

        // Live credentials
        Setting::updateOrCreate(['key' => 'sslcommerz_live_store_id'], ['value' => trim((string) $request->input('sslcommerz_live_store_id', ''))]);
        Setting::updateOrCreate(['key' => 'sslcommerz_live_url'], ['value' => trim((string) $request->input('sslcommerz_live_url', 'https://securepay.sslcommerz.com'))]);
        if ($request->filled('sslcommerz_live_store_passwd')) {
            Setting::updateOrCreate(['key' => 'sslcommerz_live_store_passwd'], ['value' => (string) $request->input('sslcommerz_live_store_passwd')]);
        }

        return redirect()->route('admin.settings.payment-gateways.index', ['tab' => 'sslcommerz'])
            ->with('success', 'SSLCommerz পেমেন্ট গেটওয়ে সেটিংস সফলভাবে সংরক্ষিত হয়েছে।');
    }

    /**
     * Update bKash configuration.
     */
    public function updateBkash(Request $request)
    {
        $enabled = $request->boolean('bkash_enabled') ? '1' : '0';
        $mode = $request->input('bkash_mode', 'sandbox');
        if (!in_array($mode, ['sandbox', 'live'])) {
            $mode = 'sandbox';
        }

        Setting::updateOrCreate(['key' => 'bkash_enabled'], ['value' => $enabled]);
        Setting::updateOrCreate(['key' => 'bkash_mode'], ['value' => $mode]);
        Setting::updateOrCreate(['key' => 'bkash_currency'], ['value' => $request->input('bkash_currency', 'BDT')]);

        // Sandbox credentials
        Setting::updateOrCreate(['key' => 'bkash_sandbox_app_key'], ['value' => trim((string) $request->input('bkash_sandbox_app_key', ''))]);
        Setting::updateOrCreate(['key' => 'bkash_sandbox_username'], ['value' => trim((string) $request->input('bkash_sandbox_username', ''))]);
        Setting::updateOrCreate(['key' => 'bkash_sandbox_base_url'], ['value' => trim((string) $request->input('bkash_sandbox_base_url', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'))]);
        if ($request->filled('bkash_sandbox_app_secret')) {
            Setting::updateOrCreate(['key' => 'bkash_sandbox_app_secret'], ['value' => (string) $request->input('bkash_sandbox_app_secret')]);
        }
        if ($request->filled('bkash_sandbox_password')) {
            Setting::updateOrCreate(['key' => 'bkash_sandbox_password'], ['value' => (string) $request->input('bkash_sandbox_password')]);
        }

        // Live credentials
        Setting::updateOrCreate(['key' => 'bkash_live_app_key'], ['value' => trim((string) $request->input('bkash_live_app_key', ''))]);
        Setting::updateOrCreate(['key' => 'bkash_live_username'], ['value' => trim((string) $request->input('bkash_live_username', ''))]);
        Setting::updateOrCreate(['key' => 'bkash_live_base_url'], ['value' => trim((string) $request->input('bkash_live_base_url', 'https://tokenized.pay.bka.sh/v1.2.0-beta'))]);
        if ($request->filled('bkash_live_app_secret')) {
            Setting::updateOrCreate(['key' => 'bkash_live_app_secret'], ['value' => (string) $request->input('bkash_live_app_secret')]);
        }
        if ($request->filled('bkash_live_password')) {
            Setting::updateOrCreate(['key' => 'bkash_live_password'], ['value' => (string) $request->input('bkash_live_password')]);
        }

        return redirect()->route('admin.settings.payment-gateways.index', ['tab' => 'bkash'])
            ->with('success', 'bKash (বিকাশ) পেমেন্ট গেটওয়ে সেটিংস সফলভাবে সংরক্ষিত হয়েছে।');
    }
}
