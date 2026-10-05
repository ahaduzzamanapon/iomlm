<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Subject;
use App\Models\Semester;
use App\Models\CourseSubjectMap;
use App\Models\AdmissionForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

echo "=== VERIFYING STUDENT ID RULES & SEMESTER SUBJECT MODAL OVERHAUL ===\n\n";

$assertions = 0;

function assertCondition($cond, $msg) {
    global $assertions;
    if (!$cond) {
        echo "❌ FAILED: $msg\n";
        exit(1);
    }
    echo "✅ PASSED: $msg\n";
    $assertions++;
}

// -------------------------------------------------------------
// 1. TEST STUDENT ID RESOLUTION METHODS ON STUDENT MODEL
// -------------------------------------------------------------
echo "\n--- 1. Testing Student Model ID Generation & Resolvers ---\n";

$b22 = Batch::find(22); // Batch '01', Course 15, Academic Year 2027 (start_date: 2027-01-01)
assertCondition($b22 !== null, "Batch 22 found");

$yearCode22 = Student::resolveAcademicYearCode($b22);
assertCondition($yearCode22 === '27', "Batch 22 Academic Year resolved to '27' (got: '{$yearCode22}')");

$batchNum22 = Student::resolveBatchNumberCode($b22);
assertCondition($batchNum22 === '01', "Batch 22 Batch Number resolved to '01' (got: '{$batchNum22}')");

$c15 = Course::find(15); // Alim Preparatory Course, code '01'
$courseCode15 = Student::resolveCourseCode($c15);
assertCondition($courseCode15 === '01', "Course 15 Course Code resolved to '01' (got: '{$courseCode15}')");

$genderMale = Student::resolveGenderCode('Male');
assertCondition($genderMale === '1', "Male gender resolves to '1'");

$genderFemale = Student::resolveGenderCode('Female');
assertCondition($genderFemale === '2', "Female gender resolves to '2'");

// Check Batch 19 (start_date: 2028-01-01)
$b19 = Batch::find(19);
if ($b19) {
    $yearCode19 = Student::resolveAcademicYearCode($b19);
    assertCondition($yearCode19 === '28', "Batch 19 Academic Year resolved to '28' (got: '{$yearCode19}')");
    $batchNum19 = Student::resolveBatchNumberCode($b19);
    assertCondition($batchNum19 === '19', "Batch 19 ('Alim 2819') Batch Number resolved to '19' (got: '{$batchNum19}')");
}

// Check Bengali numeral support in batch name (e.g. 'নাজেরা-০১')
$b14 = Batch::find(14);
if ($b14) {
    $batchNum14 = Student::resolveBatchNumberCode($b14);
    assertCondition($batchNum14 === '01', "Batch 14 ('নাজেরা-০১') Bengali numeral resolved to '01' (got: '{$batchNum14}')");
}

// -------------------------------------------------------------
// 2. TEST MAZHARUL ISLAM HRIDOY (STUDENT 24, APP-2026-0026)
// -------------------------------------------------------------
echo "\n--- 2. Verifying Student 24 (Mazharul Islam Hridoy) in Database ---\n";

$s24 = Student::find(24);
assertCondition($s24 !== null, "Student 24 found in DB");
assertCondition($s24->student_code === '27010110003', "Student 24 student_code is '27010110003' (got: '{$s24->student_code}')");

// Check the exact substring rules:
// - Digits 1-2: Academic Year ('27')
// - Digits 3-4: Batch Number ('01')
// - Digits 5-6: Course Code ('01')
// - Digit 7: Gender ('1')
// - Digits 8-11: Serial ('0003')
assertCondition(substr($s24->student_code, 0, 2) === '27', "Digits 1-2 are Academic Year '27'");
assertCondition(substr($s24->student_code, 2, 2) === '01', "Digits 3-4 are Batch Number '01'");
assertCondition(substr($s24->student_code, 4, 2) === '01', "Digits 5-6 are Course Code '01'");
assertCondition(substr($s24->student_code, 6, 1) === '1', "Digit 7 is Gender '1'");
assertCondition(substr($s24->student_code, 7, 4) === '0003', "Digits 8-11 are Serial '0003'");

// Verify Admission Form 26 renders this student ID
$form26 = AdmissionForm::where('application_no', 'APP-2026-0026')->first();
assertCondition($form26 !== null, "AdmissionForm APP-2026-0026 found");

$htmlSuccess = View::make('apply.success', [
    'form' => $form26,
    'transaction' => null,
])->render();

assertCondition(str_contains($htmlSuccess, '27010110003'), "Admission success view renders official ID '27010110003'");
assertCondition(str_contains($htmlSuccess, 'আপনার অফিসিয়াল শিক্ষার্থী আইডি'), "Admission success view has official student ID heading");

// -------------------------------------------------------------
// 3. VERIFY ALL EXISTING DATABASE STUDENT CODES
// -------------------------------------------------------------
echo "\n--- 3. Verifying All Existing Database Student Codes ---\n";

$allStudents = Student::whereNotNull('student_code')->where('student_code', '!=', '')->get();
$seenCodes = [];
foreach ($allStudents as $st) {
    assertCondition(strlen($st->student_code) === 11, "Student ID {$st->id} ({$st->name}) has 11 digits: '{$st->student_code}'");
    assertCondition(!in_array($st->student_code, $seenCodes), "Student code '{$st->student_code}' is strictly unique");
    $seenCodes[] = $st->student_code;
}
echo "Checked " . count($allStudents) . " students: all have strictly unique 11-digit compliant codes.\n";

// -------------------------------------------------------------
// 4. TEST NEW STUDENT CODE GENERATION VIA CONTROLLER & PAYMENT
// -------------------------------------------------------------
echo "\n--- 4. Testing generateStudentCode for New Batch Enrollment ---\n";

$newCodeGenerated = Student::generateStudentCode($b22, $c15, 'Female');
assertCondition(str_starts_with($newCodeGenerated, '2701012'), "New Female student code starts with '2701012'");
assertCondition(strlen($newCodeGenerated) === 11, "New student code has length 11 ('{$newCodeGenerated}')");

// -------------------------------------------------------------
// 5. TEST SEMESTER SUBJECT MODAL OVERHAUL IN SHOW.BLADE.PHP
// -------------------------------------------------------------
echo "\n--- 5. Testing Semester Subject Modal Overhaul in show.blade.php ---\n";

$adminUser = User::where('role', 'admin')->orWhere('role', 'super_admin')->first();
Auth::login($adminUser);

$viewData = View::make('admin.courses.show', [
    'course' => $c15->load(['semesters.subjects', 'batches', 'feePackages']),
    'availableSubjects' => Subject::orderBy('name')->get(),
    'allSubjects' => Subject::orderBy('name')->get(),
    'paymentGateways' => \App\Models\PaymentGateway::where('is_active', true)->get(),
])->render();

assertCondition(str_contains($viewData, 'id="addSingleSubjectModal"'), "Modal #addSingleSubjectModal present in DOM");
assertCondition(str_contains($viewData, 'id="single_subject_search_input"'), "Search input #single_subject_search_input present in DOM");
assertCondition(str_contains($viewData, 'single-subj-checkbox'), "Checkbox class .single-subj-checkbox present in DOM");
assertCondition(str_contains($viewData, 'single-subj-item'), "Item class .single-subj-item present in DOM");
assertCondition(str_contains($viewData, 'toggleSelectAllSingleSubjects(true)'), "Select all button calling toggleSelectAllSingleSubjects(true) found");
assertCondition(str_contains($viewData, 'toggleSelectAllSingleSubjects(false)'), "Deselect button calling toggleSelectAllSingleSubjects(false) found");
assertCondition(str_contains($viewData, 'id="single_subjects_selected_badge"'), "Counter badge #single_subjects_selected_badge present in DOM");
assertCondition(str_contains($viewData, 'id="single_subj_visible_counter"'), "Visible item counter #single_subj_visible_counter present in DOM");

// -------------------------------------------------------------
// 6. TEST ASSIGNING MULTIPLE SUBJECTS TO A SEMESTER
// -------------------------------------------------------------
echo "\n--- 6. Testing Multiple Subject Assignment to Semester via CourseController ---\n";

$sem1 = $c15->semesters()->first();
assertCondition($sem1 !== null, "Semester 1 found for Course 15");

$sampleSubjs = Subject::take(3)->pluck('id')->toArray();
assertCondition(count($sampleSubjs) === 3, "Found 3 sample subjects to map");

$courseController = new \App\Http\Controllers\Admin\CourseController();

$req = Request::create(route('admin.courses.subjects.assign', $c15), 'POST', [
    'semester_id' => $sem1->id,
    'subject_ids' => $sampleSubjs,
]);

$response = $courseController->assignSubject($req, $c15);
assertCondition($response->isRedirection(), "CourseController::assignSubject redirected successfully");

foreach ($sampleSubjs as $sId) {
    $mapping = CourseSubjectMap::where('course_id', $c15->id)
        ->where('subject_id', $sId)
        ->where('semester_id', $sem1->id)
        ->first();
    assertCondition($mapping !== null, "Subject ID {$sId} mapped to Course {$c15->id} Semester {$sem1->id}");
}

echo "\n======================================================\n";
echo ">>> ALL {$assertions} ASSERTIONS PASSED WITH EXIT CODE 0 <<<\n";
echo "======================================================\n";
exit(0);
