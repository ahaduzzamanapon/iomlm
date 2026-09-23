<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Exam extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_datetime'       => 'datetime',
        'end_datetime'         => 'datetime',
        'exam_date'            => 'date',
        'end_date'             => 'date',
        'is_result_published'  => 'boolean',
        'result_published_at'  => 'datetime',
        'has_mcq'              => 'boolean',
        'has_written'          => 'boolean',
        'has_tamrin'           => 'boolean',
        'has_viva'             => 'boolean',
        'mcq_marks'            => 'float',
        'written_marks'        => 'float',
        'tamrin_marks'         => 'float',
        'viva_marks'           => 'float',
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

    public function publishResults(): void
    {
        $this->update([
            'is_result_published' => true,
            'result_published_at' => now(),
        ]);
        // Also mark results as published
        $this->results()->update(['is_published' => true]);
    }

    public function unpublishResults(): void
    {
        $this->update([
            'is_result_published' => false,
            'result_published_at' => null,
        ]);
        $this->results()->update(['is_published' => false]);
    }

    /**
     * Get active assessment components with metadata
     */
    public function getActiveComponents(): array
    {
        $components = [];

        if ($this->has_mcq) {
            $components['MCQ'] = [
                'name'        => 'MCQ (বহুনির্বাচনী)',
                'key'         => 'mcq',
                'target'      => (float) ($this->mcq_marks ?? 0),
                'pool_marks'  => $this->getPoolMcqMarks(),
                'icon'        => 'fa-solid fa-list-check',
                'color'       => '#4f46e5',
                'badge_class' => 'badge-primary',
                'type'        => 'AUTO',
                'desc'        => 'অনলাইন টাইমারযুক্ত এমসিকিউ পরীক্ষা',
            ];
        }

        if ($this->has_written) {
            $components['WRITTEN'] = [
                'name'        => 'লিখিত (Written)',
                'key'         => 'written',
                'target'      => (float) ($this->written_marks ?? 0),
                'pool_marks'  => $this->getPoolWrittenMarks(),
                'icon'        => 'fa-solid fa-pen-nib',
                'color'       => '#db2777',
                'badge_class' => 'badge-danger',
                'type'        => 'TEACHER_GRADED',
                'desc'        => 'খাতায় লিখে ছবি আপলোড বা টেক্সট উত্তর',
            ];
        }

        if ($this->has_tamrin) {
            $components['TAMRIN'] = [
                'name'        => 'তামরিন (হাতের কাজ)',
                'key'         => 'tamrin',
                'target'      => (float) ($this->tamrin_marks ?? 0),
                'pool_marks'  => (float) ($this->tamrin_marks ?? 0),
                'icon'        => 'fa-solid fa-hand-holding-hand',
                'color'       => '#d97706',
                'badge_class' => 'badge-warning',
                'type'        => 'DIRECT_ENTRY',
                'desc'        => 'হোমওয়ার্ক/অ্যাসাইনমেন্ট/হাতে লেখার মার্ক',
            ];
        }

        if ($this->has_viva) {
            $components['VIVA'] = [
                'name'        => 'ভাইভা (মৌখিক)',
                'key'         => 'viva',
                'target'      => (float) ($this->viva_marks ?? 0),
                'pool_marks'  => (float) ($this->viva_marks ?? 0),
                'icon'        => 'fa-solid fa-microphone',
                'color'       => '#059669',
                'badge_class' => 'badge-success',
                'type'        => 'DIRECT_ENTRY',
                'desc'        => 'মৌখিক পরীক্ষা/তেলাওয়াত শুনে মূল্যায়ন',
            ];
        }

        return $components;
    }

    public function getPoolMcqMarks(): float
    {
        return (float) $this->examQuestions
            ->filter(fn($eq) => ($eq->question?->question_type ?? 'MCQ') === 'MCQ')
            ->sum('marks');
    }

    public function getPoolWrittenMarks(): float
    {
        return (float) $this->examQuestions
            ->filter(fn($eq) => ($eq->question?->question_type ?? '') === 'WRITTEN')
            ->sum('marks');
    }

    public function isMcqTargetMet(): bool
    {
        if (!$this->has_mcq) return true;
        return $this->getPoolMcqMarks() >= (float) ($this->mcq_marks ?? 0);
    }

    public function isWrittenTargetMet(): bool
    {
        if (!$this->has_written) return true;
        return $this->getPoolWrittenMarks() >= (float) ($this->written_marks ?? 0);
    }
}
