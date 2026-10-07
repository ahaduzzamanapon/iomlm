<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\StudentFeeService;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

echo "=== START VERIFICATION: ALL FEES IN STUDENT & ADMIN PORTALS ===\n\n";

$testsPassed = 0;
$testsTotal = 0;

function assertTest($condition, $description) {
    global $testsPassed, $testsTotal;
    $testsTotal++;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] {$description}\n";
    }
}

DB::beginTransaction();

try {
    // 1. Find a test student with enrollment
    $student = Student::with(['enrollments.course', 'user'])->has('enrollments')->first();
    if (!$student) {
        throw new \Exception("No test student found with enrollment.");
    }
    echo "Testing with Student: {$student->name} (ID: {$student->id}, Code: {$student->student_code})\n\n";

    // 2. Create a Course Activation Fee invoice for this student (category FINE, INV-ACT-...)
    $activationInvNo = 'INV-ACT-TEST-' . time();
    $activationInvoice = Invoice::create([
        'invoice_no'     => $activationInvNo,
        'student_id'     => $student->id,
        'enrollment_id'  => $student->enrollments->first()?->id,
        'category'       => 'FINE',
        'title'          => 'কোর্স এক্টিভিশন ফি - অক্টোবর ২০২৬ (১০ তারিখের পর কোর্স আনলক ফি)',
        'amount'         => 100.00,
        'discount'       => 0.00,
        'payable_amount' => 100.00,
        'paid_amount'    => 0.00,
        'due_amount'     => 100.00,
        'status'         => 'UNPAID',
        'due_date'       => now()->addDays(5),
    ]);

    // 3. Create another standalone fee: Retake / Exam Fee
    $retakeInvNo = 'INV-RET-TEST-' . time();
    $retakeInvoice = Invoice::create([
        'invoice_no'     => $retakeInvNo,
        'student_id'     => $student->id,
        'enrollment_id'  => $student->enrollments->first()?->id,
        'category'       => 'RETAKE',
        'title'          => 'বিষয় পুনঃপরীক্ষা ফি (Retake Fee)',
        'amount'         => 300.00,
        'discount'       => 0.00,
        'payable_amount' => 300.00,
        'paid_amount'    => 0.00,
        'due_amount'     => 300.00,
        'status'         => 'UNPAID',
        'due_date'       => now()->addDays(5),
    ]);

    $feeService = app(StudentFeeService::class);

    // Test A: StudentFeeService::getStudentFeeBreakdown
    echo "--- 1. Testing StudentFeeService::getStudentFeeBreakdown ---\n";
    $breakdown = $feeService->getStudentFeeBreakdown($student);
    $step1Particulars = $breakdown['step1Particulars'] ?? [];
    
    $actItem = collect($step1Particulars)->first(fn($p) => $p['invoice_id'] == $activationInvoice->id);
    assertTest(!empty($actItem), "Course Activation Fee invoice appears in step1Particulars");
    assertTest($actItem && $actItem['due'] == 100.00, "Activation Fee due amount is ৳100.00");
    assertTest($actItem && $actItem['category'] === 'FINE', "Activation Fee has category = 'FINE'");
    assertTest($actItem && !str_contains($actItem['name'], '১০ তারিখের পর'), "Activation Fee name is cleanly sanitized: {$actItem['name']}");

    $retakeItem = collect($step1Particulars)->first(fn($p) => $p['invoice_id'] == $retakeInvoice->id);
    assertTest(!empty($retakeItem), "Retake Fee invoice appears in step1Particulars");
    assertTest($retakeItem && $retakeItem['due'] == 300.00, "Retake Fee due amount is ৳300.00");

    // Test B: Verify FeeController::index produces identical data for student
    echo "\n--- 2. Testing Student FeeController::index logic ---\n";
    auth()->loginUsingId($student->user_id ?? User::first()->id);
    $feeController = new \App\Http\Controllers\Student\FeeController();
    $response = $feeController->index();
    $viewData = $response->getData();

    assertTest(isset($viewData['step1Particulars']), "FeeController::index passes step1Particulars to view");
    $controllerActItem = collect($viewData['step1Particulars'])->first(fn($p) => $p['invoice_id'] == $activationInvoice->id);
    assertTest(!empty($controllerActItem), "FeeController view contains Activation Fee item");
    assertTest(isset($viewData['sslActive']) && isset($viewData['bkashActive']), "FeeController passes gateway statuses");

    // Test C: Blade Compilation Test
    echo "\n--- 3. Testing Blade Templates Compilation ---\n";
    $studentBladeCompiled = View::make('student.fees.index', $viewData)->render();
    assertTest(str_contains($studentBladeCompiled, 'এক্টিভিশন ফি'), "Student fees blade contains 'এক্টিভিশন ফি' badge or text");
    assertTest(str_contains($studentBladeCompiled, 'Kalpurush'), "Student fees blade contains 'Kalpurush' font");
    assertTest(str_contains($studentBladeCompiled, 'data-is-monthly'), "Student fees blade contains 'data-is-monthly' on checkboxes");

    $adminBreakdown = $feeService->getStudentFeeBreakdown($student);
    $adminBladeCompiled = View::make('admin.accounts.student_ledger', $adminBreakdown)->render();
    assertTest(str_contains($adminBladeCompiled, 'এক্টিভিশন ফি'), "Admin student ledger blade contains 'এক্টিভিশন ফি' badge");
    assertTest(str_contains($adminBladeCompiled, 'particular_invoices'), "Admin student ledger contains 'particular_invoices' hidden input logic");

    // Test D: Multi-Invoice Distribution Test (Paying Tuition + Activation Fee)
    echo "\n--- 4. Testing Multi-Invoice Payment Distribution ---\n";
    $mainInvoice = $breakdown['selectedSemesterInvoice'] ?? Invoice::where('student_id', $student->id)->where('category', 'SEMESTER')->first();
    if (!$mainInvoice) {
        $mainInvoice = Invoice::create([
            'invoice_no'     => 'INV-TUI-TEST-' . time(),
            'student_id'     => $student->id,
            'category'       => 'SEMESTER',
            'title'          => 'Semester Tuition Fee',
            'amount'         => 3000.00,
            'payable_amount' => 3000.00,
            'paid_amount'    => 0.00,
            'due_amount'     => 3000.00,
            'status'         => 'UNPAID',
        ]);
    }

    $tuiPartName = 'Tuition Fee (Oct-2026)';
    $actPartName = $actItem['name'];

    $partNames = [$tuiPartName, $actPartName];
    $partDues = [
        $tuiPartName => 500.00,
        $actPartName => 100.00,
    ];
    $partAmounts = [
        $tuiPartName => 500.00,
        $actPartName => 100.00,
    ];
    $partInvoices = [
        $tuiPartName => $mainInvoice->id,
        $actPartName => $activationInvoice->id,
    ];

    $collectRes = $feeService->collectParticularPayment(
        $mainInvoice,
        $partNames,
        600.00,
        'CASH',
        'TRX-MULTI-TEST',
        null,
        'Combined Tuition + Activation Fee',
        1,
        $partDues,
        $partAmounts,
        $partInvoices
    );

    assertTest(!empty($collectRes['success']), "collectParticularPayment executed successfully across multiple invoices");

    $activationInvoice->refresh();
    $mainInvoice->refresh();

    assertTest($activationInvoice->paid_amount == 100.00, "Activation invoice was credited exactly ৳100.00 (Current: {$activationInvoice->paid_amount})");
    assertTest($activationInvoice->status === 'PAID', "Activation invoice status is PAID");
    assertTest($mainInvoice->paid_amount >= 500.00, "Main semester invoice received ৳500.00 (Current: {$mainInvoice->paid_amount})");

} catch (\Throwable $e) {
    echo "\n[ERROR]: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    // Always rollback so database remains completely clean
    DB::rollBack();
    echo "\nDatabase transaction rolled back cleanly.\n";
}

echo "\n=======================================================\n";
echo "SUMMARY: {$testsPassed} / {$testsTotal} assertions passed.\n";
if ($testsPassed === $testsTotal && $testsTotal > 0) {
    echo "STATUS: ALL TESTS PASSED SUCCESSFULLY! (Exit Code 0)\n";
    exit(0);
} else {
    echo "STATUS: TESTS FAILED!\n";
    exit(1);
}
