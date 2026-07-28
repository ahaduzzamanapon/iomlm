<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timeline extends Model
{
    protected $guarded = [];

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function module()
    {
        return $this->belongsTo(SubjectModule::class, 'module_id');
    }

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class, 'timeline_id');
    }
}
