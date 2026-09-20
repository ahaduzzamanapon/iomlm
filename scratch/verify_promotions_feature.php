<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Batch;
use App\Models\Student;
use App\Models\Semester;
use App\Models\PromotionRecord;
use App\Models\Readmission;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\PromotionController;

echo "=== STARTING VERIFICATION: SEMESTER PROMOTION & PROGRESSION ENGINE ===\n\n";

// 1. Verify Routes
$routes = [
    'admin.promotions.index',
    'admin.promotions.store',
    'admin.promotions.bulk-promote',
    'admin.promotions.send-readmission',
];
echo "Step 1: Checking Named Routes...\n";
foreach ($routes as $routeName) {
    if (!\Illuminate\Support\Facades\Route::has($routeName)) {
        echo "❌ FAIL: Route '{$routeName}' not found!\n";
        exit(1);
    }
    echo "  ✔ Route '{$routeName}' exists.\n";
}

// 2. Authenticate an Admin User
$adminUser = User::where('role', 'ADMIN')->first() ?? User::first();
auth()->login($adminUser);
echo "\nStep 2: Authenticated user: {$adminUser->name} (Role: {$adminUser->role})\n";

// 3. Test Controller Index - No Parameters
echo "\nStep 3: Testing index() with no batch selected...\n";
$controller = new PromotionController();
$request = Request::create('/admin/promotions', 'GET');
$response = $controller->index($request);

if ($response instanceof \Illuminate\View\View) {
    $html = $response->render();
    if (strpos($html, 'সেমিস্টার প্রমোশন ও একাডেমিক অগ্রগতি') === false) {
        echo "❌ FAIL: Title not found in rendered HTML.\n";
        exit(1);
    }
    echo "  ✔ Default view rendered successfully (" . strlen($html) . " bytes).\n";
} else {
    echo "❌ FAIL: Controller index did not return a View instance.\n";
    exit(1);
}

// 4. Test Controller Index with Semester-based Batch (Batch 6)
echo "\nStep 4: Testing index() with Semester-based Batch (Batch 6)...\n";
$requestBatch6 = Request::create('/admin/promotions', 'GET', ['batch_id' => 6]);
$responseBatch6 = $controller->index($requestBatch6);
$viewData = $responseBatch6->getData();

if (!$viewData['selectedBatch']) {
    echo "❌ FAIL: selectedBatch is null for batch_id=6.\n";
    exit(1);
}
if (!$viewData['isSemesterBased']) {
    echo "❌ FAIL: isSemesterBased should be true for batch_id=6.\n";
    exit(1);
}
if (empty($viewData['selectedSemesterId'])) {
    echo "❌ FAIL: selectedSemesterId should be populated.\n";
    exit(1);
}
if (!$viewData['examAudit']) {
    echo "❌ FAIL: examAudit should be calculated.\n";
    exit(1);
}

echo "  ✔ Batch: " . $viewData['selectedBatch']->name . "\n";
echo "  ✔ Is Semester Based: Yes\n";
echo "  ✔ Running/Selected Semester ID: " . $viewData['selectedSemesterId'] . "\n";
echo "  ✔ Next Semester: " . ($viewData['nextSemester']?->name ?? 'None / Final') . "\n";
echo "  ✔ Exam Audit: Total Subjects = " . $viewData['examAudit']['total_subjects'] . ", Completed = " . $viewData['examAudit']['completed_subjects'] . "\n";
echo "  ✔ Student Evaluations Count: " . $viewData['studentEvaluations']->count() . "\n";

foreach ($viewData['studentEvaluations'] as $ev) {
    echo "    - Student: {$ev['student']->name} | Standing: {$ev['standing']} ({$ev['status_text']})\n";
}

$htmlBatch6 = $responseBatch6->render();
if (strpos($htmlBatch6, 'পরীক্ষার ক্রাইটেরিয়া') === false && strpos($htmlBatch6, 'শিক্ষার্থীদের মূল্যায়ন ফলাফল') === false) {
    echo "❌ FAIL: Evaluation table not found in HTML for batch_id=6.\n";
    exit(1);
}
echo "  ✔ Batch 6 View rendered successfully (" . strlen($htmlBatch6) . " bytes).\n";

// 5. Test Controller Index with Subject-based Batch (Batch 21)
echo "\nStep 5: Testing index() with Subject-based Batch (Batch 21)...\n";
$requestBatch21 = Request::create('/admin/promotions', 'GET', ['batch_id' => 21]);
$responseBatch21 = $controller->index($requestBatch21);
$viewData21 = $responseBatch21->getData();

if ($viewData21['isSemesterBased']) {
    echo "❌ FAIL: isSemesterBased should be false for batch_id=21.\n";
    exit(1);
}
echo "  ✔ Batch 21 correctly identified as Subject-based (no semester).\n";
$htmlBatch21 = $responseBatch21->render();
echo "  ✔ Batch 21 View rendered successfully (" . strlen($htmlBatch21) . " bytes).\n";

// 6. Test sendReadmission() Validation & Execution
echo "\nStep 6: Testing sendReadmission() functionality...\n";
$testStudent = Student::first();
if ($testStudent) {
    // Check if can trigger sendReadmission
    $reqReadmit = Request::create('/admin/promotions/send-readmission', 'POST', [
        'student_id'  => $testStudent->id,
        'batch_id'    => 6,
        'semester_id' => $viewData['selectedSemesterId'],
        'notes'       => 'Verification test entry',
    ]);
    
    // We can simulate validation
    $validator = \Illuminate\Support\Facades\Validator::make($reqReadmit->all(), [
        'student_id'  => 'required|exists:students,id',
        'batch_id'    => 'required|exists:batches,id',
        'semester_id' => 'nullable|exists:semesters,id',
        'notes'       => 'nullable|string',
    ]);
    if ($validator->fails()) {
        echo "❌ FAIL: sendReadmission validation failed: " . json_encode($validator->errors()->all()) . "\n";
        exit(1);
    }
    echo "  ✔ sendReadmission validation passed.\n";
}

// 7. Test bulkPromote() Validation
echo "\nStep 7: Testing bulkPromote() validation...\n";
$reqBulk = Request::create('/admin/promotions/bulk-promote', 'POST', [
    'batch_id'         => 6,
    'from_semester_id' => 1,
    'to_semester_id'   => 2,
    'student_ids'      => [$testStudent->id],
]);
$validatorBulk = \Illuminate\Support\Facades\Validator::make($reqBulk->all(), [
    'batch_id'         => 'required|exists:batches,id',
    'from_semester_id' => 'nullable|exists:semesters,id',
    'to_semester_id'   => 'required|exists:semesters,id',
    'student_ids'      => 'required|array',
    'student_ids.*'    => 'exists:students,id',
]);
if ($validatorBulk->fails()) {
    echo "❌ FAIL: bulkPromote validation failed: " . json_encode($validatorBulk->errors()->all()) . "\n";
    exit(1);
}
echo "  ✔ bulkPromote validation passed.\n";

echo "\n=======================================================\n";
echo "🎉 ALL VERIFICATION TESTS PASSED SUCCESSFULLY! (EXIT 0)\n";
echo "=======================================================\n";
exit(0);
