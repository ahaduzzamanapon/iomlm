<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamSubmission;
use App\Models\Teacher;
use Illuminate\Http\Request;

class ExamGradingController extends Controller
{
    private function teacher(): ?Teacher
    {
        return Teacher::where('user_id', auth()->id())->first();
    }

    /**
     * Show all written answers for an exam that need grading
     */
    public function index(Exam $exam)
    {
        $exam->load(['subject', 'examQuestions.question', 'submissions.student']);

        // Get all submissions with written answers
        $submissions = ExamSubmission::where('exam_id', $exam->id)
            ->with(['student', 'answers.question'])
            ->whereIn('status', ['SUBMITTED', 'AUTO_SUBMITTED_VIOLATION'])
            ->get();

        return view('teacher.exams.grade', compact('exam', 'submissions'));
    }

    /**
     * Save teacher-graded marks for a written answer
     */
    public function grade(Request $request, ExamAnswer $answer)
    {
        $validated = $request->validate([
            'teacher_marks' => 'required|numeric|min:0',
        ]);

        $teacher = $this->teacher();

        // Ensure the marks don't exceed the exam question's marks
        $examQuestion = \App\Models\ExamQuestion::where('exam_id', $answer->submission->exam_id)
            ->where('question_id', $answer->question_id)
            ->first();

        $maxMarks = $examQuestion?->marks ?? 0;
        $marks = min((float) $validated['teacher_marks'], (float) $maxMarks);

        $answer->update([
            'teacher_marks' => $marks,
            'marks_awarded'  => $marks,  // sync for total score calculation
            'graded_by'      => $teacher?->user_id ?? auth()->id(),
        ]);

        // Recalculate submission total_score
        $submission = $answer->submission;
        $submission->load('answers');
        $totalScore = $submission->answers->sum('marks_awarded');

        $submission->update(['total_score' => $totalScore]);

        return back()->with('success', 'নম্বর সফলভাবে সেভ হয়েছে।');
    }
}
