<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    protected $guarded = [];

    protected $casts = [
        'accepted_at' => 'datetime',
        'closed_at'   => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->uuid)) {
                $ticket->uuid = (string) Str::uuid();
            }
            if (empty($ticket->ticket_no)) {
                $ticket->ticket_no = 'SUP-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            }
        });
    }

    public function department()
    {
        return $this->belongsTo(SupportDepartment::class, 'department_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function messages()
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    public function latestMessage()
    {
        return $this->hasOne(SupportMessage::class, 'ticket_id')->latestOfMany();
    }

    /**
     * Resolve associated Student record via:
     * 1. ticket's student_id (clean or dashed)
     * 2. user_id -> user->student
     * 3. email -> student email
     * 4. phone -> student phone
     */
    public function getResolvedStudentAttribute()
    {
        if (!empty($this->student_id)) {
            $clean = str_replace('-', '', $this->student_id);
            $student = Student::where('student_code', $clean)
                ->orWhere('student_code', $this->student_id)
                ->first();
            if ($student) {
                return $student;
            }
        }

        if ($this->user_id && $this->student) {
            $userStudent = Student::where('user_id', $this->user_id)->first();
            if ($userStudent) {
                return $userStudent;
            }
        }

        if (!empty($this->email)) {
            $student = Student::where('email', $this->email)->first();
            if ($student) {
                return $student;
            }
        }

        if (!empty($this->phone)) {
            $student = Student::where('phone', $this->phone)->first();
            if ($student) {
                return $student;
            }
        }

        return null;
    }
}
