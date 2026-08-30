<?php

namespace App\Http\Controllers;

use App\Models\UserFcmToken;
use Illuminate\Http\Request;

class UserFcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'fcm_token'   => 'required|string',
            'device_type' => 'nullable|string|max:50',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        UserFcmToken::updateOrCreate(
            [
                'user_id'   => $user->id,
                'fcm_token' => $request->input('fcm_token'),
            ],
            [
                'device_type'  => $request->input('device_type', 'web'),
                'user_agent'   => $request->userAgent(),
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'FCM Token saved successfully.',
        ]);
    }
}
