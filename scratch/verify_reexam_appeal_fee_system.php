<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\ExamAppeal;
use App\Models\Exam;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Subject;
use App\Services\AccountingService;
use Carbon\Carbon;

$passed = 0;
$failed = 0;

function assertCondition($desc, $cond) {
    global $passed, $failed;
    if ($cond) {
        echo "  [PASS] $desc\n";
        $passed++;
    } else {
        echo "  [FAIL] $desc\n";
        $failed++;
    }
}

echo "========================================================\n";
echo " TEST SUITE: Re-Exam Appeal Fee & Payment Workflow\n";
echo "========================================================\n\n";

// ── 1. Database Schema Verification ─────────────────────────────
echo "1. Checking database schema on exam_appeals table...\n";
assertCondition("Column 'fee_amount' exists in exam_appeals", Schema::hasColumn('exam_appeals', 'fee_amount'));
assertCondition("Column 'payment_status' exists in exam_appeals", Schema::hasColumn('exam_appeals', 'payment_status'));
assertCondition("Column 'invoice_id' exists in exam_appeals", Schema::hasColumn('exam_appeals', 'invoice_id'));

// ── 2. Setup Test Data ───────────────────────────────────────────
echo "\n2. Setting up test data (Student, Exam, User)...\n";
$user = User::first() ?? User::create([
    'name' => 'Test Admin',
    'email' => 'admin_test_' . rand(1000, 9999) . '@iom.test',
    'password' => bcrypt('password'),
    'role' => 'admin',
]);
auth()->login($user);

$studentUser = User::create([
    'name' => 'Appeal Student Test',
    'email' => 'student_appeal_' . rand(1000, 9999) . '@iom.test',
    'password' => bcrypt('password'),
    'role' => 'student',
]);

$student = Student::create([
    'user_id' => $studentUser->id,
    'name' => 'Appeal Student Test',
    'student_code' => '99999' . rand(100, 999),
    'phone' => '01700' . rand(100000, 999999),
    'status' => 'ACTIVE',
]);

$subject = Subject::first();
$expiredExam = Exam::create([
    'title' => 'Test Expired Midterm',
    'type' => 'MIDTERM',
    'subject_id' => $subject?->id,
    'full_marks' => 50,
    'pass_marks' => 20,
    'duration_minutes' => 45,
    'start_datetime' => Carbon::now()->subDays(5),
    'end_datetime' => Carbon::now()->subDays(2),
    'exam_date' => Carbon::now()->subDays(5)->toDateString(),
    'end_date' => Carbon::now()->subDays(2)->toDateString(),
    'status' => 'SCHEDULED',
]);

assertCondition("Exam is correctly recognized as expired", $expiredExam->isExpired());

// ── 3. Expired Exam Appeal with Fee Approval ────────────────────
echo "\n3. Testing appeal on expired exam with fee approval...\n";
$appeal1 = ExamAppeal::create([
    'exam_id' => $expiredExam->id,
    'student_id' => $student->id,
    'reason' => 'অসুস্থতার কারণে নির্দিষ্ট সময়ে পরীক্ষায় অংশ নিতে পারিনি।',
    'status' => 'PENDING',
]);

assertCondition("Appeal1 initial status is PENDING", $appeal1->isPending());
assertCondition("Appeal1 recognizes exam is expired", $appeal1->isExamExpired());

// Simulate Admin Approval with Fee of ৳500
$feeAmount = 500.00;
$appeal1->update([
    'status'         => 'APPROVED',
    'fee_amount'     => $feeAmount,
    'payment_status' => 'UNPAID',
    'reviewed_by'    => $user->id,
    'reviewed_at'    => now(),
    'admin_remarks'  => 'সময়ের পর আপিল অনুমোদিত। ফি প্রযোজ্য।',
]);

$invoice = AccountingService::createReExamAppealInvoice($student, $appeal1, $feeAmount);
$appeal1->refresh();

assertCondition("Appeal1 status is APPROVED", $appeal1->isApproved());
assertCondition("Appeal1 fee_amount is 500", $appeal1->fee_amount == 500);
assertCondition("Appeal1 payment_status is UNPAID", $appeal1->payment_status === 'UNPAID');
assertCondition("Appeal1 has invoice_id assigned", !empty($appeal1->invoice_id));
assertCondition("Invoice category is RE_EXAM", $invoice->category === 'RE_EXAM');
assertCondition("Invoice amount and due_amount match fee", $invoice->amount == 500 && $invoice->due_amount == 500);
assertCondition("Invoice status is UNPAID", $invoice->status === 'UNPAID');
assertCondition("requiresPayment() returns TRUE before payment", $appeal1->requiresPayment() === true);
assertCondition("isPaid() returns FALSE before payment", $appeal1->isPaid() === false);

// ── 4. Paying the Appeal Fee Invoice ────────────────────────────
echo "\n4. Testing payment settlement for re-exam appeal fee...\n";
AccountingService::receivePayment($invoice, 500.00, 'BKASH', 'TRX_TEST_' . rand(1000, 9999), 'Appeal fee paid via bKash');

$invoice->refresh();
$appeal1->refresh();

assertCondition("Invoice status updated to PAID", $invoice->status === 'PAID');
assertCondition("Invoice due_amount is now 0", $invoice->due_amount == 0);
assertCondition("Appeal1 payment_status automatically updated to PAID", $appeal1->payment_status === 'PAID');
assertCondition("requiresPayment() returns FALSE after payment", $appeal1->requiresPayment() === false);
assertCondition("isPaid() returns TRUE after payment", $appeal1->isPaid() === true);

// ── 5. Free / Zero Fee Appeal (within deadline or fee waived) ───
echo "\n5. Testing free/zero fee appeal approval...\n";
$appeal2 = ExamAppeal::create([
    'exam_id' => $expiredExam->id,
    'student_id' => $student->id,
    'reason' => 'যৌক্তিক কারণে পরীক্ষা দিতে পারিনি।',
    'status' => 'PENDING',
]);

$appeal2->update([
    'status'         => 'APPROVED',
    'fee_amount'     => 0.00,
    'payment_status' => 'PAID',
    'invoice_id'     => null,
    'reviewed_by'    => $user->id,
    'reviewed_at'    => now(),
    'admin_remarks'  => 'ফি মওকুফ করা হলো।',
]);
$appeal2->refresh();

assertCondition("Appeal2 status is APPROVED", $appeal2->isApproved());
assertCondition("Appeal2 fee_amount is 0", $appeal2->fee_amount == 0);
assertCondition("Appeal2 payment_status is PAID", $appeal2->payment_status === 'PAID');
assertCondition("Appeal2 requiresPayment() is FALSE", $appeal2->requiresPayment() === false);
assertCondition("Appeal2 isPaid() is TRUE", $appeal2->isPaid() === true);

// ── 6. Cleanup Test Data ─────────────────────────────────────────
echo "\n6. Cleaning up test data...\n";
if ($invoice) {
    DB::table('payments')->where('invoice_id', $invoice->id)->delete();
    $invoice->forceDelete();
}
$appeal1->delete();
$appeal2->delete();
$expiredExam->delete();
$student->delete();
$studentUser->delete();

echo "\n========================================================\n";
echo " SUMMARY: Passed: $passed | Failed: $failed\n";
echo "========================================================\n";

if ($failed === 0) {
    echo "SUCCESS: All tests passed with Exit Code 0!\n";
    exit(0);
} else {
    echo "ERROR: Some tests failed!\n";
    exit(1);
}
