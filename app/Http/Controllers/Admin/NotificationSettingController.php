<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\DynamicMailService;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    public function index()
    {
        $settings = Setting::whereIn('key', [
            // Firebase
            'firebase_project_id', 'firebase_api_key', 'firebase_auth_domain',
            'firebase_storage_bucket', 'firebase_messaging_sender_id', 'firebase_app_id',
            'firebase_server_key', 'firebase_vapid_key', 'firebase_service_account_json', 'firebase_enabled',
            // SMTP
            'smtp_driver', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
            'smtp_encryption', 'smtp_from_address', 'smtp_from_name', 'smtp_enabled'
        ])->pluck('value', 'key');

        return view('admin.settings.notifications', compact('settings'));
    }

    public function updateFirebase(Request $request)
    {
        $keys = [
            'firebase_project_id', 'firebase_api_key', 'firebase_auth_domain',
            'firebase_storage_bucket', 'firebase_messaging_sender_id', 'firebase_app_id',
            'firebase_server_key', 'firebase_vapid_key', 'firebase_service_account_json'
        ];

        foreach ($keys as $k) {
            Setting::updateOrCreate(['key' => $k], ['value' => $request->input($k, '')]);
        }

        Setting::updateOrCreate(
            ['key' => 'firebase_enabled'],
            ['value' => $request->boolean('firebase_enabled') ? '1' : '0']
        );

        return back()->with('success', 'Firebase Push Notification settings updated successfully.');
    }

    public function updateSmtp(Request $request)
    {
        $keys = [
            'smtp_driver', 'smtp_host', 'smtp_port', 'smtp_username',
            'smtp_encryption', 'smtp_from_address', 'smtp_from_name'
        ];

        foreach ($keys as $k) {
            Setting::updateOrCreate(['key' => $k], ['value' => $request->input($k, '')]);
        }

        // Only update password if user provided a new one
        if ($request->filled('smtp_password')) {
            Setting::updateOrCreate(['key' => 'smtp_password'], ['value' => $request->input('smtp_password')]);
        }

        Setting::updateOrCreate(
            ['key' => 'smtp_enabled'],
            ['value' => $request->boolean('smtp_enabled') ? '1' : '0']
        );

        return back()->with('success', 'SMTP Mail Configuration updated successfully.');
    }

    public function sendTestMail(Request $request, DynamicMailService $mailService)
    {
        $request->validate(['test_email' => 'required|email']);
        $result = $mailService->testConnection($request->input('test_email'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
