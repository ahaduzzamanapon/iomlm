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

        // If approved appeal has unpaid fee, block until fee is paid
        if ($approvedAppeal && $approvedAppeal->requiresPayment()) {
            return redirect()->route('student.exams.index')
                ->with('error', "পুনরায় পরীক্ষা শুরু করার পূর্বে নির্ধারিত আপিল ফি (৳" . number_format($approvedAppeal->fee_amount, 2) . ") পরিশোধ করা আবশ্যক। অনুগ্রহ করে ফি পরিশোধ করুন।");
        }

        // Check exam schedule window (bypassed if student has an approved and paid re-exam appeal)
        if (!$approvedAppeal || $approvedAppeal->requiresPayment()) {
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

        // Generate per-student shuffled questions pool up to target marks if not already assigned
        if (empty($submission->assigned_question_ids)) {
            $hasMcqSplit = $exam->has_mcq && (float) $exam->mcq_marks > 0;
            $hasWrittenSplit = $exam->has_written && (float) $exam->written_marks > 0;

            if ($hasMcqSplit || $hasWrittenSplit) {
                $selectedIds = [];

                // 1. Handle MCQ pool (e.g. 50 questions in pool, target is 20 MCQ marks)
                if ($hasMcqSplit) {
                    $targetMcq = (float) $exam->mcq_marks;
                    $mcqPool = $exam->examQuestions->filter(function ($eq) {
                        return $eq->question && $eq->question->question_type === 'MCQ';
                    })->shuffle();

                    $currMcq = 0.0;
                    $mcqIds = [];
                    foreach ($mcqPool as $eq) {
                        $qMarks = (float) ($eq->marks > 0 ? $eq->marks : 1.0);
                        if (($currMcq + $qMarks) <= ($targetMcq + 0.0001)) {
                            $mcqIds[] = $eq->question_id;
                            $currMcq += $qMarks;
                            if (abs($currMcq - $targetMcq) < 0.0001) break;
                        }
                    }
                    if ($currMcq < $targetMcq && count($mcqIds) < $mcqPool->count()) {
                        foreach ($mcqPool as $eq) {
                            if (!in_array($eq->question_id, $mcqIds)) {
                                $mcqIds[] = $eq->question_id;
                                $currMcq += (float) ($eq->marks > 0 ? $eq->marks : 1.0);
                                if ($currMcq >= $targetMcq) break;
                            }
                        }
                    }
                    $selectedIds = array_merge($selectedIds, !empty($mcqIds) ? $mcqIds : $mcqPool->pluck('question_id')->all());
                }

                // 2. Handle Written pool
                if ($hasWrittenSplit) {
                    $targetWritten = (float) $exam->written_marks;
                    $writtenPool = $exam->examQuestions->filter(function ($eq) {
                        return $eq->question && $eq->question->question_type !== 'MCQ';
                    })->shuffle();

                    $currWritten = 0.0;
                    $writtenIds = [];
                    foreach ($writtenPool as $eq) {
                        $qMarks = (float) ($eq->marks > 0 ? $eq->marks : 5.0);
                        if (($currWritten + $qMarks) <= ($targetWritten + 0.0001)) {
                            $writtenIds[] = $eq->question_id;
                            $currWritten += $qMarks;
                            if (abs($currWritten - $targetWritten) < 0.0001) break;
                        }
                    }
                    if ($currWritten < $targetWritten && count($writtenIds) < $writtenPool->count()) {
                        foreach ($writtenPool as $eq) {
                            if (!in_array($eq->question_id, $writtenIds)) {
                                $writtenIds[] = $eq->question_id;
                                $currWritten += (float) ($eq->marks > 0 ? $eq->marks : 5.0);
                                if ($currWritten >= $targetWritten) break;
                            }
                        }
                    }
                    $selectedIds = array_merge($selectedIds, !empty($writtenIds) ? $writtenIds : $writtenPool->pluck('question_id')->all());
                }

                // Shuffled question sequence for each student
                $assignedIds = collect($selectedIds)->shuffle()->values()->all();
            } else {
                $pool = $exam->examQuestions->shuffle();
                $targetMarks = (float) ($exam->full_marks > 0 ? $exam->full_marks : $pool->sum('marks'));
                $poolTotalMarks = (float) $pool->sum('marks');

                if ($targetMarks <= 0 || $poolTotalMarks <= $targetMarks) {
                    $assignedIds = $pool->pluck('question_id')->values()->all();
                } else {
                    $selectedIds = [];
                    $currentMarks = 0.0;
                    foreach ($pool as $eq) {
                        $qMarks = (float) ($eq->marks > 0 ? $eq->marks : 1.0);
                        if (($currentMarks + $qMarks) <= ($targetMarks + 0.0001)) {
                            $selectedIds[] = $eq->question_id;
                            $currentMarks += $qMarks;
                            if (abs($currentMarks - $targetMarks) < 0.0001) break;
                        }
                    }
                    if ($currentMarks < $targetMarks && count($selectedIds) < $pool->count()) {
                        foreach ($pool as $eq) {
                            if (!in_array($eq->question_id, $selectedIds)) {
                                $selectedIds[] = $eq->question_id;
                                $currentMarks += (float) ($eq->marks > 0 ? $eq->marks : 1.0);
                                if ($currentMarks >= $targetMarks) break;
                            }
                        }
                    }
                    $assignedIds = collect(!empty($selectedIds) ? $selectedIds : $pool->pluck('question_id')->all())->shuffle()->values()->all();
                }
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

        // Initialize and persist shuffled options per question for this student submission
        $shuffledOptions = $submission->shuffled_options ?? [];
        $hasNewShuffle   = false;
        $letters         = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];

        foreach ($exam->examQuestions as $eq) {
            $q = $eq->question;
            if (!$q || $q->question_type !== 'MCQ') {
                continue;
            }

            if (!isset($shuffledOptions[$q->id])) {
                $rawOptions = is_array($q->options) ? $q->options : [];
                if (count($rawOptions) > 1) {
                    $shuffledRaw             = collect($rawOptions)->shuffle()->values()->all();
                    $shuffledList            = [];
                    $shuffledCorrectOptionId = null;
                    $origCorrect             = strtolower(trim($q->correct_option_id ?? ''));

                    foreach ($shuffledRaw as $idx => $opt) {
                        $newLetter  = $letters[$idx] ?? chr(97 + $idx);
                        $origLetter = strtolower(trim($opt['id'] ?? ''));

                        if ($origLetter === $origCorrect) {
                            $shuffledCorrectOptionId = $newLetter;
                        }

                        $shuffledList[] = [
                            'id'          => $newLetter,
                            'text'        => $opt['text'] ?? '',
                            'original_id' => $origLetter,
                        ];
                    }

                    $shuffledOptions[$q->id] = [
                        'options'           => $shuffledList,
                        'correct_option_id' => $shuffledCorrectOptionId,
                        'original_correct'  => $origCorrect,
                    ];
                    $hasNewShuffle = true;
                }
            }
        }

        if ($hasNewShuffle) {
            $submission->shuffled_options = $shuffledOptions;
            $submission->save();
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
                // MCQ — auto-grade using student's persistent shuffled options
                $selectedOpt  = strtolower(trim($answersInput[$q->id] ?? ''));
                $isCorrect    = false;
                $marksAwarded = 0.00;

                $shuffledData = $submission->shuffled_options[$q->id] ?? null;
                $correctOpt   = $shuffledData && !empty($shuffledData['correct_option_id'])
                    ? strtolower(trim($shuffledData['correct_option_id']))
                    : strtolower(trim($q->correct_option_id ?? ''));

                if ($selectedOpt !== '') {
                    if ($selectedOpt === $correctOpt) {
                        $isCorrect    = true;
                        $correctCount++;
                        $marksAwarded = (float) $eq->marks;
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
