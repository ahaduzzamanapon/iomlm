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
use Illuminate\Support\Facades\Storage;

class ExamController extends Controller
{
    private function student(): ?Student
    {
        return Student::where('user_id', auth()->id())->first();
    }

    public function index()
    {
        $student = $this->student();

        $batchIds = Enrollment::where('student_id', $student?->id)
            ->where('status', 'ACTIVE')
            ->pluck('batch_id');

        $exams = Exam::with(['subject', 'examQuestions', 'submissions' => function ($q) use ($student) {
                $q->where('student_id', $student?->id);
            }])
            ->where('status', '!=', 'CANCELLED')
            ->latest()
            ->get();

        return view('student.exams.index', compact('exams', 'student'));
    }

    /**
     * Show full question paper — all questions on one scrollable page.
     * Timer displayed for reference only; no forced auto-submit.
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

        // Load any already-saved answers
        $savedAnswers = ExamAnswer::where('submission_id', $submission->id)
            ->get()
            ->keyBy('question_id');

        return view('student.exams.take', compact('exam', 'submission', 'savedAnswers'));
    }

    /**
     * Submit full paper: MCQ auto-graded; Written images stored.
     */
    public function submit(Request $request, Exam $exam)
    {
        $student = $this->student();

        $submission = ExamSubmission::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $answersInput   = $request->input('answers', []);
        $tabSwitchCount = (int) $request->input('tab_switch_count', 0);
        $isViolation    = $request->boolean('is_violation', false);

        $exam->load('examQuestions.question');

        $correctCount = 0;
        $wrongCount   = 0;
        $totalEarned  = 0.00;

        foreach ($exam->examQuestions as $eq) {
            $q = $eq->question;

            if ($q->question_type === 'WRITTEN') {
                // Handle image upload for written questions
                $imagePath = null;
                $fileKey   = 'answer_image_' . $q->id;

                if ($request->hasFile($fileKey)) {
                    $imagePath = $request->file($fileKey)
                        ->store('exam_answers/' . $exam->id, 'public');
                }

                ExamAnswer::updateOrCreate(
                    ['submission_id' => $submission->id, 'question_id' => $q->id],
                    [
                        'selected_option_id' => null,
                        'is_correct'         => 0,   // Written — teacher grades manually
                        'marks_awarded'      => 0.00,
                        'answer_image_path'  => $imagePath,
                    ]
                );

            } else {
                // MCQ — auto-grade
                $selectedOpt = strtolower($answersInput[$q->id] ?? '');
                $isCorrect   = false;
                $marksAwarded = 0.00;

                if ($selectedOpt !== '') {
                    if ($selectedOpt === strtolower($q->correct_option_id)) {
                        $isCorrect    = true;
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
        }

        // Negative marking only applies to MCQ
        $negativeRate     = (float) ($exam->negative_marking ?? 0.00);
        $negativeDeducted = $wrongCount * $negativeRate;
        $finalScore       = max(0, $totalEarned - $negativeDeducted);

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
            ->with('success', 'প্রশ্নপত্র সফলভাবে জমা দেওয়া হয়েছে!');
    }

    /**
     * View Exam Result
     */
    public function result(Exam $exam, ExamSubmission $submission)
    {
        $exam->load(['subject', 'examQuestions.question']);
        $submission->load('answers');

        $answersMap = $submission->answers->keyBy('question_id');

        return view('student.exams.result', compact('exam', 'submission', 'answersMap'));
    }
}
