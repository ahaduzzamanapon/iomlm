<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use App\Models\AdmissionForm;
use App\Models\WaiverApplication;
use App\Models\Invoice;

$st = Student::with('user', 'enrollments')->find(15);
echo "Student 15 User Email: " . $st->user?->email . PHP_EOL;

foreach ($st->enrollments as $e) {
    echo "  Enr ID: {$e->id}, Batch: {$e->batch_id}, Course: {$e->course_id}, Status: {$e->status}, BatchName: " . ($e->batch?->name ?? 'none') . ", BatchAY: " . ($e->batch?->academic_year_id ?? 'none') . PHP_EOL;
}

$invs = Invoice::where('student_id', 15)->get();
echo "Invoices for 15: " . $invs->count() . PHP_EOL;
foreach ($invs as $inv) {
    echo "  ID: {$inv->id}, No: {$inv->invoice_no}, Title: {$inv->title}, Cat: {$inv->category}, Amt: {$inv->amount}, Payable: {$inv->payable_amount}" . PHP_EOL;
}

// Also check all other students who have invoices with package in title
echo PHP_EOL . "Check other students' invoices:" . PHP_EOL;
foreach (Student::with('user')->get() as $s) {
    $firstInv = Invoice::where('student_id', $s->id)->where('category', 'SEMESTER')->first();
    if ($firstInv) {
        echo "Student {$s->id} ({$s->student_code}): FeePkg: {$s->fee_package_id} | Inv: {$firstInv->title}" . PHP_EOL;
    }
}
