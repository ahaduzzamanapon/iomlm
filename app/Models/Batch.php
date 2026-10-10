<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_admission_open' => 'boolean',
        'admission_fee'     => 'float',
        'monthly_fee'       => 'float',
    ];

    public function scopeAdmissionOpen($query)
    {
        return $query->where('status', 'ACTIVE')->where('is_admission_open', true);
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function semesterPosition()
    {
        return $this->hasOne(BatchSemesterPosition::class, 'batch_id');
    }

    public function timelines()
    {
        return $this->hasMany(Timeline::class, 'batch_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'batch_id');
    }

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class, 'batch_id');
    }

    public function routineEntries()
    {
        return $this->hasMany(RoutineEntry::class, 'batch_id');
    }

    public function getEffectiveEmailTemplate(): string
    {
        if (!empty($this->email_template)) {
            return $this->email_template;
        }

        return "আসসালামু আলাইকুম {name},\n\n"
            . "আলহামদুলিল্লাহ! ইসলামিক অনলাইন মাদ্রাসায় \"{course}\" ({batch}) কোর্সে আপনার ভর্তি সফলভাবে অনুমোদিত ও নিশ্চিত হয়েছে।\n\n"
            . "আপনার অফিসিয়াল লগইন তথ্য:\n"
            . "----------------------------------------\n"
            . "• স্টুডেন্ট আইডি / রোল: {roll}\n"
            . "• লগইন পাসওয়ার্ড: {password}\n"
            . "• পোর্টাল লিংক: {login_url}\n"
            . "----------------------------------------\n\n"
            . "আপনি আপনার স্টুডেন্ট আইডি অথবা ইমেইল এবং পাসওয়ার্ড ব্যবহার করে স্টুডেন্ট পোর্টালে লগইন করতে পারবেন।\n"
            . "নিয়মিত ক্লাসে অংশগ্রহণ ও শিক্ষণ সামগ্রী পেতে এখনই পোর্টালে প্রবেশ করুন।";
    }

    public function getEffectiveSmsTemplate(): string
    {
        if (!empty($this->sms_template)) {
            return $this->sms_template;
        }

        return "আইওএম ভর্তি কনফার্ম! নাম: {name}, কোর্স: {course}, রোল: {roll}, পাসওয়ার্ড: {password}, লগইন: {login_url}";
    }

    public function getDurationCycleTextAttribute(): string
    {
        $months = Course::monthsList();
        $startRaw = $this->start_month ?: ($this->start_date ? date('F', strtotime($this->start_date)) : null);
        $endRaw   = $this->end_month ?: ($this->expected_end_date ? date('F', strtotime($this->expected_end_date)) : null);
        $start = $startRaw ? ($months[$startRaw] ?? $startRaw) : null;
        $end   = $endRaw ? ($months[$endRaw] ?? $endRaw) : null;

        if ($start && $end) {
            return "{$start} হতে {$end}";
        }
        if ($start) {
            return "{$start} থেকে শুরু";
        }
        if ($this->course) {
            return $this->course->duration_cycle_text;
        }
        return "—";
    }

    public function getFeeCycleTextAttribute(): string
    {
        $months = Course::monthsList();
        $startRaw = $this->fee_start_month ?: ($this->start_date ? date('F', strtotime($this->start_date)) : null);
        $endRaw   = $this->fee_end_month ?: ($this->expected_end_date ? date('F', strtotime($this->expected_end_date)) : null);
        $start = $startRaw ? ($months[$startRaw] ?? $startRaw) : null;
        $end   = $endRaw ? ($months[$endRaw] ?? $endRaw) : null;

        if ($start && $end) {
            return "{$start} হতে {$end}";
        }
        if ($start) {
            return "{$start} হতে চলমান";
        }
        if ($this->course) {
            return $this->course->fee_cycle_text;
        }
        return "প্রতি মাস";
    }

    /**
     * Resolve Course Prefix / Acronym from course name or code.
     * Extracts first letter of each word in the course name.
     * E.g. "Alim Preparatory Course" -> "APC"
     * E.g. "School Maktab Nazera (Bangla)" -> "SMN"
     * E.g. "School Maktab" -> "SM"
     * E.g. "Ruqyah Nazera Course" -> "RNC"
     */
    public static function resolveCoursePrefix($course): string
    {
        if (is_numeric($course)) {
            $course = Course::find($course);
        }
        $name = is_string($course) ? $course : ($course->name ?? '');

        // Strip text in parentheses, e.g. "(Bangla)", "(Semester Based)"
        $cleaned = preg_replace('/\([^)]*\)/u', '', $name);

        // Transliterate common Bengali course words if needed
        $bnToEnWords = [
            'আলিম' => 'Alim',
            'কোর্স' => 'Course',
            'নাজেরা' => 'Nazera',
            'দুয়া' => 'Dua',
            'সুন্নাহ' => 'Sunnah',
            'মক্তব' => 'Maktab',
            'স্কুল' => 'School',
        ];
        foreach ($bnToEnWords as $bnWord => $enWord) {
            $cleaned = str_replace($bnWord, $enWord, $cleaned);
        }

        $cleaned = preg_replace('/[^A-Za-z0-9\s]/u', ' ', $cleaned);
        $words = preg_split('/\s+/u', trim($cleaned), -1, PREG_SPLIT_NO_EMPTY);

        $prefix = '';
        if (count($words) === 1) {
            $prefix = strtoupper(substr($words[0], 0, 3));
        } else {
            foreach ($words as $w) {
                $prefix .= strtoupper(substr($w, 0, 1));
            }
        }

        if (strlen($prefix) < 2 && is_object($course) && !empty($course->code)) {
            $raw = preg_replace('/[^A-Za-z0-9]/', '', $course->code);
            if (!empty($raw)) {
                $prefix = strtoupper(substr($raw, 0, 3));
            }
        }

        if (strlen($prefix) < 2) {
            $prefix = 'BAT';
        }

        // Limit prefix to max 4 chars
        if (strlen($prefix) > 4) {
            $prefix = substr($prefix, 0, 4);
        }

        return $prefix;
    }

    /**
     * Resolve 2-digit Academic Year (e.g. 2027 -> "27")
     */
    public static function resolveAcademicYearDigits($academicYear = null, $startDate = null): string
    {
        if ($academicYear) {
            if (is_numeric($academicYear)) {
                $academicYear = AcademicYear::find($academicYear);
            }
            if (is_object($academicYear)) {
                if (!empty($academicYear->year) && preg_match('/(20\d{2})/', (string)$academicYear->year, $ym)) {
                    return substr($ym[1], -2);
                }
                if (preg_match('/\b(20\d{2})\b/', (string)$academicYear->name, $ym)) {
                    return substr($ym[1], -2);
                }
                if (!empty($academicYear->start_date)) {
                    return date('y', strtotime($academicYear->start_date));
                }
            }
        }
        if (!empty($startDate)) {
            return date('y', strtotime($startDate));
        }
        return date('y');
    }

    /**
     * Resolve 2-digit Batch Number (e.g. "01" -> "01", "12" -> "12", "Alim 2717" -> "17")
     */
    public static function resolveBatchNumberDigits(?string $batchName = null, ?int $batchId = null): string
    {
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $name = str_replace($bn, $en, trim((string)$batchName));

        // 1. If purely numeric (e.g. "01", "1", "12")
        if (is_numeric($name)) {
            return str_pad(((int)$name) % 100, 2, '0', STR_PAD_LEFT);
        }

        // 2. If name contains numbers
        if (preg_match_all('/\d+/', $name, $matches)) {
            $numbers = $matches[0];
            foreach ($numbers as $numStr) {
                if (strlen($numStr) === 4) {
                    if (in_array(substr($numStr, 0, 2), ['25', '26', '27', '28', '29', '30'])) {
                        return substr($numStr, 2, 2);
                    }
                }
                if (strlen($numStr) <= 2) {
                    return str_pad(((int)$numStr) % 100, 2, '0', STR_PAD_LEFT);
                }
            }
            $lastNum = end($numbers);
            return str_pad(((int)$lastNum) % 100, 2, '0', STR_PAD_LEFT);
        }

        if ($batchId) {
            return str_pad(($batchId % 100), 2, '0', STR_PAD_LEFT);
        }

        return '01';
    }

    /**
     * Generate collision-free batch code: [Course Acronym]-[YY][BatchNumber]
     * E.g. APC-2701, SMN-2712
     */
    public static function generateBatchCode($course, $academicYear = null, ?string $batchName = null, $startDate = null, ?int $excludeBatchId = null): string
    {
        $prefix = self::resolveCoursePrefix($course);
        $yearDigits = self::resolveAcademicYearDigits($academicYear, $startDate);
        $batchDigits = self::resolveBatchNumberDigits($batchName, $excludeBatchId);

        $baseCode = "{$prefix}-{$yearDigits}{$batchDigits}";

        $candidate = $baseCode;
        $counter = 1;
        while (self::where('batch_code', $candidate)
            ->when($excludeBatchId, fn($q) => $q->where('id', '!=', $excludeBatchId))
            ->exists()) {
            $counter++;
            $candidate = "{$baseCode}-{$counter}";
        }

        return $candidate;
    }
}
