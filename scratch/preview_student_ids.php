<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Batch;
use App\Models\Course;
use App\Models\AcademicYear;
use App\Models\AdmissionForm;

function resolveAcademicYearCode(?Batch $batch = null): string {
    if ($batch) {
        $ay = $batch->academicYear ?: ($batch->academic_year_id ? AcademicYear::find($batch->academic_year_id) : null);
        if ($ay) {
            if (!empty($ay->start_date)) {
                return date('y', strtotime($ay->start_date));
            }
            if (preg_match('/\b(20\d{2})\b/', $ay->name, $ym)) {
                return substr($ym[1], -2);
            }
        }
        if (!empty($batch->start_date)) {
            return date('y', strtotime($batch->start_date));
        }
    }
    $activeAy = AcademicYear::where('is_active', 1)->first();
    if ($activeAy) {
        if (!empty($activeAy->start_date)) {
            return date('y', strtotime($activeAy->start_date));
        }
        if (preg_match('/\b(20\d{2})\b/', $activeAy->name, $ym)) {
            return substr($ym[1], -2);
        }
    }
    return date('y');
}

function resolveBatchNumberCode(?Batch $batch = null): string {
    if (!$batch) return '01';

    $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    $en = ['0','1','2','3','4','5','6','7','8','9'];
    $name = str_replace($bn, $en, trim((string)($batch->name ?? '')));
    $code = str_replace($bn, $en, trim((string)($batch->batch_code ?? '')));

    // 1. If name is purely numeric (e.g. "01", "1", "12")
    if (is_numeric($name)) {
        return str_pad(((int)$name) % 100, 2, '0', STR_PAD_LEFT);
    }

    // 2. If name contains numbers (e.g. "Batch 01", "নাজেরা-০১", "Alim 2717")
    if (preg_match_all('/\d+/', $name, $matches)) {
        $numbers = $matches[0];
        foreach ($numbers as $numStr) {
            if (strlen($numStr) === 4) {
                if (in_array(substr($numStr, 0, 2), ['20'])) {
                    continue; // Skip 2026
                }
                if (in_array(substr($numStr, 0, 2), ['25', '26', '27', '28', '29', '30'])) {
                    return substr($numStr, 2, 2);
                }
            }
            return str_pad(((int)$numStr) % 100, 2, '0', STR_PAD_LEFT);
        }
    }

    // 3. Fallback to batch_code (e.g. "ALI-2026-11" -> 11, or "16" -> 16)
    if (!empty($code)) {
        if (str_contains($code, '-')) {
            $parts = explode('-', $code);
            $lastPart = end($parts);
            if (is_numeric($lastPart)) {
                return str_pad(((int)$lastPart) % 100, 2, '0', STR_PAD_LEFT);
            }
        }
        if (is_numeric($code)) {
            return str_pad(((int)$code) % 100, 2, '0', STR_PAD_LEFT);
        }
        if (preg_match('/\d+$/', $code, $m)) {
            return str_pad(((int)$m[0]) % 100, 2, '0', STR_PAD_LEFT);
        }
    }

    return str_pad(($batch->id % 100), 2, '0', STR_PAD_LEFT);
}

function resolveCourseCode(?Course $course = null, ?int $courseId = null): string {
    if (!$course && $courseId) {
        $course = Course::find($courseId);
    }
    if ($course) {
        if (!empty($course->code)) {
            $digits = preg_replace('/\D/', '', $course->code);
            if (!empty($digits)) {
                return str_pad(substr($digits, 0, 2), 2, '0', STR_PAD_LEFT);
            }
        }
        return str_pad(($course->id % 100), 2, '0', STR_PAD_LEFT);
    }
    return '01';
}

function resolveGenderCode(?string $gender = null): string {
    if (!empty($gender)) {
        $g = strtolower(trim($gender));
        if (in_array($g, ['female', '2', 'f', 'নারি', 'নারী', 'মহিলা'])) {
            return '2';
        }
    }
    return '1';
}

echo "=== PREVIEW FOR ALL STUDENTS (COLLISION-FREE SEQUENCES) ===\n";
$assignedCodes = [];
foreach (Student::orderBy('id')->get() as $s) {
    if (empty($s->student_code)) continue;

    $form = AdmissionForm::where('student_id', $s->id)->first();
    $enrollment = $s->enrollments()->latest()->first();
    $batch = $form?->batch ?: $enrollment?->batch;
    $course = $batch?->course ?: ($form?->interestedCourse ?: $enrollment?->course);

    $year = resolveAcademicYearCode($batch);
    $batchNum = resolveBatchNumberCode($batch);
    $courseCode = resolveCourseCode($course, $batch?->course_id);
    $gender = resolveGenderCode($s->gender);

    $prefix = "{$year}{$batchNum}{$courseCode}{$gender}";

    $cleanCode = preg_replace('/\D/', '', (string)$s->student_code);
    $preferredSeq = strlen($cleanCode) >= 11 ? (int)substr($cleanCode, 7, 4) : ($s->id % 10000);
    if ($preferredSeq <= 0) $preferredSeq = 1;

    $targetSeq = $preferredSeq;
    $candidate = $prefix . str_pad($targetSeq, 4, '0', STR_PAD_LEFT);
    while (in_array($candidate, $assignedCodes)) {
        $targetSeq++;
        $candidate = $prefix . str_pad($targetSeq, 4, '0', STR_PAD_LEFT);
    }
    $assignedCodes[] = $candidate;

    echo "ID: {$s->id} | {$s->name} | Old: {$s->student_code} -> New: {$candidate}\n";
}
