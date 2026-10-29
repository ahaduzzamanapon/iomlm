<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Batch;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "========================================================================\n";
echo "VERIFICATION: STUDENT LIST COPY TO CLIPBOARD FOR GOOGLE SHEETS (TASK 18)\n";
echo "========================================================================\n\n";

$passedTests = 0;
$totalTests = 0;

function assertTest($description, $condition) {
    global $passedTests, $totalTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo " [PASS] {$description}\n";
    } else {
        echo " [FAIL] {$description}\n";
        throw new \Exception("Assertion failed: {$description}");
    }
}

DB::beginTransaction();

try {
    // -------------------------------------------------------------
    // TEST 1: Blade View Inspection
    // -------------------------------------------------------------
    echo "\n--- TEST 1: Blade View Inspection ---\n";

    $viewContent = file_get_contents(resource_path('views/admin/students/index.blade.php'));

    assertTest("View contains header copy button (btnCopyStudents)", str_contains($viewContent, 'id="btnCopyStudents"'));
    assertTest("View contains filter copy button (btnCopyStudentsFilter)", str_contains($viewContent, 'id="btnCopyStudentsFilter"'));
    assertTest("Copy buttons call copyStudentListToClipboard()", str_contains($viewContent, 'onclick="copyStudentListToClipboard()"'));
    assertTest("View contains copyStudentListToClipboard JavaScript function", str_contains($viewContent, 'function copyStudentListToClipboard()'));
    assertTest("View contains fallbackCopyText for clipboard compatibility", str_contains($viewContent, 'function fallbackCopyText('));
    assertTest("View contains showCopyToast with Kalpurush font and success indicator", str_contains($viewContent, 'showCopyToast(') && str_contains($viewContent, 'Kalpurush'));

    // -------------------------------------------------------------
    // TEST 2: Route Registration Inspection
    // -------------------------------------------------------------
    echo "\n--- TEST 2: Route Registration ---\n";

    $hasRoute = \Illuminate\Support\Facades\Route::has('admin.students.copy-data');
    assertTest("Route 'admin.students.copy-data' is registered", $hasRoute);

    $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('admin.students.copy-data');
    assertTest("Route points to StudentController@copyData", str_contains($route->getActionName(), 'StudentController@copyData'));

    // -------------------------------------------------------------
    // TEST 3: Controller API & TSV Format Inspection
    // -------------------------------------------------------------
    echo "\n--- TEST 3: Controller copyData API Response & TSV Structure ---\n";

    $controller = new \App\Http\Controllers\Admin\StudentController();

    // Create sample student data
    $course = Course::first();
    if (!$course) {
        $course = Course::create(['name' => 'Test Copy Course', 'fee' => 5000, 'code' => 'TCC01']);
    }
    $batch = Batch::first();
    if (!$batch) {
        $batch = Batch::create(['name' => 'Batch Test Copy', 'course_id' => $course->id, 'start_date' => now()]);
    }

    $student = Student::create([
        'student_code' => 'ST-' . rand(10000, 99999),
        'name'         => 'মাজহারুল ইসলাম',
        'gender'       => 'MALE',
        'phone'        => '01819000000',
        'email'        => 'mazharul_' . Str::random(5) . '@iom.test',
        'status'       => 'ACTIVE',
        'blood_group'  => 'B+',
    ]);

    Enrollment::create([
        'student_id'  => $student->id,
        'course_id'   => $course->id,
        'batch_id'    => $batch->id,
        'status'      => 'ACTIVE',
        'enrolled_at' => now(),
    ]);

    $request = new Request();
    $response = $controller->copyData($request);

    assertTest("Response status is 200", $response->getStatusCode() === 200);

    $data = $response->getData(true);
    assertTest("Response contains success => true", ($data['success'] ?? false) === true);
    assertTest("Response contains count >= 1", ($data['count'] ?? 0) >= 1);
    assertTest("Response contains tsv string", !empty($data['tsv']));

    // Analyze TSV structure
    $lines = explode("\r\n", $data['tsv']);
    assertTest("TSV has at least 2 lines (header + data)", count($lines) >= 2);

    $headerCols = explode("\t", $lines[0]);
    assertTest("Header row has exactly 15 columns", count($headerCols) === 15);
    assertTest("Header contains 'ক্রমিক (SL)'", $headerCols[0] === 'ক্রমিক (SL)');
    assertTest("Header contains 'স্টুডেন্ট আইডি (Student Code)'", $headerCols[1] === 'স্টুডেন্ট আইডি (Student Code)');
    assertTest("Header contains 'পূর্ণ নাম (Full Name)'", $headerCols[2] === 'পূর্ণ নাম (Full Name)');
    assertTest("Header contains 'লিঙ্গ (Gender)'", $headerCols[3] === 'লিঙ্গ (Gender)');
    assertTest("Header contains 'মোবাইল নম্বর (Phone)'", $headerCols[4] === 'মোবাইল নম্বর (Phone)');

    // -------------------------------------------------------------
    // TEST 4: Filter Integrity (e.g. Filter by search or batch)
    // -------------------------------------------------------------
    echo "\n--- TEST 4: Filter Parameter Handling in copyData ---\n";

    $filterReq = new Request([
        'search' => 'মাজহারুল ইসলাম'
    ]);
    $filteredResponse = $controller->copyData($filterReq);
    $filteredData = $filteredResponse->getData(true);

    assertTest("Filtered copy contains search match", $filteredData['count'] >= 1);
    assertTest("TSV includes student name 'মাজহারুল ইসলাম'", str_contains($filteredData['tsv'], 'মাজহারুল ইসলাম'));
    assertTest("TSV includes student phone '01819000000'", str_contains($filteredData['tsv'], '01819000000'));
    assertTest("TSV includes course name", str_contains($filteredData['tsv'], $course->name));

    echo "\n========================================================================\n";
    echo "ALL TESTS PASSED: {$passedTests} / {$totalTests} (Exit Code 0)\n";
    echo "========================================================================\n";

} finally {
    DB::rollBack();
}
