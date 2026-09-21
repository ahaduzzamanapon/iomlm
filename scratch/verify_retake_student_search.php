<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Subject;
use App\Models\FinalMark;
use App\Models\SubjectRetake;
use App\Models\User;
use App\Http\Controllers\Admin\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

echo "======================================================\n";
echo "VERIFYING TASK 32: RETAKE STUDENT SEARCH & API\n";
echo "======================================================\n";

$passed = 0;
$total = 0;

function assertCheck($condition, $message) {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo " [PASS] $message\n";
    } else {
        echo " [FAIL] $message\n";
    }
}

// 1. Verify Route Registration
$route = Route::getRoutes()->getByName('admin.students.search-api');
assertCheck($route !== null, "Route 'admin.students.search-api' is registered.");
if ($route) {
    assertCheck(in_array('GET', $route->methods()), "Route method is GET.");
    assertCheck($route->uri() === 'admin/students/search-api', "Route URI is 'admin/students/search-api'.");
}

// 2. Test search API controller logic directly
$student = Student::first();
if (!$student) {
    $student = Student::create([
        'user_id'      => 1,
        'student_code' => '2026-TEST-99',
        'name'         => 'টেস্ট শিক্ষার্থী',
        'phone'        => '01711999888',
        'email'        => 'test.student.retake@example.com',
        'gender'       => 'MALE',
        'status'       => 'ACTIVE'
    ]);
}

$controller = new StudentController();

// Test A: Search by exact student code with hyphen
$req1 = Request::create('/admin/students/search-api', 'GET', ['q' => $student->student_code]);
$res1 = $controller->searchApi($req1);
$data1 = json_decode($res1->getContent(), true);
assertCheck(is_array($data1) && count($data1) > 0, "API returns results when searching by code with hyphen.");

// Test B: Search by student code WITHOUT hyphen
$codeNoHyphen = str_replace('-', '', $student->student_code);
$req2 = Request::create('/admin/students/search-api', 'GET', ['q' => $codeNoHyphen]);
$res2 = $controller->searchApi($req2);
$data2 = json_decode($res2->getContent(), true);
assertCheck(is_array($data2) && count($data2) > 0, "API returns results when searching without hyphen.");

// Test C: Verify response payload structure
$firstResult = $data2[0];
assertCheck(isset($firstResult['id']), "Result contains 'id'.");
assertCheck(isset($firstResult['student_code']), "Result contains 'student_code'.");
assertCheck(isset($firstResult['name']), "Result contains 'name'.");
assertCheck(isset($firstResult['course_name']), "Result contains 'course_name'.");
assertCheck(isset($firstResult['batch_name']), "Result contains 'batch_name'.");
assertCheck(isset($firstResult['failed_subjects']), "Result contains 'failed_subjects' array.");

// Test D: Search by name substring
$nameSubstring = mb_substr($student->name, 0, 3);
$req3 = Request::create('/admin/students/search-api', 'GET', ['q' => $nameSubstring]);
$res3 = $controller->searchApi($req3);
$data3 = json_decode($res3->getContent(), true);
assertCheck(is_array($data3) && count($data3) > 0, "API returns results searching by student name substring.");

// Test E: Empty search returns empty array
$reqEmpty = Request::create('/admin/students/search-api', 'GET', ['q' => '']);
$resEmpty = $controller->searchApi($reqEmpty);
$dataEmpty = json_decode($resEmpty->getContent(), true);
assertCheck(is_array($dataEmpty) && count($dataEmpty) === 0, "Empty query returns empty array.");

// Test F: Verify failed subjects mapping when student has failed mark
$subject = Subject::first();
$batch = \App\Models\Batch::first();
if ($subject && $batch) {
    FinalMark::updateOrCreate(
        ['student_id' => $student->id, 'subject_id' => $subject->id],
        [
            'batch_id' => $batch->id,
            'total_mark' => 25,
            'gpa' => 0.0,
            'grade' => 'F',
            'status' => 'FAIL'
        ]
    );

    $reqFail = Request::create('/admin/students/search-api', 'GET', ['q' => $student->student_code]);
    $resFail = $controller->searchApi($reqFail);
    $dataFail = json_decode($resFail->getContent(), true);
    $hasFail = false;
    foreach ($dataFail as $row) {
        if ($row['id'] == $student->id && count($row['failed_subjects']) > 0) {
            $hasFail = true;
            break;
        }
    }
    assertCheck($hasFail, "API returns failed subject for student with FAIL status.");
}

// Test G: Verify retake creation for this student
$retake = SubjectRetake::create([
    'student_id'  => $student->id,
    'subject_id'  => $subject ? $subject->id : 1,
    'retake_type' => 'EXAM_ONLY',
    'status'      => 'PENDING',
    'reason'      => 'Automated verification test note',
]);
assertCheck($retake->exists, "SubjectRetake created successfully in database.");
assertCheck($retake->status === 'PENDING', "Retake status is PENDING.");
$retake->delete(); // Clean up

echo "======================================================\n";
echo "SUMMARY: $passed / $total assertions passed.\n";
echo "======================================================\n";

if ($passed === $total) {
    exit(0);
} else {
    exit(1);
}
