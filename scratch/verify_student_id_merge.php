<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\User;
use App\Http\Controllers\Admin\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "=== STARTING STUDENT ID MERGE & 1-LINE DISPLAY VERIFICATION ===\n\n";

// 1. Database Student Codes check
echo "1. Checking all student_code records in database...\n";
$dashedCodes = Student::whereNotNull('student_code')->where('student_code', 'like', '%-%')->get();
if ($dashedCodes->count() > 0) {
    echo "FAILED: Found " . $dashedCodes->count() . " student codes with dashes!\n";
    foreach ($dashedCodes as $d) {
        echo "  - ID: {$d->id}, Code: {$d->student_code}\n";
    }
    exit(1);
}

$sampleStudent = Student::whereNotNull('student_code')->first();
assert($sampleStudent !== null, "No students with student_code found in DB");
echo "  [OK] Zero student codes with dashes found. Sample merged code: '{$sampleStudent->student_code}'\n";

// 2. Search Integration check (both merged and hyphenated)
echo "2. Testing search with both merged code and hyphenated code...\n";
$controller = new StudentController();
$mergedCode = $sampleStudent->student_code;

// Search with merged code
$reqMerged = Request::create('/admin/students', 'GET', ['search' => $mergedCode]);
$respMerged = $controller->index($reqMerged);
$foundMerged = $respMerged->getData()['students'];
assert($foundMerged->total() >= 1, "Expected search by merged code to match");
echo "  [OK] Search by merged code '{$mergedCode}' matched successfully ({$foundMerged->total()} results).\n";

// Search with artificially hyphenated code
// e.g. if code is 26261510005, split into 26-26-15-1-0005
if (strlen($mergedCode) === 11) {
    $hyphenated = substr($mergedCode, 0, 2) . '-' . substr($mergedCode, 2, 2) . '-' . substr($mergedCode, 4, 2) . '-' . substr($mergedCode, 6, 1) . '-' . substr($mergedCode, 7);
    $reqHyphen = Request::create('/admin/students', 'GET', ['search' => $hyphenated]);
    $respHyphen = $controller->index($reqHyphen);
    $foundHyphen = $respHyphen->getData()['students'];
    assert($foundHyphen->total() >= 1, "Expected search by hyphenated code to match via cleanCode");
    echo "  [OK] Search by legacy hyphenated query '{$hyphenated}' matched successfully ({$foundHyphen->total()} results).\n";
}

// 3. Blade View Single-Line & No-Dash Styling check
echo "3. Testing Blade View single-line display & nowrap styling...\n";
$bladeContent = file_get_contents(resource_path('views/admin/students/index.blade.php'));

assert(str_contains($bladeContent, 'white-space: nowrap !important;'), "Missing 'white-space: nowrap !important;' on .student-code-badge");
assert(str_contains($bladeContent, 'white-space:nowrap">স্টুডেন্ট আইডি'), "Missing white-space:nowrap on table header");
assert(str_contains($bladeContent, 'padding:14px 16px;white-space:nowrap'), "Missing white-space:nowrap on table cell td");
assert(str_contains($bladeContent, "str_replace('-', '', \$st->student_code)"), "Missing str_replace to ensure no hyphens");
assert(!str_contains($bladeContent, 'fa-arrow-right-to-bracket'), "Font-dependent bracket icon should be replaced with crisp SVG");
echo "  [OK] Blade file has white-space nowrap on badge, th, and td, and SVG icon.\n";

// 4. Render Blade View and assert HTML output
echo "4. Testing Blade View Rendering with Auth user...\n";
$admin = User::first();
Auth::login($admin);
$renderReq = Request::create('/admin/students', 'GET');
$renderedHtml = $controller->index($renderReq)->render();

assert(str_contains($renderedHtml, $mergedCode), "Rendered HTML does not contain merged student code");
assert(str_contains($renderedHtml, 'white-space:nowrap'), "Rendered HTML missing nowrap attribute");
echo "  [OK] Blade view rendered with single-line merged student code.\n";

// 5. CSV Export check
echo "5. Testing CSV Export with merged student code...\n";
$exportReq = Request::create('/admin/students/export-csv', 'GET');
$exportResp = $controller->exportCsv($exportReq);
ob_start();
$exportResp->sendContent();
$csvContent = ob_get_clean();

assert(str_contains($csvContent, $mergedCode), "CSV Export missing merged student code");
echo "  [OK] CSV Export contains merged student code without hyphens.\n";

echo "\n======================================================\n";
echo ">>> ALL STUDENT ID MERGE & 1-LINE TESTS PASSED! <<<\n";
echo "======================================================\n";
exit(0);
