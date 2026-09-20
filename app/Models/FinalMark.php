<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinalMark extends Model
{
    protected $guarded = [];

    protected $casts = [
        'generated_at' => 'datetime',
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
     * Recalculate total, grade, GPA, and status when attendance or component mark changes.
     */
    public function recalculate(float $newAttendanceConverted, ?float $newAttendancePercent = null): void
    {
        $criteria = self::getCriteria();
        $this->attendance_converted = round($newAttendanceConverted, 2);
        if ($newAttendancePercent !== null) {
            $this->attendance_percent = round($newAttendancePercent, 2);
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
}
