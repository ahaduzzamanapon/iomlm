<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\FinalMark;
use App\Models\Result;
use App\Models\Semester;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinalMarkController extends Controller
{
    /**
     * Show filter form + previously generated results
     */
    public function index(Request $request)
    {
        $batches = Batch::with(['course.semesters' => function ($q) {
            $q->orderBy('sequence_no');
        }, 'course.subjects'])->orderByDesc('id')->get();

        $criteria = FinalMark::getCriteria();

        $finalMarks = collect();
        $selectedBatch = null;
        $selectedSubject = null;
        $courseSemesters = collect();
        $isSemesterBased = false;
        $runningSemesterId = null;
        $selectedSemesterId = null;

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
                }
            }
        }

        // Determine subjects for the subject dropdown
        if ($selectedBatch && $selectedBatch->course) {
            if ($isSemesterBased && $selectedSemesterId) {
                $subjects = $selectedBatch->course->subjects()
                    ->wherePivot('semester_id', $selectedSemesterId)
                    ->where('subjects.is_active', true)
                    ->orderBy('subjects.name')
                    ->get();

                if ($subjects->isEmpty()) {
                    $subjects = $selectedBatch->course->subjects;
                }
            } else {
                $subjects = $selectedBatch->course->subjects;
            }
        } else {
            $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        }

        if ($request->filled('batch_id') && $request->filled('subject_id')) {
            $selectedSubject = Subject::find($request->subject_id);

            $finalMarks = FinalMark::with(['student', 'enrollment'])
                ->where('batch_id', $request->batch_id)
                ->where('subject_id', $request->subject_id)
                ->when($selectedSemesterId, function ($q) use ($selectedSemesterId) {
                    $q->where(function ($q2) use ($selectedSemesterId) {
                        $q2->where('semester_id', $selectedSemesterId)->orWhereNull('semester_id');
                    });
                })
                ->orderBy('total_mark', 'desc')
                ->get();
        }

        return view('admin.final-marks.index', compact(
            'batches', 'subjects', 'courseSemesters', 'criteria',
            'finalMarks', 'selectedBatch', 'selectedSubject',
            'isSemesterBased', 'runningSemesterId', 'selectedSemesterId'
        ));
    }

    /**
     * Get subjects belonging to a specific batch via AJAX
     */
    public function getBatchSubjects(Request $request)
    {
        $batchId = $request->query('batch_id');
        if (!$batchId) {
            $all = Subject::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
            return response()->json([
                'course_type'         => 'SUBJECT_BASED',
                'has_semesters'       => false,
                'running_semester_id' => null,
                'semesters'           => [],
                'subjects'            => $all,
            ]);
        }

        $batch = Batch::with([
            'course.semesters' => function ($q) { $q->orderBy('sequence_no'); },
            'course.subjects'  => function ($q) { $q->where('subjects.is_active', true)->orderBy('subjects.name'); },
            'semesterPosition',
        ])->find($batchId);

        if (!$batch || !$batch->course) {
            $all = Subject::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
            return response()->json([
                'course_type'         => 'SUBJECT_BASED',
                'has_semesters'       => false,
                'running_semester_id' => null,
                'semesters'           => [],
                'subjects'            => $all,
            ]);
        }

        $course = $batch->course;
        $isSemesterBased = ($course->type === 'SEMESTER_BASED' && $course->semesters->isNotEmpty());

        $runningSemesterId = null;
        $semestersList = [];

        if ($isSemesterBased) {
            $runningSemesterId = $batch->semesterPosition?->current_semester_id
                ?? $batch->enrollments()->whereNotNull('semester_id')->latest()->value('semester_id')
                ?? $course->semesters->first()?->id;

            $semestersList = $course->semesters->map(function ($sem) use ($runningSemesterId) {
                return [
                    'id'          => $sem->id,
                    'name'        => $sem->name,
                    'sequence_no' => $sem->sequence_no,
                    'is_running'  => ($sem->id == $runningSemesterId),
                ];
            });
        }

        $subjects = $course->subjects->map(function ($s) {
            return [
                'id'          => $s->id,
                'name'        => $s->name,
                'code'        => $s->code,
                'semester_id' => $s->pivot->semester_id ?? null,
            ];
        });

        return response()->json([
            'course_type'         => $course->type ?? 'SUBJECT_BASED',
            'has_semesters'       => $isSemesterBased,
            'running_semester_id' => $runningSemesterId,
            'semesters'           => $semestersList,
            'subjects'            => $subjects,
        ]);
    }

    /**
     * Update global final mark conversion criteria
     */
    public function updateCriteria(Request $request)
    {
        $validated = $request->validate([
            'class_test_full'    => 'required|numeric|min:1',
            'class_test_convert' => 'required|numeric|min:0',
            'midterm_full'       => 'required|numeric|min:1',
            'midterm_convert'    => 'required|numeric|min:0',
            'final_full'         => 'required|numeric|min:1',
            'final_convert'      => 'required|numeric|min:0',
            'attendance_convert' => 'required|numeric|min:0',
            'pass_mark'          => 'required|numeric|min:0',
        ]);

        Setting::set('final_mark_class_test_full', $validated['class_test_full']);
        Setting::set('final_mark_class_test_convert', $validated['class_test_convert']);
        Setting::set('final_mark_midterm_full', $validated['midterm_full']);
        Setting::set('final_mark_midterm_convert', $validated['midterm_convert']);
        Setting::set('final_mark_final_full', $validated['final_full']);
        Setting::set('final_mark_final_convert', $validated['final_convert']);
        Setting::set('final_mark_attendance_convert', $validated['attendance_convert']);
        Setting::set('final_mark_pass_mark', $validated['pass_mark']);

        return back()->with('success', '✅ ফাইনাল মার্ক কনভার্সন ক্রাইটেরিয়া সফলভাবে সংরক্ষণ করা হয়েছে।');
    }

    /**
     * Update individual student attendance mark and recalculate total, grade, GPA
     */
    public function updateAttendance(Request $request, FinalMark $finalMark)
    {
        $validated = $request->validate([
            'attendance_converted' => 'required|numeric|min:0',
            'attendance_percent'   => 'nullable|numeric|min:0|max:100',
        ]);

        $finalMark->recalculate(
            (float) $validated['attendance_converted'],
            $request->filled('attendance_percent') ? (float) $validated['attendance_percent'] : null
        );

        $studentName = $finalMark->student->name ?? 'শিক্ষার্থী';
        return back()->with('success', "✅ শিক্ষার্থী '{$studentName}'-এর উপস্থিতি নম্বর সফলভাবে আপডেট করা হয়েছে। নতুন মোট নম্বর: {$finalMark->total_mark} (গ্রেড: {$finalMark->grade})");
    }

    /**
     * Generate / Regenerate final marks for a batch + subject
     */
    public function generate(Request $request)
    {
        $request->validate([
            'batch_id'    => 'required|exists:batches,id',
            'subject_id'  => 'required|exists:subjects,id',
            'semester_id' => 'nullable|exists:semesters,id',
        ]);

        $batchId   = $request->batch_id;
        $subjectId = $request->subject_id;
        $adminId   = auth()->id();
        $criteria  = FinalMark::getCriteria();

        // Get all active enrollments for this batch
        $enrollments = Enrollment::with('student')
            ->where('batch_id', $batchId)
            ->where('status', 'ACTIVE')
            ->get();

        if ($enrollments->isEmpty()) {
            return back()->with('error', 'এই ব্যাচে কোনো সক্রিয় শিক্ষার্থী পাওয়া যায়নি (No active students found in this batch).');
        }

        // Get the semester_id from request or this batch's current position (if any)
        $semesterId = $request->input('semester_id');
        if (!$semesterId) {
            $batchObj = Batch::with('course.semesters', 'semesterPosition')->find($batchId);
            if ($batchObj && $batchObj->course && $batchObj->course->type === 'SEMESTER_BASED') {
                $semesterId = $batchObj->semesterPosition?->current_semester_id
                    ?? $batchObj->enrollments()->whereNotNull('semester_id')->latest()->value('semester_id')
                    ?? $batchObj->course->semesters->first()?->id;
            }
        }

        // Gather all exams for this subject (QUIZ, MIDTERM, FINAL)
        $examsByType = Exam::where('subject_id', $subjectId)
            ->whereIn('type', ['QUIZ', 'MIDTERM', 'FINAL'])
            ->when($semesterId, fn($q) => $q->where(function ($q2) use ($semesterId) {
                $q2->where('semester_id', $semesterId)->orWhereNull('semester_id');
            }))
            ->get()
            ->groupBy('type');

        // Get all class sessions for this subject in this batch (for attendance)
        $classSessions = ClassSession::where('batch_id', $batchId)
            ->where('subject_id', $subjectId)
            ->where('status', 'COMPLETED')
            ->pluck('id');

        $totalSessions = $classSessions->count();
        $generated = 0;

        DB::transaction(function () use (
            $enrollments, $batchId, $subjectId, $semesterId,
            $examsByType, $classSessions, $totalSessions, $adminId, $criteria, &$generated
        ) {
            foreach ($enrollments as $enrollment) {
                $studentId = $enrollment->student_id;

                // ── 1. Class Test Mark (QUIZ type, highest score) ──────────
                $classTestObtained  = null;
                $classTestConverted = null;
                if (!empty($examsByType['QUIZ'])) {
                    $quizIds = $examsByType['QUIZ']->pluck('id');
                    $best = Result::whereIn('exam_id', $quizIds)
                        ->where('student_id', $studentId)
                        ->max('marks');
                    if ($best !== null) {
                        $classTestObtained  = round(min($best, $criteria['class_test_full']), 2);
                        $classTestConverted = round(($classTestObtained / $criteria['class_test_full']) * $criteria['class_test_convert'], 2);
                    }
                }

                // ── 2. Mid Term Mark (highest score) ───────────────────────
                $midtermObtained  = null;
                $midtermConverted = null;
                if (!empty($examsByType['MIDTERM'])) {
                    $midIds = $examsByType['MIDTERM']->pluck('id');
                    $best = Result::whereIn('exam_id', $midIds)
                        ->where('student_id', $studentId)
                        ->max('marks');
                    if ($best !== null) {
                        $midtermObtained  = round(min($best, $criteria['midterm_full']), 2);
                        $midtermConverted = round(($midtermObtained / $criteria['midterm_full']) * $criteria['midterm_convert'], 2);
                    }
                }

                // ── 3. Final Term Mark (highest score) ─────────────────────
                $finalObtained  = null;
                $finalConverted = null;
                if (!empty($examsByType['FINAL'])) {
                    $finalIds = $examsByType['FINAL']->pluck('id');
                    $best = Result::whereIn('exam_id', $finalIds)
                        ->where('student_id', $studentId)
                        ->max('marks');
                    if ($best !== null) {
                        $finalObtained  = round(min($best, $criteria['final_full']), 2);
                        $finalConverted = round(($finalObtained / $criteria['final_full']) * $criteria['final_convert'], 2);
                    }
                }

                // ── 4. Attendance Mark ──────────────────────────────────────
                $attendancePercent   = 0.0;
                $attendanceConverted = 0.0;
                if ($totalSessions > 0) {
                    $presentCount = Attendance::whereIn('class_session_id', $classSessions)
                        ->where('student_id', $studentId)
                        ->whereIn('status', ['PRESENT', 'LATE'])
                        ->count();
                    $attendancePercent   = round(($presentCount / $totalSessions) * 100, 2);
                    $attendanceConverted = round(($attendancePercent / 100) * $criteria['attendance_convert'], 2);
                }

                // ── 5. Total & Grade ───────────────────────────────────────
                $total = round(
                    ($classTestConverted ?? 0) +
                    ($midtermConverted   ?? 0) +
                    ($finalConverted     ?? 0) +
                    ($attendanceConverted ?? 0),
                    2
                );

                $gradeInfo = FinalMark::calculateGrade($total);
                $status    = $total >= $criteria['pass_mark'] ? 'PASS' : 'FAIL';

                FinalMark::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $subjectId,
                        'batch_id'   => $batchId,
                    ],
                    [
                        'enrollment_id'        => $enrollment->id,
                        'semester_id'          => $semesterId,
                        'class_test_obtained'  => $classTestObtained,
                        'class_test_converted' => $classTestConverted,
                        'midterm_obtained'     => $midtermObtained,
                        'midterm_converted'    => $midtermConverted,
                        'final_obtained'       => $finalObtained,
                        'final_converted'      => $finalConverted,
                        'attendance_percent'   => $attendancePercent,
                        'attendance_converted' => $attendanceConverted,
                        'total_mark'           => $total,
                        'grade'                => $gradeInfo['grade'],
                        'gpa'                  => $gradeInfo['gpa'],
                        'status'               => $status,
                        'generated_by'         => $adminId,
                        'generated_at'         => now(),
                    ]
                );
                $generated++;
            }
        });

        $redirectParams = [
            'batch_id'   => $batchId,
            'subject_id' => $subjectId,
        ];
        if ($semesterId) {
            $redirectParams['semester_id'] = $semesterId;
        }

        return redirect()
            ->route('admin.final-marks.index', $redirectParams)
            ->with('success', "✅ মোট {$generated} জন শিক্ষার্থীর জন্য ফাইনাল মার্ক সফলভাবে জেনারেট / আপডেট করা হয়েছে।");
    }

    /**
     * Export final marks as CSV
     */
    public function exportCsv(Request $request)
    {
        $request->validate([
            'batch_id'   => 'required|exists:batches,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $criteria = FinalMark::getCriteria();

        $marks = FinalMark::with('student')
            ->where('batch_id', $request->batch_id)
            ->where('subject_id', $request->subject_id)
            ->when($request->filled('semester_id'), function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('semester_id', $request->semester_id)->orWhereNull('semester_id');
                });
            })
            ->orderBy('total_mark', 'desc')
            ->get();

        $batch   = Batch::find($request->batch_id);
        $subject = Subject::find($request->subject_id);

        $filename = "final_marks_{$batch->name}_{$subject->name}_" . now()->format('Ymd') . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($marks, $criteria) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for proper Bengali character rendering in Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                '#', 'Student Name', 'Student Code',
                "Class Test (/{$criteria['class_test_full']})", "Class Test Converted (/{$criteria['class_test_convert']})",
                "Mid Term (/{$criteria['midterm_full']})",   "Mid Term Converted (/{$criteria['midterm_convert']})",
                "Final Term (/{$criteria['final_full']})",  "Final Term Converted (/{$criteria['final_convert']})",
                'Attendance %', "Attendance Mark (/{$criteria['attendance_convert']})",
                'Total (/100)', 'Grade', 'GPA', 'Status',
            ]);

            foreach ($marks as $i => $m) {
                fputcsv($handle, [
                    $i + 1,
                    $m->student->name ?? '—',
                    $m->student->student_code ?? '—',
                    $m->class_test_obtained  ?? '—',
                    $m->class_test_converted ?? '—',
                    $m->midterm_obtained     ?? '—',
                    $m->midterm_converted    ?? '—',
                    $m->final_obtained       ?? '—',
                    $m->final_converted      ?? '—',
                    $m->attendance_percent   ?? '—',
                    $m->attendance_converted ?? '—',
                    $m->total_mark           ?? '—',
                    $m->grade                ?? '—',
                    $m->gpa                  ?? '—',
                    $m->status,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
