<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionRecord extends Model
{
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function fromBatch()
    {
        return $this->belongsTo(Batch::class, 'from_batch_id');
    }

    public function toBatch()
    {
        return $this->belongsTo(Batch::class, 'to_batch_id');
    }

    public function promoter()
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }
}
