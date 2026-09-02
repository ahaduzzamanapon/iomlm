<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        $student = Student::with([
            'enrollments.course.semesters',
            'enrollments.batch.semesterPosition.currentSemester',
            'enrollments.semester'
        ])->where('user_id', auth()->id())->first();

        $activeEnrollment = $student?->enrollments->where('status', 'ACTIVE')->first()
            ?? $student?->enrollments->first();

        $course     = $activeEnrollment?->course;
        $courseType = $course?->type ?? 'SEMESTER_BASED';

        // Current Running Semester
        $runningSemester = $activeEnrollment?->batch?->semesterPosition?->currentSemester
            ?? $activeEnrollment?->semester;
        $runningSemesterName = $runningSemester?->name ?? 'চলতি সেমিস্টার';

        $invoices = Invoice::with(['enrollment.course'])
            ->where('student_id', $student?->id)
            ->latest()
            ->get();

        $payments = Payment::with('invoice')
            ->where('student_id', $student?->id)
            ->latest('paid_at')
            ->get();

        $totalDue  = $invoices->where('status', '!=', 'CANCELLED')->sum('due_amount');
        $totalPaid = $payments->sum('amount');

        // Running semester dues computation
        $runningSemesterDue = 0.0;
        foreach ($invoices as $inv) {
            $isCurrentSemester = false;

            if ($runningSemester && $inv->source_id == $runningSemester->id && $inv->source_type === \App\Models\Semester::class) {
                $isCurrentSemester = true;
            } elseif ($runningSemester && str_contains(mb_strtolower($inv->title), mb_strtolower($runningSemester->name))) {
                $isCurrentSemester = true;
            } elseif (str_contains(mb_strtolower($inv->title), 'current semester')) {
                $isCurrentSemester = true;
            }

            $inv->is_current_running_semester = $isCurrentSemester;

            if ($isCurrentSemester && $inv->status !== 'CANCELLED') {
                $runningSemesterDue += $inv->due_amount;
            }
        }

        // ── Semester-wise Breakdown: group by actual semester ─────────────
        // Preload all semesters for the course to map IDs to names
        $semesterMap = $course
            ? $course->semesters->pluck('name', 'id')->toArray()
            : [];

        $semesterBreakdown = $invoices->where('status', '!=', 'CANCELLED')->groupBy(function ($inv) use ($semesterMap, $runningSemester) {
            // 1. source_type is Semester → use that semester's actual name
            if ($inv->source_type === \App\Models\Semester::class && !empty($semesterMap[$inv->source_id])) {
                $semName  = $semesterMap[$inv->source_id];
                $isRunning = $runningSemester && $inv->source_id == $runningSemester->id;
                return $semName . ($isRunning ? ' 🔵' : '');
            }
            // 2. Admission fee
            if ($inv->category === 'ADMISSION') {
                return 'ভর্তি ফি (Admission Fee)';
            }
            // 3. Retake fee
            if ($inv->category === 'RETAKE') {
                return 'বিষয় রিটেক ফি (Retake Fee)';
            }
            // 4. Semester category — fallback to title-based name
            if ($inv->category === 'SEMESTER') {
                if ($runningSemester && (
                    str_contains(mb_strtolower($inv->title), mb_strtolower($runningSemester->name))
                    || str_contains(mb_strtolower($inv->title), 'current semester')
                )) {
                    return $runningSemester->name . ' 🔵';
                }
                return 'সেমিস্টার ফি';
            }
            return 'অন্যান্য ফি';
        });

        return view('student.fees.index', compact(
            'student', 'course', 'courseType', 'runningSemester', 'runningSemesterName',
            'invoices', 'payments', 'totalDue', 'totalPaid', 'runningSemesterDue',
            'semesterBreakdown'
        ));
    }

    public function printReceipt(Payment $payment)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        if ($payment->student_id !== $student->id) {
            abort(403, 'Unauthorized access to receipt.');
        }
        $payment->load(['invoice', 'student', 'receivedBy']);
        return view('admin.accounts.print_receipt', compact('payment'));
    }
}
