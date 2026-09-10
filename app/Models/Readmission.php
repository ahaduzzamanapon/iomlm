<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Readmission extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'failed_subject_ids'    => 'array',
        'failed_subjects_count' => 'integer',
        'readmission_fee'       => 'float',
        'decided_at'            => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function fromBatch()
    {
        return $this->belongsTo(Batch::class, 'from_batch_id');
    }

    public function toBatch()
    {
        return $this->belongsTo(Batch::class, 'to_batch_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function getFailedSubjectsAttribute()
    {
        if (empty($this->failed_subject_ids)) {
            return collect();
        }
        return Subject::whereIn('id', $this->failed_subject_ids)->get();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    public function scopeContinuedWithRetake($query)
    {
        return $query->where('status', 'CONTINUED_WITH_RETAKE');
    }
}
