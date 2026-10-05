<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Invoice;

echo "=== All Students and Packages ===" . PHP_EOL;
foreach (Student::with('user', 'enrollments.course', 'feePackage')->get() as $st) {
    $enr = $st->enrollments->first();
    echo "Student ID: {$st->id}, Name: " . ($st->user?->name ?? 'N/A') . ", Code: {$st->student_code}, FeePkg: " . ($st->fee_package_id ?? 'null') . ", PkgName: " . ($st->feePackage?->name ?? 'none') . ", EnrCourse: " . ($enr?->course?->name ?? 'none') . PHP_EOL;
}

echo PHP_EOL . "=== Invoices with amount 943.33 ===" . PHP_EOL;
foreach (Invoice::where('amount', 'like', '%943%')->orWhere('payable_amount', 'like', '%943%')->get() as $inv) {
    echo "Inv ID: {$inv->id}, InvNo: {$inv->invoice_no}, StudentID: {$inv->student_id}, Title: {$inv->title}, Amount: {$inv->amount}, Payable: {$inv->payable_amount}" . PHP_EOL;
}
