<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WaiverApplication;
use App\Models\Course;
use App\Http\Controllers\Public\WaiverApplicationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

echo "=== Verifying Poor Fund Duplicate Restriction (Just like Admission) ===\n\n";

// 1. Verify Routes exist
$hasSdCheckRoute = Route::has('poor_fund.check.duplicate');
$hasPfCheckRoute = Route::has('pf.check.duplicate');
assertCondition("Route 'poor_fund.check.duplicate' is registered", $hasSdCheckRoute);
assertCondition("Route 'pf.check.duplicate' is registered", $hasPfCheckRoute);

// 2. Prepare test data
$testEmail = 'test_dup_' . time() . '@example.com';
$testPhone = '017' . rand(10000000, 99999999);
$testNid   = 'NID' . rand(100000000, 999999999);
$course    = Course::where('is_poor_fund_applicable', true)->first();

$controller = new WaiverApplicationController();

// 3. Test AJAX duplicate check when no record exists
$reqNew = Request::create('/pf/check-duplicate', 'POST', [
    'email'       => $testEmail,
    'phone'       => $testPhone,
    'national_id' => $testNid,
    'course_id'   => $course?->id,
]);
$resNew = $controller->checkDuplicateAjax($reqNew);
$dataNew = json_decode($resNew->getContent(), true);
assertCondition("AJAX check returns exists: false for new applicant", isset($dataNew['exists']) && $dataNew['exists'] === false);

// 4. Create an existing WaiverApplication
$existingApp = WaiverApplication::create([
    'application_no'                 => WaiverApplication::generateApplicationNo(),
    'full_name'                      => 'Original Applicant',
    'email'                          => $testEmail,
    'phone'                          => $testPhone,
    'national_id'                    => $testNid,
    'date_of_birth'                  => '2000-01-01',
    'father_name'                    => 'Father Name',
    'institution_or_business'        => 'Test Madrasah',
    'source_of_income'               => 'Business',
    'monthly_income'                 => 10000,
    'family_siblings_details'        => 'Details',
    'financial_problem_description'  => 'Financial problem details',
    'apply_reason_type'              => 'Admission Fee',
    'apply_for'                      => 'ADMISSION_FEE',
    'present_address'                => 'Dhaka, Bangladesh',
    'permanent_address'              => 'Dhaka, Bangladesh',
    'status'                         => 'PENDING',
    'course_id'                      => $course?->id,
]);
assertCondition("Existing test application created (AppNo: {$existingApp->application_no})", $existingApp && $existingApp->id);

// 5. Test AJAX duplicate check with same email
$reqDupEmail = Request::create('/pf/check-duplicate', 'POST', [
    'email' => strtoupper($testEmail), // Test case-insensitivity
]);
$resDupEmail = $controller->checkDuplicateAjax($reqDupEmail);
$dataDupEmail = json_decode($resDupEmail->getContent(), true);
assertCondition("AJAX check detects duplicate by email (case-insensitive)", isset($dataDupEmail['exists']) && $dataDupEmail['exists'] === true);
assertCondition("AJAX check returns existing application_no", ($dataDupEmail['application_no'] ?? '') === $existingApp->application_no);
echo "   -> AJAX Message: " . ($dataDupEmail['message'] ?? '') . "\n";

// 6. Test AJAX duplicate check with same phone
$reqDupPhone = Request::create('/pf/check-duplicate', 'POST', [
    'phone' => $testPhone,
]);
$resDupPhone = $controller->checkDuplicateAjax($reqDupPhone);
$dataDupPhone = json_decode($resDupPhone->getContent(), true);
assertCondition("AJAX check detects duplicate by phone", isset($dataDupPhone['exists']) && $dataDupPhone['exists'] === true);

// 7. Test server-side store() duplicate rejection
$reqStoreDup = Request::create('/pf', 'POST', [
    'full_name'                      => 'Duplicate Applicant Attempt',
    'email'                          => $testEmail,
    'phone'                          => $testPhone,
    'date_of_birth'                  => '2001-02-02',
    'father_name'                    => 'Father Name',
    'national_id'                    => $testNid,
    'present_address'                => 'Address 1',
    'permanent_address'              => 'Address 2',
    'institution_or_business'        => 'School',
    'source_of_income'               => 'Tuition',
    'monthly_income'                 => 5000,
    'family_siblings_details'        => 'Siblings',
    'financial_problem_description'  => 'Need waiver',
    'apply_reason_type'              => 'Both',
    'course_id'                      => $course?->id,
]);

// Set session for redirect back
$session = app('session.store');
$reqStoreDup->setLaravelSession($session);

$initialCount = WaiverApplication::where('email', $testEmail)->count();

$response = $controller->store($reqStoreDup);

$finalCount = WaiverApplication::where('email', $testEmail)->count();

assertCondition("Server-side store() rejected duplicate submission (count unchanged = {$initialCount})", $initialCount === 1 && $finalCount === 1);
$isRedirectBack = $response->isRedirection();
assertCondition("Server-side store() redirected back with error", $isRedirectBack);
$hasSessionError = $session->has('error');
$sessionError = $session->get('error');
assertCondition("Session contains error message mentioning application number", $hasSessionError && str_contains($sessionError, $existingApp->application_no));
echo "   -> Server Error Flash: {$sessionError}\n";

// 8. Clean up test records
$existingApp->forceDelete();

echo "\nSummary: $passed / $total assertions passed.\n";

if ($passed === $total) {
    echo "ALL TESTS PASSED SUCCESSFULLY! (Exit Code 0)\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED! (Exit Code 1)\n";
    exit(1);
}
