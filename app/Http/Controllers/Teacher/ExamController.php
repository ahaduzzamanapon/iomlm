<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\SubjectTeacherAssignment;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Question;
use App\Models\ExamQuestion;
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
        $exam->load(['subject', 'examQuestions.question', 'submissions.student']);

        $subjectId  = $request->query('pool_subject_id');
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

        $availableQuestions = $query->latest()->limit(60)->get();
        $subjects           = Subject::where('is_active', true)->orderBy('name')->get();
        $sourceTags         = Question::whereNotNull('source_tag')->where('source_tag', '!=', '')->distinct()->pluck('source_tag')->filter()->values();
        $examTypes          = ['CT', 'MID', 'FINAL', 'QUIZ', 'PRACTICE'];

        return view('teacher.exams.builder', compact(
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

        return back()->with('success', 'Question attached to exam paper.');
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
}
