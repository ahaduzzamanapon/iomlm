<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = \App\Models\Student::find(24);
$activeEnrollment = $student->enrollments()->latest()->first();
$batch = $activeEnrollment?->batch;
$course = $activeEnrollment?->course;

echo "Student: {$student->name} (ID: {$student->id})\n";
echo "Batch: {$batch?->id} - {$batch?->name}\n";
echo "Course: {$course?->id} - {$course?->name}\n";

// Test Fee Package resolution
$studentFeePackage = $student->feePackage;
if (!$studentFeePackage && $student->fee_package_id) {
    $studentFeePackage = \App\Models\CourseFeePackage::find($student->fee_package_id);
}
if (!$studentFeePackage) {
    $admForm = \App\Models\AdmissionForm::where('student_id', $student->id)->latest()->first();
    $waiverCode = $admForm?->waiver_code;
    if ($waiverCode) {
        $altWaiverCode = str_starts_with($waiverCode, 'PF-')
            ? str_replace('PF-', 'POOR-', $waiverCode)
            : (str_starts_with($waiverCode, 'POOR-') ? str_replace('POOR-', 'PF-', $waiverCode) : $waiverCode);
        $waiverApp = \App\Models\WaiverApplication::where(function ($q) use ($waiverCode, $altWaiverCode) {
                $q->where('application_no', $waiverCode)->orWhere('application_no', $altWaiverCode);
            })
            ->where('status', 'APPROVED')
            ->whereNotNull('approved_package_id')
            ->first();
        if ($waiverApp) {
            $studentFeePackage = \App\Models\CourseFeePackage::find($waiverApp->approved_package_id);
        }
    }
}
echo "Resolved Package: " . ($studentFeePackage ? "{$studentFeePackage->id} - {$studentFeePackage->name}" : "None") . "\n";

// Test Rate calculation
$totalSemesters = max(1, $course?->semesters()->count() ?: 6);
$monthlyTuition = 500.0;
$midFeeAmt = 300.0;
$finalFeeAmt = 500.0;

if ($studentFeePackage) {
    $tuitionItem = $studentFeePackage->items->first(function ($it) {
        return $it->feeHead?->slug === 'tuition_fee' || str_contains(mb_strtolower($it->label ?? ''), 'tuition');
    });
    $midItem = $studentFeePackage->items->first(function ($it) {
        return $it->feeHead?->slug === 'mid_term_fee' || str_contains(mb_strtolower($it->label ?? ''), 'mid');
    });
    $finalItem = $studentFeePackage->items->first(function ($it) {
        return $it->feeHead?->slug === 'final_term_fee' || str_contains(mb_strtolower($it->label ?? ''), 'final');
    });

    if ($tuitionItem && $tuitionItem->amount_per_unit > 0) {
        $monthlyTuition = (float) $tuitionItem->amount_per_unit;
    } elseif ($tuitionItem && $tuitionItem->months_count > 0) {
        $monthlyTuition = round((float) $tuitionItem->total_amount / $tuitionItem->months_count, 2);
    } elseif ($tuitionItem && $totalSemesters > 0) {
        $monthlyTuition = round((float) $tuitionItem->total_amount / ($totalSemesters * 6), 2);
    }

    if ($midItem) {
        if ($midItem->amount_mode === 'PER_SEMESTER' && $midItem->amount_per_unit > 0) {
            $midFeeAmt = (float) $midItem->amount_per_unit;
        } elseif ($totalSemesters > 0 && $midItem->total_amount > 0) {
            $midFeeAmt = round((float) $midItem->total_amount / $totalSemesters, 2);
        } else {
            $midFeeAmt = (float) ($midItem->amount_per_unit ?? 0);
        }
    } else {
        $midFeeAmt = 0.0;
    }

    if ($finalItem) {
        if ($finalItem->amount_mode === 'PER_SEMESTER' && $finalItem->amount_per_unit > 0) {
            $finalFeeAmt = (float) $finalItem->amount_per_unit;
        } elseif ($totalSemesters > 0 && $finalItem->total_amount > 0) {
            $finalFeeAmt = round((float) $finalItem->total_amount / $totalSemesters, 2);
        } else {
            $finalFeeAmt = (float) ($finalItem->amount_per_unit ?? 0);
        }
    } else {
        $finalFeeAmt = 0.0;
    }
}

echo "Monthly Tuition: {$monthlyTuition}\n";
echo "Mid Fee: {$midFeeAmt}\n";
echo "Final Fee: {$finalFeeAmt}\n";

// Test Month calculation
$batchStartMonth = $batch?->fee_start_month ?: ($batch?->start_month ?: ($course?->fee_start_month ?: ($course?->start_month ?: null)));
$batchStartDate  = $batch?->start_date ? \Carbon\Carbon::parse($batch->start_date) : ($course?->start_date ? \Carbon\Carbon::parse($course->start_date) : null);
$configuredStartMonth = $batchStartMonth ?? 'January';
$activityStartDate = $batchStartDate ?? now();

$monthNum = (int) date('n', strtotime($configuredStartMonth . ' 1 2026'));
$startYear = (int) ($activityStartDate?->year ?: now()->year);
$baseCarbon = \Carbon\Carbon::createFromDate($startYear, $monthNum, 1);

for ($seq = 1; $seq <= 2; $seq++) {
    $startCarbon = $baseCarbon->copy()->addMonths(($seq - 1) * 6);
    echo "\nSemester {$seq} Months:\n";
    for ($i = 0; $i < 6; $i++) {
        $cDate = $startCarbon->copy()->addMonths($i);
        echo " - Month " . ($i + 1) . ": " . $cDate->format('M-Y') . "\n";
    }
}
