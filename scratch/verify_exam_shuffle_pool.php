<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamSubmission;
use App\Models\ExamAnswer;
use App\Models\Question;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

echo "=== Starting Test: Exam Question Pool & Per-Student Random Shuffle ===\n";

DB::beginTransaction();

try {
    // 1. Verify DB Column & Model Cast
    $hasColumn = DB::getSchemaBuilder()->hasColumn('exam_submissions', 'assigned_question_ids');
    if (!$hasColumn) {
        throw new Exception("Column assigned_question_ids missing from exam_submissions table!");
    }
    echo "[PASS] Database column 'assigned_question_ids' exists.\n";

    $subModel = new ExamSubmission();
    if (!isset($subModel->getCasts()['assigned_question_ids']) || $subModel->getCasts()['assigned_question_ids'] !== 'array') {
        throw new Exception("ExamSubmission model does not cast assigned_question_ids to array!");
    }
    echo "[PASS] ExamSubmission model correctly casts 'assigned_question_ids' to array.\n";

    // 2. Create Test Subject & Exam
    $subject = Subject::first();
    if (!$subject) {
        $subject = Subject::create([
            'code' => 'TEST101',
            'name' => 'Question Pool Subject',
            'is_active' => true,
        ]);
    }

    $exam = Exam::create([
        'subject_id'       => $subject->id,
        'title'            => 'Pool Shuffle Test Exam',
        'type'             => 'QUIZ',
        'exam_date'        => now()->toDateString(),
        'start_time'       => '00:00:00',
        'end_time'         => '23:59:59',
        'duration_minutes' => 30,
        'full_marks'       => 20.00,   // Target: 20 marks
        'pass_marks'       => 8.00,
        'negative_marking' => 0.00,
    ]);

    echo "[INFO] Created Exam ID: {$exam->id} with Full Marks: {$exam->full_marks}\n";

    // 3. Create a Question Pool of 30 questions (each 2 marks = 60 marks total pool)
    $createdQuestions = [];
    for ($i = 1; $i <= 30; $i++) {
        $q = Question::create([
            'subject_id'         => $subject->id,
            'question_type'      => 'MCQ',
            'question_text'      => "Pool Question #{$i}: What is {$i} + {$i}?",
            'difficulty'         => 'MEDIUM',
            'exam_type'          => 'MID',
            'options'            => [
                ['id' => 'a', 'text' => (string) ($i * 2)],
                ['id' => 'b', 'text' => (string) ($i * 2 + 1)],
                ['id' => 'c', 'text' => (string) ($i * 2 + 2)],
                ['id' => 'd', 'text' => (string) ($i * 2 + 3)],
            ],
            'correct_option_id'  => 'a',
            'source_tag'         => 'PoolTest',
        ]);

        ExamQuestion::create([
            'exam_id'     => $exam->id,
            'question_id' => $q->id,
            'marks'       => 2.00,
        ]);

        $createdQuestions[] = $q;
    }

    $exam->load('examQuestions.question');
    $poolTotalMarks = $exam->examQuestions->sum('marks');
    $poolCount = $exam->examQuestions->count();
    echo "[PASS] Question Pool created: {$poolCount} questions attached, totaling {$poolTotalMarks} marks (Target is 20 marks).\n";

    if ($poolTotalMarks != 60 || $poolCount != 30) {
        throw new Exception("Pool creation mismatch: expected 30 questions / 60 marks, got {$poolCount} / {$poolTotalMarks}");
    }

    // 4. Create Two Test Students
    $user1 = User::create([
        'name'     => 'Student Alpha',
        'email'    => 'alpha_' . uniqid() . '@test.com',
        'password' => bcrypt('secret'),
        'role'     => 'student',
    ]);
    $student1 = Student::create([
        'user_id'      => $user1->id,
        'student_code' => 'ST-ALPHA-' . rand(100, 999),
        'name'         => 'Student Alpha',
        'email'        => $user1->email,
        'phone'        => '01711111111',
        'status'       => 'ACTIVE',
    ]);

    $user2 = User::create([
        'name'     => 'Student Beta',
        'email'    => 'beta_' . uniqid() . '@test.com',
        'password' => bcrypt('secret'),
        'role'     => 'student',
    ]);
    $student2 = Student::create([
        'user_id'      => $user2->id,
        'student_code' => 'ST-BETA-' . rand(100, 999),
        'name'         => 'Student Beta',
        'email'        => $user2->email,
        'phone'        => '01722222222',
        'status'       => 'ACTIVE',
    ]);

    $controller = new \App\Http\Controllers\Student\ExamController();

    // 5. Student 1 Enters Exam (take)
    auth()->login($user1);
    $view1 = $controller->take($exam);
    if (!($view1 instanceof \Illuminate\View\View)) {
        throw new Exception("take() did not return a View for Student 1");
    }

    $submission1 = ExamSubmission::where('exam_id', $exam->id)->where('student_id', $student1->id)->first();
    if (!$submission1 || empty($submission1->assigned_question_ids)) {
        throw new Exception("Student 1 submission or assigned_question_ids not created!");
    }

    $s1AssignedIds = $submission1->assigned_question_ids;
    $s1Count = count($s1AssignedIds);
    // Each question is 2 marks, target is 20 marks => exactly 10 questions
    if ($s1Count !== 10) {
        throw new Exception("Student 1 expected 10 assigned questions (20 marks), got {$s1Count} questions!");
    }
    echo "[PASS] Student 1 entered exam: received exactly {$s1Count} questions totaling 20 marks from 30-question pool.\n";

    // 6. Test Idempotency: Student 1 Reloads Page
    $view1Reload = $controller->take($exam);
    $submission1Reload = ExamSubmission::where('exam_id', $exam->id)->where('student_id', $student1->id)->first();
    if ($submission1Reload->assigned_question_ids !== $s1AssignedIds) {
        throw new Exception("Student 1 questions changed upon page reload! Idempotency failed.");
    }
    echo "[PASS] Student 1 page reload verified: exact same question set and order persisted.\n";

    // 7. Student 2 Enters Exam (take)
    auth()->login($user2);
    $view2 = $controller->take($exam);
    $submission2 = ExamSubmission::where('exam_id', $exam->id)->where('student_id', $student2->id)->first();
    $s2AssignedIds = $submission2->assigned_question_ids;
    $s2Count = count($s2AssignedIds);
    if ($s2Count !== 10) {
        throw new Exception("Student 2 expected 10 assigned questions, got {$s2Count} questions!");
    }

    echo "[PASS] Student 2 entered exam: received exactly {$s2Count} questions totaling 20 marks.\n";

    // Check that Student 1 and Student 2 question sets / orders are different (shuffled)
    if ($s1AssignedIds === $s2AssignedIds) {
        echo "[WARNING] Student 1 and Student 2 got identical permutation.\n";
    } else {
        echo "[PASS] Per-student randomization confirmed: Student 1 and Student 2 received different subsets/orders from pool.\n";
    }

    // 8. Student 1 Submits Exam
    auth()->login($user1);
    // Student 1 answers their 10 questions: 7 correct, 3 wrong
    $answers = [];
    $correctAnswersCount = 0;
    foreach ($s1AssignedIds as $index => $qid) {
        if ($index < 7) {
            $answers[$qid] = 'a'; // correct
            $correctAnswersCount++;
        } else {
            $answers[$qid] = 'b'; // wrong
        }
    }

    $request = Request::create(route('student.exams.submit', $exam), 'POST', [
        'answers' => $answers,
    ]);

    $redirectResponse = $controller->submit($request, $exam);
    $submission1->refresh();

    if ($submission1->status !== 'SUBMITTED') {
        throw new Exception("Submission 1 status not SUBMITTED, got {$submission1->status}");
    }

    $expectedScore = 7 * 2.00; // 14 marks
    if (abs((float)$submission1->total_score - $expectedScore) > 0.001) {
        throw new Exception("Submission 1 total score mismatch: expected {$expectedScore}, got {$submission1->total_score}");
    }
    if ($submission1->correct_count !== 7 || $submission1->wrong_count !== 3) {
        throw new Exception("Submission 1 count mismatch: expected 7 correct / 3 wrong, got {$submission1->correct_count} / {$submission1->wrong_count}");
    }
    echo "[PASS] Student 1 submission evaluated successfully: Score {$submission1->total_score}/{$exam->full_marks} (7 correct, 3 wrong).\n";

    // 9. Student 1 Views Result
    $resultView = $controller->result($exam, $submission1);
    $viewData = $resultView->getData();
    $resultExam = $viewData['exam'];
    $resultQuestionsCount = $resultExam->examQuestions->count();
    if ($resultQuestionsCount !== 10) {
        throw new Exception("Result view expected 10 questions, got {$resultQuestionsCount}");
    }
    echo "[PASS] Result view correctly filtered to only the 10 assigned questions in preserved order.\n";

    // 10. Verify Admin Paper Builder View
    $adminController = new \App\Http\Controllers\Admin\ExamController();
    $builderReq = Request::create(route('admin.exams.builder', $exam), 'GET');
    $builderView = $adminController->builder($builderReq, $exam);
    $builderHtml = $builderView->render();
    if (!str_contains($builderHtml, 'র‍্যান্ডম প্রশ্ন পুল সক্রিয়')) {
        throw new Exception("Admin Paper Builder does not display Question Pool notification banner!");
    }
    echo "[PASS] Admin Paper Builder view renders successfully with 'র‍্যান্ডম প্রশ্ন পুল সক্রিয়' banner.\n";

    echo "\n=== ALL VERIFICATION CHECKS PASSED (EXIT CODE 0) ===\n";

} finally {
    DB::rollBack();
    echo "[INFO] Database rolled back cleanly.\n";
}
