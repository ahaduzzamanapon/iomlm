<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\SubjectTeacherAssignment;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Question;
use App\Models\ExamQuestion;
use App\Models\ExamAppeal;
use App\Models\Batch;
use App\Models\Semester;
use Illuminate\Http\Request;


class ExamController extends Controller
{
    private function teacher(): ?Teacher
    {
        return Teacher::where('user_id', auth()->id())->first();
    }

    public function index()
    {
        $teacher = $this->teacher();
        $assignedSubjectIds = SubjectTeacherAssignment::where('teacher_id', $teacher?->id)->pluck('subject_id');

        if ($assignedSubjectIds->isEmpty()) {
            $assignedSubjectIds = Subject::where('is_active', true)->pluck('id');
        }

        $exams = Exam::with(['subject', 'examQuestions.question', 'submissions'])
            ->whereIn('subject_id', $assignedSubjectIds)
            ->latest()
            ->get();

        $subjects = Subject::whereIn('id', $assignedSubjectIds)->orderBy('name')->get();

        return view('teacher.exams.index', compact('exams', 'subjects'));
    }

    public function store(Request $request)
    {
        $teacher = $this->teacher();
        $assignedSubjectIds = SubjectTeacherAssignment::where('teacher_id', $teacher?->id)->pluck('subject_id');

        if ($assignedSubjectIds->isEmpty()) {
            $assignedSubjectIds = Subject::where('is_active', true)->pluck('id');
        }

        $validated = $request->validate([
            'subject_id'       => 'required|in:' . $assignedSubjectIds->implode(','),
            'title'            => 'required|string|max:200',
            'type'             => 'required|in:QUIZ,MIDTERM,FINAL,RETAKE,PRACTICAL,CLASS_TEST,HALF_TERM',
            'exam_date'        => 'required|date',
            'end_date'         => 'nullable|date|after_or_equal:exam_date',
            'start_time'       => 'nullable|string',
            'end_time'         => 'nullable|string',
            'duration_minutes' => 'required|integer|min:5|max:300',
            'full_marks'       => 'required|integer|min:1',
            'pass_marks'       => 'required|integer|min:1',
            'negative_marking' => 'nullable|numeric|min:0|max:5',
            'is_anti_cheating' => 'nullable|boolean',
        ]);

        $mappedType = match($validated['type']) {
            'CLASS_TEST' => 'QUIZ',
            'HALF_TERM'  => 'MIDTERM',
            default      => $validated['type'],
        };

        $examDate  = $validated['exam_date'];
        $endDate   = $validated['end_date'] ?? $examDate;
        $startTime = $validated['start_time'] ?? null;
        $endTime   = $validated['end_time'] ?? null;

        $startDatetime = $startTime ? "{$examDate} {$startTime}:00" : "{$examDate} 00:00:00";
        $endDatetime   = $endTime ? "{$endDate} {$endTime}:00" : "{$endDate} 23:59:59";

        Exam::create([
            'subject_id'       => $validated['subject_id'],
            'title'            => $validated['title'],
            'type'             => $mappedType,
            'exam_date'        => $examDate,
            'end_date'         => $endDate,
            'start_time'       => $startTime,
            'end_time'         => $endTime,
            'start_datetime'   => $startDatetime,
            'end_datetime'     => $endDatetime,
            'duration_minutes' => $validated['duration_minutes'],
            'full_marks'       => $validated['full_marks'],
            'pass_marks'       => $validated['pass_marks'],
            'negative_marking' => $validated['negative_marking'] ?? 0.00,
            'is_anti_cheating' => $request->boolean('is_anti_cheating', true),
            'status'           => 'SCHEDULED',
        ]);

        return back()->with('success', "{$validated['type']} exam created successfully! Now attach questions from Question Bank.");
    }

    public function update(Request $request, Exam $exam)
    {
        $teacher = $this->teacher();
        $assignedSubjectIds = SubjectTeacherAssignment::where('teacher_id', $teacher?->id)->pluck('subject_id');

        if ($assignedSubjectIds->isEmpty()) {
            $assignedSubjectIds = Subject::where('is_active', true)->pluck('id');
        }

        $validated = $request->validate([
            'subject_id'       => 'required|in:' . $assignedSubjectIds->implode(','),
            'title'            => 'required|string|max:200',
            'type'             => 'required|in:QUIZ,MIDTERM,FINAL,RETAKE,PRACTICAL,CLASS_TEST,HALF_TERM',
            'exam_date'        => 'required|date',
            'end_date'         => 'nullable|date|after_or_equal:exam_date',
            'start_time'       => 'nullable|string',
            'end_time'         => 'nullable|string',
            'duration_minutes' => 'required|integer|min:5|max:300',
            'full_marks'       => 'required|integer|min:1',
            'pass_marks'       => 'required|integer|min:1',
            'negative_marking' => 'nullable|numeric|min:0|max:5',
            'is_anti_cheating' => 'nullable|boolean',
            'status'           => 'nullable|in:SCHEDULED,RUNNING,COMPLETED,CANCELLED',
        ]);

        $mappedType = match($validated['type']) {
            'CLASS_TEST' => 'QUIZ',
            'HALF_TERM'  => 'MIDTERM',
            default      => $validated['type'],
        };

        $examDate  = $validated['exam_date'];
        $endDate   = $validated['end_date'] ?? $examDate;
        $startTime = $validated['start_time'] ?? null;
        $endTime   = $validated['end_time'] ?? null;

        $startDatetime = $startTime ? "{$examDate} {$startTime}:00" : "{$examDate} 00:00:00";
        $endDatetime   = $endTime ? "{$endDate} {$endTime}:00" : "{$endDate} 23:59:59";

        $exam->update([
            'subject_id'       => $validated['subject_id'],
            'title'            => $validated['title'],
            'type'             => $mappedType,
            'exam_date'        => $examDate,
            'end_date'         => $endDate,
            'start_time'       => $startTime,
            'end_time'         => $endTime,
            'start_datetime'   => $startDatetime,
            'end_datetime'     => $endDatetime,
            'duration_minutes' => $validated['duration_minutes'],
            'full_marks'       => $validated['full_marks'],
            'pass_marks'       => $validated['pass_marks'],
            'negative_marking' => $validated['negative_marking'] ?? 0.00,
            'is_anti_cheating' => $request->boolean('is_anti_cheating', true),
            'status'           => $validated['status'] ?? $exam->status,
        ]);

        return back()->with('success', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return back()->with('success', 'Exam removed.');
    }

    public function show(Request $request, Exam $exam)
    {
        $exam->load(['subject', 'examQuestions.question', 'submissions.student', 'appeals.student']);

        $subjectId  = $request->query('pool_subject_id');
        $batchId    = $request->query('batch_id');
        $semesterId = $request->query('semester_id');
        $difficulty = $request->query('difficulty');
        $examType   = $request->query('exam_type');
        $sourceTag  = $request->query('source_tag');
        $search     = $request->query('search');

        $query = Question::with('subject')
            ->whereNotIn('id', $exam->examQuestions->pluck('question_id'));

        // If pool_subject_id is specifically chosen or defaults to exam's subject if not set
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
        $semesters          = Semester::with('course')->orderBy('sequence_no')->get();
        $sourceTags         = Question::whereNotNull('source_tag')->where('source_tag', '!=', '')->distinct()->pluck('source_tag')->filter()->values();
        $examTypes          = ['CT', 'MID', 'FINAL', 'QUIZ', 'PRACTICE'];

        return view('teacher.exams.builder', compact(
            'exam', 'availableQuestions', 'subjects', 'batches', 'semesters', 'sourceTags', 'examTypes',
            'subjectId', 'batchId', 'semesterId', 'difficulty', 'examType', 'sourceTag', 'search'
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

        return back()->with('success', 'Question attached to exam paper.');
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
        $testSubmitRoute = route('teacher.exams.test-exam.submit', $exam);
        $backUrl = route('teacher.exams.show', $exam);
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
            'backUrl'           => route('teacher.exams.show', $exam),
        ]);
    }

    public function detachQuestion(Exam $exam, ExamQuestion $examQuestion)
    {
        $examQuestion->delete();
        return back()->with('success', 'Question removed from exam paper.');
    }

    public function resetSubmission(Exam $exam, \App\Models\ExamSubmission $submission)
    {
        \App\Models\ExamAnswer::where('submission_id', $submission->id)->delete();
        $studentName = $submission->student?->name ?? 'শিক্ষার্থী';
        $submission->delete();

        return back()->with('success', "{$studentName}-এর পরীক্ষার খাতা সফলভাবে রিসেট করা হয়েছে। শিক্ষার্থী এখন পুনরায় পরীক্ষা দিতে পারবেন।");
    }

    /**
     * View appeals for teacher's exams
     */
    public function allAppeals(Request $request)
    {
        $teacher = $this->teacher();
        $assignedSubjectIds = SubjectTeacherAssignment::where('teacher_id', $teacher?->id)->pluck('subject_id');
        if ($assignedSubjectIds->isEmpty()) {
            $assignedSubjectIds = Subject::where('is_active', true)->pluck('id');
        }

        $status = $request->query('status', 'PENDING');
        $query = ExamAppeal::whereHas('exam', function ($q) use ($assignedSubjectIds) {
                $q->whereIn('subject_id', $assignedSubjectIds);
            })
            ->with(['exam.subject', 'student', 'reviewer'])
            ->latest();

        if ($status !== 'ALL') {
            $query->where('status', $status);
        }

        $appeals = $query->paginate(20);
        return view('teacher.exams.appeals', compact('appeals', 'status'));
    }

    /**
     * Approve re-exam appeal
     */
    public function approveAppeal(Request $request, ExamAppeal $appeal)
    {
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

        $appeal->update([
            'status'        => 'APPROVED',
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
            'admin_remarks' => $request->input('remarks'),
        ]);

        $studentName = $appeal->student?->name ?? 'শিক্ষার্থী';
        return back()->with('success', "{$studentName}-এর পুনরায় পরীক্ষার আপিল সফলভাবে অনুমোদন করা হয়েছে এবং পূর্বের খাতা রিসেট করা হয়েছে।");
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
