<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS using MiM SMS Gateway (sms.mimsms.com)
     *
     * @param string $phone
     * @param string $message
     * @return array ['success' => bool, 'message' => string, 'response' => mixed]
     */
    public function send(string $phone, string $message): array
    {
        try {
            $enabled = Setting::where('key', 'sms_enabled')->value('value');
        } catch (\Throwable $e) {
            $enabled = null;
        }
        $enabled = $enabled ?? env('SMS_ENABLED', '1');

        if ($enabled !== '1' && $enabled !== true) {
            Log::info("SMS sending is disabled in settings. Intended recipient: {$phone}");
            return ['success' => false, 'message' => 'SMS sending is disabled in settings.'];
        }

        $formattedPhone = $this->formatBangladeshiPhone($phone);
        if (!$formattedPhone) {
            Log::warning("Invalid Bangladeshi phone number for SMS: {$phone}");
            return ['success' => false, 'message' => 'Invalid phone number format.'];
        }

        try {
            $apiUrl     = Setting::where('key', 'sms_api_url')->value('value');
            $apiKey     = Setting::where('key', 'sms_api_key')->value('value');
            $userName   = Setting::where('key', 'sms_username')->value('value');
            $senderName = Setting::where('key', 'sms_sender_name')->value('value');
        } catch (\Throwable $e) {
            $apiUrl = $apiKey = $userName = $senderName = null;
        }

        $apiUrl     = $apiUrl     ?: env('MIMSMS_API_URL', 'https://api.mimsms.com/api/V2/SMS');
        $apiKey     = $apiKey     ?: env('MIMSMS_API_KEY', 'RZW30XSQHWPQVIH');
        $userName   = $userName   ?: env('MIMSMS_USERNAME', 'kmarsus@gmail.com');
        $senderName = $senderName ?: env('MIMSMS_SENDER_NAME', '');

        if (empty($apiKey) || empty($userName)) {
            Log::error("MiMSMS API credentials missing. Please configure them in Settings.");
            return ['success' => false, 'message' => 'SMS Gateway credentials not configured.'];
        }

        $payload = [
            'apiKey'          => $apiKey,
            'userName'        => $userName,
            'senderName'      => $senderName,
            'transactionType' => 'T',
            'mobileNumber'    => $formattedPhone,
            'message'         => $message,
        ];

        try {
            Log::info("Dispatching MiMSMS to {$formattedPhone} via {$apiUrl}");

            $response = Http::timeout(15)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($apiUrl, $payload);

            $responseData = $response->json() ?? $response->body();
            $statusCode = $response->status();

            Log::info("MiMSMS Response [Status {$statusCode}]: " . json_encode($responseData));

            if ($response->successful()) {
                return [
                    'success'  => true,
                    'message'  => 'SMS sent successfully.',
                    'response' => $responseData,
                ];
            }

            return [
                'success'  => false,
                'message'  => 'SMS Gateway responded with error code ' . $statusCode,
                'response' => $responseData,
            ];
        } catch (\Exception $e) {
            Log::error("MiMSMS Connection Exception: " . $e->getMessage(), [
                'phone'   => $formattedPhone,
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send test SMS to check connection
     */
    public function testConnection(string $phone): array
    {
        $testMsg = "IOM System SMS Test: আলহামদুলিল্লাহ, আপনার SMS গেটওয়ে সফলভাবে সংযুক্ত হয়েছে। Time: " . now()->format('d M Y, h:i A');
        return $this->send($phone, $testMsg);
    }

    /**
     * Normalize Bangladeshi mobile numbers into 8801XXXXXXXXX format
     */
    public function formatBangladeshiPhone(string $phone): ?string
    {
        // Remove spaces, dashes, parentheses
        $clean = preg_replace('/[^0-9]/', '', $phone);

        // e.g. 01712345678 -> 8801712345678
        if (str_starts_with($clean, '01') && strlen($clean) === 11) {
            return '88' . $clean;
        }

        // e.g. 8801712345678
        if (str_starts_with($clean, '8801') && strlen($clean) === 13) {
            return $clean;
        }

        // e.g. 1712345678 (10 digits) -> 8801712345678
        if (str_starts_with($clean, '1') && strlen($clean) === 10) {
            return '880' . $clean;
        }

        return !empty($clean) ? $clean : null;
    }
}
