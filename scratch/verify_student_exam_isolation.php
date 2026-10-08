<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\CourseSubjectMap;
use App\Models\Exam;
use App\Models\Student;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

echo "--- VERIFYING TASK 67: STUDENT EXAM SCOPE ISOLATION ---\n\n";

// 1. Create two distinct courses and subjects
$uniq = Str::random(5);
$courseA = Course::create(['name' => "Course Alpha {$uniq}", 'code' => "CA{$uniq}", 'is_active' => true]);
$courseB = Course::create(['name' => "Course Beta {$uniq}", 'code' => "CB{$uniq}", 'is_active' => true]);

$batchA = Batch::create(['course_id' => $courseA->id, 'name' => "Batch A {$uniq}", 'status' => 'ACTIVE', 'start_date' => now()->toDateString()]);
$batchB = Batch::create(['course_id' => $courseB->id, 'name' => "Batch B {$uniq}", 'status' => 'ACTIVE', 'start_date' => now()->toDateString()]);

$subjectA = Subject::create(['name' => "Subject A {$uniq}", 'code' => "SA{$uniq}", 'is_active' => true]);
$subjectB = Subject::create(['name' => "Subject B {$uniq}", 'code' => "SB{$uniq}", 'is_active' => true]);

CourseSubjectMap::create(['course_id' => $courseA->id, 'subject_id' => $subjectA->id]);
CourseSubjectMap::create(['course_id' => $courseB->id, 'subject_id' => $subjectB->id]);

// 2. Create exams for each subject
$examA = Exam::create([
    'subject_id'       => $subjectA->id,
    'title'            => "Exam Alpha {$uniq}",
    'type'             => 'FINAL',
    'exam_date'        => now()->addDays(2)->format('Y-m-d'),
    'start_datetime'   => now()->addDays(2),
    'end_datetime'     => now()->addDays(3),
    'full_marks'       => 100,
    'pass_marks'       => 40,
    'status'           => 'SCHEDULED',
]);

$examB = Exam::create([
    'subject_id'       => $subjectB->id,
    'title'            => "Exam Beta {$uniq}",
    'type'             => 'FINAL',
    'exam_date'        => now()->addDays(2)->format('Y-m-d'),
    'start_datetime'   => now()->addDays(2),
    'end_datetime'     => now()->addDays(3),
    'full_marks'       => 100,
    'pass_marks'       => 40,
    'status'           => 'SCHEDULED',
]);

// 3. Create Student A enrolled in Course A only
$userA = User::create(['name' => "Student A {$uniq}", 'email' => "userA_{$uniq}@example.com", 'password' => bcrypt('123'), 'role' => 'student']);
$studentA = Student::create(['user_id' => $userA->id, 'name' => "Student A {$uniq}", 'student_code' => "ST-A-{$uniq}", 'phone' => '01711111111', 'status' => 'ACTIVE']);
Enrollment::create(['student_id' => $studentA->id, 'course_id' => $courseA->id, 'batch_id' => $batchA->id, 'status' => 'ACTIVE', 'enrolled_at' => now()]);

// Create Student B enrolled in Course B only
$userB = User::create(['name' => "Student B {$uniq}", 'email' => "userB_{$uniq}@example.com", 'password' => bcrypt('123'), 'role' => 'student']);
$studentB = Student::create(['user_id' => $userB->id, 'name' => "Student B {$uniq}", 'student_code' => "ST-B-{$uniq}", 'phone' => '01722222222', 'status' => 'ACTIVE']);
Enrollment::create(['student_id' => $studentB->id, 'course_id' => $courseB->id, 'batch_id' => $batchB->id, 'status' => 'ACTIVE', 'enrolled_at' => now()]);

echo "1. Fixtures created:\n";
echo "   Student A (Course A) -> Exam A ({$examA->title})\n";
echo "   Student B (Course B) -> Exam B ({$examB->title})\n\n";

// 4. Test Student A's Exam List in Student/ExamController::index
Auth::login($userA);
$examController = app(\App\Http\Controllers\Student\ExamController::class);
$responseA = $examController->index();
$examsForA = $responseA->getData()['exams'];

echo "2. Checking Student A's Exam List:\n";
$hasExamA = $examsForA->contains('id', $examA->id);
$hasExamB = $examsForA->contains('id', $examB->id);

if (!$hasExamA) {
    echo "   [FAIL] Student A cannot see their own enrolled Exam A!\n";
    exit(1);
}
if ($hasExamB) {
    echo "   [FAIL] Student A sees Exam B from un-enrolled Course B! Scope leakage detected!\n";
    exit(1);
}
echo "   [PASS] Student A sees Exam A and does NOT see Exam B!\n\n";

// 5. Test Student B's Exam List in Student/ExamController::index
Auth::login($userB);
$responseB = $examController->index();
$examsForB = $responseB->getData()['exams'];

echo "3. Checking Student B's Exam List:\n";
$bHasExamB = $examsForB->contains('id', $examB->id);
$bHasExamA = $examsForB->contains('id', $examA->id);

if (!$bHasExamB) {
    echo "   [FAIL] Student B cannot see their own enrolled Exam B!\n";
    exit(1);
}
if ($bHasExamA) {
    echo "   [FAIL] Student B sees Exam A from un-enrolled Course A! Scope leakage detected!\n";
    exit(1);
}
echo "   [PASS] Student B sees Exam B and does NOT see Exam A!\n\n";

// 6. Test taking un-enrolled exam (Student A attempting Exam B)
Auth::login($userA);
$takeResponse = $examController->take($examB);
if (!$takeResponse->isRedirect(route('student.exams.index'))) {
    echo "   [FAIL] Student A was not redirected when trying to take un-enrolled Exam B!\n";
    exit(1);
}
$errorMsg = session('error');
if (strpos($errorMsg, 'অন্তর্ভুক্ত নয়') === false) {
    echo "   [FAIL] Expected unauthorized error message, got: '{$errorMsg}'\n";
    exit(1);
}
echo "4. Checking Exam Take Security:\n";
echo "   [PASS] Student A was successfully blocked from taking un-enrolled Exam B!\n\n";

// 7. Test Dashboard Isolation
echo "5. Checking Dashboard Upcoming Exams Isolation:\n";
Auth::login($userA);
$dashController = app(\App\Http\Controllers\Student\DashboardController::class);
$dashResponse = $dashController->index();
$dashExams = $dashResponse->getData()['upcomingExamsList'];
$dashStats = $dashResponse->getData()['stats'];

$dashHasExamA = $dashExams->contains('id', $examA->id);
$dashHasExamB = $dashExams->contains('id', $examB->id);

if (!$dashHasExamA) {
    echo "   [FAIL] Dashboard upcoming exams missing enrolled Exam A!\n";
    exit(1);
}
if ($dashHasExamB) {
    echo "   [FAIL] Dashboard upcoming exams contains un-enrolled Exam B!\n";
    exit(1);
}
echo "   [PASS] Dashboard only shows enrolled Exam A (count: {$dashStats['upcoming_exams']})!\n\n";

// 8. Cleanup
$examA->delete();
$examB->delete();
CourseSubjectMap::whereIn('course_id', [$courseA->id, $courseB->id])->delete();
Enrollment::whereIn('student_id', [$studentA->id, $studentB->id])->delete();
$studentA->delete();
$studentB->delete();
$userA->delete();
$userB->delete();
$batchA->delete();
$batchB->delete();
$subjectA->delete();
$subjectB->delete();
$courseA->delete();
$courseB->delete();

echo "ALL TASK 67 CHECKS PASSED SUCCESSFULLY (Exit Code 0)!\n";
exit(0);
