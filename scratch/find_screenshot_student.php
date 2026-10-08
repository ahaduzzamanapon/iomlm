<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Services\StudentFeeService;

$feeService = app(StudentFeeService::class);
foreach (Student::all() as $st) {
    try {
        $bd = $feeService->getStudentFeeBreakdown($st);
        $names = array_column($bd['step1Particulars'] ?? [], 'name');
        if (in_array('Tuition Fee (Jan-2027)', $names, true)) {
            echo "MATCH Student ID: {$st->id}, Name: {$st->user?->name}\n";
            foreach ($bd['step1Particulars'] as $p) {
                if ($p['is_paid']) {
                    echo "  PAID item: {$p['name']} | amount: {$p['amount']} | paid_amt: {$p['paid_amt']} | invoice_id: " . ($p['invoice_id'] ?? 'null') . "\n";
                }
            }
        }
    } catch (\Throwable $e) {}
}
