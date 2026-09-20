<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromotionRecord;
use App\Models\Student;
use App\Models\Semester;
use App\Models\Enrollment;
use App\Models\Batch;
use App\Models\Course;
use App\Models\FinalMark;
use App\Models\Exam;
use App\Models\Readmission;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    /**
     * Display promotion management, batch & semester audit, and student standing evaluation
     */
    public function index(Request $request)
    {
        $batches = Batch::with(['course.semesters' => function ($q) {
            $q->orderBy('sequence_no');
        }, 'course.subjects'])->orderByDesc('id')->get();

        $promotions = PromotionRecord::with(['student', 'fromSemester', 'toSemester', 'decidedBy'])->latest()->get();
        $students   = Student::where('status', 'ACTIVE')->orderBy('name')->get();
        $semesters  = Semester::orderBy('course_id')->orderBy('sequence_no')->get();

        $selectedBatch = null;
        $isSemesterBased = false;
        $courseSemesters = collect();
        $runningSemesterId = null;
        $selectedSemesterId = null;
        $nextSemester = null;

        $examAudit = null;
        $studentEvaluations = collect();

        if ($request->filled('batch_id')) {
            $selectedBatch = Batch::with([
                'course.semesters' => function ($q) { $q->orderBy('sequence_no'); },
                'course.subjects'  => function ($q) { $q->where('subjects.is_active', true)->orderBy('subjects.name'); },
                'semesterPosition',
            ])->find($request->batch_id);

            if ($selectedBatch && $selectedBatch->course) {
                $course = $selectedBatch->course;
                $isSemesterBased = ($course->type === 'SEMESTER_BASED' && $course->semesters->isNotEmpty());
                $courseSemesters = $course->semesters;

                if ($isSemesterBased) {
                    $runningSemesterId = $selectedBatch->semesterPosition?->current_semester_id
                        ?? $selectedBatch->enrollments()->whereNotNull('semester_id')->latest()->value('semester_id')
                        ?? $courseSemesters->first()?->id;

                    $selectedSemesterId = $request->input('semester_id', $runningSemesterId);

                    $currentSem = $courseSemesters->firstWhere('id', $selectedSemesterId);
                    if ($currentSem) {
                        $nextSemester = $courseSemesters
                            ->where('sequence_no', '>', $currentSem->sequence_no)
                            ->sortBy('sequence_no')
                            ->first();
                    }
                }

                // ── 1. Exam Completion Audit ("সব পরীক্ষা হয়েছে কিনা") ──
                if ($isSemesterBased && $selectedSemesterId) {
                    $subjects = $course->subjects()
                        ->wherePivot('semester_id', $selectedSemesterId)
                        ->where('subjects.is_active', true)
                        ->get();
                    if ($subjects->isEmpty()) {
                        $subjects = $course->subjects;
                    }
                } else {
                    $subjects = $course->subjects;
                }

                $subjectAudits = [];
                $completedSubjectsCount = 0;
                $criteria = FinalMark::getCriteria();

                foreach ($subjects as $subj) {
                    $subjectExams = Exam::where('subject_id', $subj->id)
                        ->when($selectedSemesterId, function ($q) use ($selectedSemesterId) {
                            $q->where(function ($q2) use ($selectedSemesterId) {
                                $q2->where('semester_id', $selectedSemesterId)->orWhereNull('semester_id');
                            });
                        })
                        ->get();

                    $hasQuiz    = $subjectExams->where('type', 'QUIZ')->isNotEmpty();
                    $hasMidterm = $subjectExams->where('type', 'MIDTERM')->isNotEmpty();
                    $hasFinal   = $subjectExams->where('type', 'FINAL')->isNotEmpty();

                    $hasFinalMarks = FinalMark::where('batch_id', $selectedBatch->id)
                        ->where('subject_id', $subj->id)
                        ->when($selectedSemesterId, function ($q) use ($selectedSemesterId) {
                            $q->where(function ($q2) use ($selectedSemesterId) {
                                $q2->where('semester_id', $selectedSemesterId)->orWhereNull('semester_id');
                            });
                        })
                        ->exists();

                    $isComplete = ($hasFinalMarks || ($hasQuiz && $hasMidterm && $hasFinal));
                    if ($isComplete) {
                        $completedSubjectsCount++;
                    }

                    $subjectAudits[] = [
                        'subject'         => $subj,
                        'has_quiz'        => $hasQuiz,
                        'has_midterm'     => $hasMidterm,
                        'has_final'       => $hasFinal,
                        'has_final_marks' => $hasFinalMarks,
                        'is_complete'     => $isComplete,
                    ];
                }

                $totalSubjectsCount = count($subjects);
                $allExamsCompleted = ($totalSubjectsCount > 0 && $completedSubjectsCount === $totalSubjectsCount);

                $examAudit = [
                    'subjects'             => $subjectAudits,
                    'total_subjects'       => $totalSubjectsCount,
                    'completed_subjects'   => $completedSubjectsCount,
                    'pending_subjects'     => $totalSubjectsCount - $completedSubjectsCount,
                    'all_completed'        => $allExamsCompleted,
                    'criteria'             => $criteria,
                ];

                // ── 2. Student Standing & Promotion / Readmission Evaluation ──
                $enrollments = $selectedBatch->enrollments()
                    ->with('student')
                    ->where('status', 'ACTIVE')
                    ->get();

                foreach ($enrollments as $enr) {
                    $student = $enr->student;
                    if (!$student) continue;

                    $marks = FinalMark::with('subject')
                        ->where('batch_id', $selectedBatch->id)
                        ->where('student_id', $student->id)
                        ->when($selectedSemesterId, function ($q) use ($selectedSemesterId) {
                            $q->where(function ($q2) use ($selectedSemesterId) {
                                $q2->where('semester_id', $selectedSemesterId)->orWhereNull('semester_id');
                            });
                        })
                        ->get();

                    $evaluatedCount = $marks->count();
                    $passCount = $marks->where('status', 'PASS')->count();
                    $failCount = $marks->where('status', 'FAIL')->count();
                    $totalMarks = $marks->sum('total_mark');
                    $avgMarks = $evaluatedCount > 0 ? round($totalMarks / $evaluatedCount, 1) : 0;
                    $avgGpa = $evaluatedCount > 0 ? round($marks->avg('gpa'), 2) : 0;

                    $failedSubjects = $marks->where('status', 'FAIL')->map(function ($m) {
                        return [
                            'id'   => $m->subject_id,
                            'name' => $m->subject->name ?? 'বিষয়',
                            'code' => $m->subject->code ?? '',
                            'mark' => $m->total_mark,
                        ];
                    })->values();

                    // Check if already promoted for this semester
                    $alreadyPromoted = PromotionRecord::where('student_id', $student->id)
                        ->when($selectedSemesterId, fn($q) => $q->where('from_semester_id', $selectedSemesterId))
                        ->latest()
                        ->first();

                    // Check if already registered in Readmission
                    $alreadyReadmission = Readmission::where('student_id', $student->id)
                        ->when($selectedSemesterId, fn($q) => $q->where('semester_id', $selectedSemesterId))
                        ->whereIn('status', ['PENDING', 'APPROVED', 'CONTINUED_WITH_RETAKE'])
                        ->latest()
                        ->first();

                    // Standing Decision
                    if ($alreadyPromoted) {
                        $standing = 'ALREADY_PROMOTED';
                        $statusBadge = 'PROMOTED';
                        $statusText = 'ইতিমধ্যে প্রমোশন সম্পন্ন';
                        $actionType = 'NONE';
                    } elseif ($alreadyReadmission) {
                        $standing = 'ALREADY_READMISSION';
                        $statusBadge = 'READMISSION';
                        $statusText = 'রি-এডমিশন তালিকায় অন্তর্ভুক্ত';
                        $actionType = 'NONE';
                    } elseif ($evaluatedCount === 0) {
                        $standing = 'PENDING_EXAMS';
                        $statusBadge = 'PENDING';
                        $statusText = 'মূল্যায়ন / মার্ক বাকি';
                        $actionType = 'GENERATE_MARKS';
                    } elseif ($failCount === 0) {
                        $standing = 'ELIGIBLE_PROMOTION';
                        $statusBadge = 'ELIGIBLE';
                        $statusText = '🟢 প্রমোশন পাবে (Promoted)';
                        $actionType = 'PROMOTE';
                    } elseif ($failCount <= 2) {
                        $standing = 'ELIGIBLE_RETAKE';
                        $statusBadge = 'RETAKE';
                        $statusText = '🟠 রিটেকসহ প্রমোশন যোগ্য';
                        $actionType = 'RETAKE_PROMOTE';
                    } else {
                        $standing = 'NEEDS_READMISSION';
                        $statusBadge = 'FAILED';
                        $statusText = '🔴 প্রমোশন পাবে না (রি-এডমিশন লাগবে)';
                        $actionType = 'READMISSION';
                    }

                    $studentEvaluations->push([
                        'student'            => $student,
                        'enrollment'         => $enr,
                        'marks'              => $marks,
                        'evaluated_count'    => $evaluatedCount,
                        'pass_count'         => $passCount,
                        'fail_count'         => $failCount,
                        'failed_subjects'    => $failedSubjects,
                        'total_marks'        => $totalMarks,
                        'avg_marks'          => $avgMarks,
                        'avg_gpa'            => $avgGpa,
                        'standing'           => $standing,
                        'status_badge'       => $statusBadge,
                        'status_text'        => $statusText,
                        'action_type'        => $actionType,
                        'already_promoted'   => $alreadyPromoted,
                        'already_readmission'=> $alreadyReadmission,
                    ]);
                }
            }
        }

        return view('admin.promotions.index', compact(
            'batches', 'promotions', 'students', 'semesters',
            'selectedBatch', 'isSemesterBased', 'courseSemesters',
            'runningSemesterId', 'selectedSemesterId', 'nextSemester',
            'examAudit', 'studentEvaluations'
        ));
    }

    /**
     * Store single promotion decision
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'      => 'required|exists:students,id',
            'from_semester_id'=> 'nullable|exists:semesters,id',
            'to_semester_id'  => 'nullable|exists:semesters,id',
            'decision'        => 'required|in:PROMOTED,FORCE_PROMOTED,HELD_BACK',
            'notes'           => 'nullable|string',
        ]);

        $student    = Student::findOrFail($validated['student_id']);
        /** @var \App\Models\Enrollment|null $enrollment */
        $enrollment = $student->enrollments()->where('status', 'ACTIVE')->first();

        PromotionRecord::create([
            'student_id'       => $validated['student_id'],
            'enrollment_id'    => $enrollment?->id,
            'from_semester_id' => $validated['from_semester_id'] ?? null,
            'to_semester_id'   => $validated['to_semester_id'] ?? null,
            'decision'         => $validated['decision'],
            'decided_by'       => auth()->id(),
            'notes'            => $validated['notes'] ?? null,
        ]);

        if (in_array($validated['decision'], ['PROMOTED', 'FORCE_PROMOTED']) && $enrollment) {
            if (!empty($validated['to_semester_id'])) {
                Enrollment::where('id', $enrollment->id)->update(['semester_id' => $validated['to_semester_id']]);
            }
            $toSem = Semester::find($validated['to_semester_id'] ?? null);
            if ($toSem) {
                AccountingService::createSemesterInvoice($student, $enrollment, $toSem);
            }
        }

        return back()->with('success', "✅ শিক্ষার্থী '{$student->name}'-এর প্রমোশন সিদ্ধান্ত সফলভাবে সংরক্ষিত হয়েছে এবং সংশ্লিষ্ট সেমিস্টার ইনভয়েস তৈরি হয়েছে।");
    }

    /**
     * Bulk promote all eligible students of a batch/semester to next semester
     */
    public function bulkPromote(Request $request)
    {
        $request->validate([
            'batch_id'         => 'required|exists:batches,id',
            'from_semester_id' => 'nullable|exists:semesters,id',
            'to_semester_id'   => 'required|exists:semesters,id',
            'student_ids'      => 'required|array',
            'student_ids.*'    => 'exists:students,id',
        ]);

        $toSem = Semester::findOrFail($request->to_semester_id);
        $fromSem = Semester::find($request->from_semester_id);
        $promotedCount = 0;
        $adminId = auth()->id();

        DB::transaction(function () use ($request, $toSem, $adminId, &$promotedCount) {
            foreach ($request->student_ids as $studentId) {
                $student = Student::find($studentId);
                if (!$student) continue;

                $enrollment = $student->enrollments()
                    ->where('batch_id', $request->batch_id)
                    ->where('status', 'ACTIVE')
                    ->first();

                // Prevent duplicate promotion
                $exists = PromotionRecord::where('student_id', $studentId)
                    ->where('from_semester_id', $request->from_semester_id)
                    ->where('to_semester_id', $toSem->id)
                    ->exists();

                if (!$exists) {
                    PromotionRecord::create([
                        'student_id'       => $studentId,
                        'enrollment_id'    => $enrollment?->id,
                        'from_semester_id' => $request->from_semester_id,
                        'to_semester_id'   => $toSem->id,
                        'decision'         => 'PROMOTED',
                        'decided_by'       => $adminId,
                        'notes'            => 'বাল্ক প্রমোশন: সকল ক্রাইটেরিয়া পূরণ সাপেক্ষে পরবর্তী সেমিস্টারে উত্তীর্ণ।',
                    ]);

                    if ($enrollment) {
                        Enrollment::where('id', $enrollment->id)->update(['semester_id' => $toSem->id]);
                        AccountingService::createSemesterInvoice($student, $enrollment, $toSem);
                    }
                    $promotedCount++;
                }
            }
        });

        return back()->with('success', "✅ মোট {$promotedCount} জন শিক্ষার্থীকে সফলভাবে '{$toSem->name}'-এ প্রমোশন দেওয়া হয়েছে এবং সংশ্লিষ্ট সেমিস্টার ইনভয়েস স্বয়ংক্রিয়ভাবে তৈরি হয়েছে।");
    }

    /**
     * Send failed student to readmission candidate list
     */
    public function sendReadmission(Request $request)
    {
        $request->validate([
            'student_id'  => 'required|exists:students,id',
            'batch_id'    => 'required|exists:batches,id',
            'semester_id' => 'nullable|exists:semesters,id',
            'notes'       => 'nullable|string',
        ]);

        $student = Student::findOrFail($request->student_id);
        $batch = Batch::with('course')->findOrFail($request->batch_id);
        $course = $batch->course;

        $enrollment = $student->enrollments()
            ->where('batch_id', $batch->id)
            ->where('status', 'ACTIVE')
            ->first();

        // Failed subjects from final marks
        $failedMarks = FinalMark::where('batch_id', $batch->id)
            ->where('student_id', $student->id)
            ->where('status', 'FAIL')
            ->when($request->filled('semester_id'), fn($q) => $q->where('semester_id', $request->semester_id))
            ->pluck('subject_id')
            ->toArray();

        $failedCount = count($failedMarks);
        $fee = (float)($course?->readmission_fee ?: ($course?->admission_fee ?: 0.00));

        $alreadyExists = Readmission::where('student_id', $student->id)
            ->where('from_batch_id', $batch->id)
            ->when($request->filled('semester_id'), fn($q) => $q->where('semester_id', $request->semester_id))
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->first();

        if ($alreadyExists) {
            return back()->with('info', "শিক্ষার্থী '{$student->name}' ইতিমধ্যে রি-এডমিশন প্রক্রিয়ায় অন্তর্ভুক্ত রয়েছেন।");
        }

        Readmission::create([
            'student_id'            => $student->id,
            'enrollment_id'         => $enrollment?->id,
            'course_id'             => $course?->id,
            'semester_id'           => $request->semester_id,
            'from_batch_id'         => $batch->id,
            'failed_subjects_count' => $failedCount,
            'failed_subject_ids'    => $failedMarks,
            'readmission_fee'       => $fee,
            'status'                => 'PENDING',
            'notes'                 => $request->notes ?: "প্রমোশন মূল্যায়ন: সেমিস্টারে {$failedCount}টি বিষয়ে অনুত্তীর্ণ হওয়ায় রি-এডমিশন প্রয়োজন।",
        ]);

        return back()->with('success', "✅ শিক্ষার্থী '{$student->name}'-কে রি-এডমিশন তালিকায় সফলভাবে অন্তর্ভুক্ত করা হয়েছে।");
    }
}

