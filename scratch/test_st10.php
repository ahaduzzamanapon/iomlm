<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Services\StudentFeeService;

$service = app(StudentFeeService::class);
$st = Student::find(10);
if ($st) {
    echo "--- Student 10 ({$st->user?->name}) ---\n";
    foreach ($st->enrollments as $enr) {
        $c = $enr->course;
        echo "Course: {$c?->name} (Type: {$c?->type}, ID: {$c?->id})\n";
        $bd = $service->getStudentFeeBreakdown($st, null, $c?->id);
        echo "Selected invoice: " . ($bd['selectedSemesterInvoice']?->invoice_no ?? 'NONE') . " (title: " . ($bd['selectedSemesterInvoice']?->title ?? '') . ", cat: " . ($bd['selectedSemesterInvoice']?->category ?? '') . ", amt: " . ($bd['selectedSemesterInvoice']?->payable_amount ?? 0) . ", paid: " . ($bd['selectedSemesterInvoice']?->paid_amount ?? 0) . ")\n";
        foreach ($bd['step1Particulars'] as $p) {
            echo "  - #{$p['sl']}: {$p['name']} | Amt: {$p['amount']}, Paid: {$p['paid_amt']}, Due: {$p['due']}, IsPaid: " . ($p['is_paid'] ? 'PAID' : 'DUE') . "\n";
        }
    }
}
