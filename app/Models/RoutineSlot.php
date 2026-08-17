<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutineSlot extends Model
{
    protected $guarded = [];

    public function entries()
    {
        return $this->hasMany(RoutineEntry::class, 'slot_id');
    }

    public function getTimeRangeAttribute(): string
    {
        return \Carbon\Carbon::parse($this->start_time)->format('h:i A')
            . ' - '
            . \Carbon\Carbon::parse($this->end_time)->format('h:i A');
    }
}
