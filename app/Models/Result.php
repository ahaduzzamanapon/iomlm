<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $guarded = [];

    protected $casts = [
        'marks'         => 'float',
        'mcq_marks'     => 'float',
        'written_marks' => 'float',
        'tamrin_marks'  => 'float',
        'viva_marks'    => 'float',
        'attempt_no'    => 'integer',
        'is_published'  => 'boolean',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
