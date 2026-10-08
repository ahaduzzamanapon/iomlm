<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Services\StudentFeeService;

$service = app(StudentFeeService::class);
$st = Student::find(1);

foreach ($st->enrollments as $enr) {
    $c = $enr->course;
    if (!$c) continue;
    echo "--- Student 1, Course {$c->id}: {$c->name} ({$c->type}) ---\n";
    $bd = $service->getStudentFeeBreakdown($st, null, $c->id);
    echo "Selected invoice: " . ($bd['selectedSemesterInvoice']?->invoice_no ?? 'NONE') . " (title: " . ($bd['selectedSemesterInvoice']?->title ?? '') . ", cat: " . ($bd['selectedSemesterInvoice']?->category ?? '') . ")\n";
    foreach ($bd['step1Particulars'] as $p) {
        echo "  - {$p['name']} | Amt: {$p['amount']}, Paid: {$p['paid_amt']}, Due: {$p['due']}, IsPaid: " . ($p['is_paid'] ? 'YES' : 'NO') . "\n";
    }
}
