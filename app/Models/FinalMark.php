<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinalMark extends Model
{
    protected $guarded = [];

    protected $casts = [
        'generated_at'         => 'datetime',
        'published_at'         => 'datetime',
        'is_published'         => 'boolean',
        'total_mark'           => 'float',
        'gpa'                  => 'float',
        'class_test_obtained'  => 'float',
        'class_test_converted' => 'float',
        'midterm_obtained'     => 'float',
        'midterm_converted'    => 'float',
        'final_obtained'       => 'float',
        'final_converted'      => 'float',
        'attendance_converted' => 'float',
        'attendance_percent'   => 'float',
        'tamrin_mark'          => 'float',
        'tajweed_mark'         => 'float',
        'dns_mark'             => 'float',
        'merit_position'       => 'integer',
    ];

    // ── Conversion Constants ───────────────────────────────────────────
    const CLASS_TEST_FULL    = 30;
    const CLASS_TEST_CONVERT = 20;
    const MIDTERM_FULL       = 50;
    const MIDTERM_CONVERT    = 30;
    const FINAL_FULL         = 100;
    const FINAL_CONVERT      = 40;
    const ATTENDANCE_CONVERT = 10;

    // ── Relationships ──────────────────────────────────────────────────
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    // ── Grade Calculation Helper ───────────────────────────────────────
    public static function calculateGrade(float $total): array
    {
        if ($total >= 80) return ['grade' => 'A+', 'gpa' => 5.00];
        if ($total >= 70) return ['grade' => 'A',  'gpa' => 4.00];
        if ($total >= 60) return ['grade' => 'A-', 'gpa' => 3.50];
        if ($total >= 50) return ['grade' => 'B',  'gpa' => 3.00];
        if ($total >= 40) return ['grade' => 'C',  'gpa' => 2.00];
        return ['grade' => 'F', 'gpa' => 0.00];
    }

    /**
     * Qawmi Madrasah Result Grade Standard (কওমি মাদরাসা গ্রেডিং মানদণ্ড)
     * মুমতাজ (Mumtaz): 80%+ (Star / Outstanding)
     * জায়্যিদ জিদ্দান (Jayyid Jiddan): 65% - 79.99% (1st Div)
     * জায়্যিদ (Jayyid): 50% - 64.99% (2nd Div)
     * মাকবুল (Maqbul): 40% - 49.99% (Pass / 3rd Div)
     * রাসিব (Rasib): < 40% (Fail)
     */
    public static function calculateQawmiGrade(float $total, ?float $gpa = null): array
    {
        if ($total >= 80 || ($gpa !== null && $gpa >= 4.75)) {
            return [
                'name_bn' => 'মুমতাজ (Mumtaz)',
                'name_ar' => 'ممتاز',
                'label'   => 'স্টার মার্কস / অসাধারণ',
                'class'   => 'success'
            ];
        }
        if ($total >= 65 || ($gpa !== null && $gpa >= 3.75)) {
            return [
                'name_bn' => 'জায়্যিদ জিদ্দান (Jayyid Jiddan)',
                'name_ar' => 'جيد جداً',
                'label'   => 'প্রথম বিভাগ / অতি উত্তম',
                'class'   => 'primary'
            ];
        }
        if ($total >= 50 || ($gpa !== null && $gpa >= 2.75)) {
            return [
                'name_bn' => 'জায়্যিদ (Jayyid)',
                'name_ar' => 'جيد',
                'label'   => 'দ্বিতীয় বিভাগ / উত্তম',
                'class'   => 'info'
            ];
        }
        if ($total >= 40 || ($gpa !== null && $gpa >= 2.00)) {
            return [
                'name_bn' => 'মাকবুল (Maqbul)',
                'name_ar' => 'مقبول',
                'label'   => 'উত্তীর্ণ / সাধারণ মান',
                'class'   => 'warning'
            ];
        }
        return [
            'name_bn' => 'রাসিব (Rasib)',
            'name_ar' => 'راسب',
            'label'   => 'অনুত্তীর্ণ (ফেল)',
            'class'   => 'danger'
        ];
    }

    public function getRawClassTestAttribute(): ?float
    {
        if ($this->class_test_obtained !== null) {
            return (float) $this->class_test_obtained;
        }
        if ($this->class_test_converted !== null) {
            return round(($this->class_test_converted / self::CLASS_TEST_CONVERT) * self::CLASS_TEST_FULL, 1);
        }
        return null;
    }

    public function getRawMidtermAttribute(): ?float
    {
        if ($this->midterm_obtained !== null) {
            return (float) $this->midterm_obtained;
        }
        if ($this->midterm_converted !== null) {
            return round(($this->midterm_converted / self::MIDTERM_CONVERT) * self::MIDTERM_FULL, 1);
        }
        return null;
    }

    public function getRawFinalAttribute(): ?float
    {
        if ($this->final_obtained !== null) {
            return (float) $this->final_obtained;
        }
        if ($this->final_converted !== null) {
            return round(($this->final_converted / self::FINAL_CONVERT) * self::FINAL_FULL, 1);
        }
        return null;
    }

    public function getRawAttendanceAttribute(): ?float
    {
        if ($this->attendance_converted !== null) {
            return (float) $this->attendance_converted;
        }
        return null;
    }

    public function getRawTotalObtainedAttribute(): float
    {
        return round(
            ($this->raw_class_test ?? 0) +
            ($this->raw_midterm ?? 0) +
            ($this->raw_final ?? 0) +
            ($this->raw_attendance ?? 0),
            2
        );
    }

    public function getRawTotalFullMarksAttribute(): float
    {
        return (float) (self::CLASS_TEST_FULL + self::MIDTERM_FULL + self::FINAL_FULL + self::ATTENDANCE_CONVERT);
    }

    public function getQawmiGradeAttribute(): array
    {
        return self::calculateQawmiGrade((float) $this->total_mark, (float) $this->gpa);
    }

    /**
     * Convert integer rank to Bengali numeral with suffix (১ম, ২য়, ৩য়, ৪র্থ...)
     */
    public function getMeritRankBengaliAttribute(): ?string
    {
        if (!$this->merit_position) return null;
        $num = $this->merit_position;
        $bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $bnNum = str_replace(range(0, 9), $bnDigits, (string) $num);

        $suffix = match($num) {
            1 => 'ম',
            2, 3 => 'য়',
            4 => 'র্থ',
            default => 'ম'
        };
        return $bnNum . $suffix;
    }

    /**
     * Get dynamic conversion criteria from settings, with defaults.
     */
    public static function getCriteria(): array
    {
        return [
            'class_test_full'    => (float) Setting::get('final_mark_class_test_full', self::CLASS_TEST_FULL),
            'class_test_convert' => (float) Setting::get('final_mark_class_test_convert', self::CLASS_TEST_CONVERT),
            'midterm_full'       => (float) Setting::get('final_mark_midterm_full', self::MIDTERM_FULL),
            'midterm_convert'    => (float) Setting::get('final_mark_midterm_convert', self::MIDTERM_CONVERT),
            'final_full'         => (float) Setting::get('final_mark_final_full', self::FINAL_FULL),
            'final_convert'      => (float) Setting::get('final_mark_final_convert', self::FINAL_CONVERT),
            'attendance_convert' => (float) Setting::get('final_mark_attendance_convert', self::ATTENDANCE_CONVERT),
            'pass_mark'          => (float) Setting::get('final_mark_pass_mark', 40),
        ];
    }

    /**
     * Recalculate total, grade, GPA, and status when attendance, exams, or non-exam criteria change.
     */
    public function recalculate(array $updates = []): void
    {
        $criteria = self::getCriteria();

        foreach ($updates as $key => $val) {
            $this->{$key} = ($val !== null && $val !== '') ? (float) $val : null;
        }

        $total = round(
            ($this->class_test_converted ?? 0) +
            ($this->midterm_converted    ?? 0) +
            ($this->final_converted      ?? 0) +
            ($this->attendance_converted ?? 0),
            2
        );

        $gradeInfo = self::calculateGrade($total);
        $status    = $total >= ($criteria['pass_mark'] ?? 40) ? 'PASS' : 'FAIL';

        $this->total_mark = $total;
        $this->grade      = $gradeInfo['grade'];
        $this->gpa        = $gradeInfo['gpa'];
        $this->status     = $status;
        $this->save();
    }

    /**
     * Automatically recalculate merit ranks for all final marks in a batch + subject + semester
     */
    public static function recalculateMeritRanks($batchId, $subjectId, $semesterId = null): void
    {
        $query = self::where('batch_id', $batchId)
            ->where('subject_id', $subjectId);

        if ($semesterId) {
            $query->where(function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)->orWhereNull('semester_id');
            });
        }

        $marks = $query->orderByDesc('total_mark')->get();
        $rank = 1;
        foreach ($marks as $mark) {
            $mark->merit_position = $rank++;
            $mark->saveQuietly();
        }
    }
}
