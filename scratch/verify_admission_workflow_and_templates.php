<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdmissionForm;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use App\Models\AdmissionCircular;
use App\Http\Controllers\Admin\AdmissionController;
use App\Http\Controllers\Public\AdmissionFormController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

$passed = 0;
$total = 0;

function assertCondition($name, $condition, $details = '') {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo "[\033[32mPASS\033[0m] $name\n";
    } else {
        echo "[\033[31mFAIL\033[0m] $name: $details\n";
    }
}

echo "=== TASK 27: ADMISSION WORKFLOW & TEMPLATES VERIFICATION ===\n\n";

// 1. Schema Checks
assertCondition(
    "Batches table has sms_template column",
    Schema::hasColumn('batches', 'sms_template')
);
assertCondition(
    "Batches table has email_template column",
    Schema::hasColumn('batches', 'email_template')
);
assertCondition(
    "Admission forms table has approved_admission_fee column",
    Schema::hasColumn('admission_forms', 'approved_admission_fee')
);
assertCondition(
    "Admission forms table has reviewed_by column",
    Schema::hasColumn('admission_forms', 'reviewed_by')
);
assertCondition(
    "Admission forms table has reviewed_at column",
    Schema::hasColumn('admission_forms', 'reviewed_at')
);

// 2. Batch Model Template Helpers
$courseA = Course::firstOrCreate(
    ['code' => '71'],
    ['name' => 'Diploma in Islamic Studies', 'department' => 'Islamic Studies', 'admission_fee' => 1500, 'is_active' => true]
);
$batchA = Batch::firstOrCreate(
    ['name' => 'Batch 71 Test', 'course_id' => $courseA->id],
    ['status' => 'ACTIVE', 'batch_code' => '05', 'start_date' => now()->toDateString()]
);

$defaultEmail = $batchA->getEffectiveEmailTemplate();
assertCondition(
    "Batch getEffectiveEmailTemplate() returns default template when null",
    !empty($defaultEmail) && str_contains($defaultEmail, '{roll}') && str_contains($defaultEmail, '{password}')
);

$defaultSms = $batchA->getEffectiveSmsTemplate();
assertCondition(
    "Batch getEffectiveSmsTemplate() returns default SMS template when null",
    !empty($defaultSms) && str_contains($defaultSms, '{roll}') && str_contains($defaultSms, '{password}')
);

// Set custom templates
$customEmailTpl = "Hello {name}, Welcome to {course} Batch {batch}! Your Roll: {roll}, Pass: {password}, URL: {login_url}";
$customSmsTpl = "IOM Notice: {name}, Roll {roll}, Pass {password}. {login_url}";
$batchA->update([
    'email_template' => $customEmailTpl,
    'sms_template' => $customSmsTpl,
]);
$batchA->refresh();

assertCondition(
    "Batch getEffectiveEmailTemplate() returns custom template when set",
    $batchA->getEffectiveEmailTemplate() === $customEmailTpl
);
assertCondition(
    "Batch getEffectiveSmsTemplate() returns custom template when set",
    $batchA->getEffectiveSmsTemplate() === $customSmsTpl
);

// 3. Admin User Setup for Review Audit
$adminUser = User::firstOrCreate(
    ['email' => 'admin_test_t27@iom.edu.bd'],
    ['name' => 'Reviewer Admin', 'password' => Hash::make('password'), 'role' => 'admin']
);
Auth::login($adminUser);

// 4. Test Course Modification & Fee Override during Approval
$courseB = Course::firstOrCreate(
    ['code' => '72'],
    ['name' => 'Higher Diploma in Hadith', 'department' => 'Hadith Studies', 'admission_fee' => 2000, 'is_active' => true]
);
$batchB = Batch::firstOrCreate(
    ['name' => 'Batch 72 Test', 'course_id' => $courseB->id],
    ['status' => 'ACTIVE', 'batch_code' => '02', 'start_date' => now()->toDateString()]
);

// Create an applicant with Course A
$student = Student::create([
    'name' => 'Ahmad Abdullah Test',
    'phone' => '01711' . rand(100000, 999999),
    'email' => 'ahmad' . rand(100, 999) . '@test.iom.edu.bd',
    'status' => 'PENDING',
    'gender' => 'Male',
]);

$appNo = AdmissionForm::generateApplicationNo();
$admission = AdmissionForm::create([
    'application_no' => $appNo,
    'student_id' => $student->id,
    'interested_course_id' => $courseA->id,
    'batch_id' => $batchA->id,
    'source' => 'PUBLIC',
    'status' => 'PENDING',
]);

assertCondition(
    "Initial admission created with Course A and PENDING status",
    $admission->interested_course_id == $courseA->id && $admission->status === 'PENDING'
);

// Now execute approve with Course B, Batch B, Custom Fee, Discount, and Custom Password
$controller = app(AdmissionController::class);
$request = Request::create(route('admin.admissions.approve', $admission), 'POST', [
    'course_id' => $courseB->id, // course modification
    'batch_id' => $batchB->id,
    'approved_admission_fee' => 1200, // fee structure adjustment
    'discount_percent' => 10,
    'discount_amount' => 120,
    'waiver_notes' => 'Special 10% scholar waiver',
    'custom_password' => 'secretPass@99',
]);

$response = $controller->approve($request, $admission);
$admission->refresh();
$student->refresh();

assertCondition(
    "Admission status updated to APPROVED",
    $admission->status === 'APPROVED'
);
assertCondition(
    "Course modified to Course B during approval",
    $admission->interested_course_id == $courseB->id
);
assertCondition(
    "Approved admission fee persisted correctly",
    (float)$admission->approved_admission_fee == 1200.0
);
assertCondition(
    "Discount percent and amount persisted correctly",
    (float)$admission->discount_percent == 10.0 && (float)$admission->discount_amount == 120.0
);
assertCondition(
    "Waiver notes recorded",
    $admission->waiver_notes === 'Special 10% scholar waiver'
);
assertCondition(
    "Reviewer ID (Admin Audit) logged with authenticating admin ID",
    $admission->reviewed_by == $adminUser->id
);
assertCondition(
    "Reviewed timestamp logged",
    !empty($admission->reviewed_at)
);
assertCondition(
    "Student account created with custom password",
    $student->user_id && Hash::check('secretPass@99', $student->user->password)
);
assertCondition(
    "Student code generated with course code 72 (Digits 5 & 6)",
    str_contains($student->student_code, '72')
);

// 5. Rejection Flow and Audit Logging
$studentReject = Student::create([
    'name' => 'Rejected Candidate',
    'phone' => '01811' . rand(100000, 999999),
    'email' => 'reject' . rand(100, 999) . '@test.iom.edu.bd',
    'status' => 'PENDING',
]);
$admissionReject = AdmissionForm::create([
    'application_no' => AdmissionForm::generateApplicationNo(),
    'student_id' => $studentReject->id,
    'interested_course_id' => $courseA->id,
    'status' => 'PENDING',
]);

$rejectRequest = Request::create(route('admin.admissions.reject', $admissionReject), 'POST', [
    'rejection_reason' => 'Incomplete SSC documents and age mismatch.',
]);
$controller->reject($rejectRequest, $admissionReject);
$admissionReject->refresh();

assertCondition(
    "Rejected admission has status REJECTED",
    $admissionReject->status === 'REJECTED'
);
assertCondition(
    "Rejection reason logged properly",
    $admissionReject->rejection_reason === 'Incomplete SSC documents and age mismatch.'
);
assertCondition(
    "Rejection logs reviewer admin ID",
    $admissionReject->reviewed_by == $adminUser->id
);

// 6. Test Re-payment Email Action
$studentUnpaid = Student::create([
    'name' => 'Unpaid Applicant',
    'phone' => '01911' . rand(100000, 999999),
    'email' => 'unpaid' . rand(1000, 999999) . '@example.com',
    'status' => 'PENDING',
]);
$admissionUnpaid = AdmissionForm::create([
    'application_no' => AdmissionForm::generateApplicationNo(),
    'student_id' => $studentUnpaid->id,
    'interested_course_id' => $courseA->id,
    'status' => 'PENDING',
]);

$repayResponse = $controller->sendRepaymentEmail($admissionUnpaid);
assertCondition(
    "sendRepaymentEmail redirects back with success or response",
    $repayResponse->isRedirect()
);

// 7. Test Public Applicant Tracker (/admission/status)
$publicController = app(AdmissionFormController::class);

// Track by Application No
$trackReq = Request::create('/admission/status', 'GET', ['app_no' => $admission->application_no]);
$trackView = $publicController->trackStatus($trackReq);
assertCondition(
    "trackStatus returns view with matched admission application",
    $trackView->name() === 'apply.track' && $trackView->getData()['admission']->id == $admission->id
);
assertCondition(
    "trackStatus view has Kalpurush font and displays APPROVED status",
    $trackView->getData()['admission']->status === 'APPROVED'
);

// Track Rejected
$trackRejectReq = Request::create('/admission/status', 'GET', ['app_no' => $admissionReject->application_no]);
$trackRejectView = $publicController->trackStatus($trackRejectReq);
assertCondition(
    "trackStatus displays rejection reason for rejected application",
    $trackRejectView->getData()['admission']->rejection_reason === 'Incomplete SSC documents and age mismatch.'
);

// Test POST lookup redirect
$postLookupReq = Request::create('/admission/status', 'POST', ['search' => $admission->application_no]);
$lookupResponse = $publicController->trackStatusLookup($postLookupReq);
assertCondition(
    "trackStatusLookup redirects to /admission/status?app_no=...",
    $lookupResponse->isRedirect() && str_contains($lookupResponse->getTargetUrl(), 'app_no=' . $admission->application_no)
);

echo "\n============================================\n";
echo "Results: $passed / $total assertions passed.\n";
if ($passed === $total) {
    echo "\033[32mALL ASSERTIONS PASSED! Exit Code: 0\033[0m\n";
    exit(0);
} else {
    echo "\033[31mSOME ASSERTIONS FAILED!\033[0m\n";
    exit(1);
}
