<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "=== TASK 72 VERIFICATION SCRIPT ===\n";

$passCount = 0;
function assertTest($condition, $message) {
    global $passCount;
    if (!$condition) {
        echo "❌ FAILED: {$message}\n";
        exit(1);
    }
    echo "✓ PASSED: {$message}\n";
    $passCount++;
}

// 1. Verify merchant number exists in blade template
$bladeContent = file_get_contents(resource_path('views/student/fees/index.blade.php'));
assertTest(strpos($bladeContent, '01766305059') !== false, 'Merchant number 01766305059 is present in fees index blade view');
assertTest(strpos($bladeContent, 'BKASH_MANUAL') !== false, 'BKASH_MANUAL distinct option is present in fees modal');
assertTest(strpos($bladeContent, 'copyMerchantNumber') !== false, 'copyMerchantNumber JS helper is present in blade view');

// 2. Setup mock student and invoice
$student = Student::first();
assertTest($student !== null, 'A student exists in database');
$user = $student->user ?: User::first();
Auth::login($user);

$invoice = Invoice::where('student_id', $student->id)->where('due_amount', '>', 0)->first();
if (!$invoice) {
    $invoice = Invoice::create([
        'invoice_no'     => 'INV-TEST-' . time(),
        'student_id'     => $student->id,
        'category'       => 'SEMESTER',
        'title'          => 'Test Fee Invoice',
        'amount'         => 1000,
        'payable_amount' => 1000,
        'paid_amount'    => 0,
        'due_amount'     => 1000,
        'status'         => 'UNPAID',
    ]);
}

$controller = app(\App\Http\Controllers\Student\FeeController::class);

// 3. Test Invalid TrxID length (e.g. 8 chars)
$invalidReq8 = Request::create("/student/fees/invoices/{$invoice->id}/pay", 'POST', [
    'amount'         => 500,
    'payment_method' => 'BKASH_MANUAL',
    'sender_number'  => '01712345678',
    'transaction_id' => '12345678', // only 8 chars
]);

try {
    $controller->payInvoice($invalidReq8, $invoice);
    assertTest(false, 'Should throw ValidationException for 8-char TrxID');
} catch (\Illuminate\Validation\ValidationException $e) {
    assertTest(isset($e->errors()['transaction_id']), 'ValidationException caught for 8-char TrxID');
}

// 4. Test Invalid TrxID characters (e.g. symbols)
$invalidReqSym = Request::create("/student/fees/invoices/{$invoice->id}/pay", 'POST', [
    'amount'         => 500,
    'payment_method' => 'BKASH_MANUAL',
    'sender_number'  => '01712345678',
    'transaction_id' => '12345!@#90',
]);

try {
    $controller->payInvoice($invalidReqSym, $invoice);
    assertTest(false, 'Should throw ValidationException for TrxID with symbols');
} catch (\Illuminate\Validation\ValidationException $e) {
    assertTest(isset($e->errors()['transaction_id']), 'ValidationException caught for TrxID containing special characters');
}

// 5. Test Invalid sender mobile format
$invalidReqPhone = Request::create("/student/fees/invoices/{$invoice->id}/pay", 'POST', [
    'amount'         => 500,
    'payment_method' => 'BKASH_MANUAL',
    'sender_number'  => '12345',
    'transaction_id' => '8N7A6B5C4D',
]);

try {
    $controller->payInvoice($invalidReqPhone, $invoice);
    assertTest(false, 'Should throw ValidationException for invalid sender phone');
} catch (\Illuminate\Validation\ValidationException $e) {
    assertTest(isset($e->errors()['sender_number']), 'ValidationException caught for invalid phone number');
}

// 6. Test Valid 10-char TrxID submission
$testTrxId = 'TEST' . substr(strtoupper(md5(uniqid())), 0, 6); // exactly 10 chars
$validReq = Request::create("/student/fees/invoices/{$invoice->id}/pay", 'POST', [
    'amount'         => 200,
    'payment_method' => 'BKASH_MANUAL',
    'sender_number'  => '01711223344',
    'transaction_id' => $testTrxId,
    'remarks'        => 'Test manual verification',
]);

$response = $controller->payInvoice($validReq, $invoice);
assertTest($response->isRedirection(), 'Successful manual submission redirects back with success flash');

$createdPayment = Payment::where('transaction_id', $testTrxId)->first();
assertTest($createdPayment !== null, 'Payment record was created in database');
assertTest($createdPayment->status === 'PENDING', 'Payment record status is PENDING awaiting Admin approval');
assertTest($createdPayment->payment_method === 'BKASH', 'Payment method is saved as valid DB enum BKASH');
assertTest($createdPayment->sender_number === '01711223344', 'Sender number 01711223344 is saved correctly');
assertTest(strlen($createdPayment->transaction_id) === 10, 'Transaction ID length is exactly 10');

// 7. Test Duplicate TrxID Prevention
$dupReq = Request::create("/student/fees/invoices/{$invoice->id}/pay", 'POST', [
    'amount'         => 200,
    'payment_method' => 'BKASH_MANUAL',
    'sender_number'  => '01711223344',
    'transaction_id' => $testTrxId, // duplicate TrxID!
]);

$dupResponse = $controller->payInvoice($dupReq, $invoice);
$sessionErrors = session('error');
assertTest(!empty($sessionErrors) && strpos($sessionErrors, 'ইতিপূর্বে ব্যবহার করা হয়েছে') !== false, 'Duplicate TrxID submission is blocked with Bengali duplicate warning');

// 8. Test Admin Approval flow
$accountsController = app(\App\Http\Controllers\Admin\AccountsController::class);
$dueBefore = $invoice->fresh()->due_amount;
$accountsController->approvePayment($createdPayment);

$createdPayment->refresh();
assertTest($createdPayment->status === 'APPROVED', 'Admin approves payment successfully (status changed to APPROVED)');
$dueAfter = $invoice->fresh()->due_amount;
assertTest($dueAfter === max(0, $dueBefore - 200), 'Invoice due amount is properly adjusted by approved payment amount');

// 9. Verify _history is excluded from step1Particulars
$feeService = app(\App\Services\StudentFeeService::class);
$breakdown = $feeService->getStudentFeeBreakdown($student, $student->enrollments->first()?->course_id, $student->enrollments->first()?->current_semester_id);
$partNames = array_column($breakdown['step1Particulars'], 'name');
assertTest(!in_array('_history', $partNames, true), '_history is never added as a fee row in step1Particulars');

// Cleanup test payment
$createdPayment->delete();

echo "\n🎉 ALL {$passCount} ASSERTIONS PASSED! Task 72 is verified successfully with Exit Code 0.\n";
exit(0);
