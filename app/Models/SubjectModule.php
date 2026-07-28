<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectModule extends Model
{
    protected $guarded = [];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function learningResources()
    {
        return $this->hasMany(LearningResource::class, 'module_id');
    }

    public function timelines()
    {
        return $this->hasMany(Timeline::class, 'module_id');
    }
}
