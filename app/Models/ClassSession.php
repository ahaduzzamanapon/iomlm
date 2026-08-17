<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSession extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'session_date'    => 'date',
        'teacher_present' => 'boolean',
        'class_conducted' => 'boolean',
        'started_at'      => 'datetime',
        'ended_at'        => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────

    /** Optional curriculum plan link */
    public function timeline()
    {
        return $this->belongsTo(Timeline::class, 'timeline_id');
    }

    /** Recurring routine entry this session belongs to */
    public function routineEntry()
    {
        return $this->belongsTo(RoutineEntry::class, 'routine_entry_id');
    }

    /** Direct subject ref (no need to join timeline) */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /** Direct batch ref */
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /** Which module the teacher covered in this session (optional) */
    public function moduleCovered()
    {
        return $this->belongsTo(SubjectModule::class, 'module_covered_id');
    }

    public function mergedGroups()
    {
        return $this->hasMany(MergedClassGroup::class, 'class_session_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_session_id');
    }

    // ── Accessors ──────────────────────────────────────────────

    /** Resolved subject name */
    public function getSubjectNameAttribute(): string
    {
        return $this->subject?->name ?? '—';
    }

    /** Resolved batch name */
    public function getBatchNameAttribute(): string
    {
        return $this->batch?->name ?? '—';
    }

    /** Display date */
    public function getDisplayDateAttribute(): string
    {
        return $this->session_date
            ? $this->session_date->format('d M Y (D)')
            : 'TBA';
    }
}
