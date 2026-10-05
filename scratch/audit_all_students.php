<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use App\Models\AdmissionForm;
use App\Models\Batch;
use App\Models\Invoice;

echo "=== Comprehensive Audit of All Students ===" . PHP_EOL;
foreach (Student::with('user', 'enrollments.batch.academicYear', 'feePackage')->get() as $st) {
    $enr = $st->enrollments->first();
    $batch = $enr?->batch;
    $ay = $batch?->academicYear;
    $admWithWaiver = AdmissionForm::where('student_id', $st->id)->whereNotNull('waiver_code')->first();
    $semInvs = Invoice::where('student_id', $st->id)->where('category', 'SEMESTER')->get();
    
    echo "Student ID: {$st->id} | Name: " . ($st->user?->name ?? 'N/A') . " | Code: {$st->student_code}" . PHP_EOL;
    echo "  Enrollment Batch ID: " . ($batch?->id ?? 'none') . " ({$batch?->name}) | Start: {$batch?->start_date} | AY: " . ($ay?->name ?? 'none') . PHP_EOL;
    echo "  FeePackage ID: " . ($st->fee_package_id ?? 'none') . " (" . ($st->feePackage?->name ?? 'none') . ")" . PHP_EOL;
    if ($admWithWaiver) {
        echo "  Adm Waiver: {$admWithWaiver->waiver_code} | Adm Batch ID: {$admWithWaiver->batch_id}" . PHP_EOL;
    }
    foreach ($semInvs as $si) {
        echo "  Semester Inv {$si->id}: {$si->title} | Amt: {$si->amount} | Payable: {$si->payable_amount}" . PHP_EOL;
    }
    echo "--------------------------------------------------------" . PHP_EOL;
}
