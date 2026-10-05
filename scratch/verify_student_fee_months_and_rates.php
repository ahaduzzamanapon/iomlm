<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = \App\Models\Student::find(24);
if (!$student) {
    echo "ERROR: Student 24 not found.\n";
    exit(1);
}

auth()->loginUsingId($student->user_id);

echo "========================================================\n";
echo "VERIFICATION TEST: Student Fee Months, Rates & Admission\n";
echo "========================================================\n";

// TEST 1: Request Semester 1 (default view)
$request1 = \Illuminate\Http\Request::create('/student/fees', 'GET', [
    'course_id'   => 15,
    'semester_id' => 53,
]);
app()->instance('request', $request1);

$controller = app()->make(\App\Http\Controllers\Student\FeeController::class);
$view1 = $controller->index($request1);
$data1 = $view1->getData();

$particulars1 = collect($data1['step1Particulars']);

// Check Admission Fee exists in Semester 1
$admissionItem = $particulars1->first(function ($p) {
    return str_contains(mb_strtolower($p['name']), 'admission') || str_contains($p['name'], 'ভর্তি');
});
if (!$admissionItem) {
    echo "FAILED: Admission Fee not found in Semester 1 particulars.\n";
    exit(1);
}
echo "✓ CHECK 1 PASSED: Admission Fee exists in Semester 1 particulars ('{$admissionItem['name']}', Amount: {$admissionItem['amount']}, Paid: " . ($admissionItem['is_paid'] ? 'YES' : 'NO') . ")\n";

// Check Tuition Fee Months are Jan-2027 through Jun-2027
$expectedMonths = ['Jan-2027', 'Feb-2027', 'Mar-2027', 'Apr-2027', 'May-2027', 'Jun-2027'];
$foundMonths = [];
foreach ($particulars1 as $p) {
    if (preg_match('/Tuition Fee \((.+)\)/', $p['name'], $m)) {
        $foundMonths[] = $m[1];
        if ($p['amount'] != 100) {
            echo "FAILED: Expected 100 Tk for '{$p['name']}', got {$p['amount']}\n";
            exit(1);
        }
    }
}

if ($foundMonths !== $expectedMonths) {
    echo "FAILED: Expected months " . implode(', ', $expectedMonths) . ", but got " . implode(', ', $foundMonths) . "\n";
    exit(1);
}
echo "✓ CHECK 2 PASSED: Tuition Fee months are Jan-2027 through Jun-2027 (Found: " . implode(', ', $foundMonths) . ")\n";
echo "✓ CHECK 3 PASSED: Every monthly tuition fee rate is exactly 100 Tk (Not 124 Tk).\n";

// Check Mid Term Fee and Final Term Fee rates
$midItem = $particulars1->first(fn($p) => str_contains($p['name'], 'Mid Term'));
$finalItem = $particulars1->first(fn($p) => str_contains($p['name'], 'Final Term'));

if (!$midItem || $midItem['amount'] != 10) {
    echo "FAILED: Expected Mid Term Fee to be 10 Tk, got " . ($midItem['amount'] ?? 'null') . "\n";
    exit(1);
}
echo "✓ CHECK 4 PASSED: Mid Term Fee is exactly 10 Tk.\n";

if (!$finalItem || $finalItem['amount'] != 17) {
    echo "FAILED: Expected Final Term Fee to be 17 Tk, got " . ($finalItem['amount'] ?? 'null') . "\n";
    exit(1);
}
echo "✓ CHECK 5 PASSED: Final Term Fee is exactly 17 Tk.\n";

// TEST 2: Request Admission Fee view
$request2 = \Illuminate\Http\Request::create('/student/fees', 'GET', [
    'course_id'   => 15,
    'semester_id' => 'admission',
]);
app()->instance('request', $request2);
$view2 = $controller->index($request2);
$data2 = $view2->getData();

$particulars2 = collect($data2['step1Particulars']);
$admOnlyItem = $particulars2->first(fn($p) => str_contains(mb_strtolower($p['name']), 'admission'));

if (!$admOnlyItem || $admOnlyItem['amount'] <= 0) {
    echo "FAILED: Admission fee view failed to return item.\n";
    exit(1);
}
echo "✓ CHECK 6 PASSED: Admission Fee dropdown view works properly ('{$admOnlyItem['name']}', Amount: {$admOnlyItem['amount']}).\n";

// TEST 3: Check Dropdown options include Admission Fee
$dropdownOptions = collect($data1['semesterDropdownOptions']);
$admOption = $dropdownOptions->firstWhere('id', 'admission');
if (!$admOption) {
    echo "FAILED: Dropdown options do not contain 'admission' option.\n";
    exit(1);
}
echo "✓ CHECK 7 PASSED: Dropdown contains Admission option ('{$admOption['label']}').\n";

// TEST 4: Check Semester 2 months in semesterBreakdown are Jul-2027 to Dec-2027
$breakdown = collect($data1['semesterBreakdown']);
$sem2 = $breakdown->first(fn($r) => str_contains($r['label'], 'Semester 2'));
$sem2Months = [];
foreach ($sem2['monthlyItems'] as $it) {
    if (preg_match('/\((.+)\)/', $it['label'], $m)) {
        $sem2Months[] = $m[1];
    }
}
$expectedSem2Months = ['Jul-2027', 'Aug-2027', 'Sep-2027', 'Oct-2027', 'Nov-2027', 'Dec-2027'];
if ($sem2Months !== $expectedSem2Months) {
    echo "FAILED: Expected Semester 2 months " . implode(', ', $expectedSem2Months) . ", got " . implode(', ', $sem2Months) . "\n";
    exit(1);
}
echo "✓ CHECK 8 PASSED: Semester 2 breakdown shows Jul-2027 to Dec-2027.\n";

// TEST 5: Render HTML and verify badges and text
$html = $view1->render();
if (!str_contains($html, 'Academic Year 2027')) {
    echo "FAILED: Academic Year 2027 badge not found in rendered HTML.\n";
    exit(1);
}
if (!str_contains($html, 'Jan-2027') || !str_contains($html, 'Jun-2027')) {
    echo "FAILED: Jan-2027 or Jun-2027 not found in rendered HTML.\n";
    exit(1);
}
echo "✓ CHECK 9 PASSED: Rendered HTML contains Academic Year badge ('Academic Year 2027') and fee months ('Jan-2027' to 'Jun-2027').\n";

echo "\nALL 9 CHECKS PASSED SUCCESSFULLY WITH EXIT CODE 0!\n";
exit(0);
