<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttendee extends Model
{
    protected $guarded = [];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }
}
