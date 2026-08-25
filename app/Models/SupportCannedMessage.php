<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportCannedMessage extends Model
{
    protected $guarded = [];

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
