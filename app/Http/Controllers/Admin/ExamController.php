<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Result;
use App\Models\Question;
use App\Models\ExamQuestion;
use App\Models\ExamAppeal;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $exams    = Exam::with(['subject', 'attendees.student'])->latest()->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        return view('admin.exams.index', compact('exams', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id'       => 'required|exists:subjects,id',
            'title'            => 'required|string|max:200',
            'type'             => 'required|in:MIDTERM,FINAL,RETAKE,QUIZ,PRACTICAL',
            'exam_date'        => 'required|date',
            'end_date'         => 'nullable|date|after_or_equal:exam_date',
            'start_time'       => 'nullable|string',
            'end_time'         => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:5|max:360',
            'full_marks'       => 'required|integer|min:1',
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
            'full_marks'       => $validated['full_marks'],
            'pass_marks'       => $validated['pass_marks'],
            'negative_marking' => $validated['negative_marking'] ?? 0.00,
            'status'           => 'SCHEDULED',
        ]);

        return back()->with('success', 'পরীক্ষা সফলভাবে শিডিউল করা হয়েছে।');
    }

    public function show(Exam $exam)
    {
        $exam->load(['subject', 'attendees.student', 'results.student', 'submissions.student', 'examQuestions.question', 'appeals.student']);
        return view('admin.exams.show', compact('exam'));
    }

    public function update(Request $request, Exam $exam)
    {
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

        $subjectId  = $request->query('pool_subject_id');
        $difficulty = $request->query('difficulty');
        $examType   = $request->query('exam_type');
        $sourceTag  = $request->query('source_tag');
        $search     = $request->query('search');

        $query = Question::with('subject')
            ->whereNotIn('id', $exam->examQuestions->pluck('question_id'));

        if ($subjectId !== 'all') {
            $effectiveSubjectId = $subjectId ?? $exam->subject_id;
            if ($effectiveSubjectId) {
                $query->where('subject_id', $effectiveSubjectId);
            }
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
        $sourceTags         = Question::whereNotNull('source_tag')->where('source_tag', '!=', '')->distinct()->pluck('source_tag')->filter()->values();
        $examTypes          = ['CT', 'MID', 'FINAL', 'QUIZ', 'PRACTICE'];

        return view('admin.exams.builder', compact(
            'exam', 'availableQuestions', 'subjects', 'sourceTags', 'examTypes',
            'subjectId', 'difficulty', 'examType', 'sourceTag', 'search'
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
