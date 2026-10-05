<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 1. Update Student 24 fee_package_id to 19
$student = \App\Models\Student::find(24);
if ($student) {
    $student->fee_package_id = 19;
    $student->save();
    echo "Updated Student 24: fee_package_id = 19\n";
}

// 2. Update Invoice 63 (Admission Fee)
$inv63 = \App\Models\Invoice::find(63);
if ($inv63) {
    $inv63->amount = 1500.00;
    $inv63->discount = 1500.00;
    $inv63->payable_amount = 0.00;
    $inv63->paid_amount = 0.00;
    $inv63->due_amount = 0.00;
    $inv63->status = 'PAID';
    $inv63->save();
    echo "Updated Invoice 63: amount=1500, discount=1500, payable=0, status=PAID\n";
}

// 3. Update Invoice 64 (Semester 1 Tuition Fee)
$inv64 = \App\Models\Invoice::find(64);
if ($inv64) {
    $inv64->amount = 627.00;
    $inv64->discount = 0.00;
    $inv64->payable_amount = 627.00;
    $inv64->paid_amount = 0.00;
    $inv64->due_amount = 627.00;
    $inv64->source_type = \App\Models\Semester::class;
    $inv64->source_id = 53;
    $inv64->save();
    echo "Updated Invoice 64: amount=627, payable=627, due=627, source_id=53 (Semester 1)\n";
}

// 4. Test calling FeeController@index logic for Student 24
auth()->loginUsingId(44); // Student 24 user_id is 44

$request = \Illuminate\Http\Request::create('/student/fees', 'GET', [
    'course_id' => 15,
    'semester_id' => 53,
]);
app()->instance('request', $request);

$controller = app()->make(\App\Http\Controllers\Student\FeeController::class);
$view = $controller->index($request);
$data = $view->getData();

echo "\nFeeController Index Data Check:\n";
echo "Selected Semester: " . ($data['selectedSemester']?->name ?? 'None') . "\n";
echo "Monthly Tuition: " . ($data['monthlyTuition'] ?? 'None') . "\n";

echo "\nDropdown Options:\n";
foreach ($data['semesterDropdownOptions'] as $opt) {
    echo " - ID: {$opt['id']}, Label: '{$opt['label']}', Due: {$opt['due']}\n";
}

echo "\nStep 1 Particulars (Semester 1):\n";
foreach ($data['step1Particulars'] as $p) {
    $statusText = $p['is_paid'] ? "PAID (" . number_format($p['amount'], 0) . ")" : "DUE: " . number_format($p['due'], 0);
    echo " #SL {$p['sl']}: '{$p['name']}' => {$statusText} [Inv: " . ($p['invoice_id'] ?? 'none') . "]\n";
}
