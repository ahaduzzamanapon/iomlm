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

        // ── Resolve Student's Fee Package & Batch Timing ──
        $batch = $activeEnrollment?->batch;
        $studentFeePackage = $student->feePackage;
        if (!$studentFeePackage && $student->fee_package_id) {
            $studentFeePackage = \App\Models\CourseFeePackage::find($student->fee_package_id);
        }
        if (!$studentFeePackage) {
            $admForm = \App\Models\AdmissionForm::where('student_id', $student->id)
                ->whereNotNull('waiver_code')
                ->latest()
                ->first();
            $waiverCode = $admForm?->waiver_code;
            if ($waiverCode) {
                $altWaiverCode = str_starts_with($waiverCode, 'PF-')
                    ? str_replace('PF-', 'POOR-', $waiverCode)
                    : (str_starts_with($waiverCode, 'POOR-') ? str_replace('POOR-', 'PF-', $waiverCode) : $waiverCode);
                $waiverApp = \App\Models\WaiverApplication::where(function ($q) use ($waiverCode, $altWaiverCode) {
                        $q->where('application_no', $waiverCode)->orWhere('application_no', $altWaiverCode);
                    })
                    ->where('status', 'APPROVED')
                    ->whereNotNull('approved_package_id')
                    ->first();
                if ($waiverApp) {
                    $studentFeePackage = \App\Models\CourseFeePackage::find($waiverApp->approved_package_id);
                }
            }
        }
        if (!$studentFeePackage && $student->user?->email) {
            $waiverApp = \App\Models\WaiverApplication::where('email', $student->user->email)
                ->where('status', 'APPROVED')
                ->whereNotNull('approved_package_id')
                ->latest()
                ->first();
            if ($waiverApp) {
                $studentFeePackage = \App\Models\CourseFeePackage::find($waiverApp->approved_package_id);
            }
        }
        if (!$studentFeePackage) {
            $existingSemInv = \App\Models\Invoice::where('student_id', $student->id)
                ->where('category', 'SEMESTER')
                ->latest()
                ->first();
            if ($existingSemInv && preg_match('/\(([^)]+)\)\s*(?:\([^)]+\))?$/', $existingSemInv->title, $pm)) {
                $pkgName = trim($pm[1]);
                $studentFeePackage = \App\Models\CourseFeePackage::where('name', $pkgName)->first();
            }
        }
        if (!$studentFeePackage && $course) {
            $studentFeePackage = $course->feePackages()->where('is_default', true)->first()
                ?? $course->feePackages()->first();
        }
        if ($studentFeePackage && !$student->fee_package_id) {
            $student->update(['fee_package_id' => $studentFeePackage->id]);
        }

        // ── Resolve Academic Year & Timing ──
        $academicYear = $batch?->academicYear 
            ?: ($batch?->academic_year_id ? \App\Models\AcademicYear::find($batch->academic_year_id) : null);

        $academicStartYear = null;
        if ($academicYear?->start_date) {
            $academicStartYear = (int) \Carbon\Carbon::parse($academicYear->start_date)->year;
        } elseif ($academicYear?->name && preg_match('/\b(20\d{2})\b/', $academicYear->name, $ym)) {
            $academicStartYear = (int) $ym[1];
        } elseif ($student?->student_code && strlen($student->student_code) >= 2) {
            $twoDigitYear = substr($student->student_code, 0, 2);
            if (is_numeric($twoDigitYear) && (int)$twoDigitYear >= 20 && (int)$twoDigitYear <= 99) {
                $academicStartYear = (int) ('20' . $twoDigitYear);
                if (!$academicYear) {
                    $academicYear = \App\Models\AcademicYear::where('start_date', 'like', "{$academicStartYear}%")
                        ->orWhere('name', 'like', "%{$academicStartYear}%")
                        ->first();
                }
            }
        } elseif ($batch?->start_date) {
            $academicStartYear = (int) \Carbon\Carbon::parse($batch->start_date)->year;
        }

        if (!$academicYear && $batch) {
            $academicYear = \App\Models\AcademicYear::where('is_active', 1)->first();
        }

        // Batch / Program Activity Timing Resolution
        $batchStartMonth = $batch?->fee_start_month;
        if (!$batchStartMonth) {
            $batchStartMonth = $batch?->start_month;
        }
        if (!$batchStartMonth && $academicYear?->start_date) {
            $batchStartMonth = \Carbon\Carbon::parse($academicYear->start_date)->format('F');
        }
        if (!$batchStartMonth) {
            $batchStartMonth = $course?->fee_start_month ?: ($course?->start_month ?: null);
        }
        if (!$batchStartMonth) {
            $batchStartMonth = 'January';
        }

        $batchStartDate = null;
        if ($batch?->start_date) {
            $batchStartDate = \Carbon\Carbon::parse($batch->start_date);
        } elseif ($academicYear?->start_date) {
            $batchStartDate = \Carbon\Carbon::parse($academicYear->start_date);
        } elseif ($course?->start_date) {
            $batchStartDate = \Carbon\Carbon::parse($course->start_date);
        }

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

        $configuredStartMonth = $batchStartMonth
            ?? $programActivity?->starting_month
            ?? 'January';

        $activityStartDate = $batchStartDate
            ?? ($programActivity?->start_date ? \Carbon\Carbon::parse($programActivity->start_date) : null)
            ?? now();

        try {
            $monthNum   = (int) date('n', strtotime($configuredStartMonth . ' 1 2000'));
            $startYear  = (int) ($academicStartYear ?: ($activityStartDate?->year ?: now()->year));
            $baseCarbon = \Carbon\Carbon::createFromDate($startYear, $monthNum, 1);
        } catch (\Throwable $e) {
            $baseCarbon = \Carbon\Carbon::createFromDate(now()->year, 1, 1);
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

                $tuitionItem = $studentFeePackage?->items?->first(function ($it) {
                    return $it->feeHead?->slug === 'tuition_fee' || str_contains(mb_strtolower($it->label ?? ''), 'tuition');
                });
                $packageMonthlyRate = 0.0;
                if ($tuitionItem && $tuitionItem->amount_per_unit > 0) {
                    $packageMonthlyRate = (float) $tuitionItem->amount_per_unit;
                } elseif ($tuitionItem && $tuitionItem->months_count > 0) {
                    $packageMonthlyRate = round((float) $tuitionItem->total_amount / $tuitionItem->months_count, 2);
                }

                if ($packageMonthlyRate > 0) {
                    $monthlyRate = $packageMonthlyRate;
                } elseif ($batch?->monthly_fee > 0) {
                    $monthlyRate = (float) $batch->monthly_fee;
                } else {
                    $monthlyRate = $totalSemMonths > 0 && $semPayable > 0 ? round($semPayable / $totalSemMonths, 2) : 500;
                }

                $semStartCarbon = $baseCarbon->copy()->addMonths((($sem->sequence_no ?: 1) - 1) * 6);
                $pool = $semPaid;
                $monthlyItems = [];
                $bnOrdinals = [1 => '১ম', 2 => '২য়', 3 => '৩য়', 4 => '৪র্থ', 5 => '৫ম', 6 => '৬ষ্ঠ', 7 => '৭ম', 8 => '৮ম', 9 => '৯ম', 10 => '১০ম', 11 => '১১শ', 12 => '১২শ'];
                for ($m = 1; $m <= $totalSemMonths; $m++) {
                    $mBn = $bnOrdinals[$m] ?? "{$m}ম";
                    $cMonth = $semStartCarbon->copy()->addMonths($m - 1);
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
                        'label'    => "{$mBn} মাস (" . $cMonth->format('M-Y') . ")",
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
            $defaultPkg = $studentFeePackage ?? ($course->feePackages()->where('is_default', true)->first()
                ?? $course->feePackages()->first());

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

        // ── Determine Selected Semester for Step 1 Dropdown ──
        $selectedSemesterId = request('semester_id');
        if (!$selectedSemesterId && $runningSemester) {
            $selectedSemesterId = $runningSemester->id;
        }
        if (!$selectedSemesterId && $allSemesters->isNotEmpty()) {
            $selectedSemesterId = $allSemesters->first()->id;
        }

        if ($selectedSemesterId === 'admission') {
            $selectedSemester = (object)[
                'id'          => 'admission',
                'name'        => 'ভর্তি ফি (Admission Fee)',
                'sequence_no' => 0,
            ];
        } else {
            $selectedSemester = $allSemesters->firstWhere('id', $selectedSemesterId) ?? $runningSemester ?? $allSemesters->first();
        }

        // Build Semester Dropdown Options (e.g. Admission Fee, Semester 1, Semester 2, ...)
        $semesterDropdownOptions = [];
        if ($courseType === 'SEMESTER_BASED') {
            $admDue = (float) $admissionInvoices->sum('due_amount');
            $admLabel = 'ভর্তি ফি (Admission Fee)';
            if ($admDue > 0) {
                $admLabel .= ' (বকেয়া: ৳' . number_format($admDue, 0) . ')';
            } else {
                $admLabel .= ' (পরিশোধিত)';
            }
            $semesterDropdownOptions[] = [
                'id'          => 'admission',
                'name'        => 'Admission Fee',
                'label'       => $admLabel,
                'due'         => $admDue,
                'isRunning'   => false,
                'sequence_no' => 0,
            ];

            foreach ($allSemesters as $sem) {
                $semInvs   = collect($invoicesBySemester[$sem->id] ?? []);
                $semDue    = (float) $semInvs->sum('due_amount');
                $isRunning = ($runningSemester && $sem->id == $runningSemester->id);

                // Clean and numbered label: "Semester 1", "Semester 2", ...
                $semLabel = $sem->name;
                if ($sem->sequence_no == 3) {
                    $semLabel .= ' (১ম বার্ষিক ফি সহ)';
                } elseif ($sem->sequence_no == 5) {
                    $semLabel .= ' (২য় বার্ষিক ফি সহ)';
                }
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
        $totalSemesters   = max(1, $course?->semesters()->count() ?: 6);
        $monthlyTuition   = 500.0;
        $midFeeAmt        = 0.0;
        $finalFeeAmt      = 0.0;

        if ($studentFeePackage) {
            $tuitionItem = $studentFeePackage->items->first(function ($it) {
                return $it->feeHead?->slug === 'tuition_fee' || str_contains(mb_strtolower($it->label ?? ''), 'tuition');
            });
            $midItem = $studentFeePackage->items->first(function ($it) {
                return $it->feeHead?->slug === 'mid_term_fee' || str_contains(mb_strtolower($it->label ?? ''), 'mid');
            });
            $finalItem = $studentFeePackage->items->first(function ($it) {
                return $it->feeHead?->slug === 'final_term_fee' || str_contains(mb_strtolower($it->label ?? ''), 'final');
            });

            if ($tuitionItem && $tuitionItem->amount_per_unit > 0) {
                $monthlyTuition = (float) $tuitionItem->amount_per_unit;
            } elseif ($tuitionItem && $tuitionItem->months_count > 0) {
                $monthlyTuition = round((float) $tuitionItem->total_amount / $tuitionItem->months_count, 2);
            } elseif ($tuitionItem && $totalSemesters > 0) {
                $monthlyTuition = round((float) $tuitionItem->total_amount / ($totalSemesters * 6), 2);
            }

            if ($midItem) {
                if ($midItem->amount_mode === 'PER_SEMESTER' && $midItem->amount_per_unit > 0) {
                    $midFeeAmt = (float) $midItem->amount_per_unit;
                } elseif ($totalSemesters > 0 && $midItem->total_amount > 0) {
                    $midFeeAmt = round((float) $midItem->total_amount / $totalSemesters, 2);
                } else {
                    $midFeeAmt = (float) ($midItem->amount_per_unit ?? 0);
                }
            }

            if ($finalItem) {
                if ($finalItem->amount_mode === 'PER_SEMESTER' && $finalItem->amount_per_unit > 0) {
                    $finalFeeAmt = (float) $finalItem->amount_per_unit;
                } elseif ($totalSemesters > 0 && $finalItem->total_amount > 0) {
                    $finalFeeAmt = round((float) $finalItem->total_amount / $totalSemesters, 0);
                } else {
                    $finalFeeAmt = (float) ($finalItem->amount_per_unit ?? 0);
                }
            }
        } elseif ($batch?->monthly_fee > 0) {
            $monthlyTuition = (float) $batch->monthly_fee;
        }

        if ($selectedSemesterId === 'admission') {
            $admInv = $admissionInvoices->first();
            $admDue = (float) $admissionInvoices->sum('due_amount');
            $admPaid = (float) $admissionInvoices->sum('paid_amount');
            $admPayable = (float) $admissionInvoices->sum('payable_amount');
            $pkgAdmItem = $studentFeePackage?->items?->first(fn($it) => $it->feeHead?->slug === 'admission_fee' || str_contains(mb_strtolower($it->label ?? ''), 'admission'));
            $nominalAmt = (float) ($admInv?->amount > 0 ? $admInv->amount : ($admPayable > 0 ? $admPayable : ($pkgAdmItem?->amount_per_unit ?: ($course?->admission_fee ?: 1500))));

            $isPaid = ($admInv && $admInv->status === 'PAID') || ($admDue <= 0 && $admInv !== null);
            $paidAmt = $isPaid ? ($admPaid > 0 ? $admPaid : $nominalAmt) : $admPaid;

            $step1Particulars[] = [
                'sl'         => 1,
                'name'       => 'Admission Fee (ভর্তি ফি)',
                'amount'     => $nominalAmt,
                'paid_amt'   => $paidAmt,
                'due'        => $admDue,
                'is_paid'    => $isPaid,
                'invoice_id' => $admInv?->id,
                'invoice_no' => $admInv?->invoice_no,
            ];
            $selectedSemesterInvoice = $admInv;
        } elseif ($courseType === 'SEMESTER_BASED') {
            $selectedSemInvoices = collect($invoicesBySemester[$selectedSemesterId] ?? []);
            $selectedSemesterInvoice = $selectedSemInvoices->first();

            $targetPayable = (float) $selectedSemInvoices->sum('payable_amount');
            $targetPaid    = (float) $selectedSemInvoices->sum('paid_amount');
            $targetDue     = (float) $selectedSemInvoices->sum('due_amount');

            if (!$studentFeePackage && !$batch?->monthly_fee && $targetPayable > 0) {
                $monthlyTuition = round($targetPayable / 6, 2);
            }

            // Start calendar month Carbon instance with semester offset
            try {
                $selectedSeq = (int) ($selectedSemester?->sequence_no ?? 1);
                $startCarbon = $baseCarbon->copy()->addMonths(($selectedSeq - 1) * 6);
            } catch (\Throwable $e) {
                $startCarbon = \Carbon\Carbon::createFromDate(now()->year, 1, 1);
            }

            $paidPool = $targetPaid;
            $sl = 1;

            // Prepend Admission Fee row in Semester 1 (Admission period)
            if (($selectedSemester?->sequence_no ?? 1) == 1) {
                $admInv = $admissionInvoices->first();
                $admDue = (float) $admissionInvoices->sum('due_amount');
                $admPaid = (float) $admissionInvoices->sum('paid_amount');
                $admPayable = (float) $admissionInvoices->sum('payable_amount');
                $pkgAdmItem = $studentFeePackage?->items?->first(fn($it) => $it->feeHead?->slug === 'admission_fee' || str_contains(mb_strtolower($it->label ?? ''), 'admission'));
                $nominalAmt = (float) ($admInv?->amount > 0 ? $admInv->amount : ($admPayable > 0 ? $admPayable : ($pkgAdmItem?->amount_per_unit ?: ($course?->admission_fee ?: 1500))));

                $isPaid = ($admInv && $admInv->status === 'PAID') || ($admDue <= 0 && $admInv !== null);
                $paidAmt = $isPaid ? ($admPaid > 0 ? $admPaid : $nominalAmt) : $admPaid;

                $step1Particulars[] = [
                    'sl'         => $sl++,
                    'name'       => 'Admission Fee (ভর্তি ফি)',
                    'amount'     => $nominalAmt,
                    'paid_amt'   => $paidAmt,
                    'due'        => $admDue,
                    'is_paid'    => $isPaid,
                    'invoice_id' => $admInv?->id,
                    'invoice_no' => $admInv?->invoice_no,
                ];
            }

            // Prepend 1st Annual Fee row in Semester 3 (Start of 2nd year)
            if (($selectedSemester?->sequence_no ?? 1) == 3) {
                $annual1Item = $studentFeePackage?->items?->first(function ($it) {
                    return ($it->feeHead && $it->feeHead->slug === '1st_annual_fee')
                        || str_contains(mb_strtolower($it->label ?? ''), '1st annual')
                        || (str_contains(mb_strtolower($it->label ?? ''), 'annual') && !str_contains(mb_strtolower($it->label ?? ''), '2nd'));
                });
                $annual1Amount = (float) ($annual1Item?->total_amount > 0 ? $annual1Item->total_amount : ($annual1Item?->amount_per_unit ?? 0));
                if ($annual1Amount <= 0 && $course) {
                    $defPkg = $course->feePackages()->where('is_default', true)->first();
                    $defItem = $defPkg?->items?->first(fn($it) => ($it->feeHead && $it->feeHead->slug === '1st_annual_fee') || str_contains(mb_strtolower($it->label ?? ''), '1st annual'));
                    $annual1Amount = (float) ($defItem?->total_amount > 0 ? $defItem->total_amount : ($defItem?->amount_per_unit ?? 0));
                }

                if ($annual1Amount > 0) {
                    $isPaid = false;
                    $paidAmt = 0;
                    $dueAmt = $annual1Amount;
                    if ($paidPool >= $dueAmt) {
                        $isPaid = true;
                        $paidAmt = $dueAmt;
                        $dueAmt = 0;
                        $paidPool -= $annual1Amount;
                    } elseif ($paidPool > 0) {
                        $paidAmt = $paidPool;
                        $dueAmt -= $paidPool;
                        $paidPool = 0;
                    }
                    $step1Particulars[] = [
                        'sl'         => $sl++,
                        'name'       => '১ম বার্ষিক ফি (1st Annual Fee)',
                        'amount'     => $annual1Amount,
                        'paid_amt'   => $paidAmt,
                        'due'        => $dueAmt,
                        'is_paid'    => $isPaid,
                        'invoice_id' => $selectedSemesterInvoice?->id,
                        'invoice_no' => $selectedSemesterInvoice?->invoice_no,
                    ];
                }
            }

            // Prepend 2nd Annual Fee row in Semester 5 (Start of 3rd year)
            if (($selectedSemester?->sequence_no ?? 1) == 5) {
                $annual2Item = $studentFeePackage?->items?->first(function ($it) {
                    return ($it->feeHead && $it->feeHead->slug === '2nd_annual_fee')
                        || str_contains(mb_strtolower($it->label ?? ''), '2nd annual');
                });
                $annual2Amount = (float) ($annual2Item?->total_amount > 0 ? $annual2Item->total_amount : ($annual2Item?->amount_per_unit ?? 0));
                if ($annual2Amount <= 0 && $course) {
                    $defPkg = $course->feePackages()->where('is_default', true)->first();
                    $defItem = $defPkg?->items?->first(fn($it) => ($it->feeHead && $it->feeHead->slug === '2nd_annual_fee') || str_contains(mb_strtolower($it->label ?? ''), '2nd annual'));
                    $annual2Amount = (float) ($defItem?->total_amount > 0 ? $defItem->total_amount : ($defItem?->amount_per_unit ?? 0));
                }

                if ($annual2Amount > 0) {
                    $isPaid = false;
                    $paidAmt = 0;
                    $dueAmt = $annual2Amount;
                    if ($paidPool >= $dueAmt) {
                        $isPaid = true;
                        $paidAmt = $dueAmt;
                        $dueAmt = 0;
                        $paidPool -= $annual2Amount;
                    } elseif ($paidPool > 0) {
                        $paidAmt = $paidPool;
                        $dueAmt -= $paidPool;
                        $paidPool = 0;
                    }
                    $step1Particulars[] = [
                        'sl'         => $sl++,
                        'name'       => '২য় বার্ষিক ফি (2nd Annual Fee)',
                        'amount'     => $annual2Amount,
                        'paid_amt'   => $paidAmt,
                        'due'        => $dueAmt,
                        'is_paid'    => $isPaid,
                        'invoice_id' => $selectedSemesterInvoice?->id,
                        'invoice_no' => $selectedSemesterInvoice?->invoice_no,
                    ];
                }
            }

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
                    'sl'         => $sl++,
                    'name'       => $pName,
                    'amount'     => $monthlyTuition,
                    'paid_amt'   => $paidAmt,
                    'due'        => $dueAmt,
                    'is_paid'    => $isPaid,
                    'invoice_id' => $selectedSemesterInvoice?->id,
                    'invoice_no' => $selectedSemesterInvoice?->invoice_no,
                ];
            }

            // Mid Term Fee (if configured)
            if ($midFeeAmt > 0) {
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
                    'sl'         => $sl++,
                    'name'       => 'Mid Term Fee',
                    'amount'     => $midFeeAmt,
                    'paid_amt'   => $midPaidAmt,
                    'due'        => $midDue,
                    'is_paid'    => $midPaid,
                    'invoice_id' => $selectedSemesterInvoice?->id,
                    'invoice_no' => $selectedSemesterInvoice?->invoice_no,
                ];
            }

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
                    'sl'         => $sl++,
                    'name'       => $pName,
                    'amount'     => $monthlyTuition,
                    'paid_amt'   => $paidAmt,
                    'due'        => $dueAmt,
                    'is_paid'    => $isPaid,
                    'invoice_id' => $selectedSemesterInvoice?->id,
                    'invoice_no' => $selectedSemesterInvoice?->invoice_no,
                ];
            }

            // Final Term Fee (if configured)
            if ($finalFeeAmt > 0) {
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
                    'sl'         => $sl++,
                    'name'       => 'Final Term Fee',
                    'amount'     => $finalFeeAmt,
                    'paid_amt'   => $finalPaidAmt,
                    'due'        => $finalDue,
                    'is_paid'    => $finalPaid,
                    'invoice_id' => $selectedSemesterInvoice?->id,
                    'invoice_no' => $selectedSemesterInvoice?->invoice_no,
                ];
            }
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
                $startCarbon = $baseCarbon->copy();

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
        $customOverrides = ($selectedSemesterInvoice && $selectedSemesterInvoice->source_id == $selectedSemesterId)
            ? ($selectedSemesterInvoice->custom_particulars ?? [])
            : [];
        if (!empty($customOverrides)) {
            $existingNames = [];
            foreach ($step1Particulars as &$item) {
                $existingNames[] = $item['name'];
                if (isset($customOverrides[$item['name']])) {
                    $cDue = (float) $customOverrides[$item['name']]['due'];
                    $item['due'] = $cDue;
                    if ($cDue <= 0) {
                        $item['is_paid'] = true;
                    } else {
                        $item['is_paid'] = false;
                    }
                    $item['is_custom'] = true;
                    $item['is_added']  = !empty($customOverrides[$item['name']]['is_added']);
                    if (isset($customOverrides[$item['name']]['remarks'])) {
                        $item['custom_remarks'] = $customOverrides[$item['name']]['remarks'];
                    }
                }
            }
            unset($item);

            // Also include any newly added custom particulars that weren't in default step1Particulars
            foreach ($customOverrides as $cName => $cData) {
                if (!in_array($cName, $existingNames, true)) {
                    $cDue = (float) ($cData['due'] ?? 0);
                    $cAmt = (float) ($cData['amount'] ?? $cDue);
                    $step1Particulars[] = [
                        'sl'             => $sl++,
                        'name'           => $cName,
                        'amount'         => $cAmt,
                        'paid_amt'       => max(0, $cAmt - $cDue),
                        'due'            => $cDue,
                        'is_paid'        => $cDue <= 0,
                        'is_custom'      => true,
                        'is_added'       => true,
                        'custom_remarks' => $cData['remarks'] ?? 'অ্যাডমিন কর্তৃক যুক্ত ফি',
                    ];
                }
            }
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
            'selectedSemesterInvoice', 'monthlyTuition', 'batch', 'academicYear'
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

    /**
     * Admin adds a new fee particular to an invoice.
     */
    public function storeParticular(Request $request)
    {
        $isAdmin = session()->has('admin_impersonator_id') 
            || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'ADMIN'));

        if (!$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'অননুমোদিত অনুরোধ। শুধুমাত্র অ্যাডমিন নতুন ফি যোগ করতে পারবেন।'
            ], 403);
        }

        $validated = $request->validate([
            'invoice_id'      => 'nullable|exists:invoices,id',
            'student_id'      => 'nullable|exists:students,id',
            'semester_id'     => 'nullable|integer',
            'particular_name' => 'required|string|max:150',
            'amount'          => 'required|numeric|min:1',
            'remarks'         => 'nullable|string|max:255',
        ]);

        $pName   = trim($validated['particular_name']);
        $amount  = round((float)$validated['amount'], 2);
        $remarks = $validated['remarks'] ?? 'অ্যাডমিন কর্তৃক নতুন ফি যুক্ত';

        $invoice = null;
        if (!empty($validated['invoice_id'])) {
            $invoice = Invoice::find($validated['invoice_id']);
        }

        if (!$invoice) {
            $studentId = $validated['student_id'] ?? Student::where('user_id', auth()->id())->value('id');
            $student = Student::findOrFail($studentId);
            $enrollment = $student->enrollments()->where('status', 'ACTIVE')->first() ?? $student->enrollments()->first();

            $invNo = 'INV-MAN-' . date('Ymd') . '-' . rand(1000, 9999);
            $invoice = Invoice::create([
                'invoice_no'         => $invNo,
                'student_id'         => $student->id,
                'enrollment_id'      => $enrollment?->id,
                'category'           => !empty($validated['semester_id']) ? 'SEMESTER' : 'MANUAL',
                'title'              => $pName,
                'amount'             => 0,
                'discount'           => 0,
                'payable_amount'     => 0,
                'paid_amount'        => 0,
                'due_amount'         => 0,
                'status'             => 'UNPAID',
                'due_date'           => now()->addDays(15),
                'source_type'        => !empty($validated['semester_id']) ? \App\Models\Semester::class : null,
                'source_id'          => $validated['semester_id'] ?? null,
                'created_by'         => session('admin_impersonator_id') ?? auth()->id(),
                'custom_particulars' => [],
            ]);
        }

        $custom = $invoice->custom_particulars ?? [];
        if (isset($custom[$pName])) {
            $oldDue = (float)($custom[$pName]['due'] ?? 0);
            $oldAmt = (float)($custom[$pName]['amount'] ?? $oldDue);
            $newDue = $oldDue + $amount;
            $newAmt = $oldAmt + $amount;
        } else {
            $newDue = $amount;
            $newAmt = $amount;
        }

        $custom[$pName] = [
            'name'        => $pName,
            'amount'      => $newAmt,
            'due'         => $newDue,
            'created_at'  => now()->toDateTimeString(),
            'created_by'  => session('admin_impersonator_id') ?? auth()->id(),
            'remarks'     => $remarks,
            'is_added'    => true,
        ];

        $newPayable  = max(0, $invoice->payable_amount + $amount);
        $newDueAmt   = max(0, $invoice->due_amount + $amount);
        $newTotalAmt = max(0, $invoice->amount + $amount);

        $status = 'UNPAID';
        if ($newDueAmt <= 0 && $invoice->paid_amount > 0) {
            $status = 'PAID';
        } elseif ($invoice->paid_amount > 0) {
            $status = 'PARTIAL';
        }

        $invoice->update([
            'custom_particulars' => $custom,
            'amount'             => $newTotalAmt,
            'payable_amount'     => $newPayable,
            'due_amount'         => $newDueAmt,
            'status'             => $status,
        ]);

        try {
            \App\Models\AuditLog::log(
                'fee_particular_added',
                $invoice,
                [],
                ['particular' => $pName, 'amount' => $amount, 'remarks' => $remarks],
                "অ্যাডমিন নতুন ফি '{$pName}' (৳{$amount}) ইনভয়েসে যুক্ত করেছেন।"
            );
        } catch (\Throwable $e) {}

        return response()->json([
            'success'       => true,
            'message'       => "✓ নতুন ফি '{$pName}' (৳" . number_format($amount, 0) . ") সফলভাবে যুক্ত করা হয়েছে।",
            'particular'    => [
                'name'      => $pName,
                'amount'    => $newAmt,
                'due'       => $newDue,
                'remarks'   => $remarks,
                'is_paid'   => false,
                'is_custom' => true,
                'is_added'  => true,
            ],
            'invoice_id'    => $invoice->id,
            'invoice_due'   => $newDueAmt,
        ]);
    }

    /**
     * Admin deletes a custom fee particular from an invoice.
     */
    public function deleteParticular(Request $request)
    {
        $isAdmin = session()->has('admin_impersonator_id') 
            || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'ADMIN'));

        if (!$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'অননুমোদিত অনুরোধ। শুধুমাত্র অ্যাডমিন ফি ডিলিট করতে পারবেন।'
            ], 403);
        }

        $validated = $request->validate([
            'invoice_id'      => 'required|exists:invoices,id',
            'particular_name' => 'required|string',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);
        $pName   = trim($validated['particular_name']);

        $custom = $invoice->custom_particulars ?? [];
        if (!isset($custom[$pName])) {
            return response()->json([
                'success' => false,
                'message' => 'এই ফি আইটেমটি পাওয়া যায়নি।'
            ], 404);
        }

        $dueToDeduct = (float)($custom[$pName]['due'] ?? 0);
        $amtToDeduct = (float)($custom[$pName]['amount'] ?? $dueToDeduct);

        unset($custom[$pName]);

        $newPayable  = max(0, $invoice->payable_amount - $amtToDeduct);
        $newDueAmt   = max(0, $invoice->due_amount - $dueToDeduct);
        $newTotalAmt = max(0, $invoice->amount - $amtToDeduct);

        $status = 'UNPAID';
        if ($newDueAmt <= 0 && $invoice->paid_amount > 0) {
            $status = 'PAID';
        } elseif ($invoice->paid_amount > 0) {
            $status = 'PARTIAL';
        }

        $invoice->update([
            'custom_particulars' => $custom,
            'amount'             => $newTotalAmt,
            'payable_amount'     => $newPayable,
            'due_amount'         => $newDueAmt,
            'status'             => $status,
        ]);

        try {
            \App\Models\AuditLog::log(
                'fee_particular_deleted',
                $invoice,
                [],
                ['particular' => $pName],
                "অ্যাডমিন '{$pName}' ফি বাতিল/মুছে ফেলেছেন।"
            );
        } catch (\Throwable $e) {}

        return response()->json([
            'success'     => true,
            'message'     => "✓ '{$pName}' ফি সফলভাবে মুছে ফেলা হয়েছে।",
            'invoice_id'  => $invoice->id,
            'invoice_due' => $newDueAmt,
        ]);
    }
}

