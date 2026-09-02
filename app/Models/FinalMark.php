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
}
