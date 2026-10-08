<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Course;
use App\Services\StudentFeeService;

$service = app(StudentFeeService::class);

foreach ([1, 2, 3, 5, 98] as $sId) {
    $st = Student::find($sId);
    if (!$st) continue;
    foreach ($st->enrollments as $enr) {
        if ($enr->course?->type === 'SUBJECT_BASED') {
            echo "\n============================================\n";
            echo "Student ID {$st->id} ({$st->user?->name}), Course: {$enr->course?->name} (ID {$enr->course_id})\n";
            $data = $service->getStudentFeeBreakdown($st, null, $enr->course_id);
            echo "Selected Semester Invoice: " . ($data['selectedSemesterInvoice']?->invoice_no ?? 'NONE') . " (Cat: " . ($data['selectedSemesterInvoice']?->category ?? 'NONE') . ", Paid: " . ($data['selectedSemesterInvoice']?->paid_amount ?? 0) . ", Due: " . ($data['selectedSemesterInvoice']?->due_amount ?? 0) . ")\n";
            echo "Total Invoices for student: " . $data['invoices']->count() . "\n";
            foreach ($data['invoices'] as $iv) {
                echo "   * Inv #{$iv->id} [{$iv->category}] {$iv->title} — Amt: {$iv->amount}, Paid: {$iv->paid_amount}, Due: {$iv->due_amount}, Status: {$iv->status}\n";
            }
            echo "Step 1 Particulars count: " . count($data['step1Particulars']) . "\n";
            foreach ($data['step1Particulars'] as $p) {
                echo "   - {$p['name']} | Amt: {$p['amount']}, Paid: {$p['paid_amt']}, Due: {$p['due']}, IsPaid: " . ($p['is_paid'] ? 'PAID' : 'DUE') . " (Inv ID: " . ($p['invoice_id'] ?? 'null') . ")\n";
            }
        }
    }
}
