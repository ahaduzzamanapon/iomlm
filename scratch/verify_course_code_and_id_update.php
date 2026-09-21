<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\Batch;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\CourseTransfer;
use App\Models\Readmission;
use App\Models\User;
use App\Services\CourseTransferService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "=== VERIFYING TASK 25: COURSE CODE, DEPARTMENT & DYNAMIC STUDENT ID UPDATES ===\n";

$assertions = 0;

function assertCondition($cond, $msg) {
    global $assertions;
    if (!$cond) {
        echo "❌ FAILED: $msg\n";
        exit(1);
    }
    echo "✅ PASSED: $msg\n";
    $assertions++;
}

// 1. Check Schema
assertCondition(Schema::hasColumn('courses', 'code'), "Table 'courses' has column 'code'");
assertCondition(Schema::hasColumn('courses', 'department'), "Table 'courses' has column 'department'");

// 2. Check Course Model & Helpers
$depts = Course::defaultDepartments();
assertCondition(in_array('BA in Dawah and Islamic Studies', $depts), "defaultDepartments contains 'BA in Dawah and Islamic Studies'");
assertCondition(in_array('School Maktab', $depts), "defaultDepartments contains 'School Maktab'");
assertCondition(in_array('Single Course', $depts), "defaultDepartments contains 'Single Course'");

// Clean up any previous test leftovers
DB::table('invoices')->whereIn('student_id', function($q) {
    $q->select('id')->from('students')->where('name', 'like', '%T25%');
})->delete();
DB::table('readmissions')->whereIn('student_id', function($q) {
    $q->select('id')->from('students')->where('name', 'like', '%T25%');
})->delete();
DB::table('course_transfers')->whereIn('student_id', function($q) {
    $q->select('id')->from('students')->where('name', 'like', '%T25%');
})->delete();
DB::table('enrollments')->whereIn('student_id', function($q) {
    $q->select('id')->from('students')->where('name', 'like', '%T25%');
})->delete();
Student::where('name', 'like', '%T25%')->delete();
Batch::where('name', 'like', '%T25%')->orWhereIn('batch_code', ['16', '22', '25'])->delete();
Course::where('name', 'like', '%T25%')->delete();

$testCourse = Course::create([
    'name'           => 'TEST_COURSE_T25',
    'code'           => '15',
    'department'     => 'Single Course',
    'type'           => 'SEMESTER_BASED',
    'duration_value' => 1,
    'duration_unit'  => 'YEAR',
    'admission_fee'   => 1000,
    'readmission_fee' => 500,
    'is_active'      => true,
]);

assertCondition($testCourse->formatted_code === '15', "Course formatted_code accessor returns '15'");
assertCondition($testCourse->department === 'Single Course', "Course department is 'Single Course'");

// 3. Test Course Controller CRUD
$adminUser = User::where('role', 'admin')->orWhere('role', 'super_admin')->first();
Auth::login($adminUser);

$controller = new \App\Http\Controllers\Admin\CourseController();

$updateReq = Request::create('/admin/courses/' . $testCourse->id, 'POST', [
    '_method'        => 'PUT',
    'name'           => 'TEST_COURSE_T25_UPDATED',
    'code'           => '09',
    'department'     => 'Nazera Course',
    'type'           => 'SEMESTER_BASED',
    'duration_value' => 2,
    'duration_unit'  => 'YEAR',
    'admission_fee'  => 1200,
    'readmission_fee'=> 600,
    'is_active'      => 1,
]);

$controller->update($updateReq, $testCourse);
$testCourse->refresh();
assertCondition($testCourse->name === 'TEST_COURSE_T25_UPDATED', "Course name updated via controller");
assertCondition($testCourse->formatted_code === '09', "Course code updated to '09'");
assertCondition($testCourse->department === 'Nazera Course', "Course department updated to 'Nazera Course'");

// 4. Test Student ID Format: Digits 5 & 6 represent Course Code
$testBatch = Batch::where('name', 'TEST_BATCH_T25_16')->first();
if (!$testBatch) {
    $testBatch = Batch::create([
        'course_id'   => $testCourse->id,
        'batch_code'  => '16',
        'name'        => 'TEST_BATCH_T25_16',
        'status'      => 'ACTIVE',
        'start_date'  => now(),
        'expected_end_date' => now()->addYear(),
    ]);
}

$testStudent = Student::create([
    'name'         => 'Test Student T25',
    'gender'       => 'MALE',
    'phone'        => '01711998877',
    'student_code' => '26160910099', // 26 (Year), 16 (Batch), 09 (Course), 1 (Gender), 0099 (Serial)
    'status'       => 'ACTIVE',
]);

// Digits 1-2: Year (26)
// Digits 3-4: Batch (16)
// Digits 5-6: Course (09)
// Digit 7: Gender (1)
// Digits 8-11: Serial (0099)
assertCondition(substr($testStudent->student_code, 0, 2) === '26', "Digits 1-2 are Year '26'");
assertCondition(substr($testStudent->student_code, 2, 2) === '16', "Digits 3-4 are Batch '16'");
assertCondition(substr($testStudent->student_code, 4, 2) === '09', "Digits 5-6 are Course Code '09'");
assertCondition(substr($testStudent->student_code, 6, 1) === '1', "Digit 7 is Gender '1'");
assertCondition(substr($testStudent->student_code, 7, 4) === '0099', "Digits 8-11 are Serial '0099'");

// 5. Test Course Transfer ID update (5th and 6th digits change to new course code, 3rd-4th to new batch)
$targetCourse = Course::create([
    'name'           => 'TARGET_COURSE_T25',
    'code'           => '01',
    'department'     => 'BA in Dawah and Islamic Studies',
    'type'           => 'SEMESTER_BASED',
    'duration_value' => 1,
    'duration_unit'  => 'YEAR',
    'is_active'      => true,
]);

$targetBatch = Batch::create([
    'course_id'         => $targetCourse->id,
    'batch_code'        => '22',
    'name'              => 'TARGET_BATCH_T25_22',
    'status'            => 'ACTIVE',
    'start_date'        => now(),
    'expected_end_date' => now()->addYear(),
]);

$transfer = CourseTransfer::create([
    'student_id'     => $testStudent->id,
    'from_course_id' => $testCourse->id,
    'from_batch_id'  => $testBatch->id,
    'to_course_id'   => $targetCourse->id,
    'to_batch_id'    => $targetBatch->id,
    'transfer_fee'   => 0,
    'status'         => 'PENDING',
]);

CourseTransferService::executeTransfer($transfer);
$testStudent->refresh();

assertCondition(substr($testStudent->student_code, 4, 2) === '01', "On Transfer, digits 5-6 updated to new course code '01'");
assertCondition(substr($testStudent->student_code, 2, 2) === '22', "On Transfer, digits 3-4 updated to target batch '22'");
assertCondition($testStudent->student_code === '26220110099', "Full Student ID correctly updated to 26220110099");

// 6. Test Readmission ID update (3rd and 4th digits change to new readmission batch)
$readmissionBatch = Batch::create([
    'course_id'         => $targetCourse->id,
    'batch_code'        => '25',
    'name'              => 'READMISSION_BATCH_T25_25',
    'status'            => 'ACTIVE',
    'start_date'        => now(),
    'expected_end_date' => now()->addYear(),
]);

$readmission = Readmission::create([
    'student_id'     => $testStudent->id,
    'course_id'      => $targetCourse->id,
    'from_batch_id'  => $targetBatch->id,
    'to_batch_id'    => $readmissionBatch->id,
    'readmission_fee'=> 500,
    'status'         => 'PENDING',
]);

$readmissionController = new \App\Http\Controllers\Admin\ReadmissionController();
$approveReq = Request::create('/admin/readmissions/' . $readmission->id . '/approve', 'POST', [
    'to_batch_id'     => $readmissionBatch->id,
    'readmission_fee' => 500,
    'notes'           => 'Approved for test batch',
]);

$readmissionController->approve($approveReq, $readmission);
$testStudent->refresh();

assertCondition(substr($testStudent->student_code, 2, 2) === '25', "On Readmission, digits 3-4 updated to new batch '25'");
assertCondition(substr($testStudent->student_code, 4, 2) === '01', "On Readmission, digits 5-6 retained course code '01'");
assertCondition($testStudent->student_code === '26250110099', "Full Student ID updated to 26250110099");

// 7. Check Public Admission Form passes coursesByDepartment
$publicController = new \App\Http\Controllers\Public\AdmissionFormController();
$publicView = $publicController->show();
assertCondition(isset($publicView->getData()['coursesByDepartment']), "AdmissionFormController passes coursesByDepartment to view");

// 8. Check Blade Templates
$courseIndexBlade = file_get_contents(__DIR__ . '/../resources/views/admin/courses/index.blade.php');
assertCondition(strpos($courseIndexBlade, 'formatted_code') !== false, "courses/index.blade.php displays formatted_code");
assertCondition(strpos($courseIndexBlade, 'department') !== false, "courses/index.blade.php displays department");
assertCondition(strpos($courseIndexBlade, 'name="code"') !== false, "courses/index.blade.php contains code input");

$applyBlade = file_get_contents(__DIR__ . '/../resources/views/apply/index.blade.php');
assertCondition(strpos($applyBlade, 'coursesByDepartment') !== false, "apply/index.blade.php iterates coursesByDepartment");
assertCondition(strpos($applyBlade, '<optgroup label="') !== false, "apply/index.blade.php contains optgroup for departments");

// Cleanup test records
DB::table('invoices')->where('student_id', $testStudent->id)->delete();
DB::table('readmissions')->where('student_id', $testStudent->id)->delete();
DB::table('course_transfers')->where('student_id', $testStudent->id)->delete();
DB::table('enrollments')->where('student_id', $testStudent->id)->delete();
$testStudent->delete();
$testBatch->delete();
$targetBatch->delete();
$readmissionBatch->delete();
$testCourse->delete();
$targetCourse->delete();

echo "\n🎉 ALL $assertions ASSERTIONS PASSED! EXIT CODE 0\n";
exit(0);
