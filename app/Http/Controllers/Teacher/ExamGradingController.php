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
            'teacher_marks'    => 'required|numeric|min:0',
            'teacher_feedback' => 'nullable|string|max:1000',
        ]);

        $teacher = $this->teacher();

        // Ensure the marks don't exceed the exam question's marks
        $examQuestion = \App\Models\ExamQuestion::where('exam_id', $answer->submission->exam_id)
            ->where('question_id', $answer->question_id)
            ->first();

        $maxMarks = $examQuestion?->marks ?? 0;
        $marks = min((float) $validated['teacher_marks'], (float) $maxMarks);

        $answer->update([
            'teacher_marks'    => $marks,
            'teacher_feedback' => $validated['teacher_feedback'] ?? null,
            'marks_awarded'    => $marks,  // sync for total score calculation
            'graded_by'        => $teacher?->user_id ?? auth()->id(),
        ]);

        // Recalculate submission scores
        $submission = $answer->submission;
        $submission->load(['answers.question', 'exam']);
        
        $writtenScore = 0.0;
        $mcqEarned = 0.0;
        foreach ($submission->answers as $ans) {
            if ($ans->question?->question_type === 'WRITTEN') {
                $writtenScore += (float) ($ans->marks_awarded ?? 0);
            } else {
                $mcqEarned += (float) ($ans->marks_awarded ?? 0);
            }
        }
        $negativeDeducted = (float) ($submission->negative_marks_deducted ?? 0);
        $mcqScore = max(0, $mcqEarned - $negativeDeducted);
        $tamrinScore = (float) ($submission->tamrin_score ?? 0);
        $vivaScore = (float) ($submission->viva_score ?? 0);
        $totalScore = $mcqScore + $writtenScore + $tamrinScore + $vivaScore;

        $submission->update([
            'written_score' => $writtenScore,
            'mcq_score'     => $mcqScore,
            'total_score'   => $totalScore,
        ]);

        return back()->with('success', 'নম্বর ও শিক্ষক মূল্যায়ন সফলভাবে সংরক্ষিত হয়েছে।');
    }
}
