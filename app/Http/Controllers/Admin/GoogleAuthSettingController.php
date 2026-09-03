<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class GoogleAuthSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'google_auth_enabled' => Setting::get('google_auth_enabled', '0'),
            'google_client_id' => Setting::get('google_client_id', ''),
            'google_client_secret' => Setting::get('google_client_secret', ''),
            'google_redirect_uri' => Setting::get('google_redirect_uri', url('/auth/google/callback')),
        ];

        return view('admin.settings.google_auth', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'google_auth_enabled' => 'nullable|in:0,1',
            'google_client_id' => 'nullable|string',
            'google_client_secret' => 'nullable|string',
            'google_redirect_uri' => 'nullable|string|url',
        ]);

        Setting::set('google_auth_enabled', $request->has('google_auth_enabled') ? '1' : '0');
        Setting::set('google_client_id', $request->input('google_client_id', ''));
        Setting::set('google_client_secret', $request->input('google_client_secret', ''));
        Setting::set('google_redirect_uri', $request->input('google_redirect_uri', url('/auth/google/callback')));

        return redirect()->route('admin.settings.google-auth.index')->with('success', 'Google Auth settings saved successfully.');
    }
}
