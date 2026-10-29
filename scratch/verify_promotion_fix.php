<?php

/**
 * Verification test for Semester Promotion fixes
 * Tests:
 * 1. Blade rendering of admin.promotions.index with incomplete batch evaluation (matching user scenario)
 * 2. Presence of promotion guide box, quick single promotion modal, bulk checked modal, and action buttons
 * 3. Execution verification of PromotionController::store and bulkPromote inside DB transaction
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Batch;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\PromotionRecord;
use App\Models\BatchSemesterPosition;
use App\Models\User;
use App\Http\Controllers\Admin\PromotionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

// Authenticate as first user for admin layout permissions
$user = User::first();
if ($user) {
    auth()->login($user);
}

echo "=== Starting Semester Promotion Verification ===\n";

// 1. Find a semester-based batch or create a mockup state
$batch = Batch::whereHas('course.semesters')->first();
if (!$batch) {
    echo "[-] No semester-based batch found in database.\n";
    exit(1);
}

echo "[+] Using Batch ID: {$batch->id} ('{$batch->name}')\n";
$course = $batch->course;
$semesters = $course->semesters()->orderBy('sequence_no')->get();
echo "[+] Course has {$semesters->count()} semesters.\n";

$firstSem = $semesters->first();
$secondSem = $semesters->get(1) ?? $firstSem;

// 2. Test Controller index method
$controller = app(PromotionController::class);
$request = Request::create('/admin/promotions', 'GET', [
    'batch_id' => $batch->id,
    'semester_id' => $firstSem->id,
]);

$response = $controller->index($request);
echo "[+] Controller index returned View successfully.\n";

$html = $response->render();
echo "[+] Rendered HTML length: " . strlen($html) . " bytes.\n";

// 3. Verify key elements in the rendered HTML
$checks = [
    'Promotion Guide Box' => 'id="promotionGuideBox"',
    'Promotion Guide Toggle' => 'togglePromotionGuide()',
    'Select All Checkbox' => 'id="selectAllCheckbox"',
    'Select All Force Promote Button' => 'selectAllStudentsForForcePromote()',
    'Bulk Checked Promote Button' => 'id="btnPromoteChecked"',
    'Quick Single Promote Modal' => 'id="quickSinglePromoteModal"',
    'Bulk Checked Promote Modal' => 'id="bulkPromoteCheckedModal"',
    'Single Quick Promote JS Trigger' => 'openSingleQuickPromoteModal(',
    'Bulk Promote Checked JS Trigger' => 'openBulkPromoteCheckedModal()',
    'Result Book Direct Link' => 'result-book',
    'Force Promote Decision Option' => 'FORCE_PROMOTED',
];

$failedChecks = 0;
foreach ($checks as $name => $needle) {
    if (strpos($html, $needle) !== false) {
        echo "  [PASS] {$name} found in rendered view.\n";
    } else {
        echo "  [FAIL] {$name} NOT found in rendered view (needle: {$needle}).\n";
        $failedChecks++;
    }
}

if ($failedChecks > 0) {
    echo "[-] {$failedChecks} checks failed in rendered HTML.\n";
    exit(1);
}

// 4. Test Controller store and bulkPromote logic in DB transaction (rollback at end)
echo "\n[+] Testing Promotion Controller store and bulkPromote execution...\n";
DB::beginTransaction();
try {
    // Create or find a test student enrolled in this batch & first semester
    $student = Student::first();
    if (!$student) {
        $student = Student::create([
            'name' => 'Test Student Promotion',
            'email' => 'test_promo_' . time() . '@example.com',
            'student_code' => 'TEST_P_' . time(),
            'status' => 'ACTIVE',
        ]);
    }

    $enrollment = Enrollment::firstOrCreate(
        ['student_id' => $student->id, 'batch_id' => $batch->id],
        ['course_id' => $course->id, 'semester_id' => $firstSem->id, 'status' => 'ACTIVE']
    );
    $enrollment->update(['semester_id' => $firstSem->id, 'status' => 'ACTIVE']);

    // Test Single Promotion Store
    $storeRequest = Request::create('/admin/promotions', 'POST', [
        'student_id' => $student->id,
        'from_semester_id' => $firstSem->id,
        'to_semester_id' => $secondSem->id,
        'decision' => 'FORCE_PROMOTED',
        'notes' => 'Test single promotion verification note',
    ]);

    $storeResp = $controller->store($storeRequest);
    $promoRecord = PromotionRecord::where('student_id', $student->id)
        ->where('from_semester_id', $firstSem->id)
        ->where('to_semester_id', $secondSem->id)
        ->first();

    if (!$promoRecord) {
        throw new Exception("PromotionRecord was not created by store()!");
    }
    echo "  [PASS] Single store() successfully created PromotionRecord (ID: {$promoRecord->id}, Decision: {$promoRecord->decision}).\n";

    $enrollment->refresh();
    if ($enrollment->semester_id != $secondSem->id) {
        throw new Exception("Enrollment semester_id was not updated to {$secondSem->id}! Current: {$enrollment->semester_id}");
    }
    echo "  [PASS] Enrollment semester_id updated to {$secondSem->id}.\n";

    // Delete promo record to test bulk promote cleanly
    $promoRecord->delete();

    // Reset enrollment back to first semester
    $enrollment->update(['semester_id' => $firstSem->id]);

    // Test Bulk Promotion with FORCE_PROMOTED decision
    $bulkRequest = Request::create('/admin/promotions/bulk-promote', 'POST', [
        'batch_id' => $batch->id,
        'from_semester_id' => $firstSem->id,
        'to_semester_id' => $secondSem->id,
        'student_ids' => [$student->id],
        'decision' => 'FORCE_PROMOTED',
        'notes' => 'Test bulk force promote verification',
    ]);

    $bulkResp = $controller->bulkPromote($bulkRequest);
    $bulkRecord = PromotionRecord::where('student_id', $student->id)
        ->where('from_semester_id', $firstSem->id)
        ->where('to_semester_id', $secondSem->id)
        ->first();

    if (!$bulkRecord) {
        throw new Exception("PromotionRecord was not created by bulkPromote()!");
    }
    echo "  [PASS] bulkPromote() successfully created PromotionRecord (Decision: {$bulkRecord->decision}).\n";

    $enrollment->refresh();
    if ($enrollment->semester_id != $secondSem->id) {
        throw new Exception("Enrollment semester_id was not updated by bulkPromote()!");
    }
    echo "  [PASS] Enrollment semester_id updated by bulkPromote().\n";

    $batchPos = BatchSemesterPosition::where('batch_id', $batch->id)->first();
    if (!$batchPos || $batchPos->current_semester_id != $secondSem->id) {
        throw new Exception("BatchSemesterPosition was not updated to {$secondSem->id}!");
    }
    echo "  [PASS] BatchSemesterPosition successfully updated to {$secondSem->id}.\n";

    DB::rollBack();
    echo "[+] DB Transaction rolled back cleanly. No test data persisted.\n";
} catch (Exception $e) {
    DB::rollBack();
    echo "[-] Test exception: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== ALL VERIFICATION TESTS PASSED SUCCESSFULLY (Exit Code 0) ===\n";
exit(0);
