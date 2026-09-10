<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            [
                'key'   => 'sslcommerz_enabled',
                'value' => '1',
                'type'  => 'bool',
                'group' => 'payment',
                'label' => 'SSLCommerz Enabled',
            ],
            [
                'key'   => 'sslcommerz_mode',
                'value' => 'sandbox',
                'type'  => 'string',
                'group' => 'payment',
                'label' => 'SSLCommerz Mode',
            ],
            [
                'key'   => 'sslcommerz_currency',
                'value' => 'BDT',
                'type'  => 'string',
                'group' => 'payment',
                'label' => 'SSLCommerz Currency',
            ],
            [
                'key'   => 'sslcommerz_sandbox_store_id',
                'value' => 'test6aa23e9167a8c',
                'type'  => 'string',
                'group' => 'payment',
                'label' => 'SSLCommerz Sandbox Store ID',
            ],
            [
                'key'   => 'sslcommerz_sandbox_store_passwd',
                'value' => 'Rw509Ge7gR8r',
                'type'  => 'string',
                'group' => 'payment',
                'label' => 'SSLCommerz Sandbox Store Password',
            ],
            [
                'key'   => 'sslcommerz_sandbox_url',
                'value' => 'https://sandbox.sslcommerz.com',
                'type'  => 'string',
                'group' => 'payment',
                'label' => 'SSLCommerz Sandbox URL',
            ],
            [
                'key'   => 'bkash_enabled',
                'value' => '1',
                'type'  => 'bool',
                'group' => 'payment',
                'label' => 'bKash Enabled',
            ],
            [
                'key'   => 'bkash_mode',
                'value' => 'sandbox',
                'type'  => 'string',
                'group' => 'payment',
                'label' => 'bKash Mode',
            ],
            [
                'key'   => 'bkash_currency',
                'value' => 'BDT',
                'type'  => 'string',
                'group' => 'payment',
                'label' => 'bKash Currency',
            ],
            [
                'key'   => 'bkash_sandbox_app_key',
                'value' => '4f6o0cjiki2rfm34kfdadl1eqq',
                'type'  => 'string',
                'group' => 'payment',
                'label' => 'bKash Sandbox App Key',
            ],
            [
                'key'   => 'bkash_sandbox_app_secret',
                'value' => '2is7hdktrekvrbljjh44ll3d9l1dtjo4pasmjvs5vl5qr3fug4b',
                'type'  => 'string',
                'group' => 'payment',
                'label' => 'bKash Sandbox App Secret',
            ],
            [
                'key'   => 'bkash_sandbox_username',
                'value' => 'sandboxTokenizedUser02',
                'type'  => 'string',
                'group' => 'payment',
                'label' => 'bKash Sandbox Username',
            ],
            [
                'key'   => 'bkash_sandbox_password',
                'value' => 'sandboxTokenizedUser02@12345',
                'type'  => 'string',
                'group' => 'payment',
                'label' => 'bKash Sandbox Password',
            ],
            [
                'key'   => 'bkash_sandbox_base_url',
                'value' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta',
                'type'  => 'string',
                'group' => 'payment',
                'label' => 'bKash Sandbox Base URL',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value'      => $setting['value'],
                    'type'       => $setting['type'],
                    'group'      => $setting['group'],
                    'label'      => $setting['label'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep settings or set back if required
    }
};
