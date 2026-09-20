<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$passed = 0;
$failed = 0;

function assertCond($desc, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] $desc\n";
        $passed++;
    } else {
        echo "  [FAIL] $desc\n";
        $failed++;
    }
}

echo "=== VERIFYING SEMESTER SELECTION & SUBJECT-BASED HIDING ===\n\n";

$admin = \App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::first();
auth()->login($admin);

$controller = new \App\Http\Controllers\Admin\FinalMarkController();

// ── Test 1: AJAX response for SEMESTER_BASED batch (Batch 17: Alim 2717) ──
$req17 = new \Illuminate\Http\Request(['batch_id' => 17]);
$res17 = $controller->getBatchSubjects($req17)->getData(true);

assertCond(
    "Batch 17 (Alim 2717) has_semesters is true",
    $res17['has_semesters'] === true
);

assertCond(
    "Batch 17 course_type is SEMESTER_BASED",
    $res17['course_type'] === 'SEMESTER_BASED'
);

assertCond(
    "Batch 17 has running_semester_id detected (ID: {$res17['running_semester_id']})",
    !empty($res17['running_semester_id'])
);

assertCond(
    "Batch 17 has semesters list with at least 1 running semester tagged",
    count($res17['semesters']) > 0 && collect($res17['semesters'])->contains('is_running', true)
);

assertCond(
    "Batch 17 subjects have semester_id mapped",
    collect($res17['subjects'])->whereNotNull('semester_id')->count() > 0
);

// ── Test 2: AJAX response for SUBJECT_BASED batch (Batch 21: RNC 2601) ──
$req21 = new \Illuminate\Http\Request(['batch_id' => 21]);
$res21 = $controller->getBatchSubjects($req21)->getData(true);

assertCond(
    "Batch 21 (RNC 2601) has_semesters is false",
    $res21['has_semesters'] === false
);

assertCond(
    "Batch 21 course_type is SUBJECT_BASED",
    $res21['course_type'] === 'SUBJECT_BASED'
);

assertCond(
    "Batch 21 running_semester_id is null",
    $res21['running_semester_id'] === null
);

assertCond(
    "Batch 21 semesters list is empty",
    empty($res21['semesters'])
);

assertCond(
    "Batch 21 subjects list is non-empty",
    count($res21['subjects']) > 0
);

// ── Test 3: View Rendering for SEMESTER_BASED Batch 17 ──
$view17 = $controller->index(new \Illuminate\Http\Request(['batch_id' => 17]));
$html17 = $view17->render();

assertCond(
    "View for Batch 17 shows semester select container (display: block)",
    strpos($html17, 'id="semesterSelectContainer" style="flex:1; min-width:190px; display: block;"') !== false
);

assertCond(
    "View for Batch 17 sets subject label number to ৩",
    strpos($html17, '<span id="subjectLabelNumber">৩</span>') !== false
);

assertCond(
    "View for Batch 17 has semester options rendered",
    strpos($html17, 'name="semester_id"') !== false && strpos($html17, '(রানিং / Running)') !== false
);

// ── Test 4: View Rendering for SUBJECT_BASED Batch 21 ──
$view21 = $controller->index(new \Illuminate\Http\Request(['batch_id' => 21]));
$html21 = $view21->render();

assertCond(
    "View for Batch 21 HIDES semester select container (display: none)",
    strpos($html21, 'id="semesterSelectContainer" style="flex:1; min-width:190px; display: none;"') !== false
);

assertCond(
    "View for Batch 21 sets subject label number to ২",
    strpos($html21, '<span id="subjectLabelNumber">২</span>') !== false
);

// ── Test 5: Client-side JS functions defined in HTML ──
assertCond(
    "handleSemesterChange function is defined on window",
    strpos($html17, 'window.handleSemesterChange = function') !== false
);

assertCond(
    "renderSubjectOptions function is defined",
    strpos($html17, 'function renderSubjectOptions(subjectsList)') !== false
);

// ── Test 6: Generate Marks with Semester ──
$res19 = $controller->getBatchSubjects(new \Illuminate\Http\Request(['batch_id' => 19]))->getData(true);
$subjectId19 = collect($res19['subjects'])->first()['id'];
$runningSem19 = $res19['running_semester_id'];

$genReq = new \Illuminate\Http\Request([
    'batch_id'    => 19,
    'subject_id'  => $subjectId19,
    'semester_id' => $runningSem19,
]);
$genRes = $controller->generate($genReq);

assertCond(
    "generate() with semester_id redirects back to index",
    $genRes->isRedirect()
);

$redirectUrl = $genRes->headers->get('Location');
assertCond(
    "generate() redirect URL includes semester_id",
    strpos($redirectUrl, "semester_id={$runningSem19}") !== false
);

echo "\n=====================================================\n";
echo "SUMMARY: Passed: $passed, Failed: $failed\n";
echo "=====================================================\n";

exit($failed === 0 ? 0 : 1);
