<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    protected $table = 'login_histories';

    protected $guarded = [];

    protected $casts = [
        'is_impersonated' => 'boolean',
        'login_at'        => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function impersonator()
    {
        return $this->belongsTo(User::class, 'impersonated_by');
    }

    /**
     * Record a login occurrence
     */
    public static function recordLogin($user, ?Student $student = null, bool $isImpersonated = false, ?int $impersonatedBy = null): self
    {
        $userAgent = request()->userAgent() ?? 'Unknown Agent';
        $ip = request()->ip() ?? '127.0.0.1';

        // Parse Platform
        $platform = 'Other';
        if (preg_match('/windows|win32/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        }

        // Parse Browser
        $browser = 'Other';
        if (preg_match('/edg/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome|crios/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox|fxios/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/opr\//i', $userAgent)) {
            $browser = 'Opera';
        }

        // Parse Device
        $device = 'Desktop';
        if (preg_match('/tablet|ipad/i', $userAgent)) {
            $device = 'Tablet';
        } elseif (preg_match('/mobile|android|iphone|ipod/i', $userAgent)) {
            $device = 'Mobile';
        }

        $studentId = $student ? $student->id : ($user?->student?->id ?? null);

        return self::create([
            'user_id'         => $user?->id,
            'student_id'      => $studentId,
            'ip_address'      => $ip,
            'user_agent'      => substr($userAgent, 0, 500),
            'device'          => $device,
            'browser'         => $browser,
            'platform'        => $platform,
            'is_impersonated' => $isImpersonated,
            'impersonated_by' => $impersonatedBy,
            'login_at'        => now(),
        ]);
    }
}
