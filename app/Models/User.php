<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'admin_permissions',
        'designation',
        'can_provide_support',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'admin_permissions'   => 'array',
            'can_provide_support' => 'boolean',
            'is_active'           => 'boolean',
        ];
    }

    // ── Relationships ─────────────────────────────────────────
    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function supportDepartments()
    {
        return $this->belongsToMany(SupportDepartment::class, 'support_department_user', 'user_id', 'support_department_id');
    }

    public function fcmTokens()
    {
        return $this->hasMany(UserFcmToken::class, 'user_id');
    }

    // ── Helpers ──────────────────────────────────────────────
    public function isAdmin(): bool        { return in_array($this->role, ['admin', 'super_admin']); }
    public function isSuperAdmin(): bool   { return $this->role === 'super_admin'; }
    public function isTeacher(): bool      { return $this->role === 'teacher'; }
    public function isStudent(): bool      { return $this->role === 'student'; }
    public function isSupportAgent(): bool
    {
        return in_array($this->role, ['support_agent', 'support']) || $this->can_provide_support || $this->supportDepartments()->exists();
    }

    /**
     * Check if the admin/staff user has permission to access a specific module
     */
    public function canAccess(string $module): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        // For legacy/unconfigured admins, give full access
        if ($this->role === 'admin' && ($this->admin_permissions === null || empty($this->admin_permissions))) {
            return true;
        }

        if (is_array($this->admin_permissions)) {
            return in_array($module, $this->admin_permissions, true);
        }

        return false;
    }

    /**
     * List of all functional modules available in Admin Panel
     */
    public static function adminModules(): array
    {
        return [
            'academic' => [
                'name' => 'একাডেমিক সেটআপ',
                'desc' => 'শিক্ষাবর্ষ, বিষয়, কোর্স, সেমিস্টার ও ছুটির ক্যালেন্ডার',
                'icon' => 'fa-graduation-cap',
            ],
            'admissions' => [
                'name' => 'ভর্তি ও কোর্স পরিবর্তন',
                'desc' => 'ভর্তি আবেদন, কোর্স পরিবর্তন ও পুওর ফান্ড / ওয়েভার',
                'icon' => 'fa-user-plus',
            ],
            'students' => [
                'name' => 'শিক্ষার্থী ব্যবস্থাপনা',
                'desc' => 'শিক্ষার্থী তালিকা, প্রোফাইল, আইডি কার্ড ও সার্টিফিকেট',
                'icon' => 'fa-user-graduate',
            ],
            'teachers' => [
                'name' => 'শিক্ষক ব্যবস্থাপনা',
                'desc' => 'শিক্ষক তালিকা, বিষয় বরাদ্দ ও শিক্ষক প্রোফাইল',
                'icon' => 'fa-chalkboard-user',
            ],
            'classes_batches' => [
                'name' => 'ক্লাস, ব্যাচ ও রুটিন',
                'desc' => 'ব্যাচ ম্যানেজমেন্ট, ক্লাস সেশন ও সাপ্তাহিক রুটিন',
                'icon' => 'fa-calendar-days',
            ],
            'exams' => [
                'name' => 'পরীক্ষা ও মূল্যায়ন',
                'desc' => 'প্রশ্ন ব্যাংক, পরীক্ষা শিডিউল, পেপার বিল্ডার, আপিল ও রিটেক',
                'icon' => 'fa-file-signature',
            ],
            'communication' => [
                'name' => 'যোগাযোগ ও নোটিশ',
                'desc' => 'সার্ভে ও ফর্ম, নোটিশ বোর্ড ও ব্রডকাস্ট নোটিফিকেশন',
                'icon' => 'fa-bullhorn',
            ],
            'accounts' => [
                'name' => 'একাউন্টস ও ফাইন্যান্সিয়াল',
                'desc' => 'ফি আদায়, ইনভয়েস, বকেয়া, ফি প্যাকেজ ও আয় রিপোর্ট',
                'icon' => 'fa-wallet',
            ],
            'support' => [
                'name' => 'হেল্পডেস্ক ও সাপোর্ট সেটআপ',
                'desc' => 'সাপোর্ট টিকিট, সাপোর্ট ডিপার্টমেন্ট ও এজেন্ট ব্যবস্থাপনা',
                'icon' => 'fa-headset',
            ],
            'settings' => [
                'name' => 'সিস্টেম সেটিংস',
                'desc' => 'অ্যাপ সেটিংস, পেমেন্ট গেটওয়ে, নোটিফিকেশন সেটিংস ও গুগল লগইন',
                'icon' => 'fa-sliders',
            ],
            'user_management' => [
                'name' => 'ইউজার ও রোল ম্যানেজমেন্ট',
                'desc' => 'এডমিন ও স্টাফ ইউজার তৈরি, রোল ও মেনু পারমিশন নিয়ন্ত্রণ',
                'icon' => 'fa-users-gear',
            ],
        ];
    }
}
