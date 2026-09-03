<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportDepartment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function agents()
    {
        return $this->belongsToMany(User::class, 'support_department_user', 'support_department_id', 'user_id');
    }

    public function tickets()
    {
        return $this->hasMany(SupportTicket::class, 'department_id');
    }

    public function pendingTicketsCount()
    {
        return $this->tickets()->where('status', 'PENDING')->count();
    }
}
