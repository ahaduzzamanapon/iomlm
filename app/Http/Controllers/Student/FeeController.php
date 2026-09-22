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

        if ($courseType === 'SEMESTER_BASED') {
            // 1. All course semesters (whether invoiced or not)
            $totalSemMonths = 6;
            if ($course && $course->semesters->count() > 0) {
                $totalCourseMonths = $course->duration_unit === 'YEAR' ? $course->duration_value * 12 : $course->duration_value;
                $totalSemMonths = max(1, (int) round($totalCourseMonths / $course->semesters->count()));
            }

            foreach ($allSemesters as $sem) {
                $semInvoices = collect($invoicesBySemester[$sem->id] ?? []);
                $firstUnpaid = $semInvoices->where('due_amount', '>', 0)->first() ?? $semInvoices->first();
                $isRunning   = $runningSemester && $sem->id == $runningSemester->id;

                $semPayable = (float) $semInvoices->sum('payable_amount');
                $semPaid    = (float) $semInvoices->sum('paid_amount');
                $monthlyRate = $totalSemMonths > 0 && $semPayable > 0 ? round($semPayable / $totalSemMonths, 2) : 500;

                $pool = $semPaid;
                $monthlyItems = [];
                $bnDigits = ['0'=>'০','1'=>'১','2'=>'২','3'=>'৩','4'=>'৪','5'=>'৫','6'=>'৬','7'=>'৭','8'=>'৮','9'=>'৯'];
                for ($m = 1; $m <= $totalSemMonths; $m++) {
                    $mBn = strtr((string)$m, $bnDigits);
                    if ($pool >= $monthlyRate) {
                        $mStatus = 'PAID';
                        $mPaidAmt = $monthlyRate;
                        $mDueAmt = 0;
                        $pool -= $monthlyRate;
                    } elseif ($pool > 0) {
                        $mStatus = 'PARTIAL';
                        $mPaidAmt = $pool;
                        $mDueAmt = $monthlyRate - $pool;
                        $pool = 0;
                    } else {
                        $mStatus = 'UNPAID';
                        $mPaidAmt = 0;
                        $mDueAmt = $monthlyRate;
                    }

                    $monthlyItems[] = [
                        'month_no' => $m,
                        'label'    => "{$mBn}ম মাস (Month {$m})",
                        'payable'  => $monthlyRate,
                        'paid'     => $mPaidAmt,
                        'due'      => $mDueAmt,
                        'status'   => $mStatus,
                    ];
                }

                $semesterBreakdown->push([
                    'label'        => $sem->name . ($isRunning ? ' 🔵' : ''),
                    'category'     => 'SEMESTER',
                    'isRunning'    => $isRunning,
                    'payable'      => $semPayable,
                    'paid'         => $semPaid,
                    'due'          => (float) $semInvoices->sum('due_amount'),
                    'hasInvoice'   => $semInvoices->isNotEmpty(),
                    'invoice'      => $firstUnpaid,
                    'monthlyRate'  => $monthlyRate,
                    'totalMonths'  => $totalSemMonths,
                    'monthlyItems' => $monthlyItems,
                ]);
            }
        } else {
            // SUBJECT_BASED: show SEMESTER invoices as flat "Course Tuition Fee" rows (no semester split)
            $allSemInvoices = collect();
            foreach ($invoicesBySemester as $semId => $semInvs) {
                foreach ($semInvs as $si) $allSemInvoices->push($si);
            }
            foreach ($unassignedSemesterInvoices as $si) $allSemInvoices->push($si);

            $totalCourseMonths = 1;
            if ($course) {
                $totalCourseMonths = $course->duration_unit === 'YEAR' ? (int) round($course->duration_value * 12) : (int) round($course->duration_value);
                $totalCourseMonths = max(1, $totalCourseMonths);
            }

            foreach ($allSemInvoices->unique('id') as $sInv) {
                $sPayable = (float)$sInv->payable_amount;
                $sPaid    = (float)$sInv->paid_amount;
                $mRate    = ($totalCourseMonths > 0 && $sPayable > 0) ? round($sPayable / $totalCourseMonths, 2) : $sPayable;

                $pool = $sPaid;
                $monthlyItems = [];
                $bnDigits = ['0'=>'০','1'=>'১','2'=>'২','3'=>'৩','4'=>'৪','5'=>'৫','6'=>'৬','7'=>'৭','8'=>'৮','9'=>'৯'];
                if ($totalCourseMonths > 1) {
                    for ($m = 1; $m <= $totalCourseMonths; $m++) {
                        $mBn = strtr((string)$m, $bnDigits);
                        if ($pool >= $mRate) {
                            $mStatus = 'PAID';
                            $mPaidAmt = $mRate;
                            $mDueAmt = 0;
                            $pool -= $mRate;
                        } elseif ($pool > 0) {
                            $mStatus = 'PARTIAL';
                            $mPaidAmt = $pool;
                            $mDueAmt = $mRate - $pool;
                            $pool = 0;
                        } else {
                            $mStatus = 'UNPAID';
                            $mPaidAmt = 0;
                            $mDueAmt = $mRate;
                        }

                        $monthlyItems[] = [
                            'month_no' => $m,
                            'label'    => "{$mBn}ম মাস (Month {$m})",
                            'payable'  => $mRate,
                            'paid'     => $mPaidAmt,
                            'due'      => $mDueAmt,
                            'status'   => $mStatus,
                        ];
                    }
                }

                $semesterBreakdown->push([
                    'label'        => $sInv->title ?: 'কোর্স টিউশন ফি',
                    'category'     => 'SEMESTER',
                    'isRunning'    => true,
                    'payable'      => $sPayable,
                    'paid'         => $sPaid,
                    'due'          => (float)$sInv->due_amount,
                    'hasInvoice'   => true,
                    'invoice'      => $sInv,
                    'monthlyRate'  => $mRate,
                    'totalMonths'  => $totalCourseMonths,
                    'monthlyItems' => $monthlyItems,
                ]);
            }
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

        // Build itemized package fee breakdown per semester
        // For SUBJECT_BASED courses, show full package item amounts (no per-semester division)
        $totalSems = ($courseType === 'SUBJECT_BASED') ? 1 : max(1, $course?->semesters()->count() ?: 6);
        $packageItemsBreakdown = collect();

        if ($course) {
            $defaultPkg = $course->feePackages()->where('is_default', true)->first()
                ?? $course->feePackages()->first();

            if ($defaultPkg) {
                $pkgItems = $defaultPkg->items()->with('feeHead')->get();
                foreach ($pkgItems as $pi) {
                    $headName  = $pi->label ?: ($pi->feeHead?->name ?? 'Fee Item');
                    $qty       = (int) $pi->quantity;
                    $unitPrice = (float) $pi->amount_per_unit;
                    $totalAmt  = (float) $pi->total_amount;

                    $perSemAmt = round($totalAmt / $totalSems, 2);

                    $packageItemsBreakdown->push([
                        'name'            => $headName,
                        'unit_price'      => $unitPrice,
                        'total_package'   => $totalAmt,
                        'per_semester_amt'=> $perSemAmt,
                    ]);
                }
            }
        }

        // ── Program Activity Resolution for Dynamic Month Calculation ──
        $programActivity = \App\Models\ProgramActivity::where(function ($q) use ($course, $activeEnrollment) {
            if ($course && $activeEnrollment) {
                $q->where('course_id', $course->id)->where('batch_id', $activeEnrollment->batch_id);
            }
        })->orWhere(function ($q) use ($course) {
            if ($course) {
                $q->where('course_id', $course->id)->whereNull('batch_id');
            }
        })->orWhere(function ($q) {
            $q->whereNull('course_id')->whereNull('batch_id');
        })->first();

        $configuredStartMonth = $programActivity?->starting_month ?? 'July';
        $activityStartDate    = $programActivity?->start_date ? \Carbon\Carbon::parse($programActivity->start_date) : now();

        // ── Determine Selected Semester for Step 1 Dropdown ──
        $selectedSemesterId = request('semester_id');
        if (!$selectedSemesterId && $runningSemester) {
            $selectedSemesterId = $runningSemester->id;
        }
        if (!$selectedSemesterId && $allSemesters->isNotEmpty()) {
            $selectedSemesterId = $allSemesters->first()->id;
        }

        $selectedSemester = $allSemesters->firstWhere('id', $selectedSemesterId) ?? $runningSemester ?? $allSemesters->first();

        // Build Semester Dropdown Options (e.g. Semester 1, Semester 2, ...)
        $semesterDropdownOptions = [];
        if ($courseType === 'SEMESTER_BASED') {
            foreach ($allSemesters as $sem) {
                $semInvs   = collect($invoicesBySemester[$sem->id] ?? []);
                $semDue    = (float) $semInvs->sum('due_amount');
                $isRunning = ($runningSemester && $sem->id == $runningSemester->id);

                // Clean and numbered label: "Semester 1", "Semester 2", ...
                $semLabel = $sem->name;
                if ($isRunning) {
                    $semLabel .= ' (চলতি সেমিস্টার)';
                }

                $semesterDropdownOptions[] = [
                    'id'          => $sem->id,
                    'name'        => $sem->name,
                    'label'       => $semLabel,
                    'due'         => $semDue,
                    'isRunning'   => $isRunning,
                    'sequence_no' => $sem->sequence_no,
                ];
            }
        } else {
            // SUBJECT_BASED course: No semesters, flat course fee & all subjects option
            $semesterDropdownOptions[] = [
                'id'          => 0,
                'name'        => 'Full Course',
                'label'       => 'সম্পূর্ণ কোর্স ফি (Full Course / All Subjects)',
                'due'         => $totalDue,
                'isRunning'   => true,
                'sequence_no' => 1,
            ];
        }

        // Fallback option if empty
        if (empty($semesterDropdownOptions)) {
            $semesterDropdownOptions[] = [
                'id'          => 0,
                'name'        => 'Full Course',
                'label'       => 'সম্পূর্ণ কোর্স ফি (Full Course)',
                'due'         => $totalDue,
                'isRunning'   => true,
                'sequence_no' => 1,
            ];
        }

        // ── Prior Due Guard: Check if earlier semesters have unpaid dues (Semester-based only) ──
        $hasPriorSemesterDue   = false;
        $priorDueAmount        = 0.0;
        $priorDueSemesterName  = '';
        $priorDueSemesterId    = null;

        if ($courseType === 'SEMESTER_BASED') {
            $selectedSeq = $selectedSemester?->sequence_no ?? 1;

            foreach ($allSemesters as $sem) {
                if ($sem->sequence_no < $selectedSeq) {
                    $earlierInvs = collect($invoicesBySemester[$sem->id] ?? []);
                    $earlierDue  = (float) $earlierInvs->sum('due_amount');
                    if ($earlierDue > 0) {
                        $hasPriorSemesterDue = true;
                        $priorDueAmount += $earlierDue;
                        if (!$priorDueSemesterName) {
                            $priorDueSemesterName = $sem->name;
                            $priorDueSemesterId   = $sem->id;
                        }
                    }
                }
            }

            // Also check admission fee due if not in semester 1
            $admDue = (float) $admissionInvoices->sum('due_amount');
            if ($admDue > 0 && $selectedSeq > 1) {
                $hasPriorSemesterDue = true;
                $priorDueAmount += $admDue;
                if (!$priorDueSemesterName) {
                    $priorDueSemesterName = 'ভর্তি ফি (Admission Fee)';
                }
            }
        }

        // ── Generate Step 1 Particulars ──
        $step1Particulars = [];

        if ($courseType === 'SEMESTER_BASED') {
            $selectedSemInvoices = collect($invoicesBySemester[$selectedSemesterId] ?? []);
            $selectedSemesterInvoice = $selectedSemInvoices->first() ?? $nonCancelledInvoices->first();

            $targetPayable = (float) $selectedSemInvoices->sum('payable_amount');
            $targetPaid    = (float) $selectedSemInvoices->sum('paid_amount');
            $targetDue     = (float) $selectedSemInvoices->sum('due_amount');

            $monthlyTuition = 500.0;
            $midFeeAmt      = 300.0;
            $finalFeeAmt    = 500.0;

            if ($targetPayable > 0) {
                $unitRate       = $targetPayable / 7.6;
                $monthlyTuition = round($unitRate, 0);
                $midFeeAmt      = round($unitRate * 0.6, 0);
                $finalFeeAmt    = round($unitRate, 0);
            }

            // Start calendar month Carbon instance
            try {
                $monthNum = date('n', strtotime($configuredStartMonth . ' 1 2026'));
                $startCarbon = \Carbon\Carbon::createFromDate($activityStartDate->year, $monthNum, 1);
            } catch (\Throwable $e) {
                $startCarbon = \Carbon\Carbon::createFromDate(now()->year, 7, 1);
            }

            $paidPool = $targetPaid;
            $sl = 1;

            // Months 1 to 3
            for ($i = 0; $i < 3; $i++) {
                $cDate = $startCarbon->copy()->addMonths($i);
                $pName = 'Tuition Fee (' . $cDate->format('M-Y') . ')';
                $dueAmt = $monthlyTuition;
                $isPaid = false;
                $paidAmt = 0;

                if ($paidPool >= $dueAmt) {
                    $isPaid = true;
                    $paidAmt = $dueAmt;
                    $dueAmt = 0;
                    $paidPool -= $monthlyTuition;
                } elseif ($paidPool > 0) {
                    $paidAmt = $paidPool;
                    $dueAmt = $dueAmt - $paidPool;
                    $paidPool = 0;
                }

                $step1Particulars[] = [
                    'sl'       => $sl++,
                    'name'     => $pName,
                    'amount'   => $monthlyTuition,
                    'paid_amt' => $paidAmt,
                    'due'      => $dueAmt,
                    'is_paid'  => $isPaid,
                ];
            }

            // Mid Term Fee
            $midDue = $midFeeAmt;
            $midPaid = false;
            $midPaidAmt = 0;
            if ($paidPool >= $midDue) {
                $midPaid = true;
                $midPaidAmt = $midDue;
                $midDue = 0;
                $paidPool -= $midFeeAmt;
            } elseif ($paidPool > 0) {
                $midPaidAmt = $paidPool;
                $midDue = $midDue - $paidPool;
                $paidPool = 0;
            }
            $step1Particulars[] = [
                'sl'       => $sl++,
                'name'     => 'Mid Term Fee',
                'amount'   => $midFeeAmt,
                'paid_amt' => $midPaidAmt,
                'due'      => $midDue,
                'is_paid'  => $midPaid,
            ];

            // Months 4 to 6
            for ($i = 3; $i < 6; $i++) {
                $cDate = $startCarbon->copy()->addMonths($i);
                $pName = 'Tuition Fee (' . $cDate->format('M-Y') . ')';
                $dueAmt = $monthlyTuition;
                $isPaid = false;
                $paidAmt = 0;

                if ($paidPool >= $dueAmt) {
                    $isPaid = true;
                    $paidAmt = $dueAmt;
                    $dueAmt = 0;
                    $paidPool -= $monthlyTuition;
                } elseif ($paidPool > 0) {
                    $paidAmt = $paidPool;
                    $dueAmt = $dueAmt - $paidPool;
                    $paidPool = 0;
                }

                $step1Particulars[] = [
                    'sl'       => $sl++,
                    'name'     => $pName,
                    'amount'   => $monthlyTuition,
                    'paid_amt' => $paidAmt,
                    'due'      => $dueAmt,
                    'is_paid'  => $isPaid,
                ];
            }

            // Final Term Fee
            $finalDue = $finalFeeAmt;
            $finalPaid = false;
            $finalPaidAmt = 0;
            if ($paidPool >= $finalDue) {
                $finalPaid = true;
                $finalPaidAmt = $finalDue;
                $finalDue = 0;
                $paidPool -= $finalFeeAmt;
            } elseif ($paidPool > 0) {
                $finalPaidAmt = $paidPool;
                $finalDue = $finalDue - $paidPool;
                $paidPool = 0;
            }
            $step1Particulars[] = [
                'sl'       => $sl++,
                'name'     => 'Final Term Fee',
                'amount'   => $finalFeeAmt,
                'paid_amt' => $finalPaidAmt,
                'due'      => $finalDue,
                'is_paid'  => $finalPaid,
            ];
        } else {
            // ── SUBJECT_BASED COURSE PARTICULAR GENERATION ──
            $courseInvoices = $invoices->where('status', '!=', 'CANCELLED');
            $selectedSemesterInvoice = $courseInvoices->where('category', 'SEMESTER')->first()
                ?? $courseInvoices->where('category', 'MANUAL')->first()
                ?? $courseInvoices->first();

            $totalCourseMonths = 1;
            if ($course) {
                $totalCourseMonths = $course->duration_unit === 'YEAR' 
                    ? (int) round($course->duration_value * 12) 
                    : (int) round($course->duration_value);
                $totalCourseMonths = max(1, min(12, $totalCourseMonths));
            }

            $targetPayable = $selectedSemesterInvoice ? (float) $selectedSemesterInvoice->payable_amount : 0.0;
            $targetPaid    = $selectedSemesterInvoice ? (float) $selectedSemesterInvoice->paid_amount : 0.0;
            $targetDue     = $selectedSemesterInvoice ? (float) $selectedSemesterInvoice->due_amount : 0.0;

            $sl = 1;
            $paidPool = $targetPaid;

            if ($totalCourseMonths > 1 && $targetPayable > 0) {
                $monthlyRate = round($targetPayable / $totalCourseMonths, 2);
                $startCarbon = $activityStartDate->copy();

                for ($i = 0; $i < $totalCourseMonths; $i++) {
                    $cDate   = $startCarbon->copy()->addMonths($i);
                    $pName   = 'Course Tuition Fee (' . $cDate->format('M-Y') . ')';
                    $dueAmt  = $monthlyRate;
                    $isPaid  = false;
                    $paidAmt = 0;

                    if ($paidPool >= $dueAmt) {
                        $isPaid = true;
                        $paidAmt = $dueAmt;
                        $dueAmt = 0;
                        $paidPool -= $monthlyRate;
                    } elseif ($paidPool > 0) {
                        $paidAmt = $paidPool;
                        $dueAmt = $dueAmt - $paidPool;
                        $paidPool = 0;
                    }

                    $step1Particulars[] = [
                        'sl'       => $sl++,
                        'name'     => $pName,
                        'amount'   => $monthlyRate,
                        'paid_amt' => $paidAmt,
                        'due'      => $dueAmt,
                        'is_paid'  => $isPaid,
                    ];
                }
            } elseif ($targetPayable > 0) {
                $step1Particulars[] = [
                    'sl'       => $sl++,
                    'name'     => $selectedSemesterInvoice->title ?: 'Course Tuition Fee',
                    'amount'   => $targetPayable,
                    'paid_amt' => $targetPaid,
                    'due'      => $targetDue,
                    'is_paid'  => $targetDue <= 0,
                ];
            } else {
                $step1Particulars[] = [
                    'sl'       => $sl++,
                    'name'     => $course ? "{$course->name} Tuition Fee" : 'Course Tuition Fee',
                    'amount'   => 0,
                    'paid_amt' => 0,
                    'due'      => 0,
                    'is_paid'  => true,
                ];
            }
        }

        // Apply custom_particulars overrides from $selectedSemesterInvoice
        $customOverrides = $selectedSemesterInvoice?->custom_particulars ?? [];
        if (!empty($customOverrides)) {
            foreach ($step1Particulars as &$item) {
                if (isset($customOverrides[$item['name']])) {
                    $cDue = (float) $customOverrides[$item['name']]['due'];
                    $item['due'] = $cDue;
                    if ($cDue <= 0) {
                        $item['is_paid'] = true;
                    } else {
                        $item['is_paid'] = false;
                    }
                    $item['is_custom'] = true;
                    if (isset($customOverrides[$item['name']]['remarks'])) {
                        $item['custom_remarks'] = $customOverrides[$item['name']]['remarks'];
                    }
                }
            }
            unset($item);
        }

        $sslActive   = \App\Services\PaymentGatewayService::isSslcommerzActive();
        $bkashActive = \App\Services\PaymentGatewayService::isBkashActive();

        return view('student.fees.index', compact(
            'student', 'course', 'courseType', 'runningSemester', 'runningSemesterName',
            'invoices', 'payments', 'totalDue', 'totalPaid', 'runningSemesterDue',
            'semesterBreakdown', 'studentCourses', 'packageItemsBreakdown',
            'sslActive', 'bkashActive',
            'programActivity', 'semesterDropdownOptions', 'selectedSemesterId',
            'selectedSemester', 'hasPriorSemesterDue', 'priorDueAmount',
            'priorDueSemesterName', 'priorDueSemesterId', 'step1Particulars',
            'selectedSemesterInvoice'
        ));
    }

    /**
     * Submit payment for an invoice from Student Portal (Online Gateway or Offline).
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
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string|max:100',
            'sender_number'  => 'nullable|string|max:30',
            'remarks'        => 'nullable|string|max:255',
        ]);

        $method = strtolower($validated['payment_method']);

        // 1. Direct Online Payment Gateways (bKash & SSLCommerz)
        if (in_array($method, ['bkash', 'sslcommerz'])) {
            $user = auth()->user();
            $sslActive = \App\Services\PaymentGatewayService::isSslcommerzActive();
            $bkashActive = \App\Services\PaymentGatewayService::isBkashActive();

            if ($method === 'bkash' && !$bkashActive) {
                return back()->with('error', 'বিকাশ গেটওয়ে বর্তমানে নিষ্ক্রিয় রয়েছে। অনুগ্রহ করে অন্য মাধ্যম ব্যবহার করুন।');
            }
            if ($method === 'sslcommerz' && !$sslActive) {
                return back()->with('error', 'SSLCommerz গেটওয়ে বর্তমানে নিষ্ক্রিয় রয়েছে। অনুগ্রহ করে অন্য মাধ্যম ব্যবহার করুন।');
            }

            $gatewayMode = ($method === 'bkash')
                ? \App\Services\PaymentGatewayService::getBkashConfig()['mode']
                : \App\Services\PaymentGatewayService::getSslcommerzConfig()['mode'];

            $transaction = \App\Models\GatewayTransaction::create([
                'tran_id'           => \App\Models\GatewayTransaction::generateTranId('FEE'),
                'gateway'           => $method,
                'gateway_mode'      => $gatewayMode,
                'invoice_id'        => $invoice->id,
                'student_id'        => $student->id,
                'amount'            => (float) $validated['amount'],
                'currency'          => 'BDT',
                'customer_name'     => $student->name,
                'customer_phone'    => $student->phone,
                'customer_email'    => $student->email ?: $user?->email,
                'status'            => 'INITIATED',
                'ip_address'        => $request->ip(),
            ]);

            if ($method === 'sslcommerz') {
                $initRes = \App\Services\PaymentGatewayService::initiateSslcommerz(
                    $transaction,
                    null,
                    $student->phone,
                    $student->email ?: $user?->email,
                    $student->name,
                    "Student Fee Payment - " . ($invoice->title ?: $invoice->invoice_no)
                );
            } else {
                $initRes = \App\Services\PaymentGatewayService::initiateBkash(
                    $transaction,
                    null,
                    $student->phone
                );
            }

            if (!empty($initRes['success']) && !empty($initRes['redirect_url'])) {
                return redirect()->away($initRes['redirect_url']);
            }

            return back()->with('error', $initRes['message'] ?? 'পেমেন্ট গেটওয়েতে সংযোগ করতে সমস্যা হয়েছে।');
        }

        // 2. Manual / Offline Payment (bKash Manual, Cash, Bank Transfer, Offline TrxID)
        $payment = \App\Services\AccountingService::submitStudentPayment(
            $invoice,
            (float) $validated['amount'],
            strtoupper($validated['payment_method']),
            $validated['transaction_id'] ?? null,
            $validated['remarks'] ?? null,
            $validated['sender_number'] ?? null
        );

        return back()->with('success', '⏳ পেমেন্ট ট্রানজেকশন সফলভাবে জমা দেওয়া হয়েছে! অ্যাডমিন অনুমোদন করার সাথে সাথে ফি রসিদ ও বকেয়া আপডেট হয়ে যাবে।');
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

    /**
     * Admin manual fee particular override (Taka increase / decrease & save).
     */
    public function updateParticular(Request $request)
    {
        $isAdmin = session()->has('admin_impersonator_id') 
            || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'ADMIN'));

        if (!$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'অননুমোদিত অনুরোধ। শুধুমাত্র অ্যাডমিন ফি পরিবর্তন করতে পারবেন।'
            ], 403);
        }

        $validated = $request->validate([
            'invoice_id'      => 'required|exists:invoices,id',
            'particular_name' => 'required|string',
            'new_amount'      => 'required|numeric|min:0',
            'current_due'     => 'nullable|numeric|min:0',
            'remarks'         => 'nullable|string|max:255',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);
        $pName   = trim($validated['particular_name']);
        $newDue  = round((float)$validated['new_amount'], 2);

        $custom = $invoice->custom_particulars ?? [];
        $oldDue = isset($custom[$pName]['due']) ? (float)$custom[$pName]['due'] : (float)($request->input('current_due') ?? $newDue);

        $diff = $newDue - $oldDue;

        $custom[$pName] = [
            'due'         => $newDue,
            'adjusted_at' => now()->toDateTimeString(),
            'adjusted_by' => session('admin_impersonator_id') ?? auth()->id(),
            'remarks'     => $validated['remarks'] ?? 'Admin manual adjustment',
        ];

        $newPayable = max(0, $invoice->payable_amount + $diff);
        $newDueAmt  = max(0, $invoice->due_amount + $diff);

        $status = 'UNPAID';
        if ($newDueAmt <= 0 && $invoice->paid_amount > 0) {
            $status = 'PAID';
        } elseif ($invoice->paid_amount > 0) {
            $status = 'PARTIAL';
        }

        $invoice->update([
            'custom_particulars' => $custom,
            'payable_amount'     => $newPayable,
            'due_amount'         => $newDueAmt,
            'status'             => $status,
        ]);

        try {
            \App\Models\AuditLog::log(
                'fee_particular_adjusted',
                $invoice,
                ['old_due' => $oldDue],
                ['new_due' => $newDue, 'particular' => $pName, 'remarks' => $validated['remarks']],
                "অ্যাডমিন {$pName} ফি ৳{$oldDue} থেকে পরিবর্তন করে ৳{$newDue} করেছেন।"
            );
        } catch (\Throwable $e) {}

        return response()->json([
            'success'       => true,
            'message'       => "✓ {$pName} এর ফি সফলভাবে ৳" . number_format($newDue, 0) . " এ আপডেট এবং সেভ করা হয়েছে।",
            'particular'    => $pName,
            'new_due'       => $newDue,
            'is_paid'       => $newDue <= 0,
            'invoice_due'   => $newDueAmt,
            'invoice_id'    => $invoice->id,
        ]);
    }
}
