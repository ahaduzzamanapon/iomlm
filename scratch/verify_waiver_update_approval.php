<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\CourseFeePackage;
use App\Models\FeePackageItem;
use App\Models\WaiverApplication;
use App\Models\AdmissionForm;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\WaiverApplicationController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

echo "--- STARTING VERIFICATION: WAIVER EDIT & UPDATE APPROVAL ---\n";

DB::beginTransaction();

try {
    // 1. Verify Routes Registered
    echo "[TEST 1] Verifying routes...\n";
    $hasUpdateRoute = Route::has('admin.waiver-applications.update-approval');
    $hasSendEmailRoute = Route::has('admin.waiver-applications.send-update-email');
    if (!$hasUpdateRoute || !$hasSendEmailRoute) {
        throw new \Exception("Routes not registered properly! update-approval: " . ($hasUpdateRoute ? 'YES' : 'NO') . ", send-update-email: " . ($hasSendEmailRoute ? 'YES' : 'NO'));
    }
    echo "  -> Routes verified successfully.\n";

    // 2. Prepare Test Data
    echo "[TEST 2] Preparing test Course, Packages, WaiverApplication...\n";
    $course = Course::first() ?? Course::create([
        'name' => 'Test Course for Waiver',
        'code' => 'TCW-101',
        'admission_fee' => 1500,
        'status' => 'ACTIVE'
    ]);

    $pkg1 = CourseFeePackage::first() ?? CourseFeePackage::create([
        'course_id' => $course->id,
        'name' => 'Original Package 1000',
        'is_default' => false,
    ]);

    $pkg2 = CourseFeePackage::where('id', '!=', $pkg1->id)->first() ?? CourseFeePackage::create([
        'course_id' => $course->id,
        'name' => 'Revised Package 700',
        'is_default' => false,
    ]);

    $adminUser = User::first() ?? User::create([
        'name' => 'Admin Tester',
        'email' => 'admin_test_' . time() . '@iom.edu.bd',
        'password' => bcrypt('password'),
    ]);
    auth()->login($adminUser);

    $appNo = 'PF-TEST-' . time();
    $waiver = WaiverApplication::create([
        'application_no' => $appNo,
        'full_name' => 'Mohammad Abdullah Test',
        'phone' => '01700000000',
        'email' => 'student_waiver_test@example.com',
        'course_id' => $course->id,
        'apply_for' => 'BOTH',
        'status' => 'APPROVED',
        'convenient_admission_fee' => 800,
        'convenient_monthly_fee' => 800,
        'approved_admission_fee' => 1000,
        'approved_package_id' => $pkg1->id,
        'reviewer_notes' => 'Initially approved with 1000 fee',
    ]);

    // Also simulate linked AdmissionForm and unpaid Invoice
    $baseAdm = AdmissionForm::first();
    if ($baseAdm) {
        $admData = $baseAdm->getAttributes();
        unset($admData['id'], $admData['created_at'], $admData['updated_at'], $admData['deleted_at']);
        $admData['application_no'] = 'ADM-' . time();
        $admData['waiver_code'] = $appNo;
        $admData['interested_course_id'] = $course->id;
        $admData['approved_admission_fee'] = 1000;
        $admData['discount_amount'] = 500;
        $admissionForm = AdmissionForm::create($admData);
    } else {
        $admissionForm = AdmissionForm::create([
            'application_no' => 'ADM-' . time(),
            'interested_course_id' => $course->id,
            'waiver_code' => $appNo,
            'approved_admission_fee' => 1000,
            'discount_amount' => 500,
            'discount_percent' => 33.33,
            'discount_type' => 'FIXED',
        ]);
    }

    $waiver->update(['admission_form_id' => $admissionForm->id]);

    $studentId = \App\Models\Student::first()?->id ?? 1;
    $invoice = Invoice::create([
        'invoice_no' => 'INV-' . time(),
        'student_id' => $studentId,
        'title' => 'Admission Fee',
        'source_type' => AdmissionForm::class,
        'source_id' => $admissionForm->id,
        'category' => 'ADMISSION',
        'amount' => 1500,
        'discount' => 500,
        'payable_amount' => 1000,
        'paid_amount' => 0,
        'due_amount' => 1000,
        'status' => 'UNPAID',
    ]);

    echo "  -> Test data seeded successfully (Waiver ID: {$waiver->id}).\n";

    // 3. Test Controller updateApproval method
    echo "[TEST 3] Testing WaiverApplicationController::updateApproval...\n";
    $controller = app(WaiverApplicationController::class);

    $updateRequest = new Request([
        'apply_for' => 'BOTH',
        'approved_admission_fee' => 700,
        'approved_package_id' => $pkg2->id,
        'reviewer_notes' => 'Student requested further reduction, revised to 700 Tk',
        'send_email' => 0, // don't send real smtp in unit test
    ]);

    $response = $controller->updateApproval($updateRequest, $waiver);

    $waiver->refresh();
    $admissionForm->refresh();
    $invoice->refresh();

    if ((float)$waiver->approved_admission_fee !== 700.0) {
        throw new \Exception("Waiver approved_admission_fee not updated! Got: " . $waiver->approved_admission_fee);
    }
    if ((int)$waiver->approved_package_id !== (int)$pkg2->id) {
        throw new \Exception("Waiver approved_package_id not updated! Got: " . $waiver->approved_package_id);
    }
    if ($waiver->reviewer_notes !== 'Student requested further reduction, revised to 700 Tk') {
        throw new \Exception("Waiver reviewer_notes not updated! Got: " . $waiver->reviewer_notes);
    }
    echo "  -> Waiver updated: Fee = {$waiver->approved_admission_fee}, Package ID = {$waiver->approved_package_id}\n";

    // Check linked AdmissionForm sync
    if ((float)$admissionForm->approved_admission_fee !== 700.0) {
        throw new \Exception("AdmissionForm approved_admission_fee not synced! Got: " . $admissionForm->approved_admission_fee);
    }
    if ((float)$admissionForm->discount_amount !== 800.0) { // 1500 - 700 = 800
        throw new \Exception("AdmissionForm discount_amount not synced! Expected 800, got: " . $admissionForm->discount_amount);
    }
    echo "  -> Linked AdmissionForm synced: Fee = {$admissionForm->approved_admission_fee}, Discount = {$admissionForm->discount_amount}\n";

    // Check linked Invoice sync
    if ((float)$invoice->payable_amount !== 700.0) {
        throw new \Exception("Invoice payable_amount not synced! Expected 700, got: " . $invoice->payable_amount);
    }
    if ((float)$invoice->due_amount !== 700.0) {
        throw new \Exception("Invoice due_amount not synced! Expected 700, got: " . $invoice->due_amount);
    }
    if ((float)$invoice->discount !== 800.0) {
        throw new \Exception("Invoice discount not synced! Expected 800, got: " . $invoice->discount);
    }
    echo "  -> Linked Invoice synced: Payable = {$invoice->payable_amount}, Due = {$invoice->due_amount}, Discount = {$invoice->discount}\n";

    // 4. Test Email Data Generation
    echo "[TEST 4] Testing buildUpdatedApprovalEmailData...\n";
    $emailData = $controller->buildUpdatedApprovalEmailData($waiver);

    if (empty($emailData['subject']) || !str_contains($emailData['subject'], $waiver->application_no)) {
        throw new \Exception("Email subject invalid! " . ($emailData['subject'] ?? ''));
    }
    if (!str_contains($emailData['body'], '700')) {
        throw new \Exception("Email body does not contain revised fee 700!");
    }
    if (!str_contains($emailData['body'], $pkg2->name)) {
        throw new \Exception("Email body does not contain revised package name: {$pkg2->name}!");
    }
    if (!str_contains($emailData['body'], $waiver->application_no)) {
        throw new \Exception("Email body does not contain waiver code: {$waiver->application_no}!");
    }
    echo "  -> Email data generated with Bengali text, coupon, and revised amounts.\n";

    // 5. Test Blade View Rendering
    echo "[TEST 5] Testing show.blade.php rendering with updated actions...\n";
    $view = view('admin.waiver_applications.show', [
        'waiverApplication' => $waiver,
        'coursePackages' => collect([$pkg1, $pkg2]),
    ])->render();

    if (!str_contains($view, 'editApprovedWaiverModal')) {
        throw new \Exception("View does not contain editApprovedWaiverModal!");
    }
    if (!str_contains($view, 'অনুমোদিত ফি ও প্যাকেজ এডিট')) {
        throw new \Exception("View does not contain 'অনুমোদিত ফি ও প্যাকেজ এডিট' button!");
    }
    if (!str_contains($view, 'আপডেট মেইল পাঠান')) {
        throw new \Exception("View does not contain 'আপডেট মেইল পাঠান' button!");
    }
    if (!str_contains($view, 'update-approval')) {
        throw new \Exception("View does not post to waiver-applications.update-approval route URL!");
    }
    echo "  -> Blade view renders all modal and action elements flawlessly.\n";

    DB::rollBack();
    echo "\n=== ALL TESTS PASSED SUCCESSFULLY! EXIT CODE 0 ===\n";
    exit(0);

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n[ERROR]: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
