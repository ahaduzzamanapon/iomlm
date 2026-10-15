<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Semester;
use App\Models\Course;
use App\Services\StudentFeeService;
use Illuminate\Support\Facades\DB;

echo "=======================================================\n";
echo "      STARTING COMPREHENSIVE VERIFICATION SUITE       \n";
echo "=======================================================\n\n";

DB::beginTransaction();

try {
    $service = app(StudentFeeService::class);

    // -------------------------------------------------------------------------
    // TEST 1: Poor fund / Discount Paid Amount Display (Screenshot 1 issue)
    // -------------------------------------------------------------------------
    echo "[TEST 1] Poor fund / Discount Paid Amount Calculation...\n";
    
    // Find or create a student
    $student = Student::first();
    if (!$student) {
        throw new \Exception("No student found in DB to test with.");
    }

    // Create an admission invoice with original nominal amount 1500, but discounted payable 700
    $inv1 = Invoice::create([
        'student_id'     => $student->id,
        'invoice_no'     => 'TEST-INV-DISCOUNT-' . time(),
        'category'       => 'ADMISSION',
        'title'          => 'Admission Fee',
        'amount'         => 1500.00,  // Nominal rate
        'payable_amount' => 700.00,   // Discounted fee
        'paid_amount'    => 700.00,   // Actually paid
        'due_amount'     => 0.00,
        'status'         => 'PAID',
    ]);

    // Create the actual payment record of 700
    $pmt1 = Payment::create([
        'invoice_id'     => $inv1->id,
        'student_id'     => $student->id,
        'payment_no'     => 'TEST-PAY-DISCOUNT-' . time(),
        'amount'         => 700.00,
        'payment_method' => 'BKASH',
        'status'         => 'APPROVED',
        'paid_at'        => now(),
    ]);

    // Fetch breakdown
    $breakdown = $service->getStudentFeeBreakdown($student, 'admission');
    $particulars = $breakdown['step1Particulars'];
    
    $admParticular = null;
    foreach ($particulars as $p) {
        if (str_contains(mb_strtolower($p['name']), 'admission') || str_contains(mb_strtolower($p['name']), 'ভর্তি')) {
            $admParticular = $p;
            break;
        }
    }

    if (!$admParticular) {
        throw new \Exception("TEST 1 FAILED: Admission particular not found in step1Particulars.");
    }

    echo "  - Nominal amount: {$admParticular['amount']}\n";
    echo "  - Paid amount: {$admParticular['paid_amt']}\n";
    echo "  - Due amount: {$admParticular['due']}\n";
    echo "  - Is paid: " . ($admParticular['is_paid'] ? 'YES' : 'NO') . "\n";

    if ($admParticular['paid_amt'] != 700.0) {
        throw new \Exception("TEST 1 FAILED: Expected paid_amt to be 700, got: " . $admParticular['paid_amt']);
    }
    if ($admParticular['amount'] != 1500.0) {
        throw new \Exception("TEST 1 FAILED: Expected nominal amount to be 1500, got: " . $admParticular['amount']);
    }
    if (!$admParticular['is_paid']) {
        throw new \Exception("TEST 1 FAILED: Expected is_paid to be true.");
    }
    echo "✓ TEST 1 PASSED: Discounted student correctly shows paid_amt = 700 while preserving nominal 1500!\n\n";

    // -------------------------------------------------------------------------
    // TEST 2: Partial payment then Admin Waiver / Edit (Screenshot 2 issue)
    // -------------------------------------------------------------------------
    echo "[TEST 2] Partial Payment then Admin Waiver (Due = 0 edit)...\n";

    // Create an invoice of 1500 where student paid 500, remaining due 1000
    $inv2 = Invoice::create([
        'student_id'     => $student->id,
        'invoice_no'     => 'TEST-INV-PARTIAL-' . time(),
        'category'       => 'ADMISSION',
        'title'          => 'Admission Fee',
        'amount'         => 1500.00,
        'payable_amount' => 1500.00,
        'paid_amount'    => 500.00,
        'due_amount'     => 1000.00,
        'status'         => 'PARTIAL',
    ]);

    $pmt2 = Payment::create([
        'invoice_id'     => $inv2->id,
        'student_id'     => $student->id,
        'payment_no'     => 'TEST-PAY-PARTIAL-' . time(),
        'amount'         => 500.00,
        'payment_method' => 'CASH',
        'status'         => 'APPROVED',
        'paid_at'        => now(),
    ]);

    // Admin now waives the remaining 1000 by setting new due = 0 (or new_amount = 0)
    // Admin passes current_paid = 500 and current_due = 1000
    $updateRes = $service->updateParticular(
        $inv2->id,
        'Admission Fee (ভর্তি ফি)',
        0.0, // new amount set to 0 (waiver)
        'বাকি ১০০০ টাকা কর্তৃপক্ষ মওকুফ করেছেন',
        null, // user id
        1000.0, // current due
        'at_bottom',
        null,
        500.0 // current paid
    );

    $inv2->refresh();
    echo "  - Update response: " . json_encode($updateRes, JSON_UNESCAPED_UNICODE) . "\n";
    echo "  - Invoice new payable: {$inv2->payable_amount}, paid: {$inv2->paid_amount}, due: {$inv2->due_amount}, status: {$inv2->status}\n";

    if ($inv2->due_amount != 0.0) {
        throw new \Exception("TEST 2 FAILED: Expected invoice due_amount to be 0, got: {$inv2->due_amount}");
    }
    if ($inv2->paid_amount != 500.0) {
        throw new \Exception("TEST 2 FAILED: Expected invoice paid_amount to remain 500, got: {$inv2->paid_amount}");
    }
    if ($updateRes['paid_amt'] != 500.0) {
        throw new \Exception("TEST 2 FAILED: Expected updateParticular return paid_amt = 500, got: " . $updateRes['paid_amt']);
    }
    if ($inv2->status !== 'PAID') {
        throw new \Exception("TEST 2 FAILED: Expected invoice status to be PAID, got: {$inv2->status}");
    }

    // Now test breakdown after waiver
    $breakdown2 = $service->getStudentFeeBreakdown($student, 'admission');
    $admParticular2 = null;
    foreach ($breakdown2['step1Particulars'] as $p) {
        if ($p['invoice_id'] == $inv2->id) {
            $admParticular2 = $p;
            break;
        }
    }
    if ($admParticular2) {
        echo "  - Breakdown after waiver: paid_amt = {$admParticular2['paid_amt']}, due = {$admParticular2['due']}, is_paid = " . ($admParticular2['is_paid'] ? 'true' : 'false') . "\n";
        if ($admParticular2['paid_amt'] != 500.0) {
            throw new \Exception("TEST 2 FAILED: In breakdown, paid_amt was reset to {$admParticular2['paid_amt']}, expected 500.0!");
        }
    }
    echo "✓ TEST 2 PASSED: Admin waiver (Due = 0) correctly zeroed the due while preserving previously paid 500!\n\n";

    // -------------------------------------------------------------------------
    // TEST 3: Duplicate Transaction Elimination in History Log
    // -------------------------------------------------------------------------
    echo "[TEST 3] Payment History Log Deduplication...\n";

    // Create a semester invoice
    $sem = Semester::where('course_id', $student->course_id)->first() ?? Semester::first();
    $inv3 = Invoice::create([
        'student_id'     => $student->id,
        'source_type'    => $sem ? Semester::class : null,
        'source_id'      => $sem?->id,
        'invoice_no'     => 'TEST-INV-SEM-' . time(),
        'category'       => 'SEMESTER',
        'title'          => 'Semester Tuition Fee',
        'amount'         => 1000.00,
        'payable_amount' => 1000.00,
        'paid_amount'    => 0.00,
        'due_amount'     => 1000.00,
        'status'         => 'UNPAID',
    ]);

    // Record payment through service collectParticularPayment
    $recRes = $service->collectParticularPayment(
        $inv3,
        ['Tuition Fee (Jan-2027)'],
        200.00,
        'CASH',
        null,
        null,
        'Tuition Fee (Jan-2027) বাবদ জমা'
    );

    // Call getStudentFeeBreakdown
    $breakdown3 = $service->getStudentFeeBreakdown($student, $sem ? (string)$sem->id : null);
    $historyLogs = $breakdown3['historyLogs'];
    echo "  - Total history logs in breakdown: " . count($historyLogs) . "\n";
    foreach ($historyLogs as $idx => $hl) {
        $pName = $hl['particular'] ?? ($hl['title'] ?? '');
        $act = $hl['action'] ?? ($hl['type'] ?? '');
        echo "    Log #$idx: Particular='{$pName}', Amount='{$hl['amount']}', Action='{$act}', Remarks='" . ($hl['remarks'] ?? '') . "'\n";
    }

    // Count how many entries in historyLogs correspond to this 200 TK payment for Tuition Fee (Jan-2027)
    $matchingLogs = 0;
    foreach ($historyLogs as $hl) {
        $pName = $hl['particular'] ?? ($hl['title'] ?? '');
        if (str_contains($pName, 'Tuition Fee (Jan-2027)') && abs(($hl['amount'] ?? 0) - 200.00) < 0.01) {
            $matchingLogs++;
            $act = $hl['action'] ?? ($hl['type'] ?? '');
            echo "  - Found matching log: '{$pName}', Amount: {$hl['amount']}, Action: {$act}, Remarks: " . ($hl['remarks'] ?? '') . "\n";
        }
    }

    echo "  - Total matching log entries for this 200 TK payment: $matchingLogs\n";
    if ($matchingLogs > 1) {
        throw new \Exception("TEST 3 FAILED: Found $matchingLogs duplicate log entries for the same payment in historyLogs!");
    }
    if ($matchingLogs === 0) {
        throw new \Exception("TEST 3 FAILED: Payment log entry was not found in historyLogs!");
    }
    echo "✓ TEST 3 PASSED: Exactly 1 single history log exists for payment, NO duplicates!\n\n";

    // -------------------------------------------------------------------------
    // TEST 4: History Sorting & Admission Fee Visibility Across Semesters
    // -------------------------------------------------------------------------
    echo "[TEST 4] History Sorting (Descending) & Admission Fee Visibility...\n";

    // Check if admission payment from $pmt1 or $pmt2 is visible in $historyLogs even when viewing semester
    $hasAdmInHistory = false;
    foreach ($historyLogs as $hl) {
        $pName = $hl['particular'] ?? ($hl['title'] ?? '');
        if (str_contains(mb_strtolower($pName), 'admission') || str_contains(mb_strtolower($pName), 'ভর্তি')) {
            $hasAdmInHistory = true;
            $dt = $hl['date_time'] ?? ($hl['created_at'] ?? '');
            echo "  - Admission log found in semester history: '{$pName}', Amount: {$hl['amount']}, Date: {$dt}\n";
            break;
        }
    }

    if (!$hasAdmInHistory) {
        throw new \Exception("TEST 4 FAILED: Admission fee transaction disappeared from history when semester was viewed!");
    }

    // Verify chronological descending order of historyLogs
    $prevTimestamp = PHP_INT_MAX;
    $isDescending = true;
    foreach ($historyLogs as $hl) {
        $ts = !empty($hl['created_at']) ? strtotime($hl['created_at']) : strtotime($hl['date'] ?? 'now');
        if ($ts > $prevTimestamp) {
            $isDescending = false;
            echo "  ! Out of order: {$hl['title']} at {$hl['created_at']} is newer than previous timestamp\n";
            break;
        }
        $prevTimestamp = $ts;
    }

    if (!$isDescending) {
        throw new \Exception("TEST 4 FAILED: History logs are not in descending chronological order!");
    }
    echo "✓ TEST 4 PASSED: Admission fee is visible across semesters and history is strictly sorted descending!\n\n";

    echo "=======================================================\n";
    echo "      ALL 4 TESTS PASSED SUCCESSFULLY! (EXIT CODE 0)   \n";
    echo "=======================================================\n";

} catch (\Throwable $e) {
    echo "\n❌ ERROR ENCOUNTERED:\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    DB::rollBack();
    exit(1);
} finally {
    // Clean rollback to leave database pristine
    DB::rollBack();
}

exit(0);
