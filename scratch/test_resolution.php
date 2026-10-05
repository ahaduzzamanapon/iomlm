<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Batch;
use App\Models\AcademicYear;

function resolveAcademicYearCode(Batch $batch): string {
    $academicYear = $batch->academicYear ?: ($batch->academic_year_id ? AcademicYear::find($batch->academic_year_id) : null);
    if ($academicYear) {
        if (!empty($academicYear->start_date)) {
            return date('y', strtotime($academicYear->start_date));
        }
        if (preg_match('/\b(20\d{2})\b/', $academicYear->name, $ym)) {
            return substr($ym[1], -2);
        }
    }
    if (!empty($batch->start_date)) {
        return date('y', strtotime($batch->start_date));
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

function resolveBatchNumberCode(Batch $batch): string {
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
            // If it's a 4-digit year like 2026, skip it unless it's like 2717 (YYBB)
            if (strlen($numStr) === 4) {
                if (in_array(substr($numStr, 0, 2), ['20'])) {
                    continue; // Skip calendar year like 2026
                }
                if (in_array(substr($numStr, 0, 2), ['25', '26', '27', '28', '29', '30'])) {
                    // Formatted like 2717 (Year 27, Batch 17)
                    return substr($numStr, 2, 2);
                }
            }
            // If 1-3 digits, it's the batch number (e.g. "01", "1", "3")
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

    // 4. Default to batch ID % 100
    return str_pad(($batch->id % 100), 2, '0', STR_PAD_LEFT);
}

\App\Models\Student::where('id', 24)->update(['student_code' => '27010110003']);
$st24 = \App\Models\Student::find(24);
echo "Student 24 [{$st24->name}] Student Code: {$st24->student_code}\n";
