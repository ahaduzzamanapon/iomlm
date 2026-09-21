<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_datetime' => 'datetime',
        'due_datetime'   => 'datetime',
        'total_marks'    => 'float',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function isOpen(): bool
    {
        $now = now();
        $start = $this->start_datetime ?? $this->created_at;
        $due = $this->due_datetime;

        return ($start <= $now) && (!$due || $due >= $now);
    }

    public function isExpired(): bool
    {
        return $this->due_datetime && $this->due_datetime < now();
    }

    public function submissionForStudent(?int $studentId): ?AssignmentSubmission
    {
        if (!$studentId) return null;
        return $this->submissions->firstWhere('student_id', $studentId)
            ?? $this->submissions()->where('student_id', $studentId)->first();
    }
}
