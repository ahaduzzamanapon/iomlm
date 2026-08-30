<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DynamicMailService
{
    /**
     * Dynamically configure SMTP mailer from database settings.
     */
    public function configureMailer(): bool
    {
        $settings = Setting::whereIn('key', [
            'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
            'smtp_encryption', 'smtp_from_address', 'smtp_from_name', 'smtp_enabled'
        ])->pluck('value', 'key');

        $host        = $settings['smtp_host'] ?? config('mail.mailers.smtp.host');
        $port        = $settings['smtp_port'] ?? config('mail.mailers.smtp.port');
        $username    = $settings['smtp_username'] ?? config('mail.mailers.smtp.username');
        $password    = $settings['smtp_password'] ?? config('mail.mailers.smtp.password');
        $encryption  = $settings['smtp_encryption'] ?? config('mail.mailers.smtp.encryption');
        $fromAddress = $settings['smtp_from_address'] ?? config('mail.from.address');
        $fromName    = $settings['smtp_from_name'] ?? config('mail.from.name');

        if (empty($host) || empty($port)) {
            return false;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', [
            'transport'  => 'smtp',
            'host'       => $host,
            'port'       => (int) $port,
            'encryption' => strtolower($encryption) === 'none' ? null : strtolower($encryption),
            'username'   => $username,
            'password'   => $password,
            'timeout'    => 15,
            'stream'     => [
                'ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                ],
            ],
        ]);
        Config::set('mail.from', [
            'address' => $fromAddress ?: 'noreply@iom.edu.bd',
            'name'    => $fromName ?: 'IOM Education',
        ]);

        return true;
    }

    /**
     * Send a styled HTML broadcast notification email.
     */
    public function sendHtmlNotification(string $toEmail, string $subject, string $messageBody, ?string $imageUrl = null, ?string $actionUrl = null): bool
    {
        if (!$this->configureMailer()) {
            return false;
        }

        try {
            $html = $this->buildEmailHtml($subject, $messageBody, $imageUrl, $actionUrl);

            Mail::html($html, function ($message) use ($toEmail, $subject) {
                $message->to($toEmail)->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('SMTP Mail Notification Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test SMTP Connection by sending a test mail.
     */
    public function testConnection(string $toEmail): array
    {
        if (!$this->configureMailer()) {
            return ['success' => false, 'message' => 'SMTP Host or Port is not configured.'];
        }

        try {
            $subject = 'IOM ERP — SMTP Connection Test';
            $body = 'Assalamu Alaikum! This is a test email sent from IOM ERP to verify your SMTP server configuration.';
            $html = $this->buildEmailHtml($subject, $body, null, url('/admin/settings/notifications'));

            Mail::html($html, function ($message) use ($toEmail, $subject) {
                $message->to($toEmail)->subject($subject);
            });

            return ['success' => true, 'message' => "Test email sent successfully to {$toEmail}!"];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'SMTP Connection Error: ' . $e->getMessage()];
        }
    }

    /**
     * Build branded responsive HTML email template.
     */
    private function buildEmailHtml(string $title, string $body, ?string $imageUrl = null, ?string $actionUrl = null): string
    {
        $instituteName = Setting::where('key', 'institute_name')->value('value') ?? 'IOM Technology Institute';
        $formattedBody = nl2br(e($body));
        $imgHtml = $imageUrl ? '<div style="margin:20px 0;text-align:center"><img src="'.e($imageUrl).'" style="max-width:100%;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.1)"></div>' : '';
        $btnHtml = $actionUrl ? '<div style="margin:25px 0 10px;text-align:center"><a href="'.e($actionUrl).'" style="display:inline-block;padding:12px 28px;background:linear-gradient(135deg,#2563eb,#3b82f6);color:#ffffff;text-decoration:none;font-weight:700;border-radius:8px;box-shadow:0 4px 12px rgba(37,99,235,0.3)">Open Link →</a></div>' : '';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;background-color:#f1f5f9;margin:0;padding:30px 15px;">
    <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.06);border:1px solid #e2e8f0;">
        <div style="background:linear-gradient(135deg,#1e293b,#0f172a);padding:24px 30px;text-align:center;color:#ffffff;">
            <h2 style="margin:0;font-size:20px;font-weight:800;letter-spacing:.02em;">{$instituteName}</h2>
            <p style="margin:4px 0 0;font-size:12px;color:#94a3b8;">Official Academic Notification</p>
        </div>
        <div style="padding:30px;color:#334155;line-height:1.6;">
            <h3 style="margin-top:0;color:#0f172a;font-size:18px;font-weight:700;">{$title}</h3>
            {$imgHtml}
            <div style="font-size:14px;color:#475569;">{$formattedBody}</div>
            {$btnHtml}
        </div>
        <div style="background:#f8fafc;padding:16px 30px;border-top:1px solid #f1f5f9;text-align:center;font-size:12px;color:#94a3b8;">
            © {$instituteName} · Sent via IOM Notification System
        </div>
    </div>
</body>
</html>
HTML;
    }
}
