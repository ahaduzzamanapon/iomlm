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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
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
    public function isAdmin(): bool        { return $this->role === 'admin'; }
    public function isTeacher(): bool      { return $this->role === 'teacher'; }
    public function isStudent(): bool      { return $this->role === 'student'; }
    public function isSupportAgent(): bool { return in_array($this->role, ['support_agent', 'support', 'admin']); }
}
