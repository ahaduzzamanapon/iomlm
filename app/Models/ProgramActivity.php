<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramActivity extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function getProgramNameAttribute(): string
    {
        return $this->course ? $this->course->name : 'Any Program';
    }

    public function getBatchNameAttribute(): string
    {
        return $this->batch ? $this->batch->name : 'Any';
    }
}
