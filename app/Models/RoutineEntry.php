<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutineEntry extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_override' => 'boolean',
    ];

    // Colors for auto-assigned batch coloring
    public const BATCH_COLORS = [
        '#3b82f6', '#10b981', '#8b5cf6', '#f59e0b',
        '#ef4444', '#06b6d4', '#ec4899', '#84cc16',
    ];

    public function slot()
    {
        return $this->belongsTo(RoutineSlot::class, 'slot_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class, 'class_session_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function getDayLabelAttribute(): string
    {
        return match($this->day_of_week) {
            'SAT' => 'শনিবার', 'SUN' => 'রবিবার', 'MON' => 'সোমবার',
            'TUE' => 'মঙ্গলবার', 'WED' => 'বুধবার', 'THU' => 'বৃহস্পতিবার',
            'FRI' => 'শুক্রবার', default => $this->day_of_week,
        };
    }
}
