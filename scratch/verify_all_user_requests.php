<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Course;
use App\Models\Batch;
use App\Models\Student;
use App\Models\AdmissionForm;
use App\Models\CourseCoupon;
use App\Models\User;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Admin\AdmissionController;
use App\Http\Controllers\Public\AdmissionFormController;
use App\Http\Controllers\Public\WaiverApplicationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;

echo "════════════════════════════════════════════════════════════════\n";
echo "       STARTING COMPREHENSIVE VERIFICATION OF ALL 5 REQUESTS     \n";
echo "════════════════════════════════════════════════════════════════\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(bool $condition, string $testName) {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$testName}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$testName}\n";
        $failCount++;
    }
}

// ── TEST 1: Course Code Uniqueness and Serial Ordering ──────────────────────
echo "--- TEST 1: Course Code Uniqueness & Serial Ordering ---\n";

$courses = Course::orderByRaw('CAST(code AS UNSIGNED) ASC, code ASC')->get();
$codes = $courses->pluck('code')->toArray();
$uniqueCodes = array_unique($codes);
assertTest(count($codes) === count($uniqueCodes), "All courses have strictly unique codes (" . count($codes) . " courses)");

// Verify serial ordering
$isSerial = true;
for ($i = 0; $i < count($codes) - 1; $i++) {
    if ((int)$codes[$i] > (int)$codes[$i+1]) {
        $isSerial = false;
        break;
    }
}
assertTest($isSerial, "Courses are sorted serially by code: " . implode(', ', array_slice($codes, 0, 8)) . "...");

// Try creating a course with an existing code via CourseController
$courseController = app(CourseController::class);
$existingCode = $codes[0];
$duplicateThrown = false;
try {
    $req = Request::create('/admin/courses', 'POST', [
        'name' => 'Duplicate Code Test Course',
        'code' => $existingCode,
        'department' => 'Test Dept',
        'type' => 'SEMESTER_BASED',
        'duration_value' => 1,
        'duration_unit' => 'YEAR',
    ]);
    $courseController->store($req);
} catch (ValidationException $e) {
    $duplicateThrown = true;
}
assertTest($duplicateThrown, "CourseController rejects duplicate course code '{$existingCode}'");


// ── TEST 2: Course start/end month removal, Batch start/end dates ────────────
echo "\n--- TEST 2: Course Start/End Dates Removed, Batch End Date Supported ---\n";
// Create a new batch with start_date and expected_end_date
$batchController = app(BatchController::class);
$targetCourse = $courses->first();
$batchEndTestThrown = false;
try {
    // End date before start date should fail
    $req = Request::create('/admin/batches', 'POST', [
        'name' => 'Invalid Date Batch ' . uniqid(),
        'course_id' => $targetCourse->id,
        'start_date' => '2026-06-01',
        'expected_end_date' => '2026-05-01', // earlier than start date
    ]);
    $batchController->store($req);
} catch (ValidationException $e) {
    $batchEndTestThrown = true;
}
assertTest($batchEndTestThrown, "BatchController rejects expected_end_date that is before start_date");


// ── TEST 3: Unique Batch Name per Course ─────────────────────────────────────
echo "\n--- TEST 3: Unique Batch Name per Course ---\n";
$uniqueBatchName = 'UniqueBatchTest-' . uniqid();
$validStartDate1 = '2028-01-10';
$validStartDate2 = '2028-02-10';

// Create first batch
$batch1 = Batch::create([
    'name' => $uniqueBatchName,
    'batch_code' => 'UB1-' . uniqid(),
    'course_id' => $targetCourse->id,
    'start_date' => $validStartDate1,
    'expected_end_date' => '2028-12-31',
    'status' => 'ACTIVE',
]);
assertTest($batch1->exists, "Created Batch 1 with name '{$uniqueBatchName}'");

// Try creating second batch with the same name in the same course via BatchController
$duplicateBatchNameThrown = false;
try {
    $req = Request::create('/admin/batches', 'POST', [
        'name' => $uniqueBatchName,
        'course_id' => $targetCourse->id,
        'start_date' => $validStartDate2,
    ]);
    $batchController->store($req);
} catch (ValidationException $e) {
    $duplicateBatchNameThrown = true;
}
assertTest($duplicateBatchNameThrown, "BatchController rejects duplicate batch name '{$uniqueBatchName}' in the same course");

// Try creating batch with same name in a DIFFERENT course (should be allowed)
$differentCourse = $courses->skip(1)->first();
$reqDiff = Request::create('/admin/batches', 'POST', [
    'name' => $uniqueBatchName,
    'course_id' => $differentCourse->id,
    'start_date' => '2028-03-15',
]);
try {
    $batchController->store($reqDiff);
    assertTest(true, "BatchController allows same batch name '{$uniqueBatchName}' in a DIFFERENT course");
} catch (\Exception $e) {
    assertTest(false, "BatchController failed on different course: " . $e->getMessage());
}

// Clean up test batches
Batch::where('name', $uniqueBatchName)->forceDelete();


// ── TEST 4: Manual Admission Generates Student ID ────────────────────────────
echo "\n--- TEST 4: Manual Admission Generates Student ID Automatically ---\n";
$admController = app(AdmissionController::class);
$activeBatch = Batch::where('course_id', $targetCourse->id)->where('status', 'ACTIVE')->first();
if (!$activeBatch) {
    $activeBatch = Batch::create([
        'name' => 'Auto Batch ' . uniqid(),
        'batch_code' => 'AUT-' . uniqid(),
        'course_id' => $targetCourse->id,
        'start_date' => '2027-01-01',
        'status' => 'ACTIVE',
    ]);
}

$testPhone = '01799' . rand(100000, 999999);
$testEmail = 'manual_test_' . uniqid() . '@example.com';

$manualAdmissionReq = Request::create('/admin/admissions', 'POST', [
    'interested_course_id' => $targetCourse->id,
    'batch_id'             => $activeBatch->id,
    'applicant_name'       => 'আব্দুর রহমান ম্যানুয়াল',
    'phone'                => $testPhone,
    'email'                => $testEmail,
    'gender'               => 'Male',
    'lead_source'          => 'Direct',
]);

$response = $admController->store($manualAdmissionReq);

$createdStudent = Student::where('phone', $testPhone)->first();
assertTest(!empty($createdStudent), "Manual student created");
assertTest(!empty($createdStudent->student_code), "Student ID generated immediately: " . ($createdStudent->student_code ?? 'NULL'));
assertTest(strlen($createdStudent->student_code) === 11, "Student ID format is 11 digits (YYBBCCGRRRR): {$createdStudent->student_code}");
assertTest($createdStudent->status === 'ACTIVE', "Student status set to ACTIVE");
assertTest(!empty($createdStudent->temporary_password), "Temporary random password generated: {$createdStudent->temporary_password}");
assertTest(!empty($createdStudent->user_id), "Student user portal account created with ID: {$createdStudent->user_id}");

$createdForm = AdmissionForm::where('student_id', $createdStudent->id)->first();
assertTest($createdForm && $createdForm->status === 'APPROVED', "Manual admission form automatically APPROVED");
assertTest($createdStudent->enrollments()->where('batch_id', $activeBatch->id)->exists(), "Active enrollment created for student in batch");


// ── TEST 5: Course-wise Manual Coupon Code System ───────────────────────────
echo "\n--- TEST 5: Course-wise Manual Coupon Code System ---\n";
$couponCode = 'TESTCOUP' . rand(100, 999);
$coupon = CourseCoupon::create([
    'course_id'       => $targetCourse->id,
    'code'            => $couponCode,
    'discount_type'   => 'FIXED',
    'discount_amount' => 250.00,
    'max_uses'        => 50,
    'is_active'       => true,
]);
assertTest($coupon->exists, "Created course coupon '{$couponCode}' with ৳250 fixed discount");

// Test API lookup for Coupon
$waiverController = app(WaiverApplicationController::class);
$lookupReq = Request::create('/api/waiver-lookup', 'GET', [
    'code' => $couponCode,
    'course_id' => $targetCourse->id,
]);
$lookupRes = $waiverController->lookup($lookupReq);
$data = json_decode($lookupRes->getContent(), true);

assertTest($data['valid'] === true, "Waiver lookup returns valid=true for coupon '{$couponCode}'");
assertTest($data['type'] === 'COUPON', "Waiver lookup correctly identifies type as 'COUPON'");
assertTest($data['discount_amount'] == 250, "Waiver lookup returns correct discount_amount: 250");

// Test coupon on wrong course
$lookupWrongReq = Request::create('/api/waiver-lookup', 'GET', [
    'code' => $couponCode,
    'course_id' => 999999, // wrong course
]);
$lookupWrongRes = $waiverController->lookup($lookupWrongReq);
$wrongData = json_decode($lookupWrongRes->getContent(), true);
assertTest($wrongData['valid'] === false, "Waiver lookup rejects coupon when used on another course");

// Test toggle active
$coupon->is_active = false;
$coupon->save();
$lookupInactiveReq = Request::create('/api/waiver-lookup', 'GET', [
    'code' => $couponCode,
    'course_id' => $targetCourse->id,
]);
$inactiveData = json_decode($waiverController->lookup($lookupInactiveReq)->getContent(), true);
assertTest($inactiveData['valid'] === false, "Inactive coupon rejected by lookup API");

// Reactivate for payment test
$coupon->is_active = true;
$coupon->save();


// ── TEST 6: Manual Merchant Payment Support During Admission ────────────────
echo "\n--- TEST 6: Prior / Manual Merchant Payment Support During Admission ---\n";
// Create a public pending admission form
$pubStudent = Student::create([
    'name' => 'মুহাম্মদ ইব্রাহীম',
    'phone' => '01888' . rand(100000, 999999),
    'email' => 'manual_pay_' . uniqid() . '@example.com',
    'status' => 'PENDING',
]);
$pubForm = AdmissionForm::create([
    'source' => 'PUBLIC',
    'application_no' => AdmissionForm::generateApplicationNo(),
    'student_id' => $pubStudent->id,
    'interested_course_id' => $targetCourse->id,
    'batch_id' => $activeBatch->id,
    'status' => 'PENDING',
]);

$admFormController = app(AdmissionFormController::class);
$manualTrx = 'BKASH' . strtoupper(uniqid());
$senderPhone = '01711223344';

$payReq = Request::create('/apply/payment/' . $pubForm->application_no, 'POST', [
    'waiver_code'           => $couponCode,
    'payment_gateway'       => 'manual',
    'manual_payment_method' => 'bKash',
    'manual_trx_id'         => $manualTrx,
    'manual_sender_phone'   => $senderPhone,
    'manual_payment_notes'  => 'বিকাশ মার্চেন্টে দুপুর ১২:৩০ মিনিটে পাঠানো হয়েছে',
]);

$payResponse = $admFormController->processPayment($payReq, $pubForm->application_no);
$pubForm->refresh();

assertTest($pubForm->manual_trx_id === $manualTrx, "Manual TrxID recorded: {$pubForm->manual_trx_id}");
assertTest($pubForm->manual_payment_method === 'bKash', "Manual payment method recorded: {$pubForm->manual_payment_method}");
assertTest($pubForm->manual_sender_phone === $senderPhone, "Manual sender phone recorded: {$pubForm->manual_sender_phone}");
assertTest(!empty($pubForm->manual_payment_date), "Manual payment date recorded: {$pubForm->manual_payment_date}");
assertTest($pubForm->waiver_code === $couponCode, "Course coupon code '{$couponCode}' applied to admission form");
assertTest($pubForm->discount_amount == 250, "Coupon discount of ৳250 deducted from fee");

// Verify view renders without any errors
$htmlPayment = View::make('apply.payment', [
    'form' => $pubForm,
    'course' => $targetCourse,
    'batch' => $activeBatch,
    'baseFee' => 1000.0,
    'discountAmount' => 250.0,
    'netPayable' => 750.0,
    'sslActive' => true,
    'bkashActive' => true,
])->render();
assertTest(strpos($htmlPayment, 'মার্চেন্ট নাম্বারে পূর্বেই পেমেন্ট করা থাকলে') !== false, "Payment view contains manual merchant option");
assertTest(strpos($htmlPayment, 'manual_trx_id') !== false, "Payment view contains manual_trx_id field");

$htmlSuccess = View::make('apply.success', [
    'form' => $pubForm,
    'transaction' => null,
    'instituteName' => 'Islamic Online Madrasah',
    'instituteTagline' => 'Through Knowledge, Towards Jannah',
])->render();
assertTest(strpos($htmlSuccess, $manualTrx) !== false, "Success view displays manual TrxID: {$manualTrx}");

$adminUser = User::where('role', 'admin')->first() ?: User::first();
\Illuminate\Support\Facades\Auth::login($adminUser);

$htmlAdminShow = View::make('admin.admissions.show', [
    'admission' => $pubForm->load(['student', 'interestedCourse', 'reviewer', 'batch']),
    'activeBatches' => collect([$activeBatch]),
    'allCourses' => collect([$targetCourse]),
])->render();
assertTest(strpos($htmlAdminShow, $manualTrx) !== false, "Admin admission show view displays manual TrxID: {$manualTrx}");

// Clean up test coupon and temporary data
$coupon->forceDelete();

echo "\n════════════════════════════════════════════════════════════════\n";
echo "                   VERIFICATION SUMMARY                         \n";
echo "════════════════════════════════════════════════════════════════\n";
echo "Total Tests: " . ($passCount + $failCount) . " | PASSED: {$passCount} | FAILED: {$failCount}\n";

if ($failCount === 0) {
    echo "✓ ALL TESTS PASSED SUCCESSFULLY! (EXIT CODE 0)\n";
    exit(0);
} else {
    echo "✕ SOME TESTS FAILED! (EXIT CODE 1)\n";
    exit(1);
}
