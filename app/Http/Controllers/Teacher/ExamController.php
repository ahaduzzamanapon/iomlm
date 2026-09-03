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
            'start_datetime'   => 'nullable|date',
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

        Exam::create([
            'subject_id'       => $validated['subject_id'],
            'title'            => $validated['title'],
            'type'             => $mappedType,
            'exam_date'        => $validated['exam_date'],
            'start_datetime'   => $validated['start_datetime'] ?? null,
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
            'start_datetime'   => 'nullable|date',
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

        $exam->update([
            'subject_id'       => $validated['subject_id'],
            'title'            => $validated['title'],
            'type'             => $mappedType,
            'exam_date'        => $validated['exam_date'],
            'start_datetime'   => $validated['start_datetime'] ?? null,
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

    public function show(Exam $exam)
    {
        $exam->load(['subject', 'examQuestions.question', 'submissions.student']);
        
        // Available questions for this subject
        $availableQuestions = Question::where('subject_id', $exam->subject_id)
            ->whereNotIn('id', $exam->examQuestions->pluck('question_id'))
            ->get();

        return view('teacher.exams.builder', compact('exam', 'availableQuestions'));
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
}
