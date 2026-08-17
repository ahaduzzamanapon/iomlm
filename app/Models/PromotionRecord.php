<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionRecord extends Model
{
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function fromSemester()
    {
        return $this->belongsTo(Semester::class, 'from_semester_id');
    }

    public function toSemester()
    {
        return $this->belongsTo(Semester::class, 'to_semester_id');
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
