<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSubmission extends Model
{
    protected $guarded = [];

    protected $casts = [
        'assigned_question_ids' => 'array',
        'started_at'            => 'datetime',
        'submitted_at'          => 'datetime',
        'mcq_score'             => 'float',
        'written_score'         => 'float',
        'tamrin_score'          => 'float',
        'viva_score'            => 'float',
        'total_score'           => 'float',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function answers()
    {
        return $this->hasMany(ExamAnswer::class, 'submission_id');
    }
}
