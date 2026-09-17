<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Exam extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime'   => 'datetime',
        'exam_date'      => 'date',
        'end_date'       => 'date',
    ];

    public function getEffectiveStartDatetime(): Carbon
    {
        if ($this->start_datetime) {
            return Carbon::parse($this->start_datetime);
        }
        if ($this->exam_date) {
            $date = Carbon::parse($this->exam_date)->format('Y-m-d');
            $time = $this->start_time ?: '00:00:00';
            return Carbon::parse("{$date} {$time}");
        }
        return Carbon::now()->subYears(10);
    }

    public function getEffectiveEndDatetime(): Carbon
    {
        if ($this->end_datetime) {
            return Carbon::parse($this->end_datetime);
        }
        $date = $this->end_date ? Carbon::parse($this->end_date)->format('Y-m-d') : ($this->exam_date ? Carbon::parse($this->exam_date)->format('Y-m-d') : null);
        if ($date) {
            $time = $this->end_time ?: '23:59:59';
            return Carbon::parse("{$date} {$time}");
        }
        return Carbon::now()->addYears(10);
    }

    public function getTimingStatusAttribute(): string
    {
        $now = Carbon::now();
        $start = $this->getEffectiveStartDatetime();
        $end = $this->getEffectiveEndDatetime();

        if ($now->lt($start)) {
            return 'UPCOMING';
        }
        if ($now->gt($end)) {
            return 'EXPIRED';
        }
        return 'ACTIVE';
    }

    public function isUpcoming(): bool
    {
        return $this->timing_status === 'UPCOMING';
    }

    public function isActive(): bool
    {
        return $this->timing_status === 'ACTIVE';
    }

    public function isExpired(): bool
    {
        return $this->timing_status === 'EXPIRED';
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function attendees()
    {
        return $this->hasMany(ExamAttendee::class, 'exam_id');
    }

    public function results()
    {
        return $this->hasMany(Result::class, 'exam_id');
    }

    public function examQuestions()
    {
        return $this->hasMany(ExamQuestion::class, 'exam_id')->orderBy('sort_order');
    }

    public function submissions()
    {
        return $this->hasMany(ExamSubmission::class, 'exam_id');
    }

    public function appeals()
    {
        return $this->hasMany(ExamAppeal::class, 'exam_id');
    }
}
