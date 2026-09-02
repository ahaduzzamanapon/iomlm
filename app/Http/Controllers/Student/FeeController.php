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

        // ── Semester-wise Breakdown: ALL semesters, whether invoiced or not ──
        $allSemesters = $course ? $course->semesters : collect();

        $nonCancelledInvoices = $invoices->where('status', '!=', 'CANCELLED');

        $invoicesBySemester          = [];
        $admissionInvoices           = collect();
        $retakeInvoices              = collect();
        $otherInvoices               = collect();
        $unassignedSemesterInvoices  = collect();

        foreach ($nonCancelledInvoices as $inv) {
            if ($inv->category === 'ADMISSION') {
                $admissionInvoices->push($inv);
            } elseif ($inv->category === 'RETAKE') {
                $retakeInvoices->push($inv);
            } elseif ($inv->category === 'SEMESTER' || $inv->source_type === \App\Models\Semester::class) {
                // 1. Direct match by source_type + source_id
                if ($inv->source_type === \App\Models\Semester::class && $inv->source_id && $allSemesters->pluck('id')->contains($inv->source_id)) {
                    $invoicesBySemester[$inv->source_id][] = $inv;
                } else {
                    // 2. Try title matching with specific semester names (e.g. "Semester 1", "Semester 2", "সেমিস্টার ১")
                    $matchedSemId = null;
                    foreach ($allSemesters as $sem) {
                        if ($sem->name && str_contains(mb_strtolower($inv->title), mb_strtolower($sem->name))
                            && !str_contains(mb_strtolower($sem->name), 'current')
                        ) {
                            $matchedSemId = $sem->id;
                            break;
                        }
                    }

                    if ($matchedSemId) {
                        $invoicesBySemester[$matchedSemId][] = $inv;
                        // Auto-assign in DB for permanent clean tracking
                        if (empty($inv->source_id)) {
                            $inv->update(['source_type' => \App\Models\Semester::class, 'source_id' => $matchedSemId]);
                        }
                    } else {
                        // Keep for sequential assignment below
                        $unassignedSemesterInvoices->push($inv);
                    }
                }
            } else {
                $otherInvoices->push($inv);
            }
        }

        // 3. Sequentially assign remaining unassigned SEMESTER invoices to semesters (in ID order)
        if ($unassignedSemesterInvoices->isNotEmpty()) {
            $sortedUnassigned = $unassignedSemesterInvoices->sortBy('id');

            foreach ($allSemesters as $sem) {
                if ($sortedUnassigned->isEmpty()) {
                    break;
                }
                // If this semester doesn't have an invoice assigned yet
                if (empty($invoicesBySemester[$sem->id])) {
                    $assignedInv = $sortedUnassigned->shift();
                    $invoicesBySemester[$sem->id][] = $assignedInv;
                    // Auto-assign in DB
                    if (empty($assignedInv->source_id)) {
                        $assignedInv->update([
                            'source_type' => \App\Models\Semester::class,
                            'source_id'   => $sem->id,
                        ]);
                    }
                }
            }

            // Any remaining unassigned go to otherInvoices
            while ($sortedUnassigned->isNotEmpty()) {
                $otherInvoices->push($sortedUnassigned->shift());
            }
        }

        // Compute runningSemesterDue accurately based on running semester's assigned invoice
        $runningSemesterDue = 0.0;
        foreach ($invoices as $inv) {
            $isCurrentSemester = false;

            if ($runningSemester && $inv->source_id == $runningSemester->id && $inv->source_type === \App\Models\Semester::class) {
                $isCurrentSemester = true;
            }

            $inv->is_current_running_semester = $isCurrentSemester;

            if ($isCurrentSemester && $inv->status !== 'CANCELLED') {
                $runningSemesterDue += $inv->due_amount;
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
