<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdmissionCircular;
use App\Models\AdmissionCircularBatch;
use App\Models\AdmissionForm;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Student;
use App\Http\Controllers\Admin\AdmissionCircularController;
use App\Http\Controllers\Public\AdmissionFormController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== VERIFYING TASK 26: ADMISSION SESSION & CIRCULAR MANAGEMENT SYSTEM ===\n";

$assertions = 0;
function assertCondition($condition, $message) {
    global $assertions;
    if (!$condition) {
        echo "❌ FAILED: $message\n";
        exit(1);
    }
    echo "✅ PASSED: $message\n";
    $assertions++;
}

// 1. Check Tables and Columns
assertCondition(Schema::hasTable('admission_circulars'), "Table 'admission_circulars' exists");
assertCondition(Schema::hasColumns('admission_circulars', [
    'name', 'short_name', 'semester_name', 'session_year', 'student_id_prefix',
    'ugc_id_prefix', 'student_id_suffix', 'program_type', 'circular_status',
    'is_enabled', 'is_program_batch_map_enabled', 'remark', 'exam_date',
    'admission_start_date', 'admission_end_date'
]), "Table 'admission_circulars' has all required columns");

assertCondition(Schema::hasTable('admission_circular_batches'), "Table 'admission_circular_batches' exists");
assertCondition(Schema::hasColumns('admission_circular_batches', [
    'admission_circular_id', 'course_id', 'batch_id', 'campus', 'is_online_admission_enabled'
]), "Table 'admission_circular_batches' has all required columns");

assertCondition(Schema::hasColumn('admission_forms', 'admission_circular_id'), "Table 'admission_forms' has column 'admission_circular_id'");

// 2. Pre-cleanup any previous test circulars
DB::table('admission_circular_batches')->whereIn('admission_circular_id', function ($query) {
    $query->select('id')->from('admission_circulars')->where('name', 'like', 'TEST_CIRC%');
})->delete();
DB::table('admission_circulars')->where('name', 'like', 'TEST_CIRC%')->delete();

// Setup test course & batch
$courseA = Course::firstOrCreate(
    ['name' => 'TEST_CIRC_COURSE_A'],
    [
        'code' => '88',
        'department' => 'BA in Dawah and Islamic Studies',
        'type' => 'SEMESTER_BASED',
        'duration_value' => 3,
        'duration_unit' => 'YEAR',
        'admission_fee' => 1500,
        'is_active' => true,
    ]
);

$courseB = Course::firstOrCreate(
    ['name' => 'TEST_CIRC_COURSE_B'],
    [
        'code' => '89',
        'department' => 'Single Course',
        'type' => 'SUBJECT_BASED',
        'duration_value' => 6,
        'duration_unit' => 'MONTH',
        'admission_fee' => 800,
        'is_active' => true,
    ]
);

$batchA = Batch::where('course_id', $courseA->id)->first();
if (!$batchA) {
    $batchA = Batch::create([
        'course_id' => $courseA->id,
        'batch_code' => '88',
        'name' => '88th Batch A',
        'status' => 'ACTIVE',
        'start_date' => now(),
        'expected_end_date' => now()->addYear(),
    ]);
}

$batchB = Batch::where('course_id', $courseB->id)->first();
if (!$batchB) {
    $batchB = Batch::create([
        'course_id' => $courseB->id,
        'batch_code' => '89',
        'name' => '89th Batch B',
        'status' => 'ACTIVE',
        'start_date' => now(),
        'expected_end_date' => now()->addYear(),
    ]);
}

// 3. Admin Controller store test
$adminController = new AdmissionCircularController();
$storeReq = Request::create('/admin/admission-circulars', 'POST', [
    'name'                         => 'TEST_CIRC Fall 2026',
    'short_name'                   => 'TEST_CIRC Fall 2026',
    'semester_name'                => 'Fall 2026 (Jul-Dec)',
    'session_year'                 => '2025-2026',
    'student_id_prefix'            => '26',
    'circular_status'              => 'Current',
    'is_enabled'                   => '1',
    'is_program_batch_map_enabled' => '1',
    'remark'                       => 'Test circular remark',
    'batch_settings'               => [
        $courseA->id => [
            'batch_id'   => $batchA->id,
            'campus'     => 'Main Campus',
            'is_enabled' => '1', // Enabled for Course A
        ],
        $courseB->id => [
            'batch_id'   => $batchB->id,
            'campus'     => 'Main Campus',
            'is_enabled' => '0', // Disabled for Course B
        ],
    ],
]);
$storeReq->headers->set('Accept', 'application/json');

$storeRes = $adminController->store($storeReq);
$storeData = json_decode($storeRes->getContent(), true);
if (empty($storeData['success'])) {
    echo "DEBUG STORE RESPONSE: " . $storeRes->getContent() . "\n";
}
assertCondition(!empty($storeData['success']) && $storeData['success'] === true, "Admin controller store created circular successfully");

$createdCircId = $storeData['circular']['id'];
$circular = AdmissionCircular::with('circularBatches')->find($createdCircId);
assertCondition($circular !== null, "Circular loaded from database");
assertCondition($circular->name === 'TEST_CIRC Fall 2026', "Circular name matches");
assertCondition($circular->student_id_prefix === '26', "Student ID prefix matches '26'");
assertCondition($circular->circularBatches->count() >= 2, "Batch mappings created for courses");

$batchSettingA = $circular->circularBatches->where('course_id', $courseA->id)->first();
$batchSettingB = $circular->circularBatches->where('course_id', $courseB->id)->first();
assertCondition($batchSettingA && $batchSettingA->is_online_admission_enabled === true, "Course A is enabled for online admission");
assertCondition($batchSettingB && $batchSettingB->is_online_admission_enabled === false, "Course B is disabled for online admission");

// 4. Test Show API
$showRes = $adminController->show($circular);
$showData = json_decode($showRes->getContent(), true);
assertCondition($showData['id'] == $circular->id, "Admin controller show returns circular JSON");
assertCondition(!empty($showData['circular_batches']), "Show includes circular_batches");

// 5. Test Update
$updateReq = Request::create("/admin/admission-circulars/{$circular->id}", 'PUT', [
    'name'                         => 'TEST_CIRC Fall 2026 Updated',
    'short_name'                   => 'TEST_CIRC Fall 2026 Upd',
    'semester_name'                => 'Fall 2026 (Jul-Dec)',
    'session_year'                 => '2025-2026',
    'student_id_prefix'            => '27',
    'circular_status'              => 'Current',
    'is_enabled'                   => '1',
    'is_program_batch_map_enabled' => '1',
    'remark'                       => 'Updated remark',
    'batch_settings'               => [
        $courseA->id => [
            'batch_id'   => $batchA->id,
            'campus'     => 'Main Campus',
            'is_enabled' => '1',
        ],
        $courseB->id => [
            'batch_id'   => $batchB->id,
            'campus'     => 'Main Campus',
            'is_enabled' => '1', // Now enable Course B
        ],
    ],
]);
$updateReq->headers->set('Accept', 'application/json');
$updateRes = $adminController->update($updateReq, $circular);
$updateData = json_decode($updateRes->getContent(), true);
assertCondition($updateData['success'] === true, "Admin controller update saved successfully");
$circular->refresh();
assertCondition($circular->name === 'TEST_CIRC Fall 2026 Updated', "Updated name persisted");
assertCondition($circular->student_id_prefix === '27', "Updated prefix '27' persisted");
$batchSettingB->refresh();
assertCondition($batchSettingB->is_online_admission_enabled === true, "Course B is now enabled after update");

// 6. Test Clone
$adminController->clone($circular);
$clonedCirc = AdmissionCircular::where('name', 'like', 'TEST_CIRC Fall 2026 Updated (কপি)%')->latest('id')->first();
assertCondition($clonedCirc !== null, "Circular cloned successfully");
assertCondition($clonedCirc->circular_status === 'Upcoming', "Cloned circular status is 'Upcoming'");
assertCondition($clonedCirc->circularBatches->count() >= 2, "Cloned circular duplicated all batch settings");

// 7. Test Toggle Status
$toggleRes = $adminController->toggle($circular);
$toggleData = json_decode($toggleRes->getContent(), true);
assertCondition($toggleData['success'] === true, "Toggle status returned success");
assertCondition($toggleData['is_enabled'] === false, "Circular is_enabled toggled to false");
$circular->refresh();
assertCondition($circular->is_enabled === false, "Circular is_enabled is now false in DB");

// Toggle back to true for public form testing
$adminController->toggle($circular);
$circular->refresh();
assertCondition($circular->is_enabled === true, "Circular is_enabled toggled back to true");

// Set Course B back to disabled for testing public form restrictions
AdmissionCircularBatch::where('admission_circular_id', $circular->id)
    ->where('course_id', $courseB->id)
    ->update(['is_online_admission_enabled' => false]);

// 8. Test Public AdmissionFormController with Active Circular
// Ensure our test circular is the latest current circular
$circular->update(['circular_status' => 'Current', 'is_enabled' => true]);
AdmissionCircular::where('id', '!=', $circular->id)->update(['circular_status' => 'Expired']);

$publicController = new AdmissionFormController();
$view = $publicController->show();
$viewData = $view->getData();
assertCondition($viewData['admissionOpen'] === true, "Public admission form reports admissionOpen = true");
assertCondition($viewData['activeCircular']->id === $circular->id, "Public admission uses active circular");
$displayedCourses = $viewData['courses'];
assertCondition($displayedCourses->contains('id', $courseA->id), "Enabled Course A is listed in admission courses");
assertCondition(!$displayedCourses->contains('id', $courseB->id), "Disabled Course B is excluded from admission courses");

// 9. Test Public store rejection for disabled course
$rejectReq = Request::create('/apply', 'POST', [
    'course_id'      => $courseB->id,
    'applicant_name' => 'Test Applicant',
    'phone'          => '01711998877',
    'email'          => 'test_applicant_b@example.com',
    'gender'         => 'Male',
    'terms_agreed'   => '1',
]);
$rejectReq->setLaravelSession(app('session.store'));
$rejectRes = $publicController->store($rejectReq);
assertCondition($rejectRes->isRedirect(), "Store redirects on disabled course");
assertCondition(session()->has('error'), "Session has error message for disabled course admission");

// 10. Test Public store success for enabled course
$acceptReq = Request::create('/apply', 'POST', [
    'course_id'      => $courseA->id,
    'applicant_name' => 'Test Applicant A',
    'phone'          => '01711998866',
    'email'          => 'test_applicant_a@example.com',
    'gender'         => 'Male',
    'terms_agreed'   => '1',
]);
$acceptReq->setLaravelSession(app('session.store'));
$acceptRes = $publicController->store($acceptReq);
assertCondition($acceptRes->isRedirect(), "Store redirects to payment on valid submission");

$newForm = AdmissionForm::where('interested_course_id', $courseA->id)
    ->whereHas('student', function($q) { $q->where('email', 'test_applicant_a@example.com'); })
    ->latest('id')
    ->first();
assertCondition($newForm !== null, "Admission form created in database");
assertCondition($newForm->admission_circular_id === $circular->id, "Admission form has correct admission_circular_id");
assertCondition($newForm->circular->id === $circular->id, "AdmissionForm circular relationship functions properly");

// 11. Test Turn Off entire circular admission
$circular->update(['is_enabled' => false]);
$closedView = $publicController->show();
assertCondition($closedView->getData()['admissionOpen'] === false, "Public form reports admissionOpen = false when circular disabled");

// 12. Check Blade view templates
$indexBlade = file_get_contents(__DIR__ . '/../resources/views/admin/admission_circulars/index.blade.php');
assertCondition(strpos($indexBlade, 'General Settings') !== false, "admin/admission_circulars/index.blade.php has General Settings tab");
assertCondition(strpos($indexBlade, 'Batch Settings') !== false, "admin/admission_circulars/index.blade.php has Batch Settings tab");
assertCondition(strpos($indexBlade, 'Main Campus') !== false, "admin/admission_circulars/index.blade.php has Main Campus section");
assertCondition(strpos($indexBlade, 'toggle-switch') !== false, "admin/admission_circulars/index.blade.php has YES/NO toggle switch");
assertCondition(strpos($indexBlade, 'Kalpurush') !== false, "admin/admission_circulars/index.blade.php uses Kalpurush font");

$applyBlade = file_get_contents(__DIR__ . '/../resources/views/apply/index.blade.php');
assertCondition(strpos($applyBlade, 'activeCircular') !== false, "apply/index.blade.php references activeCircular");
assertCondition(strpos($applyBlade, 'অনলাইন ভর্তি বর্তমানে বন্ধ রয়েছে') !== false, "apply/index.blade.php has closed admission message");

// 13. Cleanup test records
if ($newForm) {
    DB::table('admission_forms')->where('id', $newForm->id)->delete();
    if ($newForm->student_id) {
        DB::table('students')->where('id', $newForm->student_id)->delete();
    }
}
$circular->delete();
if ($clonedCirc) {
    $clonedCirc->delete();
}
// Restore other circulars to initial state
AdmissionCircular::where('name', 'Adm Fall 2026 (Jul-Dec)')->update(['circular_status' => 'Current', 'is_enabled' => true]);

echo "\n🎉 ALL $assertions ASSERTIONS PASSED! EXIT CODE 0\n";
exit(0);
