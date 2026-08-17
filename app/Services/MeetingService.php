<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MeetingService
{
    private string $provider;
    private array  $config;

    public function __construct()
    {
        $settings = Setting::whereIn('key', [
            'meeting_provider',
            'zoom_account_id',
            'zoom_client_id',
            'zoom_client_secret',
            'zoom_meeting_duration',
        ])->pluck('value', 'key')->toArray();

        $this->provider = $settings['meeting_provider'] ?? 'manual';
        $this->config   = $settings;
    }

    /** @return string The meeting provider name ('zoom', 'google_meet', 'manual') */
    public function provider(): string
    {
        return $this->provider;
    }

    /**
     * Generate a real meeting link for a class session.
     * Returns array ['join_url' => ..., 'meeting_id' => ...] or null
     * Throws \RuntimeException on Zoom API failure.
     */
    public function generate(string $topic, string $startTime, string $timezone = 'Asia/Dhaka'): ?array
    {
        return match ($this->provider) {
            'zoom'   => $this->createZoomMeeting($topic, $startTime, $timezone),
            default  => null,  // manual / google_meet → teacher pastes link
        };
    }

    /**
     * Get Zoom Access Token using Server-to-Server OAuth
     */
    private function getAccessToken(): string
    {
        $accountId    = $this->config['zoom_account_id']    ?? '';
        $clientId     = $this->config['zoom_client_id']     ?? '';
        $clientSecret = $this->config['zoom_client_secret'] ?? '';

        if (!$accountId || !$clientId || !$clientSecret) {
            throw new \RuntimeException('Zoom API credentials are not fully configured. Go to Settings → Meeting Platform.');
        }

        $tokenRes = Http::withBasicAuth(trim($clientId), trim($clientSecret))
            ->asForm()
            ->post('https://zoom.us/oauth/token', [
                'grant_type'    => 'account_credentials',
                'account_id'    => trim($accountId),
                'client_id'     => trim($clientId),
                'client_secret' => trim($clientSecret),
            ]);

        if (!$tokenRes->successful() || !isset($tokenRes['access_token'])) {
            Log::error('Zoom token error', $tokenRes->json() ?? []);
            throw new \RuntimeException('Failed to authenticate with Zoom API. Check your credentials in Settings.');
        }

        return $tokenRes['access_token'];
    }

    // ── Zoom Meeting Creation ──────────────────────────────────────────────────

    private function createZoomMeeting(string $topic, string $startTime, string $timezone): array
    {
        $duration = (int) ($this->config['zoom_meeting_duration'] ?? 60);
        $accessToken = $this->getAccessToken();

        $meetRes = Http::withToken($accessToken)
            ->post('https://api.zoom.us/v2/users/me/meetings', [
                'topic'      => $topic,
                'type'       => 2,                // Scheduled meeting
                'start_time' => $startTime,       // ISO 8601
                'duration'   => $duration,
                'timezone'   => $timezone,
                'settings'   => [
                    'host_video'        => true,
                    'participant_video'  => true,
                    'join_before_host'  => true,
                    'waiting_room'      => false,
                    'auto_recording'    => 'none',
                ],
            ]);

        if (!$meetRes->successful() || !isset($meetRes['join_url'])) {
            Log::error('Zoom meeting creation error', $meetRes->json() ?? []);
            throw new \RuntimeException('Zoom API meeting creation failed: ' . ($meetRes['message'] ?? 'Unknown error'));
        }

        return [
            'join_url'   => $meetRes['join_url'],
            'meeting_id' => (string) $meetRes['id'],
        ];
    }

    /**
     * Get participants list from a finished Zoom meeting for Auto-Attendance.
     * Returns array of ['name' => ..., 'user_email' => ...]
     */
    public function getZoomParticipants(string $meetingId): array
    {
        $accessToken = $this->getAccessToken();

        // 1. Try past meeting participants report API
        $response = Http::withToken($accessToken)
            ->get("https://api.zoom.us/v2/report/meetings/{$meetingId}/participants", [
                'page_size' => 300,
            ]);

        if (!$response->successful()) {
            // Fallback to past_meetings API
            $response = Http::withToken($accessToken)
                ->get("https://api.zoom.us/v2/past_meetings/{$meetingId}/participants", [
                    'page_size' => 300,
                ]);
        }

        if (!$response->successful()) {
            Log::error('Zoom participants report error', $response->json() ?? []);
            throw new \RuntimeException('Failed to fetch Zoom participants report: ' . ($response->json()['message'] ?? 'Meeting may not have ended yet or report is not available.'));
        }

        return $response->json()['participants'] ?? [];
    }
}
