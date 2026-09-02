<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix 1: Admission invoices where batch/course fee = 0 but invoice shows non-zero ===\n";

$admInvoices = App\Models\Invoice::where('category', 'ADMISSION')->get();
foreach ($admInvoices as $inv) {
    $enrollment = $inv->enrollment;
    if (!$enrollment) continue;

    $batch  = $enrollment->batch;
    $course = $enrollment->course;

    // Check if batch or course explicitly has admission_fee = 0
    $batchFee  = $batch  ? (float)$batch->admission_fee  : null;
    $courseFee = $course ? (float)$course->admission_fee : null;

    $shouldBeFee = null;
    if ($batchFee !== null) {
        $shouldBeFee = $batchFee;
    } elseif ($courseFee !== null) {
        $shouldBeFee = $courseFee;
    }

    if ($shouldBeFee !== null && $shouldBeFee == 0 && $inv->amount > 0) {
        echo "FIXING Inv ID: {$inv->id} ({$inv->invoice_no}) | Student: {$inv->student?->name} | Was: {$inv->amount} -> Should be: 0\n";
        $inv->update([
            'amount'         => 0,
            'payable_amount' => 0,
            'due_amount'     => 0,
            'status'         => 'PAID',
        ]);
    }
}

echo "\n=== Fix 2: SEMESTER invoices for SUBJECT_BASED courses that were wrongly divided by 6 ===\n";

$semInvoices = App\Models\Invoice::where('category', 'SEMESTER')->get();
foreach ($semInvoices as $inv) {
    $enrollment = $inv->enrollment;
    if (!$enrollment) continue;

    $course = $enrollment->course;
    if (!$course || $course->type !== 'SUBJECT_BASED') continue;

    // Get the correct package total
    $defaultPkg = $course->feePackages()->first();
    if (!$defaultPkg) continue;

    $pkgTotal = (float) $defaultPkg->items()->sum('total_amount');
    if ($pkgTotal <= 0) continue;

    // If the current invoice amount looks like it was divided (< pkgTotal)
    if ($inv->amount < $pkgTotal) {
        echo "FIXING Inv ID: {$inv->id} ({$inv->invoice_no}) | Student: {$inv->student?->name} | Was: {$inv->amount} -> Should be: {$pkgTotal}\n";
        $newDue = max(0, $pkgTotal - (float)$inv->paid_amount);
        $inv->update([
            'amount'         => $pkgTotal,
            'payable_amount' => $pkgTotal,
            'due_amount'     => $newDue,
            'status'         => $newDue <= 0 ? 'PAID' : ($inv->paid_amount > 0 ? 'PARTIAL' : 'UNPAID'),
        ]);
    }
}

echo "\nDone.\n";
