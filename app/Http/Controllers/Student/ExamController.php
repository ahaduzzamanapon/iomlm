<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\ExamAnswer;
use App\Models\ExamAppeal;
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

        $exams = Exam::with([
                'subject',
                'examQuestions',
                'submissions' => function ($q) use ($student) {
                    $q->where('student_id', $student?->id);
                },
                'appeals' => function ($q) use ($student) {
                    $q->where('student_id', $student?->id);
                }
            ])
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

        // Check if student has an approved appeal for re-exam
        $approvedAppeal = ExamAppeal::where('exam_id', $exam->id)
            ->where('student_id', $student?->id)
            ->where('status', 'APPROVED')
            ->latest()
            ->first();

        // Check exam schedule window (bypassed if student has an approved re-exam appeal)
        if (!$approvedAppeal) {
            if ($exam->isUpcoming()) {
                $startFormatted = $exam->getEffectiveStartDatetime()->format('d M Y, h:i A');
                return redirect()->route('student.exams.index')
                    ->with('error', "এই পরীক্ষাটি এখনো শুরু হয়নি। পরীক্ষা শুরু হবে: {$startFormatted}। নির্ধারিত সময়ের পূর্বে পরীক্ষা শুরু করা সম্ভব নয়।");
            }

            if ($exam->isExpired()) {
                $existing = ExamSubmission::where('exam_id', $exam->id)
                    ->where('student_id', $student?->id)
                    ->first();

                if (!$existing || $existing->status !== 'IN_PROGRESS') {
                    $endFormatted = $exam->getEffectiveEndDatetime()->format('d M Y, h:i A');
                    return redirect()->route('student.exams.index')
                        ->with('error', "এই পরীক্ষার নির্ধারিত সময় অতিবাহিত হয়েছে ({$endFormatted})। নতুন করে পরীক্ষা শুরু করা যাবে না।");
                }
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

        // Generate per-student shuffled questions pool up to full marks if not already assigned
        if (empty($submission->assigned_question_ids)) {
            $pool = $exam->examQuestions->shuffle();
            $targetMarks = (float) $exam->full_marks;
            $poolTotalMarks = (float) $pool->sum('marks');

            if ($targetMarks <= 0 || $poolTotalMarks <= $targetMarks) {
                // Pool total marks is <= target marks, assign all questions in shuffled order
                $assignedIds = $pool->pluck('question_id')->values()->all();
            } else {
                $selectedIds = [];
                $currentMarks = 0.0;

                // Pass 1: Greedily pick shuffled questions that fit into targetMarks
                foreach ($pool as $eq) {
                    $qMarks = (float) ($eq->marks > 0 ? $eq->marks : 1.0);
                    if (($currentMarks + $qMarks) <= ($targetMarks + 0.0001)) {
                        $selectedIds[] = $eq->question_id;
                        $currentMarks += $qMarks;
                        if (abs($currentMarks - $targetMarks) < 0.0001) {
                            break;
                        }
                    }
                }

                // Pass 2: If targetMarks not reached, pick next available questions until full marks
                if ($currentMarks < $targetMarks && count($selectedIds) < $pool->count()) {
                    foreach ($pool as $eq) {
                        if (!in_array($eq->question_id, $selectedIds)) {
                            $selectedIds[] = $eq->question_id;
                            $currentMarks += (float) ($eq->marks > 0 ? $eq->marks : 1.0);
                            if ($currentMarks >= $targetMarks) {
                                break;
                            }
                        }
                    }
                }

                $assignedIds = !empty($selectedIds) ? $selectedIds : $pool->pluck('question_id')->values()->all();
            }

            $submission->assigned_question_ids = $assignedIds;
            $submission->save();
        }

        // Apply student's assigned question subset in persistent order
        $assignedIds = $submission->assigned_question_ids ?? [];
        if (!empty($assignedIds)) {
            $eqMap = $exam->examQuestions->keyBy('question_id');
            $assignedExamQuestions = collect($assignedIds)
                ->map(fn($qid) => $eqMap->get($qid))
                ->filter()
                ->values();
            $exam->setRelation('examQuestions', $assignedExamQuestions);
        }

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

        // Grade only the student's assigned questions
        $assignedIds = $submission->assigned_question_ids;
        if (!empty($assignedIds)) {
            $eqMap = $exam->examQuestions->keyBy('question_id');
            $questionsToGrade = collect($assignedIds)
                ->map(fn($qid) => $eqMap->get($qid))
                ->filter()
                ->values();
        } else {
            $questionsToGrade = $exam->examQuestions;
        }

        $correctCount = 0;
        $wrongCount   = 0;
        $totalEarned  = 0.00;

        foreach ($questionsToGrade as $eq) {
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
            'mcq_score'               => $finalScore,
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
     * Submit an appeal for Re-exam
     */
    public function appeal(Request $request, Exam $exam)
    {
        $student = $this->student();

        $validated = $request->validate([
            'reason'        => 'required|string|min:5|max:1000',
            'submission_id' => 'nullable|integer',
        ]);

        // Check if there is already a pending appeal
        $pending = ExamAppeal::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'PENDING')
            ->first();

        if ($pending) {
            return back()->with('info', 'আপনার একটি আপিল ইতিমধ্যে পর্যালোচনায় রয়েছে। অনুগ্রহ করে অপেক্ষা করুন।');
        }

        ExamAppeal::create([
            'exam_id'       => $exam->id,
            'student_id'    => $student->id,
            'submission_id' => $validated['submission_id'] ?? null,
            'reason'        => $validated['reason'],
            'status'        => 'PENDING',
        ]);

        return back()->with('success', 'পুনরায় পরীক্ষার জন্য আপনার আবেদনটি সফলভাবে জমা হয়েছে। এডমিন বা শিক্ষক এটি পর্যালোচনা করে অনুমোদন দিলে আপনি পুনরায় পরীক্ষা দিতে পারবেন।');
    }

    /**
     * View Exam Result
     */
    public function result(Exam $exam, ExamSubmission $submission)
    {
        $exam->load(['subject', 'examQuestions.question']);
        $submission->load('answers');

        // Filter question paper to the student's assigned questions in preserved order
        if (!empty($submission->assigned_question_ids)) {
            $eqMap = $exam->examQuestions->keyBy('question_id');
            $assignedQuestions = collect($submission->assigned_question_ids)
                ->map(fn($qid) => $eqMap->get($qid))
                ->filter()
                ->values();
            $exam->setRelation('examQuestions', $assignedQuestions);
        }

        $answersMap = $submission->answers->keyBy('question_id');

        $latestAppeal = ExamAppeal::where('exam_id', $exam->id)
            ->where('student_id', $submission->student_id)
            ->latest()
            ->first();

        return view('student.exams.result', compact('exam', 'submission', 'answersMap', 'latestAppeal'));
    }
}
