<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\StudentController;
use App\Models\Student;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Semester;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

echo "=== STARTING ADVANCED STUDENT SEARCH & FILTER & EXPORT VERIFICATION ===\n\n";

$controller = new StudentController();

// 1. Basic index loading
echo "1. Testing StudentController@index without filters...\n";
$req = Request::create('/admin/students', 'GET');
$response = $controller->index($req);

if (!($response instanceof \Illuminate\View\View)) {
    echo "FAILED: index did not return View instance\n";
    exit(1);
}

$viewData = $response->getData();
assert(isset($viewData['students']), "Students collection missing in view data");
assert(isset($viewData['courses']), "Courses missing in view data");
assert(isset($viewData['batches']), "Batches missing in view data");
assert(isset($viewData['semesters']), "Semesters missing in view data");
assert(isset($viewData['bloodGroups']), "Blood groups missing in view data");
assert(isset($viewData['totalCount']), "Total count missing in view data");
echo "  [OK] Default index returned View with all required datasets.\n";

// 2. Keyword Search filter
echo "2. Testing search query keyword filter...\n";
$sampleStudent = Student::first();
if ($sampleStudent) {
    // Search by partial name
    $searchReq = Request::create('/admin/students', 'GET', ['search' => substr($sampleStudent->name, 0, 4)]);
    $searchResp = $controller->index($searchReq);
    $foundStudents = $searchResp->getData()['students'];
    echo "  Found " . $foundStudents->total() . " students matching '" . substr($sampleStudent->name, 0, 4) . "'.\n";
    assert($foundStudents->total() >= 1, "Expected at least 1 student matching name");

    // Search by student code if available
    if ($sampleStudent->student_code) {
        $codeReq = Request::create('/admin/students', 'GET', ['search' => $sampleStudent->student_code]);
        $codeResp = $controller->index($codeReq);
        $codeFound = $codeResp->getData()['students'];
        assert($codeFound->total() >= 1, "Expected student code to match");
        echo "  [OK] Search by student_code successfully matched.\n";
    }

    // Search by phone
    if ($sampleStudent->phone) {
        $phoneReq = Request::create('/admin/students', 'GET', ['search' => substr($sampleStudent->phone, -4)]);
        $phoneResp = $controller->index($phoneReq);
        $phoneFound = $phoneResp->getData()['students'];
        assert($phoneFound->total() >= 1, "Expected phone to match");
        echo "  [OK] Search by phone successfully matched.\n";
    }
}
echo "  [OK] Keyword search passed.\n";

// 3. Gender filter
echo "3. Testing Gender filter...\n";
$maleReq = Request::create('/admin/students', 'GET', ['gender' => 'MALE']);
$maleResp = $controller->index($maleReq);
$maleStudents = $maleResp->getData()['students'];
foreach ($maleStudents as $st) {
    assert(strtoupper($st->gender) === 'MALE', "Gender must be MALE");
}
echo "  [OK] Gender filter passed (" . $maleStudents->total() . " male students).\n";

// 4. Course, Batch & Semester filters
echo "4. Testing Course, Batch & Semester filters...\n";
$sampleEnrollment = Enrollment::with('batch.course')->first();
if ($sampleEnrollment) {
    $courseId = $sampleEnrollment->course_id ?? $sampleEnrollment->batch?->course_id;
    if ($courseId) {
        $courseReq = Request::create('/admin/students', 'GET', ['course_id' => $courseId]);
        $courseResp = $controller->index($courseReq);
        $courseStudents = $courseResp->getData()['students'];
        echo "  Found " . $courseStudents->total() . " students in Course ID: {$courseId}.\n";
        assert($courseStudents->total() >= 1, "Expected at least 1 student for course");
        echo "  [OK] Course filter passed.\n";
    }

    $batchId = $sampleEnrollment->batch_id;
    if ($batchId) {
        $batchReq = Request::create('/admin/students', 'GET', ['batch_id' => $batchId]);
        $batchResp = $controller->index($batchReq);
        $batchStudents = $batchResp->getData()['students'];
        echo "  Found " . $batchStudents->total() . " students in Batch ID: {$batchId}.\n";
        assert($batchStudents->total() >= 1, "Expected at least 1 student for batch");
        echo "  [OK] Batch filter passed.\n";
    }
}

// 5. Multi-Filter Combination Test
echo "5. Testing Multi-filter combination...\n";
$multiReq = Request::create('/admin/students', 'GET', [
    'gender' => 'MALE',
    'status' => 'ACTIVE',
]);
$multiResp = $controller->index($multiReq);
$multiStudents = $multiResp->getData()['students'];
foreach ($multiStudents as $st) {
    assert(strtoupper($st->gender) === 'MALE', "Gender must match MALE");
    assert(strtoupper($st->status) === 'ACTIVE', "Status must match ACTIVE");
}
echo "  [OK] Combined filters passed (" . $multiStudents->total() . " active male students).\n";

// 6. CSV Export Stream & UTF-8 BOM Verification
echo "6. Testing CSV Export endpoint (exportCsv)...\n";
$exportReq = Request::create('/admin/students/export-csv', 'GET', ['gender' => 'MALE']);
$exportResp = $controller->exportCsv($exportReq);

if (!($exportResp instanceof StreamedResponse)) {
    echo "FAILED: exportCsv did not return StreamedResponse\n";
    exit(1);
}

// Capture streamed output
ob_start();
$exportResp->sendContent();
$csvContent = ob_get_clean();

// Check UTF-8 BOM
$bom = substr($csvContent, 0, 3);
if ($bom !== "\xEF\xBB\xBF") {
    echo "FAILED: CSV output missing UTF-8 BOM (\\xEF\\xBB\\xBF) for Excel Bengali support!\n";
    exit(1);
}
echo "  [OK] UTF-8 BOM present at start of CSV stream.\n";

// Check Header Row
$lines = explode("\n", substr($csvContent, 3));
$headerLine = $lines[0];
echo "  Header line: " . trim($headerLine) . "\n";
assert(str_contains($headerLine, 'স্টুডেন্ট আইডি (Student Code)'), "Header missing Student Code");
assert(str_contains($headerLine, 'পূর্ণ নাম (Full Name)'), "Header missing Full Name");
assert(str_contains($headerLine, 'লিঙ্গ (Gender)'), "Header missing Gender");
assert(str_contains($headerLine, 'মোবাইল নম্বর (Phone)'), "Header missing Phone");
assert(str_contains($headerLine, 'এনআইডি / জন্ম নিবন্ধন (NID)'), "Header missing NID");
assert(str_contains($headerLine, 'এনরোল্ড কোর্স (Course)'), "Header missing Course");
assert(str_contains($headerLine, 'ব্যাচ (Batch)'), "Header missing Batch");
assert(str_contains($headerLine, 'বর্তমান সেমিস্টার (Semester)'), "Header missing Semester");
assert(str_contains($headerLine, 'একাডেমিক স্ট্যাটাস (Status)'), "Header missing Status");
echo "  [OK] CSV Headers verified.\n";

// Check data rows
$dataRowCount = 0;
for ($i = 1; $i < count($lines); $i++) {
    if (trim($lines[$i]) !== '') {
        $dataRowCount++;
    }
}
echo "  [OK] CSV Export generated {$dataRowCount} filtered student data rows.\n";

// Combined filter export check
$exportReqMulti = Request::create('/admin/students/export-csv', 'GET', ['gender' => 'MALE', 'status' => 'ACTIVE']);
$exportRespMulti = $controller->exportCsv($exportReqMulti);
ob_start();
$exportRespMulti->sendContent();
$csvMultiContent = ob_get_clean();
$multiLines = explode("\n", substr($csvMultiContent, 3));
$multiRowCount = 0;
for ($i = 1; $i < count($multiLines); $i++) {
    if (trim($multiLines[$i]) !== '') {
        $multiRowCount++;
    }
}
assert($multiRowCount === $multiStudents->total(), "CSV row count ($multiRowCount) must match filtered student query count ({$multiStudents->total()})");
echo "  [OK] Filtered CSV row count exactly matches query result count ($multiRowCount rows).\n";

// 7. Blade View Rendering Verification
echo "7. Testing Blade View Rendering for admin.students.index...\n";
$adminUser = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($adminUser);
$html = $response->render();
assert(str_contains($html, 'Kalpurush'), "View missing 'Kalpurush' font declaration");
assert(str_contains($html, 'course_filter'), "View missing course_filter");
assert(str_contains($html, 'batch_filter'), "View missing batch_filter");
assert(str_contains($html, 'semester_filter'), "View missing semester_filter");
assert(str_contains($html, 'admin/students/export-csv'), "View missing export-csv link");
assert(str_contains($html, 'ফিল্টার প্রয়োগ করুন'), "View missing Bengali filter button");
assert(str_contains($html, 'রিসেট'), "View missing reset button");
echo "  [OK] Blade view rendered with Kalpurush font, interactive filters, export button, and full table.\n";

echo "\n=======================================================\n";
echo ">>> ALL ADVANCED STUDENT SEARCH & FILTER TESTS PASSED! <<<\n";
echo "=======================================================\n";
exit(0);
