<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MergedClassGroup extends Model
{
    protected $guarded = [];

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class, 'class_session_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }
}
