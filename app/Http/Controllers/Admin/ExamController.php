<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Result;
use App\Models\Question;
use App\Models\ExamQuestion;
use App\Models\ExamAppeal;
use App\Models\Batch;
use App\Models\Semester;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $status     = $request->query('status');
        $subjectId  = $request->query('subject_id');
        $search     = $request->query('search');
        $examType   = $request->query('type');
        $batchId    = $request->query('batch_id');
        $semesterId = $request->query('semester_id');

        $query = Exam::with(['subject', 'semester', 'attendees.student']);

        if ($status && in_array(strtoupper($status), ['SCHEDULED', 'RUNNING', 'COMPLETED', 'CANCELLED'])) {
            $query->where('status', strtoupper($status));
        }

        if ($examType && in_array(strtoupper($examType), ['FINAL', 'MIDTERM', 'QUIZ', 'RETAKE', 'PRACTICAL'])) {
            $query->where('type', strtoupper($examType));
        }

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        if ($batchId) {
            $batch = Batch::with('course.semesters', 'course.subjects')->find($batchId);
            if ($batch && $batch->course) {
                $batchCourseSubjectIds = $batch->course->subjects->pluck('id');
                $batchCourseSemIds     = $batch->course->semesters->pluck('id');
                $query->where(function ($q) use ($batchCourseSubjectIds, $batchCourseSemIds) {
                    $q->whereIn('subject_id', $batchCourseSubjectIds)
                      ->orWhereIn('semester_id', $batchCourseSemIds);
                });
            }
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('subject', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        $exams     = $query->latest()->get();
        $subjects  = Subject::where('is_active', true)->orderBy('name')->get();
        $batches   = Batch::with('course.semesters')->orderByDesc('id')->get();
        $semesters = Semester::orderBy('sequence_no')->get();

        $statusCounts = [
            'ALL'       => Exam::count(),
            'SCHEDULED' => Exam::where('status', 'SCHEDULED')->count(),
            'RUNNING'   => Exam::where('status', 'RUNNING')->count(),
            'COMPLETED' => Exam::where('status', 'COMPLETED')->count(),
            'CANCELLED' => Exam::where('status', 'CANCELLED')->count(),
        ];

        return view('admin.exams.index', compact(
            'exams', 'subjects', 'batches', 'semesters',
            'status', 'subjectId', 'search', 'examType', 'batchId', 'semesterId', 'statusCounts'
        ));
    }

    public function store(Request $request)
    {
        $hasMcq     = $request->boolean('has_mcq');
        $hasWritten = $request->boolean('has_written');
        $hasTamrin  = $request->boolean('has_tamrin');
        $hasViva    = $request->boolean('has_viva');

        // If none checked, default to MCQ
        if (!$hasMcq && !$hasWritten && !$hasTamrin && !$hasViva) {
            $hasMcq = true;
        }

        $mcqMarks     = $hasMcq ? (float) $request->input('mcq_marks', 0) : 0.00;
        $writtenMarks = $hasWritten ? (float) $request->input('written_marks', 0) : 0.00;
        $tamrinMarks  = $hasTamrin ? (float) $request->input('tamrin_marks', 0) : 0.00;
        $vivaMarks    = $hasViva ? (float) $request->input('viva_marks', 0) : 0.00;

        $computedFullMarks = $mcqMarks + $writtenMarks + $tamrinMarks + $vivaMarks;
        $requestedFullMarks = (int) $request->input('full_marks', 0);
        $finalFullMarks = ($requestedFullMarks > 0) ? $requestedFullMarks : (int) $computedFullMarks;
        if ($finalFullMarks <= 0) {
            $finalFullMarks = 100;
        }

        // If only MCQ is active and mcq_marks was 0, sync with finalFullMarks
        if ($hasMcq && !$hasWritten && !$hasTamrin && !$hasViva && $mcqMarks <= 0) {
            $mcqMarks = $finalFullMarks;
        }

        $validated = $request->validate([
            'subject_id'       => 'required|exists:subjects,id',
            'title'            => 'required|string|max:200',
            'type'             => 'required|in:MIDTERM,FINAL,RETAKE,QUIZ,PRACTICAL',
            'exam_date'        => 'required|date',
            'end_date'         => 'nullable|date|after_or_equal:exam_date',
            'start_time'       => 'nullable|string',
            'end_time'         => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:5|max:360',
            'full_marks'       => 'nullable|integer|min:1',
            'pass_marks'       => 'required|integer|min:1',
            'negative_marking' => 'nullable|numeric|min:0|max:5',
        ]);

        $examDate  = $validated['exam_date'];
        $endDate   = $validated['end_date'] ?? $examDate;
        $startTime = $validated['start_time'] ?? null;
        $endTime   = $validated['end_time'] ?? null;

        $startDatetime = $startTime ? "{$examDate} {$startTime}:00" : "{$examDate} 00:00:00";
        $endDatetime   = $endTime ? "{$endDate} {$endTime}:00" : "{$endDate} 23:59:59";

        Exam::create([
            'subject_id'       => $validated['subject_id'],
            'title'            => $validated['title'],
            'type'             => $validated['type'],
            'exam_date'        => $examDate,
            'end_date'         => $endDate,
            'start_time'       => $startTime,
            'end_time'         => $endTime,
            'start_datetime'   => $startDatetime,
            'end_datetime'     => $endDatetime,
            'duration_minutes' => $validated['duration_minutes'] ?? 90,
            'full_marks'       => $finalFullMarks,
            'pass_marks'       => $validated['pass_marks'],
            'negative_marking' => $validated['negative_marking'] ?? 0.00,
            'has_mcq'          => $hasMcq,
            'mcq_marks'        => $mcqMarks,
            'has_written'      => $hasWritten,
            'written_marks'    => $writtenMarks,
            'has_tamrin'       => $hasTamrin,
            'tamrin_marks'     => $tamrinMarks,
            'has_viva'         => $hasViva,
            'viva_marks'       => $vivaMarks,
            'status'           => 'SCHEDULED',
        ]);

        return back()->with('success', 'পরীক্ষা সফলভাবে শিডিউল করা হয়েছে।');
    }

    public function show(Exam $exam)
    {
        $exam->load([
            'subject',
            'semester.course',
            'attendees.student',
            'results.student',
            'submissions.student',
            'examQuestions.question',
            'appeals.student'
        ]);

        $results = $exam->results;
        $allStudentScores = [];

        foreach ($results as $res) {
            if (!$res->student) continue;
            $allStudentScores[$res->student_id] = [
                'student'       => $res->student,
                'marks'         => (float) $res->marks,
                'mcq_marks'     => $res->mcq_marks,
                'written_marks' => $res->written_marks,
                'tamrin_marks'  => $res->tamrin_marks,
                'viva_marks'    => $res->viva_marks,
                'grade'         => $res->grade,
                'status'        => $res->status,
                'attempt_no'    => $res->attempt_no ?? 1,
            ];
        }

        foreach ($exam->submissions as $sub) {
            if (!$sub->student) continue;
            if (!isset($allStudentScores[$sub->student_id])) {
                $status = ($sub->total_score >= $exam->pass_marks) ? 'PASS' : 'FAIL';
                $pct = $exam->full_marks > 0 ? (($sub->total_score / $exam->full_marks) * 100) : 0;
                $grade = match(true) {
                    $pct >= 80 => 'A+',
                    $pct >= 70 => 'A',
                    $pct >= 60 => 'A-',
                    $pct >= 50 => 'B',
                    $pct >= 40 => 'C',
                    default    => 'F',
                };
                $allStudentScores[$sub->student_id] = [
                    'student'       => $sub->student,
                    'marks'         => (float) $sub->total_score,
                    'mcq_marks'     => $sub->mcq_score,
                    'written_marks' => $sub->written_score,
                    'tamrin_marks'  => $sub->tamrin_score,
                    'viva_marks'    => $sub->viva_score,
                    'grade'         => $grade,
                    'status'        => $status,
                    'attempt_no'    => 1,
                ];
            }
        }

        // Sort descending by marks
        usort($allStudentScores, fn($a, $b) => $b['marks'] <=> $a['marks']);

        $bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        foreach ($allStudentScores as $idx => &$item) {
            $pos = $idx + 1;
            $bnNum = str_replace(range(0, 9), $bnDigits, (string) $pos);
            $suffix = match($pos) {
                1 => 'ম',
                2, 3 => 'য়',
                4 => 'র্থ',
                default => 'ম'
            };
            $item['merit_rank_bengali'] = $bnNum . $suffix;
            $item['merit_position'] = $pos;

            $fullMarks = $exam->full_marks > 0 ? $exam->full_marks : 100;
            $pct = round(($item['marks'] / $fullMarks) * 100, 1);
            $item['percentage'] = $pct;
            $item['qawmi_grade'] = \App\Models\FinalMark::calculateQawmiGrade($pct);
            if (empty($item['grade'])) {
                $gInfo = \App\Models\FinalMark::calculateGrade($pct);
                $item['grade'] = $gInfo['grade'];
            }
        }
        unset($item);

        $meritList = collect($allStudentScores);

        return view('admin.exams.show', compact('exam', 'meritList'));
    }

    public function update(Request $request, Exam $exam)
    {
        if ($request->has('has_mcq') || $request->has('title') || $request->has('full_marks')) {
            $hasMcq     = $request->boolean('has_mcq');
            $hasWritten = $request->boolean('has_written');
            $hasTamrin  = $request->boolean('has_tamrin');
            $hasViva    = $request->boolean('has_viva');

            $mcqMarks     = $hasMcq ? (float) $request->input('mcq_marks', 0) : 0.00;
            $writtenMarks = $hasWritten ? (float) $request->input('written_marks', 0) : 0.00;
            $tamrinMarks  = $hasTamrin ? (float) $request->input('tamrin_marks', 0) : 0.00;
            $vivaMarks    = $hasViva ? (float) $request->input('viva_marks', 0) : 0.00;

            $computedFullMarks = $mcqMarks + $writtenMarks + $tamrinMarks + $vivaMarks;
            $requestedFullMarks = (int) $request->input('full_marks', 0);
            $finalFullMarks = ($requestedFullMarks > 0) ? $requestedFullMarks : (($computedFullMarks > 0) ? (int)$computedFullMarks : $exam->full_marks);

            $updateData = [
                'has_mcq'       => $hasMcq,
                'mcq_marks'     => $mcqMarks,
                'has_written'   => $hasWritten,
                'written_marks' => $writtenMarks,
                'has_tamrin'    => $hasTamrin,
                'tamrin_marks'  => $tamrinMarks,
                'has_viva'      => $hasViva,
                'viva_marks'    => $vivaMarks,
                'full_marks'    => $finalFullMarks,
            ];

            if ($request->filled('pass_marks')) {
                $updateData['pass_marks'] = (int) $request->input('pass_marks');
            }
            if ($request->filled('status')) {
                $updateData['status'] = $request->input('status');
            }

            $exam->update($updateData);
            return back()->with('success', 'পরীক্ষার মূল্যায়ন কাঠামো ও তথ্য সফলভাবে আপডেট করা হয়েছে।');
        }

        $validated = $request->validate([
            'status' => 'required|in:SCHEDULED,ONGOING,COMPLETED,CANCELLED',
        ]);
        $exam->update($validated);
        return back()->with('success', 'পরীক্ষার স্ট্যাটাস আপডেট করা হয়েছে।');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return back()->with('success', 'পরীক্ষা মুছে ফেলা হয়েছে।');
    }

    public function resetSubmission(Exam $exam, \App\Models\ExamSubmission $submission)
    {
        \App\Models\ExamAnswer::where('submission_id', $submission->id)->delete();
        $studentName = $submission->student?->name ?? 'শিক্ষার্থী';
        $submission->delete();

        return back()->with('success', "{$studentName}-এর পরীক্ষার খাতা সফলভাবে রিসেট করা হয়েছে। শিক্ষার্থী পুনরায় পরীক্ষা দিতে পারবেন।");
    }

    public function regradeAll(Exam $exam)
    {
        $submissions = $exam->submissions()->with('answers')->get();
        $examQuestions = $exam->examQuestions()->with('question')->get()->keyBy('question_id');

        $regradedCount = 0;
        foreach ($submissions as $submission) {
            $correctCount = 0;
            $wrongCount   = 0;
            $totalEarned  = 0.00;

            foreach ($submission->answers as $ans) {
                $eq = $examQuestions->get($ans->question_id);
                $qMarks = $eq ? (float) $eq->marks : 1.00;
                $q = $eq?->question;

                if ($q && $q->question_type === 'MCQ') {
                    $selected      = strtolower(trim($ans->selected_option_id ?? ''));
                    $correctOption = strtolower(trim($q->correct_option_id ?? ''));
                    $isCorrect     = ($selected !== '' && $selected === $correctOption);
                    $marksAwarded  = $isCorrect ? $qMarks : 0.00;

                    $ans->update([
                        'is_correct'    => $isCorrect ? 1 : 0,
                        'marks_awarded' => $marksAwarded,
                    ]);

                    if ($isCorrect) {
                        $correctCount++;
                        $totalEarned += $marksAwarded;
                    } else {
                        if ($ans->selected_option_id !== null && $ans->selected_option_id !== '') {
                            $wrongCount++;
                        }
                    }
                } else {
                    if ($ans->is_correct) {
                        $correctCount++;
                        $totalEarned += (float) $ans->marks_awarded;
                    } else {
                        if ($ans->teacher_marks !== null) {
                            $totalEarned += (float) $ans->teacher_marks;
                        }
                    }
                }
            }

            $negativeRate     = (float) ($exam->negative_marking ?? 0.00);
            $negativeDeducted = $wrongCount * $negativeRate;
            $finalScore       = max(0, $totalEarned - $negativeDeducted);

            $submission->update([
                'total_score'             => $finalScore,
                'correct_count'           => $correctCount,
                'wrong_count'             => $wrongCount,
                'negative_marks_deducted' => $negativeDeducted,
            ]);

            $regradedCount++;
        }

        return back()->with('success', "এই পরীক্ষার সকল ({$regradedCount}টি) খাতা বর্তমান প্রশ্ন ও সঠিক উত্তর অনুযায়ী সফলভাবে রি-গ্রেড (Re-graded) করা হয়েছে।");
    }

    public function builder(Request $request, Exam $exam)
    {
        $exam->load(['subject', 'examQuestions.question', 'submissions.student']);

        $subjectId     = $request->query('pool_subject_id');
        $batchId       = $request->query('batch_id');
        $semesterId    = $request->query('semester_id');
        $difficulty    = $request->query('difficulty');
        $examType      = $request->query('exam_type');
        $sourceTag     = $request->query('source_tag');
        $search        = $request->query('search');
        $selectedQType = $request->query('question_type');

        $query = Question::with('subject')
            ->whereNotIn('id', $exam->examQuestions->pluck('question_id'));

        // Smart auto-filter based on exam component configuration
        if ($selectedQType) {
            $query->where('question_type', strtoupper($selectedQType));
        } else {
            if ($exam->has_mcq && !$exam->has_written) {
                $query->where('question_type', 'MCQ');
            } elseif ($exam->has_written && !$exam->has_mcq) {
                $query->where('question_type', 'WRITTEN');
            }
        }

        if ($subjectId !== 'all') {
            $effectiveSubjectId = $subjectId ?? $exam->subject_id;
            if ($effectiveSubjectId) {
                $query->where('subject_id', $effectiveSubjectId);
            }
        }

        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        if ($difficulty) {
            $query->where('difficulty', $difficulty);
        }

        if ($examType) {
            $query->where('exam_type', $examType);
        }

        if ($sourceTag) {
            $query->where('source_tag', $sourceTag);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('question_text', 'like', "%{$search}%")
                  ->orWhere('source_tag', 'like', "%{$search}%");
            });
        }

        $availableQuestions = $query->latest()->limit(80)->get();
        $subjects           = Subject::where('is_active', true)->orderBy('name')->get();
        $batches            = Batch::where('status', 'ACTIVE')->orderBy('name')->get();
        $semesters          = Semester::with('course')->orderBy('course_id')->orderBy('sequence_no')->get();
        $sourceTags         = Question::whereNotNull('source_tag')->where('source_tag', '!=', '')->distinct()->pluck('source_tag')->filter()->values();
        $examTypes          = ['CT', 'MID', 'FINAL', 'QUIZ', 'PRACTICE'];

        return view('admin.exams.builder', compact(
            'exam', 'availableQuestions', 'subjects', 'batches', 'semesters', 'sourceTags', 'examTypes',
            'subjectId', 'batchId', 'semesterId', 'difficulty', 'examType', 'sourceTag', 'search', 'selectedQType'
        ));
    }

    public function attachQuestion(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'marks'       => 'nullable|numeric|min:0.5',
        ]);

        ExamQuestion::firstOrCreate(
            ['exam_id' => $exam->id, 'question_id' => $validated['question_id']],
            ['marks' => $validated['marks'] ?? 1.00]
        );

        return back()->with('success', 'প্রশ্নটি পরীক্ষার প্রশ্নপত্রে সফলভাবে যুক্ত করা হয়েছে।');
    }

    public function attachRandomQuestions(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'count'              => 'required|integer|min:1|max:200',
            'marks_per_question' => 'nullable|numeric|min:0.5|max:100',
            'pool_subject_id'    => 'nullable|string',
            'exam_type'          => 'nullable|string',
            'batch_id'           => 'nullable|exists:batches,id',
            'semester_id'        => 'nullable|exists:semesters,id',
            'difficulty'         => 'nullable|string',
            'type'               => 'nullable|string',
            'search'             => 'nullable|string',
        ]);

        $count = (int) $validated['count'];
        $marks = (float) ($validated['marks_per_question'] ?? 1.00);

        $alreadyAttachedIds = $exam->examQuestions()->pluck('question_id');

        $query = Question::whereNotIn('id', $alreadyAttachedIds);

        $subjectId = $request->input('pool_subject_id');
        if ($subjectId && $subjectId !== 'all') {
            $query->where('subject_id', $subjectId);
        } elseif (!$subjectId && $exam->subject_id) {
            $query->where('subject_id', $exam->subject_id);
        }

        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->input('exam_type'));
        }

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->input('batch_id'));
        }

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->input('semester_id'));
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->input('difficulty'));
        }

        if ($request->filled('type')) {
            $query->where('question_type', strtoupper($request->input('type')));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('question_text', 'like', "%{$search}%")
                  ->orWhere('source_tag', 'like', "%{$search}%");
            });
        }

        $randomQuestions = $query->inRandomOrder()->take($count)->get();

        if ($randomQuestions->isEmpty()) {
            return back()->with('error', 'নির্বাচিত শর্ত অনুযায়ী প্রশ্ন ব্যাংকে কোনো নতুন প্রশ্ন পাওয়া যায়নি।');
        }

        $attachedCount = 0;
        foreach ($randomQuestions as $rq) {
            ExamQuestion::firstOrCreate(
                ['exam_id' => $exam->id, 'question_id' => $rq->id],
                ['marks' => $marks]
            );
            $attachedCount++;
        }

        return back()->with('success', "স্বয়ংক্রিয়ভাবে {$attachedCount}টি র‍্যান্ডম প্রশ্ন সফলভাবে প্রশ্নপত্রে যুক্ত করা হয়েছে।");
    }

    public function testExam(Exam $exam)
    {
        $exam->load(['subject', 'examQuestions.question']);

        if ($exam->examQuestions->isEmpty()) {
            return back()->with('error', 'এই পরীক্ষার প্রশ্নপত্রে এখনো কোনো প্রশ্ন যুক্ত করা হয়নি। আগে প্রশ্ন যুক্ত করুন।');
        }

        $isTestMode = true;
        $testSubmitRoute = route('admin.exams.test-exam.submit', $exam);
        $backUrl = route('admin.exams.show', $exam);
        $savedAnswers = collect();

        return view('student.exams.take', compact('exam', 'isTestMode', 'testSubmitRoute', 'backUrl', 'savedAnswers'));
    }

    public function submitTestExam(Request $request, Exam $exam)
    {
        $exam->load('examQuestions.question');
        $answersInput = $request->input('answers', []);

        $mcqQuestions = $exam->examQuestions->filter(fn($eq) => $eq->question?->question_type === 'MCQ');
        $writtenQuestions = $exam->examQuestions->filter(fn($eq) => $eq->question?->question_type === 'WRITTEN');

        $correctCount = 0;
        $wrongCount = 0;
        $unansweredCount = 0;
        $totalEarned = 0.00;

        foreach ($mcqQuestions as $eq) {
            $q = $eq->question;
            $userAns = isset($answersInput[$q->id]) ? strtolower(trim($answersInput[$q->id])) : null;
            $correctAns = strtolower(trim($q->correct_option_id ?? ''));

            if ($userAns === null || $userAns === '') {
                $unansweredCount++;
            } elseif ($userAns === $correctAns) {
                $correctCount++;
                $totalEarned += (float) $eq->marks;
            } else {
                $wrongCount++;
            }
        }

        $negativeRate = (float) ($exam->negative_marking ?? 0.00);
        $negativeDeducted = $wrongCount * $negativeRate;
        $finalScore = max(0, $totalEarned - $negativeDeducted);

        return view('admin.exams.test_result', [
            'exam'              => $exam,
            'totalScore'        => $finalScore,
            'earnedMarks'       => $totalEarned,
            'negativeDeducted'  => $negativeDeducted,
            'correctCount'      => $correctCount,
            'wrongCount'        => $wrongCount,
            'unansweredCount'   => $unansweredCount,
            'totalMcq'          => $mcqQuestions->count(),
            'writtenCount'      => $writtenQuestions->count(),
            'answersInput'      => $answersInput,
            'backUrl'           => route('admin.exams.show', $exam),
        ]);
    }

    public function detachQuestion(Exam $exam, ExamQuestion $examQuestion)
    {
        $examQuestion->delete();
        return back()->with('success', 'প্রশ্নটি প্রশ্নপত্র থেকে অপসারণ করা হয়েছে।');
    }

    /**
     * View all exam appeals (Admin)
     */
    public function allAppeals(Request $request)
    {
        $status = $request->query('status', 'PENDING');
        $query = ExamAppeal::with(['exam.subject', 'student', 'reviewer'])->latest();

        if ($status !== 'ALL') {
            $query->where('status', $status);
        }

        $appeals = $query->paginate(20);
        return view('admin.exams.appeals', compact('appeals', 'status'));
    }

    /**
     * Approve re-exam appeal
     */
    public function approveAppeal(Request $request, ExamAppeal $appeal)
    {
        // 1. Reset previous submission & answers if any
        if ($appeal->submission_id) {
            \App\Models\ExamAnswer::where('submission_id', $appeal->submission_id)->delete();
            \App\Models\ExamSubmission::where('id', $appeal->submission_id)->delete();
        } else {
            $existing = \App\Models\ExamSubmission::where('exam_id', $appeal->exam_id)
                ->where('student_id', $appeal->student_id)
                ->first();
            if ($existing) {
                \App\Models\ExamAnswer::where('submission_id', $existing->id)->delete();
                $existing->delete();
            }
        }

        // 2. Mark appeal as APPROVED
        $appeal->update([
            'status'        => 'APPROVED',
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
            'admin_remarks' => $request->input('remarks'),
        ]);

        $studentName = $appeal->student?->name ?? 'শিক্ষার্থী';
        return back()->with('success', "{$studentName}-এর পুনরায় পরীক্ষার আপিল সফলভাবে অনুমোদন করা হয়েছে এবং খাতা রিসেট করা হয়েছে। শিক্ষার্থী এখন নতুন করে পরীক্ষা দিতে পারবেন।");
    }

    /**
     * Reject re-exam appeal
     */
    public function rejectAppeal(Request $request, ExamAppeal $appeal)
    {
        $appeal->update([
            'status'        => 'REJECTED',
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
            'admin_remarks' => $request->input('remarks'),
        ]);

        $studentName = $appeal->student?->name ?? 'শিক্ষার্থী';
        return back()->with('success', "{$studentName}-এর পুনরায় পরীক্ষার আপিল বাতিল করা হয়েছে।");
    }
}
