<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    /**
     * Send FCM Push Notification to array of device tokens using FCM v1 or Legacy API.
     */
    public function sendPushNotification(array $fcmTokens, string $title, string $body, ?string $imageUrl = null, ?string $actionUrl = null): array
    {
        $isEnabled      = Setting::where('key', 'firebase_enabled')->value('value');
        $serverKey      = Setting::where('key', 'firebase_server_key')->value('value');
        $serviceAccount = Setting::where('key', 'firebase_service_account_json')->value('value');
        $projectId      = Setting::where('key', 'firebase_project_id')->value('value') ?? 'iomm-316e7';

        if ($isEnabled === '0') {
            return [
                'success' => false,
                'sent'    => 0,
                'message' => 'Firebase Push Notification is disabled in settings.',
            ];
        }

        $fcmTokens = array_values(array_filter(array_unique($fcmTokens)));
        if (empty($fcmTokens)) {
            return [
                'success' => false,
                'sent'    => 0,
                'message' => 'No active FCM device tokens found for target recipients.',
            ];
        }

        // Try FCM HTTP v1 via Service Account JSON first
        if (!empty($serviceAccount)) {
            $json = json_decode($serviceAccount, true);
            if (is_array($json)) {
                $accessToken = $this->getAccessTokenFromServiceAccount($json);
                if ($accessToken) {
                    $targetProjectId = $json['project_id'] ?? $projectId;
                    return $this->sendFcmV1($fcmTokens, $targetProjectId, $accessToken, $title, $body, $imageUrl, $actionUrl);
                }
            }
        }

        // Fallback to FCM Legacy Server Key API
        if (!empty($serverKey)) {
            return $this->sendFcmLegacy($fcmTokens, $serverKey, $title, $body, $imageUrl, $actionUrl);
        }

        return [
            'success' => false,
            'sent'    => 0,
            'message' => 'Neither Firebase Service Account JSON nor FCM Server Key is configured. Please enable Legacy API from Google Cloud Console or upload Service Account JSON.',
        ];
    }

    /**
     * Send FCM Push via HTTP v1 API (Recommended).
     */
    private function sendFcmV1(array $tokens, string $projectId, string $accessToken, string $title, string $body, ?string $imageUrl, ?string $actionUrl): array
    {
        $sentCount = 0;
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        foreach ($tokens as $token) {
            $payload = [
                'message' => [
                    'token'        => $token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => [
                        'title'      => $title,
                        'body'       => $body,
                        'image_url'  => $imageUrl ?: '',
                        'action_url' => $actionUrl ?: url('/'),
                    ],
                    'webpush' => [
                        'headers' => [
                            'Urgency' => 'high'
                        ],
                        'notification' => [
                            'title'        => $title,
                            'body'         => $body,
                            'icon'         => url('/images/logo.png'),
                            'image'        => $imageUrl ?: null,
                            'click_action' => $actionUrl ?: url('/'),
                        ],
                        'fcm_options' => [
                            'link' => $actionUrl ?: url('/')
                        ]
                    ]
                ]
            ];

            try {
                $res = Http::withToken($accessToken)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, $payload);

                if ($res->successful()) {
                    $sentCount++;
                } else {
                    Log::error('FCM HTTP v1 Error: ' . $res->body());
                }
            } catch (\Exception $e) {
                Log::error('FCM HTTP v1 Exception: ' . $e->getMessage());
            }
        }

        return [
            'success' => $sentCount > 0,
            'sent'    => $sentCount,
            'message' => "Successfully delivered {$sentCount} push notification(s) via FCM HTTP v1 API.",
        ];
    }

    /**
     * Send FCM Push via Legacy API.
     */
    private function sendFcmLegacy(array $fcmTokens, string $serverKey, string $title, string $body, ?string $imageUrl, ?string $actionUrl): array
    {
        $sentCount = 0;
        $chunks = array_chunk($fcmTokens, 500);

        foreach ($chunks as $chunk) {
            $payload = [
                'registration_ids' => $chunk,
                'notification'     => [
                    'title'        => $title,
                    'body'         => $body,
                    'icon'         => url('/images/logo.png'),
                    'image'        => $imageUrl ?: null,
                    'click_action' => $actionUrl ?: url('/'),
                ],
                'data'             => [
                    'title'      => $title,
                    'body'       => $body,
                    'image_url'  => $imageUrl ?: '',
                    'action_url' => $actionUrl ?: url('/'),
                ],
                'priority' => 'high',
            ];

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . trim($serverKey),
                    'Content-Type'  => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', $payload);

                if ($response->successful()) {
                    $json = $response->json();
                    $sentCount += $json['success'] ?? 0;
                } else {
                    Log::error('FCM Legacy Response Error: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('FCM Push Notification Exception: ' . $e->getMessage());
            }
        }

        return [
            'success' => $sentCount > 0,
            'sent'    => $sentCount,
            'message' => "Successfully delivered {$sentCount} push notification(s) via Legacy FCM API.",
        ];
    }

    /**
     * Generate Google OAuth 2.0 Access Token from Service Account JSON using native PHP RS256.
     */
    private function getAccessTokenFromServiceAccount(array $json): ?string
    {
        $clientEmail = $json['client_email'] ?? null;
        $privateKey  = $json['private_key'] ?? null;

        if (!$clientEmail || !$privateKey) {
            return null;
        }

        $header   = ['alg' => 'RS256', 'typ' => 'JWT'];
        $now      = time();
        $claimSet = [
            'iss'   => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'exp'   => $now + 3600,
            'iat'   => $now,
        ];

        $b64Header  = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($header)));
        $b64Claim   = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($claimSet)));
        $signatureInput = $b64Header . '.' . $b64Claim;

        $signature = '';
        if (!openssl_sign($signatureInput, $signature, $privateKey, 'SHA256')) {
            return null;
        }

        $b64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $signatureInput . '.' . $b64Signature;

        try {
            $res = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if ($res->successful()) {
                return $res->json()['access_token'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('FCM Service Account Access Token Error: ' . $e->getMessage());
        }

        return null;
    }
}
