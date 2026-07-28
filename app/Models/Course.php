<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $guarded = [];

    public function semesters()
    {
        return $this->hasMany(Semester::class, 'course_id')->orderBy('sequence_no');
    }

    public function courseSubjectMaps()
    {
        return $this->hasMany(CourseSubjectMap::class, 'course_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'course_subject_maps', 'course_id', 'subject_id')
            ->withPivot('semester_id', 'sort_order');
    }

    public function batches()
    {
        return $this->hasMany(Batch::class, 'course_id');
    }
}
