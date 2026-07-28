<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    protected $guarded = [];

    public function timeline()
    {
        return $this->belongsTo(Timeline::class, 'timeline_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function mergedGroups()
    {
        return $this->hasMany(MergedClassGroup::class, 'class_session_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_session_id');
    }
}
