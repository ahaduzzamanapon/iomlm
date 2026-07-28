<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmissionForm extends Model
{
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function interestedCourse()
    {
        return $this->belongsTo(Course::class, 'interested_course_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
