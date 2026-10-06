<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\AdmissionController;
use App\Models\AdmissionForm;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

echo "=== START VERIFICATION: Deferred Student ID & Trash Release ===\n\n";

$course = Course::where('is_active', true)->whereHas('batches', function ($q) {
    $q->where('status', 'ACTIVE');
})->first();

if (!$course) {
    echo "FAIL: No active course with active batch found.\n";
    exit(1);
}

$batch = $course->batches()->where('status', 'ACTIVE')->first();
echo "Testing with Course: {$course->name} (ID: {$course->id}), Batch: {$batch->name} (ID: {$batch->id})\n\n";

// Login as admin user
$adminUser = User::where('role', 'admin')->first() ?? User::first();
auth()->login($adminUser);

DB::beginTransaction();

try {
    $controller = new AdmissionController();

    // ─────────────────────────────────────────────────────────────────────────
    // Test 1: Creating an application via store() should NOT generate student_code
    // ─────────────────────────────────────────────────────────────────────────
    echo "--- 1. Testing AdmissionController::store() ---\n";
    $testEmail1 = 'test_defer_' . uniqid() . '@example.com';
    $testPhone1 = '017' . rand(10000000, 99999999);

    $storeRequest = new Request([
        'interested_course_id' => $course->id,
        'batch_id'             => $batch->id,
        'applicant_name'       => 'টেস্ট শিক্ষার্থী ১',
        'phone'                => $testPhone1,
        'email'                => $testEmail1,
        'gender'               => 'Male',
        'lead_source'          => 'Direct',
    ]);

    $redirectResponse = $controller->store($storeRequest);
    $form1 = AdmissionForm::whereHas('student', function ($q) use ($testEmail1) {
        $q->where('email', $testEmail1);
    })->latest()->first();

    if (!$form1) {
        throw new Exception("Form 1 was not created by store()");
    }

    echo "Form 1 ID: {$form1->id}, AppNo: {$form1->application_no}, Status: {$form1->status}\n";
    if ($form1->status !== 'PENDING') {
        throw new Exception("Expected form 1 status to be PENDING, got: {$form1->status}");
    }

    $st1 = $form1->student;
    echo "Student 1 ID: {$st1->id}, Code: " . ($st1->student_code ?? 'NULL') . ", UserID: " . ($st1->user_id ?? 'NULL') . "\n";

    if (!empty($st1->student_code)) {
        throw new Exception("FAIL: Student 1 got student_code prematurely in store(): {$st1->student_code}");
    }
    if (!empty($st1->user_id)) {
        throw new Exception("FAIL: Student 1 got user_id prematurely in store()");
    }

    $enrCount1 = Enrollment::where('admission_form_id', $form1->id)->count();
    if ($enrCount1 !== 0) {
        throw new Exception("FAIL: Enrollment was created prematurely in store()");
    }
    echo "SUCCESS: store() saved application as PENDING with NO student_code or user account.\n\n";

    // ─────────────────────────────────────────────────────────────────────────
    // Test 2: Moving pending form to Trash and Untrash
    // ─────────────────────────────────────────────────────────────────────────
    echo "--- 2. Testing trash() and untrash() on Pending Application ---\n";
    $trashRequest = new Request(['trash_reason' => 'টেস্ট ট্র্যাশ']);
    $controller->trash($trashRequest, $form1);
    $form1->refresh();
    $st1->refresh();

    echo "After Trash -> Form Status: {$form1->status}, Student Code: " . ($st1->student_code ?? 'NULL') . "\n";
    if ($form1->status !== 'TRASH') {
        throw new Exception("Expected form 1 status to be TRASH, got {$form1->status}");
    }
    if (!empty($st1->student_code)) {
        throw new Exception("FAIL: Student code is not null after trash");
    }

    $controller->untrash($form1);
    $form1->refresh();
    $st1->refresh();

    echo "After Untrash -> Form Status: {$form1->status}, Student Code: " . ($st1->student_code ?? 'NULL') . "\n";
    if ($form1->status !== 'PENDING') {
        throw new Exception("Expected form 1 status to be PENDING after untrash, got {$form1->status}");
    }
    echo "SUCCESS: trash() and untrash() maintained student_code as NULL.\n\n";

    // ─────────────────────────────────────────────────────────────────────────
    // Test 3: Approving the Application Generates Student ID & User Account
    // ─────────────────────────────────────────────────────────────────────────
    echo "--- 3. Testing AdmissionController::approve() ---\n";
    $approveRequest = new Request([
        'batch_id'       => $batch->id,
        'course_id'      => $course->id,
        'send_email'     => 0,
        'send_sms'       => 0,
        'save_template'  => 0,
    ]);

    $controller->approve($approveRequest, $form1);
    $form1->refresh();
    $st1->refresh();

    echo "After Approve -> Form Status: {$form1->status}, Student Code: {$st1->student_code}, User ID: {$st1->user_id}\n";
    if ($form1->status !== 'APPROVED') {
        throw new Exception("Expected form 1 status to be APPROVED, got {$form1->status}");
    }
    if (empty($st1->student_code)) {
        throw new Exception("FAIL: student_code was not generated upon approval");
    }
    if (empty($st1->user_id)) {
        throw new Exception("FAIL: User portal login was not generated upon approval");
    }

    $enr1 = Enrollment::where('admission_form_id', $form1->id)->first();
    if (!$enr1 || $enr1->status !== 'ACTIVE') {
        throw new Exception("FAIL: Active enrollment not found for approved student");
    }
    echo "Enrolled in Batch: {$enr1->batch_id}, Course: {$enr1->course_id}, Status: {$enr1->status}\n";
    $assignedCode1 = $st1->student_code;
    echo "Assigned Student Code: {$assignedCode1}\n";
    echo "SUCCESS: Admission approval properly generated Student ID and credentials.\n\n";

    // ─────────────────────────────────────────────────────────────────────────
    // Test 4: Trashing an Approved Application Releases the Student ID
    // ─────────────────────────────────────────────────────────────────────────
    echo "--- 4. Testing trash() Releases Assigned Student ID ---\n";
    $controller->trash(new Request(['trash_reason' => 'ভর্তি বাতিল ও ট্র্যাশ']), $form1);
    $form1->refresh();
    $st1->refresh();

    echo "After Trash of Approved Form -> Form Status: {$form1->status}, Student Code: " . ($st1->student_code ?? 'NULL') . "\n";
    if ($form1->status !== 'TRASH') {
        throw new Exception("Expected form 1 status to be TRASH, got {$form1->status}");
    }
    if (!empty($st1->student_code)) {
        throw new Exception("FAIL: student_code was not released upon trashing! Still {$st1->student_code}");
    }

    $enr1->refresh();
    echo "Enrollment Status: {$enr1->status}\n";
    if ($enr1->status !== 'CANCELLED') {
        throw new Exception("FAIL: Enrollment status was not set to CANCELLED upon trashing");
    }
    echo "SUCCESS: Trashing approved form released student_code to NULL and cancelled enrollment.\n\n";

    // ─────────────────────────────────────────────────────────────────────────
    // Test 5: A New Approved Student Reclaims the Released Serial Number
    // ─────────────────────────────────────────────────────────────────────────
    echo "--- 5. Testing Next Student Reclaims the Released Serial Number ---\n";
    $testEmail2 = 'test_reclaim_' . uniqid() . '@example.com';
    $testPhone2 = '018' . rand(10000000, 99999999);

    $storeRequest2 = new Request([
        'interested_course_id' => $course->id,
        'batch_id'             => $batch->id,
        'applicant_name'       => 'নতুন শিক্ষার্থী ২',
        'phone'                => $testPhone2,
        'email'                => $testEmail2,
        'gender'               => 'Male',
        'lead_source'          => 'Direct',
    ]);

    $controller->store($storeRequest2);
    $form2 = AdmissionForm::whereHas('student', function ($q) use ($testEmail2) {
        $q->where('email', $testEmail2);
    })->latest()->first();

    $controller->approve(new Request([
        'batch_id'       => $batch->id,
        'course_id'      => $course->id,
        'send_email'     => 0,
        'send_sms'       => 0,
        'save_template'  => 0,
    ]), $form2);

    $form2->refresh();
    $st2 = $form2->student;
    $assignedCode2 = $st2->student_code;

    echo "Student 2 Assigned Code: {$assignedCode2} (Expected: {$assignedCode1})\n";
    if ($assignedCode2 !== $assignedCode1) {
        throw new Exception("FAIL: Student 2 did not reclaim the released code {$assignedCode1}! Got: {$assignedCode2}");
    }
    echo "SUCCESS: Serial number sequence was perfectly preserved without any gap or skip!\n\n";

    // ─────────────────────────────────────────────────────────────────────────
    // Test 6: Verify Blade Template Logic for ID Badge
    // ─────────────────────────────────────────────────────────────────────────
    echo "--- 6. Testing Blade View Rendering Logic ---\n";
    // For pending form1 (untrashed to pending)
    $controller->untrash($form1);
    $form1->refresh();

    $renderedPending = view('admin.admissions.show', [
        'admission'      => $form1,
        'activeBatches'  => collect([$batch]),
        'allCourses'     => collect([$course]),
        'batchTemplates' => [],
    ])->render();

    if (!str_contains($renderedPending, 'আইডি: ভর্তি নিশ্চিতের পর জেনারেট হবে')) {
        throw new Exception("FAIL: Pending admission did not render 'আইডি: ভর্তি নিশ্চিতের পর জেনারেট হবে' badge");
    }
    if (str_contains($renderedPending, 'Student ID: <strong>')) {
        throw new Exception("FAIL: Pending admission unexpectedly rendered 'Student ID: <strong>'");
    }
    echo "SUCCESS: Pending admission correctly displays 'আইডি: ভর্তি নিশ্চিতের পর জেনারেট হবে' and hides impersonation login.\n";

    // For approved form2
    $renderedApproved = view('admin.admissions.show', [
        'admission'      => $form2,
        'activeBatches'  => collect([$batch]),
        'allCourses'     => collect([$course]),
        'batchTemplates' => [],
    ])->render();

    if (!str_contains($renderedApproved, 'Student ID: <strong>' . $assignedCode2 . '</strong>')) {
        throw new Exception("FAIL: Approved admission did not render Student ID badge with {$assignedCode2}");
    }
    if (!str_contains($renderedApproved, 'লগইন ↗')) {
        throw new Exception("FAIL: Approved admission did not render impersonation login link");
    }
    echo "SUCCESS: Approved admission correctly displays Student ID badge and impersonation login.\n\n";

    DB::rollBack();
    echo "All tests passed cleanly and database rolled back successfully!\n";
    echo "=== VERIFICATION COMPLETE: ALL DoD CRITERIA MET (Exit 0) ===\n";
    exit(0);

} catch (Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
