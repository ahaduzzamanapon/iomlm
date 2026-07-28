<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $guarded = [];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function courseSubjectMaps()
    {
        return $this->hasMany(CourseSubjectMap::class, 'semester_id');
    }
}
