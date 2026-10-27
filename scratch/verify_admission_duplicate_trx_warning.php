<?php

/**
 * Verification Script: Duplicate Transaction ID Warning on Admission Payment
 * Tests:
 * 1. Real-time AJAX endpoint /apply/check-trx-id returns duplicate warning for used TrxID
 * 2. ProcessPayment validation rejects duplicate TrxID with Bengali warning and sets session('error')
 * 3. Blade template payment.blade.php renders error warning banner and field-level error
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdmissionForm;
use App\Models\Course;
use App\Models\GatewayTransaction;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

echo "=== Verifying Duplicate Transaction ID Warning on Admission Payment ===\n";

$passCount = 0;
$totalCount = 0;

function assertCondition($desc, $cond) {
    global $passCount, $totalCount;
    $totalCount++;
    if ($cond) {
        echo "  [PASS] {$desc}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$desc}\n";
    }
}

// 1. Setup in-memory / temporary test data
$testTrxId = 'TEST' . rand(100000, 999999);

// Create student 1 with used TrxID
$student1 = Student::create([
    'name' => 'Existing Student',
    'phone' => '01711000001',
    'email' => 'existing' . rand(100, 999) . '@example.com',
    'status' => 'LEAD',
]);

$course = Course::first() ?? Course::create([
    'name' => 'General Alim Course',
    'course_code' => 'GAC-' . rand(10, 99),
    'admission_fee' => 1500,
    'status' => 'ACTIVE',
]);

$existingForm = AdmissionForm::create([
    'application_no' => 'ADM-EXISTING-' . rand(1000, 9999),
    'student_id' => $student1->id,
    'interested_course_id' => $course->id,
    'source' => 'PUBLIC',
    'manual_payment_method' => 'bKash',
    'manual_trx_id' => $testTrxId,
    'manual_sender_phone' => '01711000001',
    'manual_paid_amount' => 1500,
    'status' => 'PENDING',
]);

// Create student 2 who attempts to use the SAME TrxID
$student2 = Student::create([
    'name' => 'New Applicant',
    'phone' => '01711000002',
    'email' => 'applicant' . rand(100, 999) . '@example.com',
    'status' => 'LEAD',
]);

$newForm = AdmissionForm::create([
    'application_no' => 'ADM-NEW-' . rand(1000, 9999),
    'student_id' => $student2->id,
    'interested_course_id' => $course->id,
    'source' => 'PUBLIC',
    'status' => 'PENDING',
]);

echo "\nTest Setup:\n";
echo "  Existing Form: {$existingForm->application_no} with TrxID: {$testTrxId}\n";
echo "  New Form: {$newForm->application_no} attempting to use same TrxID: {$testTrxId}\n\n";

// ─────────────────────────────────────────────────────────────
// Test 1: AJAX Endpoint /apply/check-trx-id
// ─────────────────────────────────────────────────────────────
echo "1. Testing AJAX duplicate check endpoint (checkTrxIdAjax):\n";

$req1 = Request::create('/apply/check-trx-id', 'GET', [
    'trx_id' => $testTrxId,
    'application_no' => $newForm->application_no,
]);

$controller = app(\App\Http\Controllers\Public\AdmissionFormController::class);
$res1 = $controller->checkTrxIdAjax($req1);
$data1 = json_decode($res1->getContent(), true);

assertCondition('Duplicate TrxID is detected by AJAX endpoint (duplicate == true)', $data1['duplicate'] === true);
assertCondition('Warning message contains Bengali notice mentioning TrxID', str_contains($data1['message'], $testTrxId) && str_contains($data1['message'], 'ইতিমধ্যে সিস্টেমে'));

// Test 1.2: A fresh unique TrxID
$req2 = Request::create('/apply/check-trx-id', 'GET', [
    'trx_id' => 'FRESH99999',
    'application_no' => $newForm->application_no,
]);
$res2 = $controller->checkTrxIdAjax($req2);
$data2 = json_decode($res2->getContent(), true);

assertCondition('Fresh unused TrxID returns duplicate == false', $data2['duplicate'] === false);

// ─────────────────────────────────────────────────────────────
// Test 2: ProcessPayment Server-side Validation
// ─────────────────────────────────────────────────────────────
echo "\n2. Testing ProcessPayment server-side validation with duplicate TrxID:\n";

$postReq = Request::create("/apply/payment/{$newForm->application_no}", 'POST', [
    'payment_gateway' => 'manual',
    'manual_payment_method' => 'bKash',
    'manual_sender_phone' => '01711000002',
    'manual_paid_amount' => 1500,
    'manual_trx_id' => $testTrxId,
]);

// Set session on request
$session = app('session.store');
$postReq->setLaravelSession($session);

$postRes = $controller->processPayment($postReq, $newForm->application_no);

assertCondition('ProcessPayment returns a redirect response on validation failure', $postRes->isRedirect());

$redirectSession = $postRes->getSession();
$flashedError = $redirectSession->get('error');
$errorBag = $redirectSession->get('errors');

assertCondition('session("error") is populated with Bengali duplicate TrxID message', !empty($flashedError) && str_contains($flashedError, $testTrxId) && str_contains($flashedError, 'ইতিমধ্যে সিস্টেমে অন্য একটি আবেদনের জন্য ব্যবহৃত হয়েছে'));
assertCondition('Validation errors bag has manual_trx_id error', $errorBag && $errorBag->has('manual_trx_id'));

// ─────────────────────────────────────────────────────────────
// Test 3: Blade Template Render & UI Elements
// ─────────────────────────────────────────────────────────────
echo "\n3. Testing payment.blade.php error rendering:\n";

$viewHtml = view('apply.payment', [
    'form' => $newForm,
    'course' => $course,
    'batch' => null,
    'baseFee' => 1500,
    'discountAmount' => 0,
    'netPayable' => 1500,
    'sslActive' => true,
    'bkashActive' => true,
    'offlineGateways' => [],
])->withErrors([
    'manual_trx_id' => "এই ট্রাঞ্জেকশন আইডিটি ({$testTrxId}) ইতিমধ্যে সিস্টেমে অন্য একটি আবেদনের জন্য ব্যবহৃত হয়েছে। একই TrxID দিয়ে একাধিকবার আবেদন করা যাবে না।"
])->render();

assertCondition('Blade renders error alert header "পেমেন্ট তথ্য যাচাইকরণে ত্রুটি হয়েছে:"', str_contains($viewHtml, 'পেমেন্ট তথ্য যাচাইকরণে ত্রুটি হয়েছে:'));
assertCondition('Blade renders specific field error message below TrxID input', str_contains($viewHtml, "এই ট্রাঞ্জেকশন আইডিটি ({$testTrxId}) ইতিমধ্যে সিস্টেমে"));
assertCondition('Blade highlights TrxID input with red error border and background', str_contains($viewHtml, '1.5px solid #dc2626') && str_contains($viewHtml, '#fef2f2'));
assertCondition('Blade opens manual payment container when errors exist', str_contains($viewHtml, 'id="manual-payment-details"') && str_contains($viewHtml, 'style="display:block;'));
assertCondition('Blade JS includes real-time Trx duplicate check function checkTrxDuplicateAjax', str_contains($viewHtml, 'function checkTrxDuplicateAjax'));
assertCondition('Blade JS includes submit blocker if TrxID is duplicate', str_contains($viewHtml, 'if (isTrxDuplicate)'));

// Clean up test records
$existingForm->delete();
$newForm->delete();
$student1->delete();
$student2->delete();

echo "\n==================================================\n";
echo "SUMMARY: {$passCount} of {$totalCount} assertions passed.\n";

if ($passCount === $totalCount) {
    echo "STATUS: ALL TESTS PASSED! (Exit Code 0)\n";
    exit(0);
} else {
    echo "STATUS: SOME TESTS FAILED!\n";
    exit(1);
}
