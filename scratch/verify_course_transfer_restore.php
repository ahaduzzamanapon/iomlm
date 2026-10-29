<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\Batch;
use App\Models\CourseTransfer;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

echo "========================================================\n";
echo "VERIFICATION: COURSE TRANSFER CANCEL AND RESTORE\n";
echo "========================================================\n\n";

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
    // TEST 1: Blade Views Inspection
    // -------------------------------------------------------------
    echo "\n--- TEST 1: Blade View Inspection ---\n";

    $studentView = file_get_contents(resource_path('views/student/course-transfers/index.blade.php'));
    $adminView = file_get_contents(resource_path('views/admin/course-transfers/index.blade.php'));

    assertTest("Student view has student.course-transfers.restore route", str_contains($studentView, 'route(\'student.course-transfers.restore\''));
    assertTest("Student view has 'রিস্টোর করুন' button", str_contains($studentView, 'রিস্টোর করুন'));
    assertTest("Student view has 'পূর্বের আবেদন রিস্টোর করুন' notice", str_contains($studentView, 'পূর্বের আবেদন রিস্টোর করুন'));
    assertTest("Student view has 'বাতিলকৃত' badge", str_contains($studentView, 'বাতিলকৃত'));

    assertTest("Admin view has admin.course-transfers.restore route", str_contains($adminView, 'route(\'admin.course-transfers.restore\''));
    assertTest("Admin view has 'রিস্টোর' action button", str_contains($adminView, 'রিস্টোর'));
    assertTest("Admin view has CANCELLED_OR_REJECTED filter", str_contains($adminView, 'CANCELLED_OR_REJECTED'));
    assertTest("Admin view supports 'বাতিলকৃত' for CANCELLED status", str_contains($adminView, 'বাতিলকৃত'));

    // -------------------------------------------------------------
    // TEST 2: Setup Test Data
    // -------------------------------------------------------------
    echo "\n--- TEST 2: Setup Test Data ---\n";

    $user = User::factory()->create(['role' => 'STUDENT']);
    $student = Student::create([
        'user_id'      => $user->id,
        'student_code' => 'TEST-TR-' . rand(1000, 9999),
        'name'         => 'Transfer Test Student',
        'phone'        => '017' . rand(10000000, 99999999),
        'gender'       => 'MALE',
        'status'       => 'ACTIVE',
    ]);

    $course1 = Course::create(['name' => 'From Course ' . rand(10, 99), 'code' => 'FC' . rand(10, 99), 'is_active' => true]);
    $course2 = Course::create(['name' => 'To Course ' . rand(10, 99), 'code' => 'TC' . rand(10, 99), 'is_active' => true]);
    $batch1 = Batch::create(['course_id' => $course1->id, 'name' => 'Batch FC', 'status' => 'ACTIVE', 'start_date' => now()]);
    $batch2 = Batch::create(['course_id' => $course2->id, 'name' => 'Batch TC', 'status' => 'ACTIVE', 'start_date' => now()]);

    $enrollment = Enrollment::create([
        'student_id'  => $student->id,
        'course_id'   => $course1->id,
        'batch_id'    => $batch1->id,
        'status'      => 'ACTIVE',
        'enrolled_at' => now(),
    ]);

    auth()->login($user);

    $studentController = new \App\Http\Controllers\Student\CourseTransferController();
    $adminController = new \App\Http\Controllers\Admin\CourseTransferController();

    // -------------------------------------------------------------
    // TEST 3: Student Store, Cancel, and Restore
    // -------------------------------------------------------------
    echo "\n--- TEST 3: Student Store, Cancel, and Restore Workflow ---\n";

    $storeReq = Request::create(route('student.course-transfers.store'), 'POST', [
        'to_course_id' => $course2->id,
        'reason'       => 'আমি এই কোর্স থেকে অন্য কোর্সে স্থানান্তর হতে চাই (১০+ অক্ষর)।',
    ]);

    $studentController->store($storeReq);

    $transfer = CourseTransfer::where('student_id', $student->id)->latest()->first();
    assertTest("Transfer created with status PENDING", $transfer && $transfer->status === 'PENDING');

    // Student cancels the transfer
    $studentController->cancel($transfer);
    $transfer->refresh();
    assertTest("Transfer status updated to CANCELLED after student cancel", $transfer->status === 'CANCELLED');

    // Student restores the transfer
    $studentController->restore($transfer);
    $transfer->refresh();
    assertTest("Transfer status restored back to PENDING after student restore", $transfer->status === 'PENDING');

    // -------------------------------------------------------------
    // TEST 4: Student Cancel/Restore with Approved Pending Payment
    // -------------------------------------------------------------
    echo "\n--- TEST 4: Cancel and Restore with Invoice ---\n";

    $adminUser = User::factory()->create(['role' => 'ADMIN']);
    auth()->login($adminUser);

    // Admin approves transfer with fee
    $approveReq = Request::create(route('admin.course-transfers.approve', $transfer), 'POST', [
        'to_batch_id'  => $batch2->id,
        'transfer_fee' => 300.00,
        'admin_notes'  => 'Approved with fee',
    ]);
    $adminController->approve($approveReq, $transfer);
    $transfer->refresh();

    assertTest("Transfer approved with status APPROVED_PENDING_PAYMENT", $transfer->status === 'APPROVED_PENDING_PAYMENT');
    assertTest("Invoice generated with status UNPAID", $transfer->invoice && $transfer->invoice->status === 'UNPAID');

    // Student logs back in and cancels
    auth()->login($user);
    $studentController->cancel($transfer);
    $transfer->refresh();

    assertTest("Transfer cancelled from APPROVED_PENDING_PAYMENT", $transfer->status === 'CANCELLED');
    assertTest("Associated unpaid invoice also marked CANCELLED", $transfer->invoice->fresh()->status === 'CANCELLED');

    // Student restores the cancelled transfer
    $studentController->restore($transfer);
    $transfer->refresh();

    assertTest("Transfer restored back to PENDING from CANCELLED", $transfer->status === 'PENDING');

    // Admin re-approves restored transfer
    auth()->login($adminUser);
    $adminController->approve($approveReq, $transfer);
    $transfer->refresh();

    assertTest("Admin can re-approve restored transfer", $transfer->status === 'APPROVED_PENDING_PAYMENT');
    assertTest("Invoice reactivated with status UNPAID", $transfer->invoice->fresh()->status === 'UNPAID');

    // -------------------------------------------------------------
    // TEST 5: Admin Reject and Admin Restore
    // -------------------------------------------------------------
    echo "\n--- TEST 5: Admin Reject and Admin Restore ---\n";

    // Admin rejects transfer
    $rejectReq = Request::create(route('admin.course-transfers.reject', $transfer), 'POST', [
        'rejection_reason' => 'আসন সংখ্যা খালি নেই।',
    ]);
    $adminController->reject($rejectReq, $transfer);
    $transfer->refresh();

    assertTest("Admin rejected transfer successfully (status REJECTED)", $transfer->status === 'REJECTED');
    assertTest("Invoice cancelled on rejection", $transfer->invoice->fresh()->status === 'CANCELLED');

    // Admin restores transfer
    $restoreReq = Request::create(route('admin.course-transfers.restore', $transfer), 'POST');
    $adminController->restore($restoreReq, $transfer);
    $transfer->refresh();

    assertTest("Admin restored transfer successfully (status PENDING)", $transfer->status === 'PENDING');
    assertTest("Rejection reason cleared upon restore", empty($transfer->rejection_reason));

    // -------------------------------------------------------------
    // TEST 6: Conflict Prevention (Cannot restore if already another active transfer)
    // -------------------------------------------------------------
    echo "\n--- TEST 6: Conflict Prevention ---\n";

    // Create a secondary cancelled transfer
    $cancelledTransfer2 = CourseTransfer::create([
        'student_id'         => $student->id,
        'from_course_id'     => $course1->id,
        'from_batch_id'      => $batch1->id,
        'from_enrollment_id' => $enrollment->id,
        'to_course_id'       => $course2->id,
        'reason'             => 'Second request that was cancelled',
        'status'             => 'CANCELLED',
    ]);

    // Student tries to restore second transfer while first transfer is currently PENDING
    auth()->login($user);
    $response = $studentController->restore($cancelledTransfer2);

    $cancelledTransfer2->refresh();
    assertTest("Second transfer cannot be restored while another is PENDING", $cancelledTransfer2->status === 'CANCELLED');

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
} finally {
    DB::rollBack();
}

echo "\n========================================================\n";
echo "ALL TESTS PASSED: {$passedTests} / {$totalTests} (Exit Code 0)\n";
echo "========================================================\n";
exit(0);
