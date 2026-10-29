<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Batch;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

echo "========================================================================\n";
echo "VERIFICATION: COURSE ACTIVATION FEE & AUTO ACCESS TOGGLE ON PAYMENT\n";
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

    $adminView = file_get_contents(resource_path('views/admin/accounts/dashboard.blade.php'));
    $studentDash = file_get_contents(resource_path('views/student/dashboard.blade.php'));
    $middlewareCode = file_get_contents(app_path('Http/Middleware/EnsureCourseAccess.php'));

    assertTest("Admin view contains activationCourseSelect", str_contains($adminView, 'id="activationCourseSelect"'));
    assertTest("Admin view contains activationBatchSelect", str_contains($adminView, 'id="activationBatchSelect"'));
    assertTest("Admin view has 'সব ব্যাচ (একসাথে সব ব্যাচে প্রয়োগ)' option", str_contains($adminView, 'সব ব্যাচ (একসাথে সব ব্যাচে প্রয়োগ)'));
    assertTest("Admin view has month selector with Bengali labels", str_contains($adminView, 'অক্টোবর (Oct)'));
    assertTest("Admin view has year selector", str_contains($adminView, 'name="year"'));
    assertTest("Admin view has activation fee amount field (default 100)", str_contains($adminView, 'name="amount"') && str_contains($adminView, 'value="100"'));
    assertTest("Admin view has dynamic course-batch filtering JS", str_contains($adminView, 'activationCourseSelect') && str_contains($adminView, 'activationBatchSelect'));

    assertTest("Student dashboard has suspended access notice when has_course_access is false", str_contains($studentDash, 'কোর্স ও ক্লাসরুম অ্যাক্সেস সাময়িকভাবে স্থগিত'));
    assertTest("Middleware has updated message mentioning auto access on payment", str_contains($middlewareCode, 'বকেয়া ফি পরিশোধ করলে তাৎক্ষণিকভাবে স্বয়ংক্রিয়ভাবে অটো এক্সেস চালু হয়ে যাবে'));

    // -------------------------------------------------------------
    // TEST 2: Setup Test Data (Courses, Batches, Students & Invoices)
    // -------------------------------------------------------------
    echo "\n--- TEST 2: Setup Test Data ---\n";

    $targetMonth = 10;
    $targetYear  = 2026;

    $courseA = Course::create(['name' => 'Test Course A ' . rand(10, 99), 'code' => 'TCA' . rand(10, 99), 'is_active' => true]);
    $batchA1 = Batch::create(['course_id' => $courseA->id, 'name' => 'Batch A1', 'status' => 'ACTIVE', 'start_date' => now()]);
    $batchA2 = Batch::create(['course_id' => $courseA->id, 'name' => 'Batch A2', 'status' => 'ACTIVE', 'start_date' => now()]);

    $courseB = Course::create(['name' => 'Test Course B ' . rand(10, 99), 'code' => 'TCB' . rand(10, 99), 'is_active' => true]);
    $batchB1 = Batch::create(['course_id' => $courseB->id, 'name' => 'Batch B1', 'status' => 'ACTIVE', 'start_date' => now()]);

    function createTestStudent($name, $course, $batch) {
        $user = User::factory()->create(['role' => 'STUDENT']);
        $student = Student::create([
            'user_id'           => $user->id,
            'student_code'      => 'STU-' . rand(10000, 99999),
            'name'              => $name,
            'phone'             => '017' . rand(10000000, 99999999),
            'gender'            => 'MALE',
            'status'            => 'ACTIVE',
            'has_course_access' => true,
        ]);
        $enrollment = Enrollment::create([
            'student_id'  => $student->id,
            'course_id'   => $course->id,
            'batch_id'    => $batch->id,
            'status'      => 'ACTIVE',
            'enrolled_at' => now(),
        ]);
        return [$user, $student, $enrollment];
    }

    // Student 1: Course A, Batch A1 -> UNPAID October 2026 tuition
    [$u1, $s1, $e1] = createTestStudent('Student 1 (Batch A1 Unpaid)', $courseA, $batchA1);
    Invoice::create([
        'invoice_no'     => 'INV-TUI-' . rand(1000, 9999),
        'student_id'     => $s1->id,
        'enrollment_id'  => $e1->id,
        'category'       => 'SEMESTER',
        'title'          => 'Tuition Fee (Oct-2026)',
        'amount'         => 500.00,
        'payable_amount' => 500.00,
        'paid_amount'    => 0.00,
        'due_amount'     => 500.00,
        'status'         => 'UNPAID',
        'created_at'     => Carbon::createFromDate($targetYear, $targetMonth, 1),
    ]);

    // Student 2: Course A, Batch A1 -> PAID October 2026 tuition
    [$u2, $s2, $e2] = createTestStudent('Student 2 (Batch A1 Paid)', $courseA, $batchA1);
    Invoice::create([
        'invoice_no'     => 'INV-TUI-' . rand(1000, 9999),
        'student_id'     => $s2->id,
        'enrollment_id'  => $e2->id,
        'category'       => 'SEMESTER',
        'title'          => 'Tuition Fee (Oct-2026)',
        'amount'         => 500.00,
        'payable_amount' => 500.00,
        'paid_amount'    => 500.00,
        'due_amount'     => 0.00,
        'status'         => 'PAID',
        'created_at'     => Carbon::createFromDate($targetYear, $targetMonth, 1),
    ]);

    // Student 3: Course A, Batch A2 -> UNPAID October 2026 tuition
    [$u3, $s3, $e3] = createTestStudent('Student 3 (Batch A2 Unpaid)', $courseA, $batchA2);
    Invoice::create([
        'invoice_no'     => 'INV-TUI-' . rand(1000, 9999),
        'student_id'     => $s3->id,
        'enrollment_id'  => $e3->id,
        'category'       => 'SEMESTER',
        'title'          => 'Tuition Fee (Oct-2026)',
        'amount'         => 500.00,
        'payable_amount' => 500.00,
        'paid_amount'    => 0.00,
        'due_amount'     => 500.00,
        'status'         => 'UNPAID',
        'created_at'     => Carbon::createFromDate($targetYear, $targetMonth, 1),
    ]);

    // Student 4: Course B, Batch B1 -> UNPAID October 2026 tuition
    [$u4, $s4, $e4] = createTestStudent('Student 4 (Course B Unpaid)', $courseB, $batchB1);
    Invoice::create([
        'invoice_no'     => 'INV-TUI-' . rand(1000, 9999),
        'student_id'     => $s4->id,
        'enrollment_id'  => $e4->id,
        'category'       => 'SEMESTER',
        'title'          => 'Tuition Fee (Oct-2026)',
        'amount'         => 500.00,
        'payable_amount' => 500.00,
        'paid_amount'    => 0.00,
        'due_amount'     => 500.00,
        'status'         => 'UNPAID',
        'created_at'     => Carbon::createFromDate($targetYear, $targetMonth, 1),
    ]);

    // -------------------------------------------------------------
    // TEST 3: Apply Activation Fee to Individual Batch (Course A, Batch A1)
    // -------------------------------------------------------------
    echo "\n--- TEST 3: Apply to Specific Batch (Batch A1 Only) ---\n";

    $result1 = AccountingService::applyCourseActivationFeesWithFilters(
        (string)$courseA->id,
        (string)$batchA1->id,
        $targetMonth,
        $targetYear,
        100.00
    );

    $s1->refresh();
    $s2->refresh();
    $s3->refresh();
    $s4->refresh();

    $actInvS1 = Invoice::where('student_id', $s1->id)->where('category', 'FINE')->first();
    $actInvS2 = Invoice::where('student_id', $s2->id)->where('category', 'FINE')->first();
    $actInvS3 = Invoice::where('student_id', $s3->id)->where('category', 'FINE')->first();
    $actInvS4 = Invoice::where('student_id', $s4->id)->where('category', 'FINE')->first();

    assertTest("Student 1 (Batch A1, Unpaid) received ৳100 activation fee", $actInvS1 !== null && (float)$actInvS1->amount === 100.00);
    assertTest("Student 1 portal access is turned OFF (has_course_access = false)", $s1->has_course_access === false);

    assertTest("Student 2 (Batch A1, Paid) did NOT receive activation fee", $actInvS2 === null);
    assertTest("Student 2 portal access remains ON (has_course_access = true)", $s2->has_course_access === true);

    assertTest("Student 3 (Batch A2) did NOT receive fee when only Batch A1 targeted", $actInvS3 === null);
    assertTest("Student 3 portal access remains ON", $s3->has_course_access === true);

    assertTest("Student 4 (Course B) did NOT receive fee", $actInvS4 === null);
    assertTest("Student 4 portal access remains ON", $s4->has_course_access === true);

    // -------------------------------------------------------------
    // TEST 4: Apply Activation Fee to All Batches of Course A
    // -------------------------------------------------------------
    echo "\n--- TEST 4: Apply to All Batches of Course A ---\n";

    $result2 = AccountingService::applyCourseActivationFeesWithFilters(
        (string)$courseA->id,
        'all', // All batches of Course A
        $targetMonth,
        $targetYear,
        100.00
    );

    $s1->refresh();
    $s3->refresh();

    $actInvS3 = Invoice::where('student_id', $s3->id)->where('category', 'FINE')->first();
    $actInvoicesCountS1 = Invoice::where('student_id', $s1->id)->where('category', 'FINE')->count();

    assertTest("Student 3 (Batch A2, Unpaid) now received activation fee", $actInvS3 !== null && (float)$actInvS3->amount === 100.00);
    assertTest("Student 3 portal access is now turned OFF", $s3->has_course_access === false);
    assertTest("Student 1 was NOT duplicated with second activation fee", $actInvoicesCountS1 === 1);

    // -------------------------------------------------------------
    // TEST 5: Automatic Portal Access Restoration on Payment
    // -------------------------------------------------------------
    echo "\n--- TEST 5: Payment Restores Portal Access Automatically ---\n";

    assertTest("Before payment, Student 1 portal access is false", $s1->has_course_access === false);

    // Student 1 pays the ৳100 activation fee invoice
    $payment = AccountingService::receivePayment(
        $actInvS1,
        100.00,
        'BKASH',
        'TRX-RESTORE-TEST',
        'Student paid activation fee via bKash'
    );

    $actInvS1->refresh();
    $s1->refresh();

    assertTest("Activation fee invoice status is now PAID", $actInvS1->status === 'PAID' && $actInvS1->due_amount == 0);
    assertTest("Student 1 portal access is AUTOMATICALLY turned back ON (has_course_access = true)", $s1->has_course_access === true);

    // -------------------------------------------------------------
    // TEST 6: Admin Approval of Online Payment also Restores Access
    // -------------------------------------------------------------
    echo "\n--- TEST 6: Admin Approval of Online Payment Restores Access ---\n";

    assertTest("Before payment, Student 3 portal access is false", $s3->has_course_access === false);

    // Student 3 submits online payment (PENDING status)
    $pendingPayment = Payment::create([
        'payment_no'     => 'PAY-ONLINE-' . rand(1000, 9999),
        'invoice_id'     => $actInvS3->id,
        'student_id'     => $s3->id,
        'amount'         => 100.00,
        'payment_method' => 'NAGAD',
        'transaction_id' => 'TRX-NAGAD-TEST',
        'status'         => 'PENDING',
        'paid_at'        => now(),
    ]);

    // Admin approves payment
    AccountingService::approvePayment($pendingPayment);

    $actInvS3->refresh();
    $s3->refresh();

    assertTest("Student 3 activation fee is now PAID", $actInvS3->status === 'PAID' && $actInvS3->due_amount == 0);
    assertTest("Student 3 portal access is AUTOMATICALLY turned back ON after admin approval", $s3->has_course_access === true);

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
} finally {
    DB::rollBack();
}

echo "\n========================================================================\n";
echo "ALL TESTS PASSED: {$passedTests} / {$totalTests} (Exit Code 0)\n";
echo "========================================================================\n";
exit(0);
