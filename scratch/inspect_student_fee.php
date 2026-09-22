<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Course;

$student = Student::where('student_code', 'STD2026002')->first() ?? Student::find(2);
echo "=== STUDENT INFO ===" . PHP_EOL;
echo "ID: {$student->id} | Code: {$student->student_code} | Name: {$student->name}" . PHP_EOL;

echo PHP_EOL . "=== ENROLLMENTS ===" . PHP_EOL;
foreach ($student->enrollments as $e) {
    echo "Enrollment #{$e->id} | Course ID: {$e->course_id} ({$e->course?->name}) | Batch ID: {$e->batch_id} ({$e->batch?->name}) | Sem ID: {$e->semester_id} ({$e->semester?->name}) | Status: {$e->status}" . PHP_EOL;
}

echo PHP_EOL . "=== INVOICES ===" . PHP_EOL;
foreach ($student->invoices as $inv) {
    echo "Invoice #{$inv->id} | Cat: {$inv->category} | Title: {$inv->title} | Source: {$inv->source_type} #{$inv->source_id} | Payable: {$inv->payable_amount} | Paid: {$inv->paid_amount} | Due: {$inv->due_amount} | Status: {$inv->status}" . PHP_EOL;
    if (!empty($inv->custom_particulars)) {
        echo "  Custom Particulars: " . json_encode($inv->custom_particulars, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}

echo PHP_EOL . "=== PAYMENTS ===" . PHP_EOL;
foreach ($student->payments as $p) {
    echo "Payment #{$p->id} | Inv: #{$p->invoice_id} | Amount: {$p->amount} | Method: {$p->payment_method} | Status: {$p->status}" . PHP_EOL;
}

exit(0);
