<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $guarded = [];

    public function modules()
    {
        return $this->hasMany(SubjectModule::class, 'subject_id')->orderBy('sequence_no');
    }

    public function courseSubjectMaps()
    {
        return $this->hasMany(CourseSubjectMap::class, 'subject_id');
    }

    public function teacherAssignments()
    {
        return $this->hasMany(SubjectTeacherAssignment::class, 'subject_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'subject_id');
    }
}
