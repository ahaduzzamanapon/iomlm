<?php

require 'e:/My Soft/iom/learning-plus/vendor/autoload.php';
$app = require_once 'e:/My Soft/iom/learning-plus/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\AdmissionForm;
use App\Models\AcademicSession;
use App\Models\Batch;

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

echo "=== Testing Admission Session Migration & Consistency ===\n\n";

// 1. Check Migration File Exists
$migrationFile = 'e:/My Soft/iom/learning-plus/database/migrations/2026_10_14_160000_sync_admission_forms_academic_sessions.php';
assertCondition("Migration file exists", file_exists($migrationFile));

// 2. Simulate or prepare an admission form with wrong session to verify migration behavior
$testForm = AdmissionForm::first();
if ($testForm) {
    // Intentionally change session to 2 (Fall) to verify that migration or logic fixes it
    $originalSession = $testForm->academic_session_id;
    $testForm->update(['academic_session_id' => 2]);
    assertCondition("Set test form {$testForm->application_no} session to Fall (2)", $testForm->fresh()->academic_session_id === 2);
}

// 3. Run artisan migrate
$exitCode = Artisan::call('migrate', ['--force' => true]);
$output = Artisan::output();
assertCondition("Artisan migrate ran successfully (Exit Code 0)", $exitCode === 0);
echo "Migrate output:\n" . trim($output) . "\n\n";

// 4. Verify that the test form was synced back to its correct session
if ($testForm) {
    $freshForm = $testForm->fresh();
    $batch = $freshForm->batch;
    $expectedSession = AcademicSession::resolveForBatchOrYear($batch?->id, $batch?->academic_year_id);
    assertCondition("Admission form {$freshForm->application_no} session was corrected to {$expectedSession->id} ({$expectedSession->name})", $freshForm->academic_session_id === $expectedSession->id);
}

// 5. Verify all active admission forms have the correct session
$mismatches = 0;
$allForms = AdmissionForm::with(['batch.academicYear', 'session'])->get();
foreach ($allForms as $f) {
    $b = $f->batch;
    if ($b && $b->academic_year_id) {
        $expected = AcademicSession::where('academic_year_id', $b->academic_year_id)
            ->where('is_active', true)
            ->latest('id')
            ->first();
        if ($expected && $f->academic_session_id !== $expected->id) {
            $mismatches++;
            echo " [MISMATCH] Form {$f->application_no} has session {$f->academic_session_id}, expected {$expected->id}\n";
        }
    }
}
assertCondition("Zero session mismatches found across all forms", $mismatches === 0);

echo "\nSummary: $passed / $total assertions passed.\n";

if ($passed === $total) {
    echo "ALL TESTS PASSED SUCCESSFULLY! (Exit Code 0)\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED! (Exit Code 1)\n";
    exit(1);
}
