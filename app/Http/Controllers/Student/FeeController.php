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

        $selectedCourseId = request('course_id');

        $activeEnrollment = null;
        if ($selectedCourseId) {
            $activeEnrollment = $student?->enrollments->where('course_id', $selectedCourseId)->first();
        }
        if (!$activeEnrollment) {
            $activeEnrollment = $student?->enrollments->where('status', 'ACTIVE')->first()
                ?? $student?->enrollments->first();
        }

        $studentCourses = $student?->enrollments->map(fn($e) => $e->course)->filter()->unique('id') ?? collect();

        $course     = $activeEnrollment?->course;
        $courseType = $course?->type ?? 'SEMESTER_BASED';

        // Current Running Semester
        $runningSemester = $activeEnrollment?->batch?->semesterPosition?->currentSemester
            ?? $activeEnrollment?->semester;
        $runningSemesterName = $runningSemester?->name ?? 'চলতি সেমিস্টার';

        $invoicesQuery = Invoice::with(['enrollment.course'])
            ->where('student_id', $student?->id);

        if ($course) {
            $invoicesQuery->where(function ($q) use ($course, $activeEnrollment) {
                $q->where('enrollment_id', $activeEnrollment->id)
                  ->orWhereHas('enrollment', fn($q2) => $q2->where('course_id', $course->id))
                  ->orWhereNull('enrollment_id');
            });
        }

        $invoices = $invoicesQuery->latest()->get();

        $payments = Payment::with('invoice')
            ->where('student_id', $student?->id)
            ->whereHas('invoice', function ($q) use ($course, $activeEnrollment) {
                if ($course) {
                    $q->where('enrollment_id', $activeEnrollment->id)
                      ->orWhereHas('enrollment', fn($q2) => $q2->where('course_id', $course->id))
                      ->orWhereNull('enrollment_id');
                }
            })
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

        // 3. Sequentially assign remaining unassigned SEMESTER invoices to semesters
        if ($unassignedSemesterInvoices->isNotEmpty()) {
            // Sort unassigned semester invoices: PAID first, then PARTIAL, then UNPAID (so Semester 1 gets PAID invoice first)
            $sortedUnassigned = $unassignedSemesterInvoices->sort(function ($a, $b) {
                $statusOrder = ['PAID' => 1, 'PARTIAL' => 2, 'UNPAID' => 3];
                $orderA = $statusOrder[$a->status] ?? 4;
                $orderB = $statusOrder[$b->status] ?? 4;

                if ($orderA === $orderB) {
                    return $a->id <=> $b->id;
                }
                return $orderA <=> $orderB;
            })->values();

            foreach ($allSemesters as $sem) {
                if ($sortedUnassigned->isEmpty()) {
                    break;
                }
                // If this semester doesn't have an invoice assigned yet
                if (empty($invoicesBySemester[$sem->id])) {
                    $assignedInv = $sortedUnassigned->shift();
                    $invoicesBySemester[$sem->id][] = $assignedInv;
                    // Auto-assign in DB for clean future tracking
                    try {
                        $assignedInv->update([
                            'source_type' => \App\Models\Semester::class,
                            'source_id'   => $sem->id,
                        ]);
                    } catch (\Throwable $e) {}
                }
            }

            // Any remaining unassigned semester invoices get individual separate entries
            while ($sortedUnassigned->isNotEmpty()) {
                $extraInv = $sortedUnassigned->shift();
                $otherInvoices->push($extraInv);
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
            $firstUnpaid = $semInvoices->where('due_amount', '>', 0)->first() ?? $semInvoices->first();
            $isRunning   = $runningSemester && $sem->id == $runningSemester->id;

            $semesterBreakdown->push([
                'label'      => $sem->name . ($isRunning ? ' 🔵' : ''),
                'category'   => 'SEMESTER',
                'isRunning'  => $isRunning,
                'payable'    => $semInvoices->sum('payable_amount'),
                'paid'       => $semInvoices->sum('paid_amount'),
                'due'        => $semInvoices->sum('due_amount'),
                'hasInvoice' => $semInvoices->isNotEmpty(),
                'invoice'    => $firstUnpaid,
            ]);
        }

        // 2. Admission fee row
        if ($admissionInvoices->isNotEmpty()) {
            $firstUnpaid = $admissionInvoices->where('due_amount', '>', 0)->first() ?? $admissionInvoices->first();
            $semesterBreakdown->push([
                'label'      => 'ভর্তি ফি (Admission Fee)',
                'category'   => 'ADMISSION',
                'isRunning'  => false,
                'payable'    => $admissionInvoices->sum('payable_amount'),
                'paid'       => $admissionInvoices->sum('paid_amount'),
                'due'        => $admissionInvoices->sum('due_amount'),
                'hasInvoice' => true,
                'invoice'    => $firstUnpaid,
            ]);
        }

        // 3. Retake / Exam fee row
        if ($retakeInvoices->isNotEmpty()) {
            foreach ($retakeInvoices as $rInv) {
                $semesterBreakdown->push([
                    'label'      => $rInv->title ?: 'বিষয় রিটেক / পরীক্ষা ফি',
                    'category'   => 'RETAKE',
                    'isRunning'  => false,
                    'payable'    => (float)$rInv->payable_amount,
                    'paid'       => (float)$rInv->paid_amount,
                    'due'        => (float)$rInv->due_amount,
                    'hasInvoice' => true,
                    'invoice'    => $rInv,
                ]);
            }
        }

        // 4. Other / unmatched / monthly fee invoices (each listed individually)
        foreach ($otherInvoices as $oInv) {
            $label = $oInv->title ?: 'ফি ইনভয়েস (' . $oInv->invoice_no . ')';
            $semesterBreakdown->push([
                'label'      => $label,
                'category'   => $oInv->category ?: 'OTHER',
                'isRunning'  => false,
                'payable'    => (float)$oInv->payable_amount,
                'paid'       => (float)$oInv->paid_amount,
                'due'        => (float)$oInv->due_amount,
                'hasInvoice' => true,
                'invoice'    => $oInv,
            ]);
        }

        return view('student.fees.index', compact(
            'student', 'course', 'courseType', 'runningSemester', 'runningSemesterName',
            'invoices', 'payments', 'totalDue', 'totalPaid', 'runningSemesterDue',
            'semesterBreakdown', 'studentCourses'
        ));
    }

    /**
     * Submit payment for an invoice from Student Portal.
     */
    public function payInvoice(Request $request, Invoice $invoice)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        if ($invoice->student_id !== $student->id) {
            abort(403, 'Unauthorized access to invoice.');
        }

        if ($invoice->status === 'PAID' || $invoice->due_amount <= 0) {
            return back()->with('error', 'এই ইনভয়েসটির সকল বকেয়া ইতিমধ্যে পরিশোধিত হয়েছে।');
        }

        $validated = $request->validate([
            'amount'         => 'required|numeric|min:1|max:' . $invoice->due_amount,
            'payment_method' => 'required|string|in:BKASH,NAGAD,ROCKET,ONLINE,BANK_TRANSFER,CASH',
            'transaction_id' => 'nullable|string|max:100',
            'remarks'        => 'nullable|string|max:255',
        ]);

        $payment = \App\Services\AccountingService::receivePayment(
            $invoice,
            (float) $validated['amount'],
            $validated['payment_method'],
            $validated['transaction_id'] ?? null,
            $validated['remarks'] ?? 'Student Portal Online Payment'
        );

        return back()->with('success', "🎉 পেমেন্ট সফলভাবে সম্পন্ন হয়েছে! Receipt No: {$payment->payment_no} (পরিমাণ: ৳" . number_format($payment->amount, 2) . ")");
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
