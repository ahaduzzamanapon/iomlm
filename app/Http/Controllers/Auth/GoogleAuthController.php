<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        $enabled = Setting::get('google_auth_enabled', '0');
        $clientId = Setting::get('google_client_id');
        $redirectUri = Setting::get('google_redirect_uri', url('/auth/google/callback'));

        if ($enabled !== '1' || !$clientId) {
            return redirect()->route('login')->with('error', 'Google Sign-In is currently disabled or unconfigured.');
        }

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('login')->with('error', 'Google authentication was cancelled or failed.');
        }

        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('login')->with('error', 'No authorization code received from Google.');
        }

        $clientId = Setting::get('google_client_id');
        $clientSecret = Setting::get('google_client_secret');
        $redirectUri = Setting::get('google_redirect_uri', url('/auth/google/callback'));

        try {
            // Exchange code for token
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);

            if ($tokenResponse->failed()) {
                Log::error('Google OAuth Token Exchange Failed', ['body' => $tokenResponse->body()]);
                return redirect()->route('login')->with('error', 'Failed to authenticate with Google API.');
            }

            $accessToken = $tokenResponse->json('access_token');
            if (!$accessToken) {
                return redirect()->route('login')->with('error', 'Invalid token returned from Google.');
            }

            // Fetch User Details from Google UserInfo endpoint
            $userResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');
            if ($userResponse->failed()) {
                return redirect()->route('login')->with('error', 'Failed to retrieve user profile from Google.');
            }

            $googleUser = $userResponse->json();
            $email = $googleUser['email'] ?? null;
            $googleId = $googleUser['sub'] ?? null;

            if (!$email) {
                return redirect()->route('login')->with('error', 'No email associated with this Google account.');
            }

            // Strictly match existing user in the database (No new auto-registration)
            $user = User::where('email', $email)->orWhere('google_id', $googleId)->first();

            if (!$user) {
                return redirect()->route('login')->with('error', 'No account found matching Google email (' . $email . '). Please contact the administrator.');
            }

            // Update google_id if missing or changed
            if ($googleId && $user->google_id !== $googleId) {
                $user->update(['google_id' => $googleId]);
            }

            // Log in the user
            Auth::login($user);
            $request->session()->regenerate();

            // Redirect according to user role
            return match ($user->role ?? 'admin') {
                'teacher' => redirect()->route('teacher.dashboard'),
                'student' => redirect()->route('student.dashboard'),
                default   => redirect()->route('admin.dashboard'),
            };

        } catch (\Exception $e) {
            Log::error('Google Auth Exception: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Google authentication error: ' . $e->getMessage());
        }
    }
}
