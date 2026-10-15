<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\AdmissionForm;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Services\AccountingService;

$passed = 0;
$total = 0;

function assertCondition($desc, $cond) {
    global $passed, $total;
    $total++;
    if ($cond) {
        $passed++;
        echo " [PASS] $desc\n";
    } else {
        echo " [FAIL] $desc\n";
    }
}

echo "=== Verifying Payment Method String Column & Admission Confirmation ===\n\n";

// 1. Verify DB Schema for payments.payment_method
$col = DB::select("SHOW COLUMNS FROM payments WHERE Field = 'payment_method'");
assertCondition("Column payment_method exists in payments table", !empty($col));
$colType = strtolower($col[0]->Type ?? '');
echo "   -> Column Type detected: $colType\n";
assertCondition("Column payment_method is VARCHAR (not ENUM)", str_contains($colType, 'varchar'));

// 2. Test inserting Payment records with various real-world payment methods
$dummyStudent = Student::create([
    'student_code' => 'STU-TEST-' . time(),
    'name' => 'Test Student',
    'email' => 'test_' . time() . '@example.com',
    'phone' => '01711111111',
]);

$dummyInvoice = Invoice::create([
    'invoice_no' => 'INV-TEST-' . time(),
    'student_id' => $dummyStudent->id,
    'title' => 'Test Invoice',
    'amount' => 1500,
    'payable_amount' => 1500,
    'paid_amount' => 0,
    'due_amount' => 1500,
    'status' => 'UNPAID',
]);

// Test 'Bank'
$p1 = Payment::create([
    'payment_no'     => 'TEST-P1-' . time(),
    'invoice_id'     => $dummyInvoice->id,
    'student_id'     => $dummyStudent->id,
    'amount'         => 500,
    'payment_method' => 'Bank',
    'status'         => 'APPROVED',
    'paid_at'        => now(),
]);
assertCondition("Payment created successfully with method 'Bank'", $p1 && $p1->payment_method === 'Bank');

// Test 'Bank Transfer'
$p2 = Payment::create([
    'payment_no'     => 'TEST-P2-' . time(),
    'invoice_id'     => $dummyInvoice->id,
    'student_id'     => $dummyStudent->id,
    'amount'         => 500,
    'payment_method' => 'Bank Transfer',
    'status'         => 'APPROVED',
    'paid_at'        => now(),
]);
assertCondition("Payment created successfully with method 'Bank Transfer'", $p2 && $p2->payment_method === 'Bank Transfer');

// Test 'bKash' (mixed case)
$p3 = Payment::create([
    'payment_no'     => 'TEST-P3-' . time(),
    'invoice_id'     => $dummyInvoice->id,
    'student_id'     => $dummyStudent->id,
    'amount'         => 500,
    'payment_method' => 'bKash',
    'status'         => 'APPROVED',
    'paid_at'        => now(),
]);
assertCondition("Payment created successfully with method 'bKash'", $p3 && $p3->payment_method === 'bKash');

// 3. Test AccountingService::createAdmissionInvoice with fresh student, fresh enrollment, and 'Bank'
$course = Course::first();
$batch = Batch::first();

$freshStudent = Student::create([
    'student_code' => 'STU-ADM-' . time(),
    'name' => 'Fresh Applicant',
    'email' => 'fresh_' . time() . '@example.com',
    'phone' => '01899999999',
]);

$freshEnrollment = Enrollment::create([
    'student_id' => $freshStudent->id,
    'course_id' => $course?->id ?? 1,
    'batch_id' => $batch?->id ?? 1,
    'status' => 'ACTIVE',
    'enrolled_at' => now(),
]);

$freshAdmission = AdmissionForm::create([
    'application_no' => 'APP-TEST-BANK-' . time(),
    'student_id' => $freshStudent->id,
    'gender' => 'MALE',
    'status' => 'APPROVED',
    'manual_payment_method' => 'Bank',
    'manual_trx_id' => 'TXN-BANK-' . time(),
    'manual_sender_phone' => '01888888888',
    'manual_paid_amount' => 1500,
]);

$createdInv = AccountingService::createAdmissionInvoice(
    $freshStudent,
    $freshAdmission,
    $freshEnrollment,
    1500,
    [
        'method'        => 'Bank',
        'trx_id'        => 'T2610140702021556672355',
        'sender_number' => '8649801904',
        'notes'         => 'Admission fee payment approved by Admin during admission review',
    ]
);

assertCondition("Admission Invoice created via AccountingService", $createdInv instanceof Invoice);

$recordedPmt = Payment::where('invoice_id', $createdInv->id)->latest('id')->first();
assertCondition("Payment recorded against admission invoice with method 'Bank'", $recordedPmt && $recordedPmt->payment_method === 'Bank');
if ($recordedPmt) {
    echo "   -> Recorded Payment No: {$recordedPmt->payment_no}, Method: {$recordedPmt->payment_method}, Amount: {$recordedPmt->amount}\n";
}

// Clean up test records with forceDelete
Payment::whereIn('id', [$p1->id, $p2->id, $p3->id])->delete();
if ($recordedPmt) $recordedPmt->delete();
$dummyInvoice->forceDelete();
$createdInv->forceDelete();
$freshAdmission->forceDelete();
$freshEnrollment->forceDelete();
$dummyStudent->forceDelete();
$freshStudent->forceDelete();

echo "\nSummary: $passed / $total assertions passed.\n";

if ($passed === $total) {
    echo "ALL TESTS PASSED SUCCESSFULLY! (Exit Code 0)\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED! (Exit Code 1)\n";
    exit(1);
}
