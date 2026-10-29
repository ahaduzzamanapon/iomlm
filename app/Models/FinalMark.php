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
        'ct_tamrin'            => 'float',
        'midterm_tamrin'       => 'float',
        'final_tamrin'         => 'float',
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
     * Get gender-based merit description (e.g. ভাইদের মধ্যে ১ম স্থান / বোনদের মধ্যে ১ম স্থান)
     */
    public function getGenderMeritTextAttribute(): ?string
    {
        if (!$this->merit_position) return null;
        $isSister = $this->student?->isSister() ?? false;
        return ($isSister ? 'বোনদের মধ্যে ' : 'ভাইদের মধ্যে ') . $this->merit_rank_bengali . ' স্থান';
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

        // Auto-calculate converted score if obtained is provided and converted is not explicitly overridden
        if (isset($updates['class_test_obtained']) && !isset($updates['class_test_converted']) && $this->class_test_obtained !== null) {
            $this->class_test_converted = round(($this->class_test_obtained / $criteria['class_test_full']) * $criteria['class_test_convert'], 2);
        }
        if (isset($updates['midterm_obtained']) && !isset($updates['midterm_converted']) && $this->midterm_obtained !== null) {
            $this->midterm_converted = round(($this->midterm_obtained / $criteria['midterm_full']) * $criteria['midterm_convert'], 2);
        }
        if (isset($updates['final_obtained']) && !isset($updates['final_converted']) && $this->final_obtained !== null) {
            $this->final_converted = round(($this->final_obtained / $criteria['final_full']) * $criteria['final_convert'], 2);
        }

        // Sum of exam-wise Tamrin marks
        if ($this->ct_tamrin !== null || $this->midterm_tamrin !== null || $this->final_tamrin !== null) {
            $this->tamrin_mark = round(
                ($this->ct_tamrin ?? 0) +
                ($this->midterm_tamrin ?? 0) +
                ($this->final_tamrin ?? 0),
                2
            );
        }

        // Auto-compute attendance mark if student is Facebook Live approved (unless explicitly passed)
        if (!isset($updates['attendance_converted'])) {
            $st = $this->student ?? Student::find($this->student_id);
            if ($st && $st->facebook_live_approved) {
                $attConv = $this->calculateFacebookLiveAttendance();
                $this->attendance_converted = $attConv;
                $attMax = (float) ($criteria['attendance_convert'] ?? self::ATTENDANCE_CONVERT);
                $this->attendance_percent = $attMax > 0 ? round(($attConv / $attMax) * 100, 2) : 0.0;
            }
        }

        // Semester Total Mark out of 100:
        // CT converted (20) + Midterm converted (30) + Final converted (40) + Semester Attendance (10)
        // Tamrin marks are exam-wise components of CT, Midterm, and Final exams.
        // Attendance mark is strictly semester-wise.
        $examConvertedSum = round(
            ($this->class_test_converted ?? 0) +
            ($this->midterm_converted    ?? 0) +
            ($this->final_converted      ?? 0) +
            ($this->attendance_converted ?? 0),
            2
        );

        // Fallback for legacy records that had standalone non-exam marks
        $hasExams = ($this->class_test_converted !== null || $this->midterm_converted !== null || $this->final_converted !== null);
        if ($hasExams || $this->attendance_converted !== null) {
            $total = $examConvertedSum;
        } else {
            $total = round(($this->tamrin_mark ?? 0) + ($this->attendance_converted ?? 0), 2);
        }

        $gradeInfo = self::calculateGrade($total);
        $status    = $total >= ($criteria['pass_mark'] ?? 40) ? 'PASS' : 'FAIL';

        $this->total_mark = $total;
        $this->grade      = $gradeInfo['grade'];
        $this->gpa        = $gradeInfo['gpa'];
        $this->status     = $status;
        $this->save();
    }

    /**
     * Calculate attendance mark for Facebook Live approved students.
     * Rule: Attendance mark is 10% of the student's exam/subject score out of 100.
     * Example: 100 -> 10 marks, 90 -> 9 marks, 85 -> 8.5 marks.
     */
    public function calculateFacebookLiveAttendance(): float
    {
        $criteria = self::getCriteria();
        $attMax = (float) ($criteria['attendance_convert'] ?? self::ATTENDANCE_CONVERT); // 10.0

        $ctConv  = $this->class_test_converted;
        $midConv = $this->midterm_converted;
        $finConv = $this->final_converted;

        $examConvSum = 0;
        $examConvFull = 0;

        if ($ctConv !== null) {
            $examConvSum += (float) $ctConv;
            $examConvFull += (float) ($criteria['class_test_convert'] ?? self::CLASS_TEST_CONVERT); // 20
        }
        if ($midConv !== null) {
            $examConvSum += (float) $midConv;
            $examConvFull += (float) ($criteria['midterm_convert'] ?? self::MIDTERM_CONVERT); // 30
        }
        if ($finConv !== null) {
            $examConvSum += (float) $finConv;
            $examConvFull += (float) ($criteria['final_convert'] ?? self::FINAL_CONVERT); // 40
        }

        $scoreOutOf100 = null;
        if ($examConvFull > 0) {
            $scoreOutOf100 = ($examConvSum / $examConvFull) * 100;
        } elseif ($this->final_obtained !== null) {
            $scoreOutOf100 = (float) $this->final_obtained;
        } elseif ($this->midterm_obtained !== null) {
            $scoreOutOf100 = ((float) $this->midterm_obtained / self::MIDTERM_FULL) * 100;
        } elseif ($this->class_test_obtained !== null) {
            $scoreOutOf100 = ((float) $this->class_test_obtained / self::CLASS_TEST_FULL) * 100;
        } else {
            // Check Result records for exams in this subject
            $results = Result::where('student_id', $this->student_id)
                ->whereHas('exam', function ($q) {
                    $q->where('subject_id', $this->subject_id);
                })
                ->with('exam')
                ->get();

            if ($results->isNotEmpty()) {
                $totObtained = 0;
                $totFull = 0;
                foreach ($results as $res) {
                    if ($res->exam && (float)$res->exam->full_marks > 0) {
                        $totObtained += (float)$res->marks;
                        $totFull += (float)$res->exam->full_marks;
                    }
                }
                if ($totFull > 0) {
                    $scoreOutOf100 = ($totObtained / $totFull) * 100;
                }
            }
        }

        if ($scoreOutOf100 === null) {
            return 0.0;
        }

        // 10% of score (100 -> 10, 90 -> 9, 85 -> 8.5)
        $attMark = round(($scoreOutOf100 / 100) * $attMax, 2);
        return max(0.0, min($attMax, $attMark));
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

        $marks = $query->with('student')->orderByDesc('total_mark')->get();
        $brotherRank = 1;
        $sisterRank = 1;
        foreach ($marks as $mark) {
            $isSister = $mark->student?->isSister() ?? false;
            $mark->merit_position = $isSister ? $sisterRank++ : $brotherRank++;
            $mark->saveQuietly();
        }
    }
}
