<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $guarded = [];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function attendees()
    {
        return $this->hasMany(ExamAttendee::class, 'exam_id');
    }

    public function results()
    {
        return $this->hasMany(Result::class, 'exam_id');
    }
}
