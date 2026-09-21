<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\CourseTransfer;
use App\Models\Invoice;
use App\Models\User;
use App\Http\Controllers\Admin\CourseTransferController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

echo "======================================================\n";
echo "VERIFYING TASK 33: ADMIN MANUAL COURSE TRANSFER ACTION\n";
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
$route = Route::getRoutes()->getByName('admin.course-transfers.manual');
assertCheck($route !== null, "Route 'admin.course-transfers.manual' is registered.");
if ($route) {
    assertCheck(in_array('POST', $route->methods()), "Route method is POST.");
    assertCheck($route->uri() === 'admin/course-transfers/manual', "Route URI is 'admin/course-transfers/manual'.");
}

// Login as admin for auth context
$adminUser = User::where('role', 'SUPER_ADMIN')->orWhere('role', 'ADMIN')->first() ?? User::first();
Auth::login($adminUser);

// 2. Setup Test Data (2 Courses & 2 Batches)
$batchA = Batch::whereHas('course', fn($q) => $q->where('is_active', true))->first();
$courseA = $batchA->course;

$batchB = Batch::where('course_id', '!=', $courseA->id)
    ->whereHas('course', fn($q) => $q->where('is_active', true))
    ->first();

if (!$batchB) {
    $courseB = Course::where('id', '!=', $courseA->id)->where('is_active', true)->first();
    $batchB = Batch::create([
        'course_id'   => $courseB->id,
        'name'        => 'Batch B Test',
        'code'        => 'B-TEST-' . rand(100, 999),
        'batch_code'  => 'BT' . rand(10, 99),
        'start_date'  => now()->toDateString(),
        'status'      => 'ACTIVE'
    ]);
} else {
    $courseB = $batchB->course;
}

// Create or get a student for testing
$student = Student::where('email', 'test.transfer.student@example.com')->first();
if (!$student) {
    $user = User::create([
        'name' => 'Transfer Test Student',
        'email' => 'test.transfer.student@example.com',
        'password' => bcrypt('secret123'),
        'role' => 'STUDENT'
    ]);
    $student = Student::create([
        'user_id' => $user->id,
        'student_code' => '2026-TR-001',
        'name' => 'Transfer Test Student',
        'phone' => '01700999111',
        'email' => 'test.transfer.student@example.com',
        'gender' => 'MALE',
        'status' => 'ACTIVE'
    ]);
}

// Clear any existing active enrollments for a fresh state
Enrollment::where('student_id', $student->id)->update(['status' => 'TRANSFERRED']);

// Enroll in Course A
$initialEnrollment = Enrollment::create([
    'student_id' => $student->id,
    'course_id' => $courseA->id,
    'batch_id' => $batchA->id,
    'enrolled_at' => now(),
    'status' => 'ACTIVE',
]);

assertCheck($initialEnrollment->status === 'ACTIVE', "Initial enrollment in Course A is ACTIVE.");

// Test Case 1: Manual Transfer with IMMEDIATE execution and 0 fee
$controller = new CourseTransferController();

$req1 = Request::create('/admin/course-transfers/manual', 'POST', [
    'student_id'     => $student->id,
    'to_course_id'   => $courseB->id,
    'to_batch_id'    => $batchB->id,
    'transfer_fee'   => 0,
    'immediate'      => 1,
    'reason'         => 'Direct immediate manual transfer by admin',
    'admin_notes'    => 'Approved free by principal',
]);

$response1 = $controller->manualTransfer($req1);
assertCheck($response1->isRedirection(), "manualTransfer() returned a redirect response.");

$initialEnrollment->refresh();
assertCheck($initialEnrollment->status === 'TRANSFERRED', "Previous Course A enrollment marked as TRANSFERRED.");

$newEnrollment = Enrollment::where('student_id', $student->id)
    ->where('course_id', $courseB->id)
    ->where('batch_id', $batchB->id)
    ->where('status', 'ACTIVE')
    ->first();

assertCheck($newEnrollment !== null, "New enrollment in Course B is created and ACTIVE.");

$transferRecord1 = CourseTransfer::where('student_id', $student->id)
    ->where('from_course_id', $courseA->id)
    ->where('to_course_id', $courseB->id)
    ->latest()
    ->first();

assertCheck($transferRecord1 !== null, "CourseTransfer record was saved in database.");
assertCheck($transferRecord1->status === 'COMPLETED', "CourseTransfer record status is COMPLETED.");
assertCheck($transferRecord1->new_enrollment_id == $newEnrollment->id, "CourseTransfer links to new enrollment.");

// Test Case 2: Manual Transfer with Fee and Awaiting Payment (immediate = 0)
$req2 = Request::create('/admin/course-transfers/manual', 'POST', [
    'student_id'     => $student->id,
    'to_course_id'   => $courseA->id,
    'to_batch_id'    => $batchA->id,
    'transfer_fee'   => 750.00,
    'immediate'      => 0,
    'reason'         => 'Transfer back with fee required',
    'admin_notes'    => 'Student needs to pay 750 BDT at counter',
]);

$response2 = $controller->manualTransfer($req2);
assertCheck($response2->isRedirection(), "manualTransfer() with fee returned a redirect response.");

$transferRecord2 = CourseTransfer::where('student_id', $student->id)
    ->where('from_course_id', $courseB->id)
    ->where('to_course_id', $courseA->id)
    ->latest()
    ->first();

assertCheck($transferRecord2 !== null, "Second CourseTransfer record saved.");
assertCheck($transferRecord2->status === 'APPROVED_PENDING_PAYMENT', "Second transfer status is APPROVED_PENDING_PAYMENT.");
assertCheck($transferRecord2->transfer_fee == 750.00, "Transfer fee recorded as 750.00.");

// Verify Invoice created for transfer 2
$invoice = Invoice::where('student_id', $student->id)
    ->where('payable_amount', 750.00)
    ->latest()
    ->first();

assertCheck($invoice !== null, "Invoice of 750 BDT generated for student.");
if ($invoice) {
    assertCheck($invoice->status === 'UNPAID', "Generated invoice status is UNPAID.");
}

// Clean up test transfer records
$transferRecord1->delete();
$transferRecord2->delete();
if ($invoice) $invoice->delete();

echo "======================================================\n";
echo "SUMMARY: $passed / $total assertions passed.\n";
echo "======================================================\n";

if ($passed === $total) {
    exit(0);
} else {
    exit(1);
}
