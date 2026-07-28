<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchSemesterPosition extends Model
{
    protected $guarded = [];

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function currentSemester()
    {
        return $this->belongsTo(Semester::class, 'current_semester_id');
    }
}
