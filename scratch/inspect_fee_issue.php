<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = \App\Models\Student::find(24);
$enrollment = $student->enrollments()->latest()->first();
$batch = $enrollment?->batch;
$course = $enrollment?->course;

echo "Student ID: {$student->id}, Name: {$student->name}\n";
echo "Batch: {$batch?->id} - {$batch?->name}\n";
echo "Batch fee_start_month: {$batch?->fee_start_month}, fee_end_month: {$batch?->fee_end_month}, start_date: {$batch?->start_date}\n";
echo "Course: {$course?->id} - {$course?->name}\n";
echo "Course fee_start_month: {$course?->fee_start_month}, fee_end_month: {$course?->fee_end_month}, start_date: {$course?->start_date}\n";

$approvedPackage = $student->approvedFeePackage ?? $student->feePackage;
echo "Student approved fee package: " . ($approvedPackage ? $approvedPackage->id . ' - ' . $approvedPackage->name : 'None') . "\n";
if ($approvedPackage) {
    foreach ($approvedPackage->items as $item) {
        echo " - Item: {$item->label} / fee_head: {$item->feeHead?->name}, amount_mode: {$item->amount_mode}, unit: {$item->amount_per_unit}, total: {$item->total_amount}, months: {$item->months_count}\n";
    }
}

$invoices = \App\Models\Invoice::where('student_id', $student->id)->get();
echo "\nInvoices for Student 24:\n";
foreach ($invoices as $inv) {
    echo " Invoice #{$inv->id} [{$inv->invoice_no}]: cat={$inv->category}, sem_id={$inv->semester_id}, title={$inv->title}, payable={$inv->payable_amount}, paid={$inv->paid_amount}, due={$inv->due_amount}, status={$inv->status}\n";
}
