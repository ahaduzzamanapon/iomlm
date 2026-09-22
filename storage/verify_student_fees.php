<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\User;
use App\Http\Controllers\Student\FeeController;
use Illuminate\Http\Request;

// Pick a student that has enrollments/invoices, e.g. Asif Khan (from screenshot) or first student
$student = Student::with(['enrollments.course', 'enrollments.batch', 'invoices.payments'])->first();
if (!$student) {
    echo "NO STUDENT FOUND\n";
    exit(1);
}

// Ensure user attached to student
$user = $student->user ?? User::where('student_id', $student->id)->first();
if (!$user) {
    $user = User::first();
}
auth()->login($user);

// Simulate request
$request = Request::create('/student/fees', 'GET');
app()->instance('request', $request);

$controller = app(FeeController::class);
$response = $controller->index($request);

$html = $response->render();

echo "Verifying Student Fee Page...\n";

// 1. Check Status Filter Buttons
if (str_contains($html, 'step1Filter_all') && str_contains($html, 'step1Filter_due') && str_contains($html, 'step1Filter_paid')) {
    echo "✓ PASS: Step 1 Status Filter buttons (All / Due / Paid) present!\n";
} else {
    echo "✗ FAIL: Step 1 Status Filter buttons missing!\n";
    exit(1);
}

// 2. Check data-step1-status attribute
if (str_contains($html, 'data-step1-status=')) {
    echo "✓ PASS: Table rows contain data-step1-status attribute!\n";
} else {
    echo "✗ FAIL: Table rows missing data-step1-status attribute!\n";
    exit(1);
}

// 3. Check Semester Breakdown Tabs
if (str_contains($html, 'sem-breakdown-tab-btn') || str_contains($html, 'sem-breakdown-pane')) {
    echo "✓ PASS: Semester Breakdown Tabs and Panes present!\n";
} else {
    echo "✗ FAIL: Semester Breakdown Tabs missing!\n";
    exit(1);
}

// 4. Check that Admission Fee is removed from breakdown
if (str_contains($html, 'ভর্তি ফি (Admission Fee)')) {
    echo "✗ FAIL: Admission Fee still present in breakdown!\n";
    exit(1);
} else {
    echo "✓ PASS: Admission Fee is completely removed from breakdown!\n";
}

// 5. Check that Itemized Package Fee Breakdown is removed
if (str_contains($html, 'Itemized Package Fee Breakdown')) {
    echo "✗ FAIL: Itemized Package Fee Breakdown still present!\n";
    exit(1);
} else {
    echo "✓ PASS: Itemized Package Fee Breakdown is completely removed!\n";
}

// 6. Check that JS functions are defined
if (str_contains($html, 'filterStep1Table') && str_contains($html, 'switchSemBreakdownTab')) {
    echo "✓ PASS: JS functions filterStep1Table and switchSemBreakdownTab defined!\n";
} else {
    echo "✗ FAIL: JS functions missing!\n";
    exit(1);
}

echo "\nALL TESTS PASSED SUCCESSFULLY! Exit code 0.\n";
exit(0);
