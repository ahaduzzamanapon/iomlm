<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Services\StudentFeeService;

$service = app(StudentFeeService::class);
$st = Student::find(1);
$data = $service->getStudentFeeBreakdown($st);

echo "Course: " . ($data['course']?->name ?? 'None') . " (Type: " . $data['courseType'] . ")\n";
echo "Selected Semester: " . ($data['selectedSemester']?->name ?? 'None') . " (ID: " . $data['selectedSemesterId'] . ")\n";
echo "Selected Semester Invoice: " . ($data['selectedSemesterInvoice']?->invoice_no ?? 'None') . " (ID: " . ($data['selectedSemesterInvoice']?->id ?? 'None') . ", Cat: " . ($data['selectedSemesterInvoice']?->category ?? 'None') . ", Paid: " . ($data['selectedSemesterInvoice']?->paid_amount ?? 0) . ")\n";
echo "Step 1 Particulars count: " . count($data['step1Particulars']) . "\n";
foreach ($data['step1Particulars'] as $p) {
    echo " - #{$p['sl']}: {$p['name']} | Amount: {$p['amount']}, Paid: {$p['paid_amt']}, Due: {$p['due']}, IsPaid: " . ($p['is_paid'] ? 'YES' : 'NO') . " (Inv ID: " . ($p['invoice_id'] ?? 'null') . ")\n";
}
