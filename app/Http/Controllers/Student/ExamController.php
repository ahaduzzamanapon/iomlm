<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\ExamAnswer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    private function student(): ?Student
    {
        return Student::where('email', auth()->user()->email)->first();
    }

    public function index()
    {
        $student = $this->student();

        $batchIds = Enrollment::where('student_id', $student?->id)
            ->where('status', 'ACTIVE')
            ->pluck('batch_id');

        // Get exams for courses of these batches
        $exams = Exam::with(['subject', 'examQuestions', 'submissions' => function ($q) use ($student) {
                $q->where('student_id', $student?->id);
            }])
            ->where('status', '!=', 'CANCELLED')
            ->latest()
            ->get();

        return view('student.exams.index', compact('exams', 'student'));
    }

    /**
     * Take Online Exam with Anti-Cheating Protection & Timer
     */
    public function take(Exam $exam)
    {
        $student = $this->student();

        if ($student) {
            $guard = \App\Services\EnforcementService::canTakeExam($student);
            if (!$guard['allowed']) {
                return redirect()->route('student.exams.index')->with('info', $guard['reason']);
            }
        }

        // Check if already submitted
        $existing = ExamSubmission::where('exam_id', $exam->id)
            ->where('student_id', $student?->id)
            ->first();

        if ($existing && $existing->status !== 'IN_PROGRESS') {
            return redirect()->route('student.exams.result', [$exam, $existing])
                ->with('info', 'You have already submitted this exam.');
        }

        $exam->load(['subject', 'examQuestions.question']);

        if ($exam->examQuestions->isEmpty()) {
            return back()->with('error', 'This exam question paper has not been configured yet.');
        }

        // Create or get submission
        $submission = ExamSubmission::firstOrCreate(
            ['exam_id' => $exam->id, 'student_id' => $student->id],
            ['status' => 'IN_PROGRESS', 'started_at' => now()]
        );

        return view('student.exams.take', compact('exam', 'submission'));
    }

    /**
     * Submit Exam Answers with Auto-Grading & Negative Marking
     */
    public function submit(Request $request, Exam $exam)
    {
        $student = $this->student();

        $submission = ExamSubmission::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $answersInput    = $request->input('answers', []);
        $tabSwitchCount  = (int) $request->input('tab_switch_count', 0);
        $isViolation     = $request->boolean('is_violation', false);

        $exam->load('examQuestions.question');

        $correctCount = 0;
        $wrongCount   = 0;
        $totalEarned  = 0.00;

        foreach ($exam->examQuestions as $eq) {
            $q = $eq->question;
            $selectedOpt = strtolower($answersInput[$q->id] ?? '');

            $isCorrect = false;
            $marksAwarded = 0.00;

            if ($selectedOpt !== '') {
                if ($selectedOpt === strtolower($q->correct_option_id)) {
                    $isCorrect = true;
                    $correctCount++;
                    $marksAwarded = $eq->marks;
                } else {
                    $wrongCount++;
                }
            }

            ExamAnswer::updateOrCreate(
                ['submission_id' => $submission->id, 'question_id' => $q->id],
                [
                    'selected_option_id' => $selectedOpt,
                    'is_correct'         => $isCorrect,
                    'marks_awarded'      => $marksAwarded,
                ]
            );

            $totalEarned += $marksAwarded;
        }

        // Calculate Negative Marks
        $negativeRate = (float) ($exam->negative_marking ?? 0.00);
        $negativeDeducted = $wrongCount * $negativeRate;
        $finalScore = max(0, $totalEarned - $negativeDeducted);

        $submission->update([
            'total_score'             => $finalScore,
            'correct_count'           => $correctCount,
            'wrong_count'             => $wrongCount,
            'negative_marks_deducted' => $negativeDeducted,
            'tab_switch_count'        => $tabSwitchCount,
            'status'                  => $isViolation ? 'AUTO_SUBMITTED_VIOLATION' : 'SUBMITTED',
            'submitted_at'            => now(),
        ]);

        return redirect()->route('student.exams.result', [$exam, $submission])
            ->with('success', 'Exam submitted successfully!');
    }

    /**
     * View Exam Result & Explanation Feedback
     */
    public function result(Exam $exam, ExamSubmission $submission)
    {
        $exam->load(['subject', 'examQuestions.question']);
        $submission->load('answers');

        $answersMap = $submission->answers->keyBy('question_id');

        return view('student.exams.result', compact('exam', 'submission', 'answersMap'));
    }
}
