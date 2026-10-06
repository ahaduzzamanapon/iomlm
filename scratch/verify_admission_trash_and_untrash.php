<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdmissionForm;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Student;
use App\Models\User;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\AdmissionController;
use App\Http\Controllers\Public\AdmissionFormController;

echo "=== STARTING VERIFICATION: ADMISSION TRASH & UNTRASH ===\n\n";

DB::beginTransaction();

try {
    $adminUser = User::where('role', 'admin')->first() ?: User::first();
    Auth::login($adminUser);

    $admissionController = new AdmissionController();
    $publicAdmissionController = new AdmissionFormController();

    // ── 1. Check Existing TRASH Records & Filter ──
    echo "[1] Testing Trash Filter in AdmissionController::index...\n";
    $reqFilter = Request::create('/admin/admissions', 'GET', ['status' => 'TRASH']);
    $viewRes = $admissionController->index($reqFilter);
    $renderedHtml = $viewRes->render();

    if (str_contains($renderedHtml, 'Trash') && str_contains($renderedHtml, 'fa-trash-can')) {
        echo "  ✓ Index page renders 'Trash' filter pill and fa-trash-can icon successfully.\n";
    } else {
        throw new Exception("Index page does not render 'Trash' filter!");
    }

    if (str_contains($renderedHtml, 'Untrash')) {
        echo "  ✓ Index page renders 'Untrash' action button for trashed applications.\n";
    } else {
        throw new Exception("Index page does not render 'Untrash' button!");
    }

    // ── 2. Testing Move to Trash ──
    echo "\n[2] Testing moving a pending admission to Trash...\n";
    $course = Course::first();
    $batch = Batch::where('course_id', $course->id)->first() ?: Batch::first();

    $testStudent = Student::create([
        'name' => 'Trash Test Applicant',
        'phone' => '01799887766',
        'email' => 'trashtest_' . uniqid() . '@example.com',
        'status' => 'PENDING',
    ]);

    $testAdmission = AdmissionForm::create([
        'application_no' => 'APP-TEST-' . uniqid(),
        'student_id' => $testStudent->id,
        'interested_course_id' => $course->id,
        'batch_id' => $batch->id,
        'source' => 'PUBLIC',
        'status' => 'PENDING',
    ]);

    $trashReq = Request::create("/admin/admissions/{$testAdmission->id}/trash", 'PATCH', [
        'trash_reason' => 'অসম্পূর্ণ কাগজপত্র ও ভুল মোবাইল নম্বর',
    ]);
    $trashResp = $admissionController->trash($trashReq, $testAdmission);
    $testAdmission->refresh();

    if ($testAdmission->status === 'TRASH' && $testAdmission->rejection_reason === 'অসম্পূর্ণ কাগজপত্র ও ভুল মোবাইল নম্বর') {
        echo "  ✓ Admission status successfully updated to 'TRASH' with reason logged.\n";
    } else {
        throw new Exception("Failed to move admission to TRASH! Status is: {$testAdmission->status}");
    }

    // Check Show view for TRASH admission
    $showView = $admissionController->show($testAdmission)->render();
    if (str_contains($showView, 'আবেদনটি ট্র্যাশে রয়েছে') && str_contains($showView, 'আন-ট্র্যাশ করুন (Untrash)')) {
        echo "  ✓ Show page correctly displays Trash alert and 'Untrash' action button.\n";
    } else {
        throw new Exception("Show view does not display Untrash button/alert properly!");
    }

    // ── 3. Testing Manual Untrash Process ──
    echo "\n[3] Testing manual Untrash process...\n";
    $untrashResp = $admissionController->untrash($testAdmission);
    $testAdmission->refresh();

    if ($testAdmission->status === 'PENDING' && $testAdmission->rejection_reason === null && str_contains($testAdmission->notes, 'Untrashed:')) {
        echo "  ✓ Admission successfully Untrashed to 'PENDING', reason cleared, and notes logged.\n";
    } else {
        throw new Exception("Untrash failed! Status: {$testAdmission->status}, Reason: {$testAdmission->rejection_reason}");
    }

    // Verify that it can now be confirmed/approved
    echo "  Testing admission approval after Untrash...\n";
    $approveReq = Request::create("/admin/admissions/{$testAdmission->id}/approve", 'PATCH', [
        'batch_id' => $batch->id,
        'course_id' => $course->id,
        'custom_password' => 'Pass1234',
        'send_email' => false,
        'send_sms' => false,
        'save_template' => false,
    ]);
    $approveResp = $admissionController->approve($approveReq, $testAdmission);
    $testAdmission->refresh();

    if ($testAdmission->status === 'APPROVED' && $testAdmission->student->status === 'ACTIVE' && !empty($testAdmission->student->student_code)) {
        echo "  ✓ Admission successfully approved after untrash! Student ID: {$testAdmission->student->student_code}\n";
    } else {
        throw new Exception("Admission approval failed after untrash!");
    }

    // ── 4. Testing Auto-Untrash on Subsequent Payment ──
    echo "\n[4] Testing Auto-Untrash on Subsequent Payment...\n";
    $testStudent2 = Student::create([
        'name' => 'Auto Untrash Applicant',
        'phone' => '01899112233',
        'email' => 'autountrash_' . uniqid() . '@example.com',
        'status' => 'PENDING',
    ]);

    $testAdmission2 = AdmissionForm::create([
        'application_no' => 'APP-AUTO-' . uniqid(),
        'student_id' => $testStudent2->id,
        'interested_course_id' => $course->id,
        'batch_id' => $batch->id,
        'source' => 'PUBLIC',
        'status' => 'TRASH',
        'rejection_reason' => 'Previous rejection reason',
    ]);

    echo "  Initial status of test admission: {$testAdmission2->status}\n";

    // Student submits manual payment for this trashed admission
    $trxId = 'TRX' . strtoupper(uniqid());
    $payReq = Request::create("/apply/payment/{$testAdmission2->application_no}", 'POST', [
        'payment_gateway' => 'manual',
        'manual_payment_method' => 'bKash Merchant',
        'manual_trx_id' => $trxId,
        'manual_sender_phone' => '01899112233',
        'manual_payment_notes' => 'ফি পরিশোধ করলাম, ভর্তি কনফার্ম করুন',
    ]);

    $payResp = $publicAdmissionController->processPayment($payReq, $testAdmission2->application_no);
    $testAdmission2->refresh();

    if ($testAdmission2->status === 'PENDING') {
        echo "  ✓ Trashed admission was AUTOMATICALLY UN-TRASHED to 'PENDING' upon manual payment submission!\n";
    } else {
        throw new Exception("Auto-untrash failed! Current status: {$testAdmission2->status}");
    }

    if ($testAdmission2->manual_trx_id === $trxId && $testAdmission2->rejection_reason === null && str_contains($testAdmission2->notes, 'আন-ট্র্যাশ')) {
        echo "  ✓ TrxID ({$trxId}) recorded and notes indicate auto-untrash.\n";
    } else {
        throw new Exception("Payment details or notes missing after auto-untrash!");
    }

    // Now admin confirms and approves the auto-untrashed admission
    echo "  Testing admin confirming admission after auto-untrash on payment...\n";
    $approveReq2 = Request::create("/admin/admissions/{$testAdmission2->id}/approve", 'PATCH', [
        'batch_id' => $batch->id,
        'course_id' => $course->id,
        'custom_password' => 'Pass5678',
        'send_email' => false,
        'send_sms' => false,
        'save_template' => false,
    ]);
    $admissionController->approve($approveReq2, $testAdmission2);
    $testAdmission2->refresh();

    if ($testAdmission2->status === 'APPROVED' && !empty($testAdmission2->student->student_code)) {
        echo "  ✓ Admin successfully confirmed admission after auto-untrash! Student Code: {$testAdmission2->student->student_code}\n";
    } else {
        throw new Exception("Confirmation failed after auto-untrash!");
    }

} finally {
    DB::rollBack();
}

echo "\n======================================================\n";
echo "✓ ALL TESTS PASSED SUCCESSFULLY! EXIT CODE 0.\n";
echo "======================================================\n";
exit(0);
