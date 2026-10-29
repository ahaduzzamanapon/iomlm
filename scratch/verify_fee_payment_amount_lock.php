<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

echo "========================================================\n";
echo "VERIFICATION: STUDENT FEE PAYMENT AMOUNT LOCK & ADMIN EDIT\n";
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
    // TEST 1: Blade View Inspection (Readonly & Labels)
    // -------------------------------------------------------------
    echo "\n--- TEST 1: Blade View Readonly & Bengali Labels ---\n";

    $viewContent = file_get_contents(resource_path('views/student/fees/index.blade.php'));

    assertTest("payAmountInput contains readonly attribute", str_contains($viewContent, 'id="payAmountInput" name="amount" class="form-control" required readonly'));
    assertTest("payAmountInput blocks keystrokes and paste", str_contains($viewContent, 'onkeydown="return false;" onpaste="return false;"'));
    assertTest("Modal contains 'নির্ধারিত সম্পূর্ণ ফি প্রদেয় (এডিট অযোগ্য)'", str_contains($viewContent, 'নির্ধারিত সম্পূর্ণ ফি প্রদেয় (এডিট অযোগ্য)'));
    assertTest("Old '(আংশিক বা সম্পূর্ণ প্রদেয়)' is completely removed", !str_contains($viewContent, '(আংশিক বা সম্পূর্ণ প্রদেয়)'));
    assertTest("Notice explains student cannot edit, admin permission required", str_contains($viewContent, 'শিক্ষার্থী ফি এর পরিমাণ এডিট করতে পারবেন না'));
    assertTest("Uses 'Kalpurush' font style", str_contains($viewContent, "'Kalpurush'"));

    // -------------------------------------------------------------
    // TEST 2: Backend Enforcement of Full Determined Fee
    // -------------------------------------------------------------
    echo "\n--- TEST 2: Backend Fee Controller Enforces Full Due Amount ---\n";

    $student = Student::with('user')->first();
    if (!$student) {
        $user = User::factory()->create(['role' => 'STUDENT']);
        $student = Student::create([
            'user_id'      => $user->id,
            'student_code' => 'STU-FEE-TEST-' . rand(100, 999),
            'name'         => 'Test Fee Student',
            'phone'        => '01700000000',
            'gender'       => 'MALE',
            'status'       => 'ACTIVE',
        ]);
    } else {
        $user = $student->user ?? User::factory()->create(['role' => 'STUDENT']);
        if (!$student->user_id) {
            $student->update(['user_id' => $user->id]);
        }
    }

    $course = Course::first() ?? Course::create(['name' => 'General Course', 'code' => 'GEN', 'is_active' => true]);
    $enrollment = $student->enrollments()->first();
    if (!$enrollment) {
        $batch = \App\Models\Batch::first();
        $enrollment = Enrollment::create([
            'student_id'  => $student->id,
            'course_id'   => $course->id,
            'batch_id'    => $batch?->id,
            'status'      => 'ACTIVE',
            'enrolled_at' => now(),
        ]);
    }

    $invoice = Invoice::create([
        'invoice_no'     => 'INV-TEST-' . rand(1000, 9999),
        'student_id'     => $student->id,
        'enrollment_id'  => $enrollment->id,
        'category'       => 'SEMESTER',
        'title'          => 'Tuition Fee (Jan-2027)',
        'amount'         => 500.00,
        'discount'       => 0.00,
        'payable_amount' => 500.00,
        'paid_amount'    => 0.00,
        'due_amount'     => 500.00,
        'status'         => 'UNPAID',
    ]);

    auth()->login($user);

    $feeController = new \App\Http\Controllers\Student\FeeController();

    // Student attempts to send amount = 400 (even though invoice due is 500)
    $tamperedRequest = Request::create(route('student.fees.pay', $invoice->id), 'POST', [
        'amount'         => 400.00,
        'payment_method' => 'CASH',
        'remarks'        => 'Student trying to pay 400 instead of 500',
    ]);

    $response = $feeController->payInvoice($tamperedRequest, $invoice);

    // Verify created payment in database
    $createdPayment = Payment::where('invoice_id', $invoice->id)->latest()->first();

    assertTest("Payment created successfully", $createdPayment !== null);
    assertTest("Payment amount forced to full determined due amount (500.00), not tampered 400.00", (float) $createdPayment->amount === 500.00);

    // -------------------------------------------------------------
    // TEST 3: Admin Edit Security & Particular Update
    // -------------------------------------------------------------
    echo "\n--- TEST 3: Admin Fee Edit Security & Permission Enforcement ---\n";

    // Non-admin student attempts to call updateParticular
    $studentEditRequest = Request::create('/student/fees/particulars/update', 'POST', [
        'invoice_id'      => $invoice->id,
        'particular_name' => 'Tuition Fee (Jan-2027)',
        'new_amount'      => 450.00,
    ]);

    $studentEditResponse = $feeController->updateParticular($studentEditRequest);
    assertTest("Non-admin request to update fee particular is blocked with 403 Forbidden", $studentEditResponse->getStatusCode() === 403);

    // Admin impersonation or Admin user attempts updateParticular
    $adminUser = User::factory()->create(['role' => 'ADMIN']);
    session(['admin_impersonator_id' => $adminUser->id]);

    $adminEditRequest = Request::create('/student/fees/particulars/update', 'POST', [
        'invoice_id'      => $invoice->id,
        'particular_name' => 'Tuition Fee (Jan-2027)',
        'new_amount'      => 450.00,
        'remarks'         => 'Admin approved fee adjustment',
    ]);

    $adminEditResponse = $feeController->updateParticular($adminEditRequest);
    $adminEditData = json_decode($adminEditResponse->getContent(), true);

    assertTest("Admin can successfully edit fee particular", !empty($adminEditData['success']) && $adminEditData['success'] === true);

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n❌ ERROR DURING VERIFICATION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
} finally {
    DB::rollBack();
}

echo "\n========================================================\n";
echo "ALL TESTS PASSED: {$passedTests} / {$totalTests} (Exit Code 0)\n";
echo "========================================================\n";
exit(0);
