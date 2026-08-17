<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timeline extends Model
{
    use SoftDeletes;

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

    /** The recurring weekly routine pattern this timeline slot belongs to */
    public function routineEntry()
    {
        return $this->belongsTo(RoutineEntry::class, 'routine_entry_id');
    }

    /** All class sessions for this timeline slot (a module can have multiple) */
    public function classSessions()
    {
        return $this->hasMany(ClassSession::class, 'timeline_id');
    }

    /** Convenience: the primary/first class session */
    public function classSession()
    {
        return $this->hasOne(ClassSession::class, 'timeline_id')->latestOfMany();
    }
}

