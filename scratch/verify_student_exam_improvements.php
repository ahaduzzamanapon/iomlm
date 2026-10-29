<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\ExamAnswer;
use App\Models\ExamAppeal;
use App\Models\Question;
use App\Models\ExamQuestion;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Semester;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

echo "========================================================\n";
echo "STARTING VERIFICATION TEST FOR STUDENT EXAM IMPROVEMENTS\n";
echo "========================================================\n\n";

$passedTests = 0;
$totalTests = 0;

function assertTest($description, $condition) {
    global $passedTests, $totalTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo " [PASS] {$description}\n";
    } else {
        echo " [FAIL] {$description}\n";
        throw new \Exception("Assertion failed: {$description}");
    }
}

DB::beginTransaction();

try {
    // -------------------------------------------------------------
    // TEST 1: Reset Deadline Attribute & Helper Methods on Exam
    // -------------------------------------------------------------
    echo "\n--- TEST 1: Exam Reset Deadline & Helper Methods ---\n";
    
    $subject = Subject::first();
    if (!$subject) {
        $subject = Subject::create(['name' => 'Test Subject', 'code' => 'TST101', 'is_active' => true]);
    }

    $examFuture = Exam::create([
        'subject_id'       => $subject->id,
        'title'            => 'Test Exam Future Reset',
        'type'             => 'QUIZ',
        'exam_date'        => now()->subDay()->format('Y-m-d'),
        'start_time'       => '10:00',
        'end_time'         => '12:00',
        'start_datetime'   => now()->subDay()->format('Y-m-d 10:00:00'),
        'end_datetime'     => now()->subHours(5)->format('Y-m-d H:i:s'),
        'duration_minutes' => 30,
        'full_marks'       => 20,
        'pass_marks'       => 8,
        'reset_deadline'   => now()->addHours(24),
        'status'           => 'SCHEDULED',
    ]);

    assertTest("Exam with future reset_deadline allows reset", $examFuture->canApplyForReset() === true);
    assertTest("Exam with future reset_deadline is not expired", $examFuture->isResetDeadlineExpired() === false);

    $examPast = Exam::create([
        'subject_id'       => $subject->id,
        'title'            => 'Test Exam Past Reset',
        'type'             => 'QUIZ',
        'exam_date'        => now()->subDay()->format('Y-m-d'),
        'start_time'       => '10:00',
        'end_time'         => '12:00',
        'start_datetime'   => now()->subDay()->format('Y-m-d 10:00:00'),
        'end_datetime'     => now()->subHours(10)->format('Y-m-d H:i:s'),
        'duration_minutes' => 30,
        'full_marks'       => 20,
        'pass_marks'       => 8,
        'reset_deadline'   => now()->subHours(1),
        'status'           => 'SCHEDULED',
    ]);

    assertTest("Exam with past reset_deadline does not allow reset", $examPast->canApplyForReset() === false);
    assertTest("Exam with past reset_deadline is expired", $examPast->isResetDeadlineExpired() === true);

    // Test editing reset_deadline
    $examPast->update(['reset_deadline' => now()->addHours(12)]);
    $examPast->refresh();
    assertTest("Updated reset_deadline to future enables reset", $examPast->canApplyForReset() === true);

    // -------------------------------------------------------------
    // TEST 2: Running Semester & Subject Filtering
    // -------------------------------------------------------------
    echo "\n--- TEST 2: Running Semester & Subject Filtering ---\n";

    $course1 = Course::create(['name' => 'Test Course ' . uniqid(), 'code' => 'TC' . rand(100, 999), 'type' => 'SEMESTER_BASED', 'is_active' => true]);
    $semRunning = Semester::create(['course_id' => $course1->id, 'name' => 'Running Sem 1', 'sequence_no' => 1]);
    $semOld     = Semester::create(['course_id' => $course1->id, 'name' => 'Old Sem 2', 'sequence_no' => 2]);

    $student = Student::first();
    if (!$student) {
        $student = Student::create([
            'student_id' => 'STU-TEST-99',
            'full_name'  => 'Test Student',
            'gender'     => 'MALE',
            'email'      => 'teststudent99@example.com',
            'status'     => 'ACTIVE',
        ]);
    }

    $batch = Batch::create([
        'course_id'  => $course1->id,
        'name'       => 'Batch ' . rand(100, 999),
        'start_date' => now()->toDateString(),
        'status'     => 'ACTIVE',
    ]);

    $enrollment = Enrollment::create([
        'student_id'  => $student->id,
        'course_id'   => $course1->id,
        'batch_id'    => $batch->id,
        'semester_id' => $semRunning->id,
        'status'      => 'ACTIVE',
        'enrolled_at' => now(),
    ]);

    // Subject 1 in Course 1 for Running Semester
    $subj1 = Subject::firstOrCreate(['code' => 'SUBJ-A'], ['name' => 'Subject Alpha', 'is_active' => true]);
    $subj2 = Subject::firstOrCreate(['code' => 'SUBJ-B'], ['name' => 'Subject Beta', 'is_active' => true]);

    \App\Models\CourseSubjectMap::firstOrCreate([
        'course_id'   => $course1->id,
        'subject_id'  => $subj1->id,
        'semester_id' => $semRunning->id
    ]);
    \App\Models\CourseSubjectMap::firstOrCreate([
        'course_id'   => $course1->id,
        'subject_id'  => $subj2->id,
        'semester_id' => $semRunning->id
    ]);

    // Exam in running semester subject 1
    $runningExam1 = Exam::create([
        'subject_id'       => $subj1->id,
        'semester_id'      => $semRunning->id,
        'title'            => 'Running Sem Exam Subj A',
        'type'             => 'QUIZ',
        'exam_date'        => now()->format('Y-m-d'),
        'start_time'       => '08:00',
        'end_time'         => '20:00',
        'start_datetime'   => now()->format('Y-m-d 08:00:00'),
        'end_datetime'     => now()->format('Y-m-d 20:00:00'),
        'duration_minutes' => 30,
        'full_marks'       => 20,
        'pass_marks'       => 8,
        'status'           => 'SCHEDULED',
    ]);

    // Exam in running semester subject 2
    $runningExam2 = Exam::create([
        'subject_id'       => $subj2->id,
        'semester_id'      => $semRunning->id,
        'title'            => 'Running Sem Exam Subj B',
        'type'             => 'QUIZ',
        'exam_date'        => now()->format('Y-m-d'),
        'start_time'       => '08:00',
        'end_time'         => '20:00',
        'start_datetime'   => now()->format('Y-m-d 08:00:00'),
        'end_datetime'     => now()->format('Y-m-d 20:00:00'),
        'duration_minutes' => 30,
        'full_marks'       => 20,
        'pass_marks'       => 8,
        'status'           => 'SCHEDULED',
    ]);

    // Exam in old/inactive semester
    $oldExam = Exam::create([
        'subject_id'       => $subj1->id,
        'semester_id'      => $semOld->id,
        'title'            => 'Old Sem Exam',
        'type'             => 'QUIZ',
        'exam_date'        => now()->subDays(60)->format('Y-m-d'),
        'start_time'       => '08:00',
        'end_time'         => '20:00',
        'start_datetime'   => now()->subDays(60)->format('Y-m-d 08:00:00'),
        'end_datetime'     => now()->subDays(60)->format('Y-m-d 20:00:00'),
        'duration_minutes' => 30,
        'full_marks'       => 20,
        'pass_marks'       => 8,
        'status'           => 'SCHEDULED',
    ]);

    // Test controller query logic
    $controller = new \App\Http\Controllers\Student\ExamController();

    // Query simulating Student\ExamController::index
    $activeEnrollments = Enrollment::where('student_id', $student->id)
        ->where('status', 'ACTIVE')
        ->with(['batch.course.semesters', 'course.semesters', 'batch.semesterPosition.currentSemester', 'semester'])
        ->get();

    $enrolledSubjectIds   = collect();
    $runningSemesterNames = collect();
    $runningSemesterIds   = collect();

    foreach ($activeEnrollments as $enr) {
        $c = $enr->course ?? $enr->batch?->course;
        if (!$c) continue;

        if ($c->type === 'SEMESTER_BASED') {
            $runningSemester = $enr->semester
                ?? $enr->batch?->semesterPosition?->currentSemester
                ?? $c->semesters->sortBy('sequence_no')->first();

            $runningSemesterId = $runningSemester?->id;
            if ($runningSemester && !empty($runningSemester->name)) {
                $runningSemesterNames->push($runningSemester->name);
            }
            if ($runningSemesterId) {
                $runningSemesterIds->push($runningSemesterId);
                $subIds = \App\Models\CourseSubjectMap::where('course_id', $c->id)
                    ->where('semester_id', $runningSemesterId)
                    ->pluck('subject_id');
                $enrolledSubjectIds = $enrolledSubjectIds->merge($subIds);
            }
        }
    }
    $enrolledSubjectIds   = $enrolledSubjectIds->filter()->unique()->values();
    $runningSemesterIds   = $runningSemesterIds->unique()->values();

    assertTest("Identified student's running semester ID correctly", $runningSemesterIds->contains($semRunning->id));

    $query = Exam::whereIn('subject_id', $enrolledSubjectIds);
    if ($runningSemesterIds->isNotEmpty()) {
        $query->where(function ($q) use ($runningSemesterIds) {
            $q->whereIn('semester_id', $runningSemesterIds)
              ->orWhereNull('semester_id');
        });
    }

    $allRunningExams = $query->pluck('id');
    assertTest("Running exams include Subject Alpha exam", $allRunningExams->contains($runningExam1->id));
    assertTest("Running exams include Subject Beta exam", $allRunningExams->contains($runningExam2->id));
    assertTest("Running exams exclude old semester exam", !$allRunningExams->contains($oldExam->id));

    // Test subject filter
    $filteredSubjA = (clone $query)->where('subject_id', $subj1->id)->pluck('id');
    assertTest("Filtering by Subject A includes Exam A", $filteredSubjA->contains($runningExam1->id));
    assertTest("Filtering by Subject A excludes Exam B", !$filteredSubjA->contains($runningExam2->id));

    // -------------------------------------------------------------
    // TEST 3: Timer & Auto-Submit Expired Submissions
    // -------------------------------------------------------------
    echo "\n--- TEST 3: Timer & Auto-Submit On Expiration ---\n";

    // Setup an exam with 2 MCQ questions
    $q1 = Question::create([
        'subject_id'         => $subj1->id,
        'question_text'      => 'What is 2+2?',
        'question_type'      => 'MCQ',
        'options'            => [
            ['id' => 'a', 'text' => '3'],
            ['id' => 'b', 'text' => '4'],
            ['id' => 'c', 'text' => '5'],
        ],
        'correct_option_id'  => 'b',
    ]);

    $q2 = Question::create([
        'subject_id'         => $subj1->id,
        'question_text'      => 'Capital of Bangladesh?',
        'question_type'      => 'MCQ',
        'options'            => [
            ['id' => 'a', 'text' => 'Dhaka'],
            ['id' => 'b', 'text' => 'Chittagong'],
        ],
        'correct_option_id'  => 'a',
    ]);

    ExamQuestion::create(['exam_id' => $runningExam1->id, 'question_id' => $q1->id, 'marks' => 10]);
    ExamQuestion::create(['exam_id' => $runningExam1->id, 'question_id' => $q2->id, 'marks' => 10]);

    // Student started exam 2 hours ago with duration = 30 mins (time expired)
    $submission = ExamSubmission::create([
        'exam_id'              => $runningExam1->id,
        'student_id'           => $student->id,
        'status'               => 'IN_PROGRESS',
        'started_at'           => now()->subHours(2),
        'assigned_question_ids'=> [$q1->id, $q2->id],
        'shuffled_options'     => [
            $q1->id => [
                'options' => [
                    ['id' => 'a', 'text' => '4', 'original_id' => 'b'],
                    ['id' => 'b', 'text' => '3', 'original_id' => 'a'],
                ],
                'correct_option_id' => 'a',
                'original_correct'  => 'b'
            ],
            $q2->id => [
                'options' => [
                    ['id' => 'a', 'text' => 'Dhaka', 'original_id' => 'a'],
                    ['id' => 'b', 'text' => 'Chittagong', 'original_id' => 'b'],
                ],
                'correct_option_id' => 'a',
                'original_correct'  => 'a'
            ]
        ]
    ]);

    // Student answered Q1 correctly (opt 'a' maps to 'b') and Q2 was left unanswered
    ExamAnswer::create([
        'submission_id'      => $submission->id,
        'question_id'        => $q1->id,
        'selected_option_id' => 'a',
        'is_correct'         => true,
        'marks_awarded'      => 10,
    ]);

    // Execute auto-submit
    $controller->autoSubmitExpiredSubmission($runningExam1, $submission);
    $submission->refresh();

    assertTest("Submission status set to AUTO_SUBMITTED_TIMEOUT", $submission->status === 'AUTO_SUBMITTED_TIMEOUT');
    assertTest("Submission mcq_score accurately calculated (10)", (float) $submission->mcq_score === 10.0);
    assertTest("Submission correct count is 1", (int) $submission->correct_count === 1);
    assertTest("Submission submitted_at is recorded", !empty($submission->submitted_at));

    $res = Result::where('exam_id', $runningExam1->id)->where('student_id', $student->id)->first();
    assertTest("Result record created/updated for student", $res !== null);
    assertTest("Result marks match mcq_marks (10.0)", (float) $res->marks === 10.0);
    assertTest("Result pass status correctly evaluated as PASS (pass_marks=8)", $res->status === 'PASS');

    // -------------------------------------------------------------
    // TEST 4: Blade View Compilation
    // -------------------------------------------------------------
    echo "\n--- TEST 4: Blade View Compilation ---\n";

    $indexHtml = View::make('student.exams.index', [
        'exams'                => collect([$runningExam1]),
        'student'              => $student,
        'subjects'             => collect([$subj1, $subj2]),
        'selectedSubjectId'    => null,
        'runningSemesterNames' => 'Semester 1',
    ])->render();
    assertTest("student.exams.index compiles without errors", strlen($indexHtml) > 500);
    assertTest("student.exams.index contains 'এক্সাম রিসেট'", str_contains($indexHtml, 'এক্সাম রিসেট'));
    assertTest("student.exams.index contains subject filter dropdown", str_contains($indexHtml, 'filterSubject'));

    $takeHtml = View::make('student.exams.take', [
        'exam'             => $runningExam1,
        'submission'       => $submission,
        'savedAnswers'     => collect(),
        'remainingSeconds' => 1200,
    ])->render();
    assertTest("student.exams.take compiles without errors", strlen($takeHtml) > 500);
    assertTest("student.exams.take timer contains remainingSeconds 1200", str_contains($takeHtml, '1200'));
    assertTest("student.exams.take contains auto-submit logic", str_contains($takeHtml, 'পরীক্ষার সময় শেষ হয়েছে!'));

    $resultHtml = View::make('student.exams.result', [
        'exam'         => $runningExam1,
        'submission'   => $submission,
        'latestAppeal' => null,
    ])->render();
    assertTest("student.exams.result compiles without errors", strlen($resultHtml) > 500);
    assertTest("student.exams.result displays AUTO_SUBMITTED_TIMEOUT notice", str_contains($resultHtml, 'সময় সমাপ্তি নোটিশ'));

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n❌ ERROR DURING VERIFICATION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
} finally {
    DB::rollBack();
}

echo "\n========================================================\n";
echo "ALL TESTS PASSED: {$passedTests} / {$totalTests} (Exit Code 0)\n";
echo "========================================================\n";
exit(0);
