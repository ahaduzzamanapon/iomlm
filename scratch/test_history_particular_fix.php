<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = App\Models\Student::find(12);
if (!$student) {
    echo "Student 12 not found!\n";
    exit(1);
}

$feeService = app(\App\Services\StudentFeeService::class);
$feeData = $feeService->getStudentFeeBreakdown($student, 12, 39);

$particularNames = array_column($feeData['step1Particulars'], 'name');
echo "Particulars count: " . count($particularNames) . "\n";
echo "Particulars: " . implode(', ', $particularNames) . "\n";

if (in_array('_history', $particularNames, true)) {
    echo "FAILED: '_history' is still present in particulars!\n";
    exit(1);
}

echo "SUCCESS: '_history' is cleanly excluded from particulars!\n";
exit(0);
