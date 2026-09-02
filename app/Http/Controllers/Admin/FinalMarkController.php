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
use App\Models\Subject;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinalMarkController extends Controller
{
    // ── IOM Conversion Criteria ────────────────────────────────────────
    const CLASS_TEST_FULL    = 30;
    const CLASS_TEST_CONVERT = 20;
    const MIDTERM_FULL       = 50;
    const MIDTERM_CONVERT    = 30;
    const FINAL_FULL         = 100;
    const FINAL_CONVERT      = 40;
    const ATTENDANCE_CONVERT = 10;
    const PASS_MARK          = 40; // out of 100

    /**
     * Show filter form + previously generated results
     */
    public function index(Request $request)
    {
        $batches  = Batch::orderByDesc('id')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $semesters = Semester::orderBy('id')->get();

        $finalMarks = collect();
        $selectedBatch   = null;
        $selectedSubject = null;

        if ($request->filled('batch_id') && $request->filled('subject_id')) {
            $selectedBatch   = Batch::find($request->batch_id);
            $selectedSubject = Subject::find($request->subject_id);

            $finalMarks = FinalMark::with(['student', 'enrollment'])
                ->where('batch_id', $request->batch_id)
                ->where('subject_id', $request->subject_id)
                ->orderBy('total_mark', 'desc')
                ->get();
        }

        return view('admin.final-marks.index', compact(
            'batches', 'subjects', 'semesters',
            'finalMarks', 'selectedBatch', 'selectedSubject'
        ));
    }

    /**
     * Generate / Regenerate final marks for a batch + subject
     */
    public function generate(Request $request)
    {
        $request->validate([
            'batch_id'   => 'required|exists:batches,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $batchId   = $request->batch_id;
        $subjectId = $request->subject_id;
        $adminId   = auth()->id();

        // Get all active enrollments for this batch
        $enrollments = Enrollment::with('student')
            ->where('batch_id', $batchId)
            ->where('status', 'ACTIVE')
            ->get();

        if ($enrollments->isEmpty()) {
            return back()->with('error', 'No active students found in this batch.');
        }

        // Get the semester_id from this batch's current position (if any)
        $semesterId = DB::table('batch_semester_positions')
            ->where('batch_id', $batchId)
            ->value('current_semester_id');

        // ── Gather all exams for this subject (any batch-linked by semester or general) ──
        // Class Test: type = QUIZ  (mapped as class test)
        // Mid Term:   type = MIDTERM
        // Final Term: type = FINAL
        $examsByType = Exam::where('subject_id', $subjectId)
            ->whereIn('type', ['QUIZ', 'MIDTERM', 'FINAL'])
            ->where('status', 'COMPLETED')
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
            $examsByType, $classSessions, $totalSessions, $adminId, &$generated
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
                        $classTestObtained  = round(min($best, self::CLASS_TEST_FULL), 2);
                        $classTestConverted = round(($classTestObtained / self::CLASS_TEST_FULL) * self::CLASS_TEST_CONVERT, 2);
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
                        $midtermObtained  = round(min($best, self::MIDTERM_FULL), 2);
                        $midtermConverted = round(($midtermObtained / self::MIDTERM_FULL) * self::MIDTERM_CONVERT, 2);
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
                        $finalObtained  = round(min($best, self::FINAL_FULL), 2);
                        $finalConverted = round(($finalObtained / self::FINAL_FULL) * self::FINAL_CONVERT, 2);
                    }
                }

                // ── 4. Attendance Mark ──────────────────────────────────────
                $attendancePercent   = null;
                $attendanceConverted = null;
                if ($totalSessions > 0) {
                    $presentCount = Attendance::whereIn('class_session_id', $classSessions)
                        ->where('student_id', $studentId)
                        ->whereIn('status', ['PRESENT', 'LATE'])
                        ->count();
                    $attendancePercent   = round(($presentCount / $totalSessions) * 100, 2);
                    $attendanceConverted = round(($attendancePercent / 100) * self::ATTENDANCE_CONVERT, 2);
                }

                // ── 5. Total & Grade ───────────────────────────────────────
                $total = round(
                    ($classTestConverted ?? 0) +
                    ($midtermConverted   ?? 0) +
                    ($finalConverted     ?? 0) +
                    ($attendanceConverted ?? 0),
                    2
                );

                // Only generate if at least one component has data
                $hasData = $classTestObtained !== null
                    || $midtermObtained !== null
                    || $finalObtained   !== null
                    || $attendanceConverted !== null;

                if (!$hasData) continue;

                $gradeInfo = FinalMark::calculateGrade($total);
                $status    = $total >= self::PASS_MARK ? 'PASS' : 'FAIL';

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

        return redirect()
            ->route('admin.final-marks.index', [
                'batch_id'   => $batchId,
                'subject_id' => $subjectId,
            ])
            ->with('success', "✅ Final marks generated for {$generated} students successfully.");
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

        $marks = FinalMark::with('student')
            ->where('batch_id', $request->batch_id)
            ->where('subject_id', $request->subject_id)
            ->orderBy('total_mark', 'desc')
            ->get();

        $batch   = Batch::find($request->batch_id);
        $subject = Subject::find($request->subject_id);

        $filename = "final_marks_{$batch->name}_{$subject->name}_" . now()->format('Ymd') . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($marks) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                '#', 'Student Name', 'Student Code',
                'Class Test (/30)', 'Class Test Converted (/20)',
                'Mid Term (/50)',   'Mid Term Converted (/30)',
                'Final Term (/100)','Final Term Converted (/40)',
                'Attendance %',    'Attendance Mark (/10)',
                'Total (/100)',    'Grade', 'GPA', 'Status',
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
