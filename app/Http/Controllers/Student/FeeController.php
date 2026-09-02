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

        // ── Semester-wise Breakdown: ALL semesters, whether invoiced or not ──
        // Load all semesters of the course (ordered by sequence)
        $allSemesters = $course ? $course->semesters : collect();

        // Build a lookup: semester_id → [invoices]
        $invoicesBySemester = [];
        $admissionInvoices  = collect();
        $retakeInvoices     = collect();
        $otherInvoices      = collect();

        foreach ($invoices->where('status', '!=', 'CANCELLED') as $inv) {
            $matched = false;

            // Match by source_type+source_id (most reliable)
            if ($inv->source_type === \App\Models\Semester::class && $inv->source_id) {
                $invoicesBySemester[$inv->source_id][] = $inv;
                $matched = true;
            }
            // Match by category SEMESTER — try to link to running semester
            elseif ($inv->category === 'SEMESTER') {
                if ($runningSemester && (
                    str_contains(mb_strtolower($inv->title), mb_strtolower($runningSemester->name))
                    || str_contains(mb_strtolower($inv->title), 'current semester')
                )) {
                    $invoicesBySemester[$runningSemester->id][] = $inv;
                    $matched = true;
                }
                if (!$matched) {
                    $otherInvoices->push($inv);
                }
            }
            // Admission
            elseif ($inv->category === 'ADMISSION') {
                $admissionInvoices->push($inv);
                $matched = true;
            }
            // Retake
            elseif ($inv->category === 'RETAKE') {
                $retakeInvoices->push($inv);
                $matched = true;
            }
            else {
                $otherInvoices->push($inv);
            }
        }

        // Build ordered breakdown rows
        $semesterBreakdown = collect();

        // 1. All course semesters (whether invoiced or not)
        foreach ($allSemesters as $sem) {
            $semInvoices = collect($invoicesBySemester[$sem->id] ?? []);
            $isRunning   = $runningSemester && $sem->id == $runningSemester->id;

            $semesterBreakdown->push([
                'label'     => $sem->name . ($isRunning ? ' 🔵' : ''),
                'isRunning' => $isRunning,
                'payable'   => $semInvoices->sum('payable_amount'),
                'paid'      => $semInvoices->sum('paid_amount'),
                'due'       => $semInvoices->sum('due_amount'),
                'hasInvoice'=> $semInvoices->isNotEmpty(),
            ]);
        }

        // 2. Admission fee row
        if ($admissionInvoices->isNotEmpty()) {
            $semesterBreakdown->push([
                'label'     => 'ভর্তি ফি (Admission Fee)',
                'isRunning' => false,
                'payable'   => $admissionInvoices->sum('payable_amount'),
                'paid'      => $admissionInvoices->sum('paid_amount'),
                'due'       => $admissionInvoices->sum('due_amount'),
                'hasInvoice'=> true,
            ]);
        }

        // 3. Retake fee row
        if ($retakeInvoices->isNotEmpty()) {
            $semesterBreakdown->push([
                'label'     => 'বিষয় রিটেক ফি (Retake Fee)',
                'isRunning' => false,
                'payable'   => $retakeInvoices->sum('payable_amount'),
                'paid'      => $retakeInvoices->sum('paid_amount'),
                'due'       => $retakeInvoices->sum('due_amount'),
                'hasInvoice'=> true,
            ]);
        }

        // 4. Other / unmatched invoices
        if ($otherInvoices->isNotEmpty()) {
            $semesterBreakdown->push([
                'label'     => 'অন্যান্য ফি',
                'isRunning' => false,
                'payable'   => $otherInvoices->sum('payable_amount'),
                'paid'      => $otherInvoices->sum('paid_amount'),
                'due'       => $otherInvoices->sum('due_amount'),
                'hasInvoice'=> true,
            ]);
        }

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
