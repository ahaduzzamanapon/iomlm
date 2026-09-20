<?php

use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\FinalMark;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Http\Controllers\Admin\FinalMarkController;
use Illuminate\Http\Request;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=====================================================\n";
echo "=== VERIFYING FINAL MARK GENERATOR ENHANCEMENTS   ===\n";
echo "=====================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertCondition($description, $condition) {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$description}\n";
        $failCount++;
    }
}

// 1. Verify FinalMark::getCriteria() default values
$criteria = FinalMark::getCriteria();
assertCondition("FinalMark::getCriteria() returns all required keys", 
    isset($criteria['class_test_full'], $criteria['class_test_convert'], $criteria['midterm_full'], 
          $criteria['midterm_convert'], $criteria['final_full'], $criteria['final_convert'], 
          $criteria['attendance_convert'], $criteria['pass_mark'])
);
assertCondition("Default attendance convert is numeric", is_numeric($criteria['attendance_convert']));

// 2. Test updating criteria via controller
$controller = new FinalMarkController();
$reqUpdateCriteria = Request::create('/admin/final-marks/update-criteria', 'POST', [
    'class_test_full'    => 40,
    'class_test_convert' => 20,
    'midterm_full'       => 60,
    'midterm_convert'    => 25,
    'final_full'         => 100,
    'final_convert'      => 40,
    'attendance_convert' => 15,
    'pass_mark'          => 45,
]);

$responseCriteria = $controller->updateCriteria($reqUpdateCriteria);
assertCondition("Criteria update returns redirect response (302)", $responseCriteria->getStatusCode() === 302);

$updatedCriteria = FinalMark::getCriteria();
assertCondition("Updated class_test_full is 40", $updatedCriteria['class_test_full'] == 40);
assertCondition("Updated midterm_convert is 25", $updatedCriteria['midterm_convert'] == 25);
assertCondition("Updated attendance_convert is 15", $updatedCriteria['attendance_convert'] == 15);
assertCondition("Updated pass_mark is 45", $updatedCriteria['pass_mark'] == 45);

// 3. Test getBatchSubjects endpoint
$reqBatchSubjects = Request::create('/admin/final-marks/batch-subjects', 'GET', ['batch_id' => 6]);
$resBatchSubjects = $controller->getBatchSubjects($reqBatchSubjects);
$data = json_decode($resBatchSubjects->getContent(), true);
assertCondition("getBatchSubjects returns JSON with subjects array", isset($data['subjects']) && is_array($data['subjects']));
assertCondition("Subjects array is not empty for Batch 6", count($data['subjects']) > 0);

// 4. Test Final Mark Generation for Batch 6 & Subject 3 (ফিকহ-১)
// Ensure an admin is logged in
$admin = User::whereIn('role', ['admin', 'super_admin'])->first() ?? User::first();
if ($admin) {
    auth()->guard('web')->login($admin);
}

// Check if batch 6 has enrolled students
$batch = Batch::find(6);
$subject = Subject::find(3) ?? Subject::first();

$reqGen = Request::create('/admin/final-marks/generate', 'POST', [
    'batch_id'   => $batch->id,
    'subject_id' => $subject->id,
]);
$resGen = $controller->generate($reqGen);
assertCondition("Final mark generate returns redirect response (302)", $resGen->getStatusCode() === 302);

$generatedMark = FinalMark::where('batch_id', $batch->id)->where('subject_id', $subject->id)->first();
assertCondition("FinalMark record exists in database after generation", $generatedMark !== null);
assertCondition("FinalMark has grade assigned", !empty($generatedMark->grade));
assertCondition("FinalMark has status assigned (PASS or FAIL)", in_array($generatedMark->status, ['PASS', 'FAIL']));

// 5. Test Individual Student Attendance Mark Update & Recalculate
$originalTotal = $generatedMark->total_mark;
$newAttendance = 12.5; // Custom attendance mark out of 15
$reqAtt = Request::create("/admin/final-marks/{$generatedMark->id}/update-attendance", 'PATCH', [
    'attendance_converted' => $newAttendance,
    'attendance_percent'   => 85.0,
]);
$resAtt = $controller->updateAttendance($reqAtt, $generatedMark);
assertCondition("updateAttendance returns redirect response (302)", $resAtt->getStatusCode() === 302);

$freshMark = $generatedMark->fresh();
assertCondition("Updated attendance_converted is {$newAttendance}", (float)$freshMark->attendance_converted === $newAttendance);
assertCondition("Updated attendance_percent is 85.0", (float)$freshMark->attendance_percent === 85.0);

$expectedTotal = round(
    ($freshMark->class_test_converted ?? 0) +
    ($freshMark->midterm_converted ?? 0) +
    ($freshMark->final_converted ?? 0) +
    $newAttendance,
    2
);
assertCondition("Recalculated total_mark matches sum ({$expectedTotal})", (float)$freshMark->total_mark === $expectedTotal);
$expectedGrade = FinalMark::calculateGrade($expectedTotal)['grade'];
assertCondition("Recalculated grade matches calculated grade ({$expectedGrade})", $freshMark->grade === $expectedGrade);

// 6. Reset criteria to standard default values
Setting::set('final_mark_class_test_full', 30);
Setting::set('final_mark_class_test_convert', 20);
Setting::set('final_mark_midterm_full', 50);
Setting::set('final_mark_midterm_convert', 30);
Setting::set('final_mark_final_full', 100);
Setting::set('final_mark_final_convert', 40);
Setting::set('final_mark_attendance_convert', 10);
Setting::set('final_mark_pass_mark', 40);

$resetCriteria = FinalMark::getCriteria();
assertCondition("Criteria reset to standard IOM values (CT 20, Mid 30, Final 40, Att 10)", 
    $resetCriteria['class_test_convert'] == 20 && 
    $resetCriteria['midterm_convert'] == 30 && 
    $resetCriteria['final_convert'] == 40 && 
    $resetCriteria['attendance_convert'] == 10
);

// 7. Verify Blade Compilation for admin.final-marks.index
try {
    $rendered = view('admin.final-marks.index', [
        'batches'         => Batch::all(),
        'subjects'        => Subject::all(),
        'semesters'       => \App\Models\Semester::all(),
        'criteria'        => FinalMark::getCriteria(),
        'finalMarks'      => collect(),
        'selectedBatch'   => null,
        'selectedSubject' => null,
    ])->render();
    assertCondition("Blade template admin.final-marks.index renders without syntax errors", strlen($rendered) > 100);
} catch (\Throwable $e) {
    echo "Blade render error: " . $e->getMessage() . "\n";
    assertCondition("Blade template admin.final-marks.index renders without syntax errors", false);
}

echo "\n=====================================================\n";
echo "SUMMARY: Passes = {$passCount}, Failures = {$failCount}\n";
echo "=====================================================\n";

if ($failCount > 0) {
    exit(1);
}

exit(0);
