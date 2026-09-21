<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectModule extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_hidden'                => 'boolean',
        'is_active'                => 'boolean',
        'is_locked_until_previous' => 'boolean',
    ];

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

    public function cloneModule(): SubjectModule
    {
        $maxSeq = SubjectModule::where('subject_id', $this->subject_id)->max('sequence_no') ?? $this->sequence_no;

        $newMod = $this->replicate();
        $newMod->title = $this->title . ' (Copy)';
        $newMod->sequence_no = $maxSeq + 1;
        $newMod->save();

        foreach ($this->learningResources as $lr) {
            $newLr = $lr->replicate();
            $newLr->module_id = $newMod->id;
            $newLr->save();
        }

        return $newMod;
    }
}
