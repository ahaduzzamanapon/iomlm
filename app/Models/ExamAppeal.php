<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAppeal extends Model
{
    protected $guarded = [];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function submission()
    {
        return $this->belongsTo(ExamSubmission::class, 'submission_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    public function isRejected(): bool
    {
        return $this->status === 'REJECTED';
    }
}
