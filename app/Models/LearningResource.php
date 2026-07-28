<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningResource extends Model
{
    protected $guarded = [];

    public function module()
    {
        return $this->belongsTo(SubjectModule::class, 'module_id');
    }
}
