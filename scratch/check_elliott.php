<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = \App\Models\Student::find(10);
echo "=== STUDENT 10: {$student->name} ===\n";
echo "fee_package_id: {$student->fee_package_id}\n";
$feePkg = $student->feePackage;
echo "Fee Package: " . ($feePkg ? $feePkg->name : 'None') . "\n";
if ($feePkg) {
    foreach ($feePkg->items as $it) {
        $slug = $it->feeHead ? $it->feeHead->slug : 'None';
        echo "  - Item: '{$it->label}', Slug: {$slug}, PerUnit: {$it->amount_per_unit}, Total: {$it->total_amount}\n";
    }
}

echo "\n=== ENROLLMENTS ===\n";
foreach ($student->enrollments()->with('course')->get() as $en) {
    echo "ID: {$en->id}, Course: {$en->course?->name} (ID: {$en->course_id}), Batch: {$en->batch_id}, Status: {$en->status}, EnrolledAt: {$en->enrolled_at}\n";
}

echo "\n=== INVOICES ===\n";
foreach ($student->invoices()->get() as $inv) {
    echo "ID: {$inv->id}, No: {$inv->invoice_no}, Title: {$inv->title}, Cat: {$inv->category}, Payable: {$inv->payable_amount}, Status: {$inv->status}\n";
}

$feeService = app(\App\Services\StudentFeeService::class);
$breakdown = $feeService->getStudentFeeBreakdown($student);
echo "\n=== FEE BREAKDOWN STEP 1 PARTICULARS ===\n";
foreach ($breakdown['step1Particulars'] as $p) {
    echo "  - SL: {$p['sl']}, Name: '{$p['name']}', Amount: {$p['amount']}, Paid: {$p['paid_amt']}, Due: {$p['due']}\n";
}
