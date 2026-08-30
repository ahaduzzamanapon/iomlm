<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    /**
     * Send FCM Push Notification to array of device tokens.
     */
    public function sendPushNotification(array $fcmTokens, string $title, string $body, ?string $imageUrl = null, ?string $actionUrl = null): array
    {
        $serverKey = Setting::where('key', 'firebase_server_key')->value('value');
        $isEnabled = Setting::where('key', 'firebase_enabled')->value('value');

        if ($isEnabled === '0' || empty($serverKey)) {
            return [
                'success' => false,
                'sent'    => 0,
                'message' => 'Firebase Push Notification is disabled or Server Key is missing.',
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

        $sentCount = 0;
        // Chunk tokens into batches of 500 (FCM limit per multicast request)
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
                    'title'     => $title,
                    'body'      => $body,
                    'image_url' => $imageUrl ?: '',
                    'action_url'=> $actionUrl ?: '',
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
                    Log::error('FCM HTTP Response Error: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('FCM Push Notification Exception: ' . $e->getMessage());
            }
        }

        return [
            'success' => $sentCount > 0,
            'sent'    => $sentCount,
            'message' => "Successfully delivered {$sentCount} push notification(s).",
        ];
    }
}
