<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

auth()->loginUsingId(44);

$request = \Illuminate\Http\Request::create('/student/fees', 'GET', [
    'course_id' => 15,
    'semester_id' => 53,
]);
app()->instance('request', $request);

$controller = app()->make(\App\Http\Controllers\Student\FeeController::class);
$view = $controller->index($request);
$data = $view->getData();

echo "Semester Breakdown:\n";
foreach ($data['semesterBreakdown'] as $row) {
    echo "Semester: {$row['label']}, monthlyRate: {$row['monthlyRate']}, totalMonths: {$row['totalMonths']}\n";
    if (!empty($row['monthlyItems'])) {
        foreach ($row['monthlyItems'] as $m) {
            echo "   - {$m['label']} => payable: {$m['payable']}, status: {$m['status']}\n";
        }
    }
}
