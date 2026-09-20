<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$passed = 0;
$failed = 0;

function assertCondition($desc, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] $desc\n";
        $passed++;
    } else {
        echo "  [FAIL] $desc\n";
        $failed++;
    }
}

echo "=== VERIFYING FINAL MARKS CONVERSION CRITERIA MODAL FIX ===\n";

$admin = \App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::first();
auth()->login($admin);

$batches = \App\Models\Batch::all();
$view = view('admin.final-marks.index', [
    'criteria' => \App\Models\FinalMark::getCriteria(),
    'batches' => $batches,
    'subjects' => collect([]),
    'semesters' => collect([]),
    'finalMarks' => collect([]),
    'selectedBatch' => null,
    'selectedSubject' => null,
]);

$html = $view->render();

// 1. Button existence & attributes
assertCondition(
    "Button 'কনভার্সন ক্রাইটেরিয়া পরিবর্তন' has id='btnOpenCriteriaModal'",
    strpos($html, 'id="btnOpenCriteriaModal"') !== false
);

assertCondition(
    "Button has onclick calling openCriteriaModal",
    preg_match('/id="btnOpenCriteriaModal"[^>]*onclick="openCriteriaModal\(event\)"/', $html) === 1
);

// 2. Modal markup
assertCondition(
    "criteriaModal exists with class 'modal-overlay'",
    strpos($html, '<div id="criteriaModal" class="modal-overlay"') !== false
);

assertCondition(
    "criteriaModal does NOT have inline style='display:none' preventing class toggle",
    strpos($html, '<div id="criteriaModal" class="modal-overlay" style="display:none"') === false
);

assertCondition(
    "criteriaModal backdrop click handler exists",
    strpos($html, 'onclick="if(event.target===this) closeCriteriaModal()"') !== false
);

// 3. CSS checks
assertCondition(
    "CSS has .modal-overlay with z-index: 99999 !important",
    strpos($html, 'z-index: 99999 !important;') !== false
);

assertCondition(
    "CSS has .modal-overlay.open with display: flex !important and opacity: 1 !important",
    strpos($html, 'display: flex !important;') !== false && strpos($html, 'opacity: 1 !important;') !== false
);

// 4. JavaScript functions
assertCondition(
    "window.openCriteriaModal function is defined",
    strpos($html, 'window.openCriteriaModal = function') !== false
);

assertCondition(
    "window.closeCriteriaModal function is defined",
    strpos($html, 'window.closeCriteriaModal = function') !== false
);

assertCondition(
    "window.calcTotalConvert function is defined",
    strpos($html, 'window.calcTotalConvert = function') !== false
);

assertCondition(
    "window.openAttendanceModal function is defined",
    strpos($html, 'window.openAttendanceModal = function') !== false
);

assertCondition(
    "initFinalMarksPage attaches addEventListener to btnOpenCriteriaModal",
    strpos($html, "btnCriteria.addEventListener('click'") !== false
);

// 5. POST route for criteria update works
$response = (new \App\Http\Controllers\Admin\FinalMarkController())->updateCriteria(new \Illuminate\Http\Request([
    'class_test_full' => 30,
    'class_test_convert' => 20,
    'midterm_full' => 50,
    'midterm_convert' => 30,
    'final_full' => 100,
    'final_convert' => 40,
    'attendance_convert' => 10,
    'pass_mark' => 40,
]));

assertCondition(
    "updateCriteria() controller redirects with success",
    $response->isRedirect()
);

echo "\nVerification Summary: Passed: $passed, Failed: $failed\n";
exit($failed === 0 ? 0 : 1);
