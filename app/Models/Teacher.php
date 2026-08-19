<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignments()
    {
        return $this->hasMany(SubjectTeacherAssignment::class, 'teacher_id');
    }

    public function classes()
    {
        return $this->hasMany(ClassSession::class, 'teacher_id');
    }
}
