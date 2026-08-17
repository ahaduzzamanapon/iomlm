<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectRetake extends Model
{
    protected $guarded = [];

    protected $casts = [
        'retake_fee' => 'float',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }
}
