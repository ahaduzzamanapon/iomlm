<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use App\Http\Controllers\Student\FeeController;
use Illuminate\Http\Request;

echo "\n>>> Testing Standard Package Student (Student 17) <<<\n";
$st17 = Student::find(17);
auth()->loginUsingId($st17->user_id);
$controller = app()->make(FeeController::class);

$req3 = Request::create('/student/fees', 'GET', ['course_id' => 15, 'semester_id' => 55]);
app()->instance('request', $req3);
$view3 = $controller->index($req3);
$data3 = $view3->getData();
$parts3 = collect($data3['step1Particulars']);

$ann1 = $parts3->first(fn($p) => str_contains($p['name'], 'Annual Fee') || str_contains($p['name'], 'বার্ষিক'));
echo "Standard Package Semester 3 Annual Fee: " . ($ann1['name'] ?? 'none') . " | Amount: " . ($ann1['amount'] ?? 'null') . "\n";
if (!$ann1 || $ann1['amount'] != 1000) {
    echo "FAILED: Standard package expected 1000 Annual fee, got " . ($ann1['amount'] ?? 'null') . "\n";
    exit(1);
}
echo "✓ Standard Package Semester 3: 1st Annual Fee is 1000 Tk\n";

$tuitionRow = $parts3->first(fn($p) => str_contains($p['name'], 'Tuition Fee'));
echo "Standard Package Monthly Tuition Rate: " . ($tuitionRow['amount'] ?? 'null') . "\n";
if (!$tuitionRow || $tuitionRow['amount'] != 500) {
    echo "FAILED: Standard package expected 500 tuition rate, got " . ($tuitionRow['amount'] ?? 'null') . "\n";
    exit(1);
}
echo "✓ Standard Package Monthly Tuition Rate is 500 Tk\n";

echo "ALL STANDARD PACKAGE CHECKS PASSED!\n";
exit(0);
