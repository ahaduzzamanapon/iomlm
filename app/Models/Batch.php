<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_admission_open' => 'boolean',
    ];

    public function scopeAdmissionOpen($query)
    {
        return $query->where('status', 'ACTIVE')->where('is_admission_open', true);
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function semesterPosition()
    {
        return $this->hasOne(BatchSemesterPosition::class, 'batch_id');
    }

    public function timelines()
    {
        return $this->hasMany(Timeline::class, 'batch_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'batch_id');
    }

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class, 'batch_id');
    }

    public function routineEntries()
    {
        return $this->hasMany(RoutineEntry::class, 'batch_id');
    }
}
