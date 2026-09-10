<?php

namespace App\Services;

use App\Models\Setting;

class PaymentGatewayService
{
    /**
     * Get SSLCommerz active configuration based on sandbox/live toggle.
     *
     * @return array
     */
    public static function getSslcommerzConfig(): array
    {
        $enabled = Setting::where('key', 'sslcommerz_enabled')->value('value') === '1';
        $mode = Setting::where('key', 'sslcommerz_mode')->value('value') ?: 'sandbox';
        $isSandbox = ($mode === 'sandbox');

        $storeId = $isSandbox
            ? (Setting::where('key', 'sslcommerz_sandbox_store_id')->value('value') ?: '')
            : (Setting::where('key', 'sslcommerz_live_store_id')->value('value') ?: '');

        $storePasswd = $isSandbox
            ? (Setting::where('key', 'sslcommerz_sandbox_store_passwd')->value('value') ?: '')
            : (Setting::where('key', 'sslcommerz_live_store_passwd')->value('value') ?: '');

        $baseUrl = $isSandbox
            ? (Setting::where('key', 'sslcommerz_sandbox_url')->value('value') ?: 'https://sandbox.sslcommerz.com')
            : (Setting::where('key', 'sslcommerz_live_url')->value('value') ?: 'https://securepay.sslcommerz.com');

        $currency = Setting::where('key', 'sslcommerz_currency')->value('value') ?: 'BDT';

        return [
            'enabled'      => $enabled,
            'mode'         => $mode,
            'is_sandbox'   => $isSandbox,
            'store_id'     => $storeId,
            'store_passwd' => $storePasswd,
            'base_url'     => rtrim($baseUrl, '/'),
            'currency'     => $currency,
        ];
    }

    /**
     * Get Direct bKash active configuration based on sandbox/live toggle.
     *
     * @return array
     */
    public static function getBkashConfig(): array
    {
        $enabled = Setting::where('key', 'bkash_enabled')->value('value') === '1';
        $mode = Setting::where('key', 'bkash_mode')->value('value') ?: 'sandbox';
        $isSandbox = ($mode === 'sandbox');

        $appKey = $isSandbox
            ? (Setting::where('key', 'bkash_sandbox_app_key')->value('value') ?: '')
            : (Setting::where('key', 'bkash_live_app_key')->value('value') ?: '');

        $appSecret = $isSandbox
            ? (Setting::where('key', 'bkash_sandbox_app_secret')->value('value') ?: '')
            : (Setting::where('key', 'bkash_live_app_secret')->value('value') ?: '');

        $username = $isSandbox
            ? (Setting::where('key', 'bkash_sandbox_username')->value('value') ?: '')
            : (Setting::where('key', 'bkash_live_username')->value('value') ?: '');

        $password = $isSandbox
            ? (Setting::where('key', 'bkash_sandbox_password')->value('value') ?: '')
            : (Setting::where('key', 'bkash_live_password')->value('value') ?: '');

        $baseUrl = $isSandbox
            ? (Setting::where('key', 'bkash_sandbox_base_url')->value('value') ?: 'https://tokenized.sandbox.bka.sh/v1.2.0-beta')
            : (Setting::where('key', 'bkash_live_base_url')->value('value') ?: 'https://tokenized.pay.bka.sh/v1.2.0-beta');

        $currency = Setting::where('key', 'bkash_currency')->value('value') ?: 'BDT';

        return [
            'enabled'    => $enabled,
            'mode'       => $mode,
            'is_sandbox' => $isSandbox,
            'app_key'    => $appKey,
            'app_secret' => $appSecret,
            'username'   => $username,
            'password'   => $password,
            'base_url'   => rtrim($baseUrl, '/'),
            'currency'   => $currency,
        ];
    }

    /**
     * Check if SSLCommerz is enabled.
     */
    public static function isSslcommerzActive(): bool
    {
        $config = self::getSslcommerzConfig();
        return $config['enabled'] && !empty($config['store_id']) && !empty($config['store_passwd']);
    }

    /**
     * Check if Direct bKash is enabled.
     */
    public static function isBkashActive(): bool
    {
        $config = self::getBkashConfig();
        return $config['enabled'] && !empty($config['app_key']) && !empty($config['app_secret']);
    }
}
