<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseTransfer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'transfer_fee' => 'float',
        'approved_at'  => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function fromCourse()
    {
        return $this->belongsTo(Course::class, 'from_course_id');
    }

    public function fromBatch()
    {
        return $this->belongsTo(Batch::class, 'from_batch_id');
    }

    public function fromEnrollment()
    {
        return $this->belongsTo(Enrollment::class, 'from_enrollment_id');
    }

    public function toCourse()
    {
        return $this->belongsTo(Course::class, 'to_course_id');
    }

    public function toBatch()
    {
        return $this->belongsTo(Batch::class, 'to_batch_id');
    }

    public function toSemester()
    {
        return $this->belongsTo(Semester::class, 'to_semester_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function newEnrollment()
    {
        return $this->belongsTo(Enrollment::class, 'new_enrollment_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeApprovedPendingPayment($query)
    {
        return $query->where('status', 'APPROVED_PENDING_PAYMENT');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'COMPLETED');
    }
}
