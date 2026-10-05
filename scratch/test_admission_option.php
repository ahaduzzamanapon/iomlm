<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

auth()->loginUsingId(44);

$request = \Illuminate\Http\Request::create('/student/fees', 'GET', [
    'course_id' => 15,
    'semester_id' => 'admission',
]);
app()->instance('request', $request);

$controller = app()->make(\App\Http\Controllers\Student\FeeController::class);
$view = $controller->index($request);
$data = $view->getData();

echo "Selected Semester: " . ($data['selectedSemester']?->name ?? 'None') . "\n";
echo "Step 1 Particulars (Admission):\n";
foreach ($data['step1Particulars'] as $p) {
    $statusText = $p['is_paid'] ? "PAID (" . number_format($p['amount'], 0) . ")" : "DUE: " . number_format($p['due'], 0);
    echo " #SL {$p['sl']}: '{$p['name']}' => {$statusText} [Inv: " . ($p['invoice_id'] ?? 'none') . "]\n";
}
