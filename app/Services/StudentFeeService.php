<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\AdmissionForm;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\CourseFeePackage;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ProgramActivity;
use App\Models\Semester;
use App\Models\Student;
use App\Models\WaiverApplication;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StudentFeeService
{
    /**
     * Resolves the complete student fee breakdown, semester tabs, and month-by-month particulars.
     */
    public function getStudentFeeBreakdown(Student $student, ?string $selectedSemesterId = null, ?int $selectedCourseId = null): array
    {
        $student->loadMissing([
            'enrollments.course.semesters',
            'enrollments.batch.semesterPosition.currentSemester',
            'enrollments.semester',
            'feePackage.items.feeHead',
            'user',
        ]);

        $activeEnrollment = null;
        if ($selectedCourseId) {
            $activeEnrollment = $student->enrollments->firstWhere('course_id', $selectedCourseId);
        }
        if (!$activeEnrollment) {
            $activeEnrollment = $student->enrollments->where('status', 'ACTIVE')->first()
                ?? $student->enrollments->first();
        }

        $studentCourses = $student->enrollments->map(fn($e) => $e->course)->filter()->unique('id');

        $course     = $activeEnrollment?->course;
        $courseType = $course?->type ?? 'SEMESTER_BASED';

        // Current Running Semester
        $runningSemester = $activeEnrollment?->batch?->semesterPosition?->currentSemester
            ?? $activeEnrollment?->semester;
        $runningSemesterName = $runningSemester?->name ?? 'চলতি সেমিস্টার';

        $invoicesQuery = Invoice::with(['enrollment.course'])
            ->where('student_id', $student->id);

        if ($course) {
            $invoicesQuery->where(function ($q) use ($course, $activeEnrollment) {
                $q->where('enrollment_id', $activeEnrollment->id)
                  ->orWhereHas('enrollment', fn($q2) => $q2->where('course_id', $course->id))
                  ->orWhereNull('enrollment_id');
            });
        }

        $invoices = $invoicesQuery->latest()->get();

        $payments = Payment::with('invoice')
            ->where('student_id', $student->id)
            ->whereHas('invoice', function ($q) use ($course, $activeEnrollment) {
                if ($course) {
                    $q->where('enrollment_id', $activeEnrollment->id)
                      ->orWhereHas('enrollment', fn($q2) => $q2->where('course_id', $course->id))
                      ->orWhereNull('enrollment_id');
                }
            })
            ->latest('paid_at')
            ->get();

        $activeInvoices = $invoices->where('status', '!=', 'CANCELLED');
        $totalBilled = (float) $activeInvoices->sum('payable_amount');
        $totalPaid   = (float) $activeInvoices->sum('paid_amount');
        $totalDue    = (float) $activeInvoices->sum('due_amount');

        // Semester-wise Breakdown
        $allSemesters = $course ? $course->semesters : collect();
        $invoicesBySemester         = [];
        $admissionInvoices          = collect();
        $retakeInvoices             = collect();
        $otherInvoices              = collect();
        $unassignedSemesterInvoices = collect();

        foreach ($activeInvoices as $inv) {
            if ($inv->category === 'ADMISSION') {
                $admissionInvoices->push($inv);
            } elseif ($inv->category === 'RETAKE') {
                $retakeInvoices->push($inv);
            } elseif ($inv->category === 'SEMESTER' || $inv->source_type === Semester::class) {
                if ($inv->source_type === Semester::class && $inv->source_id && $allSemesters->pluck('id')->contains($inv->source_id)) {
                    $invoicesBySemester[$inv->source_id][] = $inv;
                } else {
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
                        if (empty($inv->source_id)) {
                            try {
                                $inv->update(['source_type' => Semester::class, 'source_id' => $matchedSemId]);
                            } catch (\Throwable $e) {}
                        }
                    } else {
                        $unassignedSemesterInvoices->push($inv);
                    }
                }
            } else {
                $otherInvoices->push($inv);
            }
        }

        // Sequentially assign remaining unassigned SEMESTER invoices
        if ($unassignedSemesterInvoices->isNotEmpty()) {
            $sortedUnassigned = $unassignedSemesterInvoices->sort(function ($a, $b) {
                $statusOrder = ['PAID' => 1, 'PARTIAL' => 2, 'UNPAID' => 3];
                $orderA = $statusOrder[$a->status] ?? 4;
                $orderB = $statusOrder[$b->status] ?? 4;
                return ($orderA === $orderB) ? ($a->id <=> $b->id) : ($orderA <=> $orderB);
            })->values();

            foreach ($allSemesters as $sem) {
                if ($sortedUnassigned->isEmpty()) break;
                if (empty($invoicesBySemester[$sem->id])) {
                    $assignedInv = $sortedUnassigned->shift();
                    $invoicesBySemester[$sem->id][] = $assignedInv;
                    try {
                        $assignedInv->update([
                            'source_type' => Semester::class,
                            'source_id'   => $sem->id,
                        ]);
                    } catch (\Throwable $e) {}
                }
            }

            while ($sortedUnassigned->isNotEmpty()) {
                $otherInvoices->push($sortedUnassigned->shift());
            }
        }

        // Running semester due
        $runningSemesterDue = 0.0;
        foreach ($invoices as $inv) {
            $isCurrentSemester = false;
            if ($runningSemester && $inv->source_id == $runningSemester->id && $inv->source_type === Semester::class) {
                $isCurrentSemester = true;
            }
            $inv->is_current_running_semester = $isCurrentSemester;
            if ($isCurrentSemester && $inv->status !== 'CANCELLED') {
                $runningSemesterDue += (float) $inv->due_amount;
            }
        }

        // Resolve Student Fee Package
        $batch = $activeEnrollment?->batch;
        $studentFeePackage = $student->feePackage;
        if (!$studentFeePackage && $student->fee_package_id) {
            $studentFeePackage = CourseFeePackage::find($student->fee_package_id);
        }
        if ($studentFeePackage && $course && $studentFeePackage->course_id != $course->id) {
            $studentFeePackage = null;
        }
        if (!$studentFeePackage) {
            $admForm = AdmissionForm::where('student_id', $student->id)->whereNotNull('waiver_code')->latest()->first();
            $waiverCode = $admForm?->waiver_code;
            if ($waiverCode) {
                $altWaiverCode = str_starts_with($waiverCode, 'PF-')
                    ? str_replace('PF-', 'POOR-', $waiverCode)
                    : (str_starts_with($waiverCode, 'POOR-') ? str_replace('POOR-', 'PF-', $waiverCode) : $waiverCode);
                $waiverApp = WaiverApplication::where(function ($q) use ($waiverCode, $altWaiverCode) {
                        $q->where('application_no', $waiverCode)->orWhere('application_no', $altWaiverCode);
                    })
                    ->where('status', 'APPROVED')
                    ->whereNotNull('approved_package_id')
                    ->first();
                if ($waiverApp) {
                    $pkg = CourseFeePackage::find($waiverApp->approved_package_id);
                    if ($pkg && (!$course || $pkg->course_id == $course->id)) {
                        $studentFeePackage = $pkg;
                    }
                }
            }
        }
        if (!$studentFeePackage && $student->user?->email) {
            $waiverApp = WaiverApplication::where('email', $student->user->email)
                ->where('status', 'APPROVED')
                ->whereNotNull('approved_package_id')
                ->latest()
                ->first();
            if ($waiverApp) {
                $pkg = CourseFeePackage::find($waiverApp->approved_package_id);
                if ($pkg && (!$course || $pkg->course_id == $course->id)) {
                    $studentFeePackage = $pkg;
                }
            }
        }
        if (!$studentFeePackage) {
            $existingSemInv = Invoice::where('student_id', $student->id)->where('category', 'SEMESTER')->latest()->first();
            if ($existingSemInv && preg_match('/\(([^)]+)\)\s*(?:\([^)]+\))?$/', $existingSemInv->title, $pm)) {
                $pkgName = trim($pm[1]);
                $pkg = CourseFeePackage::where('name', $pkgName)->where('course_id', $course?->id)->first();
                if ($pkg) {
                    $studentFeePackage = $pkg;
                }
            }
        }
        if (!$studentFeePackage && $course) {
            $studentFeePackage = $course->feePackages()->where('is_default', true)->first()
                ?? $course->feePackages()->first();
        }
        if ($studentFeePackage && (!$student->fee_package_id || $student->fee_package_id != $studentFeePackage->id)) {
            try {
                $student->update(['fee_package_id' => $studentFeePackage->id]);
            } catch (\Throwable $e) {}
        }

        // Academic Year & Timing
        $academicYear = $batch?->academicYear
            ?: ($batch?->academic_year_id ? AcademicYear::find($batch->academic_year_id) : null);
        $academicStartYear = null;
        if ($academicYear?->start_date) {
            $academicStartYear = (int) Carbon::parse($academicYear->start_date)->year;
        } elseif ($academicYear?->name && preg_match('/\b(20\d{2})\b/', $academicYear->name, $ym)) {
            $academicStartYear = (int) $ym[1];
        } elseif ($student->student_code && strlen($student->student_code) >= 2) {
            $twoDigitYear = substr($student->student_code, 0, 2);
            if (is_numeric($twoDigitYear) && (int)$twoDigitYear >= 20 && (int)$twoDigitYear <= 99) {
                $academicStartYear = (int) ('20' . $twoDigitYear);
                if (!$academicYear) {
                    $academicYear = AcademicYear::where('start_date', 'like', "{$academicStartYear}%")
                        ->orWhere('name', 'like', "%{$academicStartYear}%")
                        ->first();
                }
            }
        } elseif ($batch?->start_date) {
            $academicStartYear = (int) Carbon::parse($batch->start_date)->year;
        }

        if (!$academicYear && $batch) {
            $academicYear = AcademicYear::where('is_active', 1)->first();
        }

        $batchStartMonth = $batch?->fee_start_month ?: ($batch?->start_month ?: null);
        if (!$batchStartMonth && $batch?->start_date) {
            $batchStartMonth = Carbon::parse($batch->start_date)->format('F');
        }
        if (!$batchStartMonth && $academicYear?->start_date) {
            $batchStartMonth = Carbon::parse($academicYear->start_date)->format('F');
        }
        if (!$batchStartMonth) {
            $batchStartMonth = $course?->fee_start_month ?: ($course?->start_month ?: 'January');
        }

        $batchStartDate = null;
        if ($batch?->start_date) {
            $batchStartDate = Carbon::parse($batch->start_date);
        } elseif ($academicYear?->start_date) {
            $batchStartDate = Carbon::parse($academicYear->start_date);
        } elseif ($course?->start_date) {
            $batchStartDate = Carbon::parse($course->start_date);
        }

        $programActivity = ProgramActivity::where(function ($q) use ($course, $activeEnrollment) {
            if ($course && $activeEnrollment) {
                $q->where('course_id', $course->id)->where('batch_id', $activeEnrollment->batch_id);
            }
        })->orWhere(function ($q) use ($course) {
            if ($course) $q->where('course_id', $course->id)->whereNull('batch_id');
        })->orWhere(function ($q) {
            $q->whereNull('course_id')->whereNull('batch_id');
        })->first();

        $configuredStartMonth = $batchStartMonth ?? $programActivity?->starting_month ?? 'January';
        $activityStartDate    = $batchStartDate ?? ($programActivity?->start_date ? Carbon::parse($programActivity->start_date) : null) ?? now();

        try {
            $monthNum   = (int) date('n', strtotime($configuredStartMonth . ' 1 2000'));
            $startYear  = (int) ($academicStartYear ?: ($activityStartDate?->year ?: now()->year));
            $baseCarbon = Carbon::createFromDate($startYear, $monthNum, 1);
        } catch (\Throwable $e) {
            $baseCarbon = Carbon::createFromDate(now()->year, 1, 1);
        }

        // Ordered Semester Breakdown (for Tabs)
        $semesterBreakdown = collect();
        if ($courseType === 'SEMESTER_BASED') {
            $totalSemMonths = 6;
            if ($course && $course->semesters->count() > 0) {
                $totalCourseMonths = $course->duration_unit === 'YEAR' ? $course->duration_value * 12 : $course->duration_value;
                $totalSemMonths = max(1, (int) round($totalCourseMonths / $course->semesters->count()));
            }

            foreach ($allSemesters as $sem) {
                $semInvoices = collect($invoicesBySemester[$sem->id] ?? []);
                $semPayable = (float) $semInvoices->sum('payable_amount');
                $semPaid    = (float) $semInvoices->sum('paid_amount');

                $tuitionItem = $studentFeePackage?->items?->first(fn($it) => $it->feeHead?->slug === 'tuition_fee' || str_contains(mb_strtolower($it->label ?? ''), 'tuition'));
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
                        'month_no'    => $m,
                        'month_bn'    => $mBn,
                        'month_name'  => $cMonth->format('F Y'),
                        'month_short' => $cMonth->format('M Y'),
                        'rate'        => $monthlyRate,
                        'paid'        => $mPaidAmt,
                        'due'         => $mDueAmt,
                        'status'      => $mStatus,
                    ];
                }

                $semesterBreakdown->push([
                    'semester'     => $sem,
                    'label'        => $sem->name,
                    'category'     => 'SEMESTER',
                    'isRunning'    => ($runningSemester && $sem->id == $runningSemester->id),
                    'payable'      => $semPayable,
                    'paid'         => $semPaid,
                    'due'          => (float) $semInvoices->sum('due_amount'),
                    'hasInvoice'   => $semInvoices->isNotEmpty(),
                    'invoice'      => $semInvoices->first(),
                    'monthlyRate'  => $monthlyRate,
                    'totalMonths'  => $totalSemMonths,
                    'monthlyItems' => $monthlyItems,
                ]);
            }
        }

        // Package Items Breakdown
        $totalSems = ($courseType === 'SUBJECT_BASED') ? 1 : max(1, $course?->semesters()->count() ?: 6);
        $packageItemsBreakdown = collect();
        if ($course) {
            $defaultPkg = $studentFeePackage ?? ($course->feePackages()->where('is_default', true)->first() ?? $course->feePackages()->first());
            if ($defaultPkg) {
                foreach ($defaultPkg->items()->with('feeHead')->get() as $pi) {
                    $headName  = $pi->label ?: ($pi->feeHead?->name ?? 'Fee Item');
                    $packageItemsBreakdown->push([
                        'name'             => $headName,
                        'unit_price'       => (float) $pi->amount_per_unit,
                        'total_package'    => (float) $pi->total_amount,
                        'per_semester_amt' => round((float) $pi->total_amount / $totalSems, 2),
                    ]);
                }
            }
        }

        // Resolve Selected Semester for Dropdown
        if (!$selectedSemesterId && $runningSemester) {
            $selectedSemesterId = (string) $runningSemester->id;
        }
        if (!$selectedSemesterId && $allSemesters->isNotEmpty()) {
            $selectedSemesterId = (string) $allSemesters->first()->id;
        }

        if ($selectedSemesterId === 'admission') {
            $selectedSemester = (object)[
                'id'          => 'admission',
                'name'        => 'ভর্তি ফি (Admission Fee)',
                'sequence_no' => 0,
            ];
        } else {
            $selectedSemester = $allSemesters->firstWhere('id', $selectedSemesterId)
                ?? $runningSemester
                ?? $allSemesters->first();
            if ($selectedSemester) {
                $selectedSemesterId = (string) $selectedSemester->id;
            }
        }

        // Build Semester Dropdown Options
        $semesterDropdownOptions = [];
        if ($courseType === 'SEMESTER_BASED') {
            $admDue = (float) $admissionInvoices->sum('due_amount');
            $admLabel = 'ভর্তি ফি (Admission Fee)';
            $admLabel .= ($admDue > 0) ? ' (বকেয়া: ৳' . number_format($admDue, 0) . ')' : ' (পরিশোধিত)';

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
                    'id'          => (string) $sem->id,
                    'name'        => $sem->name,
                    'label'       => $semLabel,
                    'due'         => $semDue,
                    'isRunning'   => $isRunning,
                    'sequence_no' => $sem->sequence_no,
                ];
            }
        } else {
            $semesterDropdownOptions[] = [
                'id'          => '0',
                'name'        => 'Full Course',
                'label'       => 'সম্পূর্ণ কোর্স ফি (Full Course / All Subjects)',
                'due'         => $totalDue,
                'isRunning'   => true,
                'sequence_no' => 1,
            ];
        }

        if (empty($semesterDropdownOptions)) {
            $semesterDropdownOptions[] = [
                'id'          => '0',
                'name'        => 'Full Course',
                'label'       => 'সম্পূর্ণ কোর্স ফি (Full Course)',
                'due'         => $totalDue,
                'isRunning'   => true,
                'sequence_no' => 1,
            ];
        }

        // Prior Due Guard
        $hasPriorSemesterDue  = false;
        $priorDueAmount       = 0.0;
        $priorDueSemesterName = '';
        $priorDueSemesterId   = null;

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
                            $priorDueSemesterId   = (string) $sem->id;
                        }
                    }
                }
            }

            $admDue = (float) $admissionInvoices->sum('due_amount');
            if ($admDue > 0 && $selectedSeq > 1) {
                $hasPriorSemesterDue = true;
                $priorDueAmount += $admDue;
                if (!$priorDueSemesterName) {
                    $priorDueSemesterName = 'ভর্তি ফি (Admission Fee)';
                }
            }
        }

        // Generate Step 1 Particulars
        $step1Particulars = [];
        $totalSemesters   = max(1, $course?->semesters()->count() ?: 6);
        $monthlyTuition   = 500.0;
        $midFeeAmt        = 0.0;
        $finalFeeAmt      = 0.0;

        if ($studentFeePackage) {
            $tuitionItem = $studentFeePackage->items->first(fn($it) => $it->feeHead?->slug === 'tuition_fee' || str_contains(mb_strtolower($it->label ?? ''), 'tuition'));
            $midItem     = $studentFeePackage->items->first(fn($it) => $it->feeHead?->slug === 'mid_term_fee' || str_contains(mb_strtolower($it->label ?? ''), 'mid'));
            $finalItem   = $studentFeePackage->items->first(fn($it) => $it->feeHead?->slug === 'final_term_fee' || str_contains(mb_strtolower($it->label ?? ''), 'final'));

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

        $selectedSemesterInvoice = null;

        if ($selectedSemesterId === 'admission') {
            $admInv = $admissionInvoices->first();
            $admDue = (float) $admissionInvoices->sum('due_amount');
            $admPaid = (float) $admissionInvoices->sum('paid_amount');
            $admPayable = (float) $admissionInvoices->sum('payable_amount');
            $pkgAdmItem = $studentFeePackage?->items?->first(fn($it) => $it->feeHead?->slug === 'admission_fee' || str_contains(mb_strtolower($it->label ?? ''), 'admission'));
            $nominalAmt = (float) ($admInv?->amount > 0 ? $admInv->amount : ($admPayable > 0 ? $admPayable : ($pkgAdmItem?->amount_per_unit ?: ($course?->admission_fee ?: 1500))));

            $admCustom = $admInv?->custom_particulars['Admission Fee (ভর্তি ফি)'] ?? null;
            if ($admCustom) {
                $nominalAmt = isset($admCustom['amount']) ? (float)$admCustom['amount'] : $nominalAmt;
                $admDue     = (float) $admCustom['due'];
                $admPaid    = isset($admCustom['paid_amt']) ? (float)$admCustom['paid_amt'] : $admPaid;
            }

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

            try {
                $selectedSeq = (int) ($selectedSemester?->sequence_no ?? 1);
                $startCarbon = $baseCarbon->copy()->addMonths(($selectedSeq - 1) * 6);
            } catch (\Throwable $e) {
                $startCarbon = Carbon::createFromDate(now()->year, 1, 1);
            }

            $customOverrides = $selectedSemesterInvoice?->custom_particulars ?? [];
            $customPaidTotal = 0;
            foreach ($customOverrides as $cov) {
                $customPaidTotal += (float) ($cov['paid_amt'] ?? 0);
            }
            $paidPool = max(0, $targetPaid - $customPaidTotal);
            $sl = 1;

            $resolveParticular = function(string $pName, float $defaultAmt, ?int $invId = null, ?string $invNo = null) use (&$sl, &$paidPool, $customOverrides, $selectedSemesterInvoice) {
                $targetInvId = $invId ?? $selectedSemesterInvoice?->id;
                $targetInvNo = $invNo ?? $selectedSemesterInvoice?->invoice_no;

                if (isset($customOverrides[$pName])) {
                    $cData  = $customOverrides[$pName];
                    $cAmt   = isset($cData['amount']) ? (float)$cData['amount'] : (float)$defaultAmt;
                    $cDue   = (float)($cData['due'] ?? 0);
                    $cPaid  = isset($cData['paid_amt']) ? (float)$cData['paid_amt'] : max(0, $cAmt - $cDue);
                    $isPaid = ($cDue <= 0);

                    return [
                        'sl'             => $sl++,
                        'name'           => $pName,
                        'amount'         => $cAmt,
                        'paid_amt'       => $cPaid,
                        'due'            => $cDue,
                        'is_paid'        => $isPaid,
                        'is_custom'      => true,
                        'is_added'       => !empty($cData['is_added']),
                        'custom_remarks' => $cData['remarks'] ?? null,
                        'invoice_id'     => $targetInvId,
                        'invoice_no'     => $targetInvNo,
                    ];
                }

                $nominal = (float) $defaultAmt;
                if ($paidPool >= $nominal) {
                    $paidAmt  = $nominal;
                    $dueAmt   = 0;
                    $isPaid   = true;
                    $paidPool -= $nominal;
                } elseif ($paidPool > 0) {
                    $paidAmt  = $paidPool;
                    $dueAmt   = max(0, $nominal - $paidPool);
                    $isPaid   = false;
                    $paidPool = 0;
                } else {
                    $paidAmt  = 0;
                    $dueAmt   = $nominal;
                    $isPaid   = false;
                }

                return [
                    'sl'         => $sl++,
                    'name'       => $pName,
                    'amount'     => $nominal,
                    'paid_amt'   => $paidAmt,
                    'due'        => $dueAmt,
                    'is_paid'    => $isPaid,
                    'invoice_id' => $targetInvId,
                    'invoice_no' => $targetInvNo,
                ];
            };

            // Semester 1: Prepend Admission Fee row
            if (($selectedSemester?->sequence_no ?? 1) == 1) {
                $admInv = $admissionInvoices->first();
                $admDue = (float) $admissionInvoices->sum('due_amount');
                $admPaid = (float) $admissionInvoices->sum('paid_amount');
                $admPayable = (float) $admissionInvoices->sum('payable_amount');
                $pkgAdmItem = $studentFeePackage?->items?->first(fn($it) => $it->feeHead?->slug === 'admission_fee' || str_contains(mb_strtolower($it->label ?? ''), 'admission'));
                $nominalAmt = (float) ($admInv?->amount > 0 ? $admInv->amount : ($admPayable > 0 ? $admPayable : ($pkgAdmItem?->amount_per_unit ?: ($course?->admission_fee ?: 1500))));

                $admCustom = $admInv?->custom_particulars['Admission Fee (ভর্তি ফি)'] ?? null;
                if ($admCustom) {
                    $nominalAmt = isset($admCustom['amount']) ? (float)$admCustom['amount'] : $nominalAmt;
                    $admDue     = (float) $admCustom['due'];
                    $admPaid    = isset($admCustom['paid_amt']) ? (float)$admCustom['paid_amt'] : $admPaid;
                }

                $isPaid  = ($admInv && $admInv->status === 'PAID') || ($admDue <= 0 && $admInv !== null);
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

            // Semester 3: Prepend 1st Annual Fee row
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
                    $step1Particulars[] = $resolveParticular('১ম বার্ষিক ফি (1st Annual Fee)', $annual1Amount);
                }
            }

            // Semester 5: Prepend 2nd Annual Fee row
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
                    $step1Particulars[] = $resolveParticular('২য় বার্ষিক ফি (2nd Annual Fee)', $annual2Amount);
                }
            }

            // Months 1 to 3
            for ($i = 0; $i < 3; $i++) {
                $cDate = $startCarbon->copy()->addMonths($i);
                $pName = 'Tuition Fee (' . $cDate->format('M-Y') . ')';
                $step1Particulars[] = $resolveParticular($pName, $monthlyTuition);
            }

            // Mid Term Fee
            if ($midFeeAmt > 0) {
                $step1Particulars[] = $resolveParticular('Mid Term Fee', $midFeeAmt);
            }

            // Months 4 to 6
            for ($i = 3; $i < 6; $i++) {
                $cDate = $startCarbon->copy()->addMonths($i);
                $pName = 'Tuition Fee (' . $cDate->format('M-Y') . ')';
                $step1Particulars[] = $resolveParticular($pName, $monthlyTuition);
            }

            // Final Term Fee
            if ($finalFeeAmt > 0) {
                $step1Particulars[] = $resolveParticular('Final Term Fee', $finalFeeAmt);
            }

            // Additional custom-added items not in the template
            $existingNames = array_column($step1Particulars, 'name');
            foreach ($customOverrides as $cName => $cData) {
                if (!in_array($cName, $existingNames, true)) {
                    $cDue  = (float) ($cData['due'] ?? 0);
                    $cAmt  = (float) ($cData['amount'] ?? $cDue);
                    $cPaid = isset($cData['paid_amt']) ? (float)$cData['paid_amt'] : max(0, $cAmt - $cDue);
                    $step1Particulars[] = [
                        'sl'             => $sl++,
                        'name'           => $cName,
                        'amount'         => $cAmt,
                        'paid_amt'       => $cPaid,
                        'due'            => $cDue,
                        'is_paid'        => ($cDue <= 0),
                        'is_custom'      => true,
                        'is_added'       => true,
                        'custom_remarks' => $cData['remarks'] ?? 'অ্যাডমিন কর্তৃক যুক্ত ফি',
                        'invoice_id'     => $selectedSemesterInvoice?->id,
                        'invoice_no'     => $selectedSemesterInvoice?->invoice_no,
                    ];
                }
            }
        } else {
            // SUBJECT_BASED COURSE
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

            $customOverrides = $selectedSemesterInvoice?->custom_particulars ?? [];
            $customPaidTotal = 0;
            foreach ($customOverrides as $cov) {
                $customPaidTotal += (float) ($cov['paid_amt'] ?? 0);
            }
            $paidPool = max(0, $targetPaid - $customPaidTotal);
            $sl = 1;

            if ($totalCourseMonths > 1 && $targetPayable > 0) {
                $monthlyRate = round($targetPayable / $totalCourseMonths, 2);
                $startCarbon = $baseCarbon->copy();

                for ($i = 0; $i < $totalCourseMonths; $i++) {
                    $cDate = $startCarbon->copy()->addMonths($i);
                    $pName = 'Course Tuition Fee (' . $cDate->format('M-Y') . ')';

                    if (isset($customOverrides[$pName])) {
                        $cData  = $customOverrides[$pName];
                        $cAmt   = isset($cData['amount']) ? (float)$cData['amount'] : (float)$monthlyRate;
                        $cDue   = (float)($cData['due'] ?? 0);
                        $cPaid  = isset($cData['paid_amt']) ? (float)$cData['paid_amt'] : max(0, $cAmt - $cDue);
                        $isPaid = ($cDue <= 0);

                        $step1Particulars[] = [
                            'sl'             => $sl++,
                            'name'           => $pName,
                            'amount'         => $cAmt,
                            'paid_amt'       => $cPaid,
                            'due'            => $cDue,
                            'is_paid'        => $isPaid,
                            'is_custom'      => true,
                            'is_added'       => !empty($cData['is_added']),
                            'custom_remarks' => $cData['remarks'] ?? null,
                            'invoice_id'     => $selectedSemesterInvoice?->id,
                            'invoice_no'     => $selectedSemesterInvoice?->invoice_no,
                        ];
                    } else {
                        $nominal = $monthlyRate;
                        if ($paidPool >= $nominal) {
                            $paidAmt  = $nominal;
                            $dueAmt   = 0;
                            $isPaid   = true;
                            $paidPool -= $nominal;
                        } elseif ($paidPool > 0) {
                            $paidAmt  = $paidPool;
                            $dueAmt   = max(0, $nominal - $paidPool);
                            $isPaid   = false;
                            $paidPool = 0;
                        } else {
                            $paidAmt  = 0;
                            $dueAmt   = $nominal;
                            $isPaid   = false;
                        }

                        $step1Particulars[] = [
                            'sl'         => $sl++,
                            'name'       => $pName,
                            'amount'     => $nominal,
                            'paid_amt'   => $paidAmt,
                            'due'        => $dueAmt,
                            'is_paid'    => $isPaid,
                            'invoice_id' => $selectedSemesterInvoice?->id,
                            'invoice_no' => $selectedSemesterInvoice?->invoice_no,
                        ];
                    }
                }
            } elseif ($targetPayable > 0) {
                $pName = $selectedSemesterInvoice->title ?: 'Course Tuition Fee';
                if (isset($customOverrides[$pName])) {
                    $cData  = $customOverrides[$pName];
                    $cAmt   = isset($cData['amount']) ? (float)$cData['amount'] : (float)$targetPayable;
                    $cDue   = (float)($cData['due'] ?? 0);
                    $cPaid  = isset($cData['paid_amt']) ? (float)$cData['paid_amt'] : max(0, $cAmt - $cDue);
                    $step1Particulars[] = [
                        'sl'             => $sl++,
                        'name'           => $pName,
                        'amount'         => $cAmt,
                        'paid_amt'       => $cPaid,
                        'due'            => $cDue,
                        'is_paid'        => ($cDue <= 0),
                        'is_custom'      => true,
                        'is_added'       => !empty($cData['is_added']),
                        'custom_remarks' => $cData['remarks'] ?? null,
                        'invoice_id'     => $selectedSemesterInvoice?->id,
                        'invoice_no'     => $selectedSemesterInvoice?->invoice_no,
                    ];
                } else {
                    $step1Particulars[] = [
                        'sl'         => $sl++,
                        'name'       => $pName,
                        'amount'     => $targetPayable,
                        'paid_amt'   => $targetPaid,
                        'due'        => $targetDue,
                        'is_paid'    => $targetDue <= 0,
                        'invoice_id' => $selectedSemesterInvoice?->id,
                        'invoice_no' => $selectedSemesterInvoice?->invoice_no,
                    ];
                }
            } else {
                $step1Particulars[] = [
                    'sl'         => $sl++,
                    'name'       => $course ? "{$course->name} Tuition Fee" : 'Course Tuition Fee',
                    'amount'     => 0,
                    'paid_amt'   => 0,
                    'due'        => 0,
                    'is_paid'    => true,
                    'invoice_id' => null,
                    'invoice_no' => null,
                ];
            }

            // Additional custom-added items for subject-based
            $existingNames = array_column($step1Particulars, 'name');
            foreach ($customOverrides as $cName => $cData) {
                if (!in_array($cName, $existingNames, true)) {
                    $cDue  = (float) ($cData['due'] ?? 0);
                    $cAmt  = (float) ($cData['amount'] ?? $cDue);
                    $cPaid = isset($cData['paid_amt']) ? (float)$cData['paid_amt'] : max(0, $cAmt - $cDue);
                    $step1Particulars[] = [
                        'sl'             => $sl++,
                        'name'           => $cName,
                        'amount'         => $cAmt,
                        'paid_amt'       => $cPaid,
                        'due'            => $cDue,
                        'is_paid'        => ($cDue <= 0),
                        'is_custom'      => true,
                        'is_added'       => true,
                        'custom_remarks' => $cData['remarks'] ?? 'অ্যাডমিন কর্তৃক যুক্ত ফি',
                        'invoice_id'     => $selectedSemesterInvoice?->id,
                        'invoice_no'     => $selectedSemesterInvoice?->invoice_no,
                    ];
                }
            }
        }

        // ── Append All Other Invoices (Course Activation Fee, Fines, Extra Fees, Document Fees, Retakes, etc.) ──
        if ($selectedSemesterId !== 'admission') {
            $alreadyRepresentedInvoiceIds = array_filter(array_column($step1Particulars, 'invoice_id'));
            $existingPartNames = array_map('mb_strtolower', array_column($step1Particulars, 'name'));

            foreach ($activeInvoices as $otherInv) {
                // Skip the main semester invoice which is already represented by months/term fees
                if ($selectedSemesterInvoice && $otherInv->id === $selectedSemesterInvoice->id) {
                    continue;
                }

                // Skip admission invoice if already represented
                if ($otherInv->category === 'ADMISSION' && in_array($otherInv->id, $alreadyRepresentedInvoiceIds, true)) {
                    continue;
                }

                // Check if this invoice is explicitly linked to another semester
                if ($otherInv->source_type === Semester::class && $otherInv->source_id && $selectedSemester && $otherInv->source_id != $selectedSemester->id) {
                    continue;
                }

                $belongsToSelected = false;

                if ($otherInv->source_type === Semester::class && $otherInv->source_id == $selectedSemester?->id) {
                    $belongsToSelected = true;
                } elseif ($selectedSemester && $runningSemester && $selectedSemester->id == $runningSemester->id) {
                    // Running semester shows all unassigned/general student fees (activation fees, fines, extra)
                    $belongsToSelected = true;
                } elseif ($otherInv->due_amount > 0) {
                    // Any unpaid due should be visible on the active/selected semester so it can be paid
                    $belongsToSelected = true;
                } else {
                    // Check if created_at or due_date falls within the semester startCarbon -> startCarbon + 6 months
                    if (isset($startCarbon) && $otherInv->created_at) {
                        $invDate = Carbon::parse($otherInv->created_at);
                        $semEnd = $startCarbon->copy()->addMonths(6);
                        if ($invDate->between($startCarbon, $semEnd)) {
                            $belongsToSelected = true;
                        }
                    }
                }

                if (!$belongsToSelected) {
                    continue;
                }

                // If invoice has custom particulars:
                if (!empty($otherInv->custom_particulars)) {
                    foreach ($otherInv->custom_particulars as $cpName => $cpData) {
                        if (in_array(mb_strtolower($cpName), $existingPartNames, true)) {
                            continue;
                        }
                        $cDue   = (float) ($cpData['due'] ?? 0);
                        $cAmt   = (float) ($cpData['amount'] ?? $cDue);
                        $cPaid  = isset($cpData['paid_amt']) ? (float)$cpData['paid_amt'] : max(0, $cAmt - $cDue);
                        $isPaid = ($cDue <= 0);

                        $step1Particulars[] = [
                            'sl'             => $sl++,
                            'name'           => $cpName,
                            'amount'         => $cAmt,
                            'paid_amt'       => $cPaid,
                            'due'            => $cDue,
                            'is_paid'        => $isPaid,
                            'is_custom'      => true,
                            'is_added'       => true,
                            'custom_remarks' => $cpData['remarks'] ?? $otherInv->notes ?? $otherInv->category,
                            'invoice_id'     => $otherInv->id,
                            'invoice_no'     => $otherInv->invoice_no,
                            'category'       => $otherInv->category,
                        ];
                        $existingPartNames[] = mb_strtolower($cpName);
                    }
                } else {
                    // The invoice itself is the fee item
                    $invTitle = $otherInv->title ?: ($otherInv->category . ' Fee');
                    
                    // Clean long auto-generated activation fee titles if needed
                    $isActivation = ($otherInv->category === 'FINE' && (str_contains(mb_strtolower($invTitle), 'activation') || str_contains($invTitle, 'এক্টিভিশন') || str_contains($otherInv->invoice_no, 'INV-ACT-')));
                    if ($isActivation && str_contains($invTitle, '(')) {
                        $parts = explode('(', $invTitle, 2);
                        $invTitle = trim($parts[0]);
                    }

                    if (in_array(mb_strtolower($invTitle), $existingPartNames, true)) {
                        continue;
                    }

                    $invPayable = $otherInv->payable_amount > 0 ? (float)$otherInv->payable_amount : (float)$otherInv->amount;
                    $invDue     = (float)$otherInv->due_amount;
                    $invPaid    = (float)$otherInv->paid_amount;
                    $isPaid     = ($invDue <= 0 && $otherInv->status === 'PAID');

                    $step1Particulars[] = [
                        'sl'             => $sl++,
                        'name'           => $invTitle,
                        'amount'         => $invPayable,
                        'paid_amt'       => $invPaid,
                        'due'            => $invDue,
                        'is_paid'        => $isPaid,
                        'is_custom'      => true,
                        'is_added'       => true,
                        'custom_remarks' => $otherInv->notes ?? ($isActivation ? 'কোর্স এক্টিভিশন ফি' : $otherInv->category),
                        'invoice_id'     => $otherInv->id,
                        'invoice_no'     => $otherInv->invoice_no,
                        'category'       => $otherInv->category,
                    ];
                    $existingPartNames[] = mb_strtolower($invTitle);
                }
            }
        }

        return [
            'student'                 => $student,
            'course'                  => $course,
            'courseType'              => $courseType,
            'runningSemester'         => $runningSemester,
            'runningSemesterName'     => $runningSemesterName,
            'invoices'                => $invoices,
            'payments'                => $payments,
            'totalBilled'             => $totalBilled,
            'totalDue'                => $totalDue,
            'totalPaid'               => $totalPaid,
            'runningSemesterDue'      => $runningSemesterDue,
            'semesterBreakdown'       => $semesterBreakdown,
            'studentCourses'          => $studentCourses,
            'packageItemsBreakdown'   => $packageItemsBreakdown,
            'programActivity'         => $programActivity,
            'semesterDropdownOptions' => $semesterDropdownOptions,
            'selectedSemesterId'      => $selectedSemesterId,
            'selectedSemester'        => $selectedSemester,
            'hasPriorSemesterDue'     => $hasPriorSemesterDue,
            'priorDueAmount'          => $priorDueAmount,
            'priorDueSemesterName'    => $priorDueSemesterName,
            'priorDueSemesterId'      => $priorDueSemesterId,
            'step1Particulars'        => $step1Particulars,
            'selectedSemesterInvoice' => $selectedSemesterInvoice,
            'monthlyTuition'          => $monthlyTuition,
            'batch'                   => $batch,
            'academicYear'            => $academicYear,
        ];
    }

    /**
     * Admin adjusts/edits the due amount of an individual particular.
     */
    public function updateParticular(
        int $invoiceId,
        string $particularName,
        float $newDue,
        ?string $remarks = null,
        ?int $userId = null,
        ?float $currentDueHint = null
    ): array {
        $invoice = Invoice::findOrFail($invoiceId);
        $pName   = trim($particularName);
        $newDue  = round($newDue, 2);

        $custom = $invoice->custom_particulars ?? [];
        $currentPaid = isset($custom[$pName]['paid_amt']) ? (float)$custom[$pName]['paid_amt'] : 0.0;

        if (isset($custom[$pName]['due'])) {
            $oldDue = (float) $custom[$pName]['due'];
        } elseif ($currentDueHint !== null) {
            $oldDue = (float) $currentDueHint;
        } else {
            $oldDue = $newDue;
        }

        $diff = $newDue - $oldDue;
        $newAmount = $newDue + $currentPaid;

        $custom[$pName] = [
            'name'        => $pName,
            'amount'      => $newAmount,
            'paid_amt'    => $currentPaid,
            'due'         => $newDue,
            'is_paid'     => ($newDue <= 0),
            'adjusted_at' => now()->toDateTimeString(),
            'adjusted_by' => $userId ?? auth()->id(),
            'remarks'     => $remarks ?? 'ফি পরিবর্তন',
            'is_custom'   => true,
        ];

        $newPayable     = max(0, $invoice->payable_amount + $diff);
        $newDueAmt      = max(0, $invoice->due_amount + $diff);
        $newTotalAmount = max(0, $invoice->amount + $diff);

        $status = 'UNPAID';
        if ($newDueAmt <= 0 && $invoice->paid_amount > 0) {
            $status = 'PAID';
        } elseif ($invoice->paid_amount > 0) {
            $status = 'PARTIAL';
        }

        $invoice->update([
            'custom_particulars' => $custom,
            'amount'             => $newTotalAmount,
            'payable_amount'     => $newPayable,
            'due_amount'         => $newDueAmt,
            'status'             => $status,
        ]);

        try {
            AuditLog::log(
                'fee_particular_adjusted',
                $invoice,
                ['old_due' => $oldDue],
                ['new_due' => $newDue, 'particular' => $pName, 'remarks' => $remarks],
                "অ্যাডমিন {$pName} ফি ৳{$oldDue} থেকে পরিবর্তন করে ৳{$newDue} করেছেন।"
            );
        } catch (\Throwable $e) {}

        return [
            'success'     => true,
            'message'     => "✓ {$pName} এর ফি সফলভাবে ৳" . number_format($newDue, 0) . " এ আপডেট এবং সেভ করা হয়েছে।",
            'particular'  => $pName,
            'new_due'     => $newDue,
            'new_amount'  => $newAmount,
            'is_paid'     => ($newDue <= 0),
            'invoice_due' => $newDueAmt,
            'invoice_id'  => $invoice->id,
        ];
    }

    /**
     * Admin adds a new custom fee particular to an invoice.
     */
    public function storeParticular(?int $invoiceId, ?int $studentId, mixed $semesterId, string $particularName, float $amount, ?string $remarks = null, ?int $userId = null): array
    {
        $pName   = trim($particularName);
        $amount  = round($amount, 2);
        $remarks = $remarks ?? 'অ্যাডমিন কর্তৃক নতুন ফি যুক্ত';

        $invoice = null;
        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
        }

        if (!$invoice && $studentId) {
            $student = Student::findOrFail($studentId);
            $enrollment = $student->enrollments()->where('status', 'ACTIVE')->first() ?? $student->enrollments()->first();
            $invNo = 'INV-MAN-' . date('Ymd') . '-' . rand(1000, 9999);

            $isNumericSemester = !empty($semesterId) && is_numeric($semesterId) && (int)$semesterId > 0;

            $invoice = Invoice::create([
                'invoice_no'         => $invNo,
                'student_id'         => $student->id,
                'enrollment_id'      => $enrollment?->id,
                'category'           => $isNumericSemester ? 'SEMESTER' : 'MANUAL',
                'title'              => $pName,
                'amount'             => 0,
                'discount'           => 0,
                'payable_amount'     => 0,
                'paid_amount'        => 0,
                'due_amount'         => 0,
                'status'             => 'UNPAID',
                'due_date'           => now()->addDays(15),
                'source_type'        => $isNumericSemester ? Semester::class : null,
                'source_id'          => $isNumericSemester ? (int)$semesterId : null,
                'created_by'         => $userId ?? auth()->id(),
                'custom_particulars' => [],
            ]);
        }

        if (!$invoice) {
            return [
                'success' => false,
                'message' => 'ইনভয়েস বা শিক্ষার্থী নির্বাচন সঠিকভাবে করা সম্ভব হয়নি।',
            ];
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
            'name'       => $pName,
            'amount'     => $newAmt,
            'due'        => $newDue,
            'created_at' => now()->toDateTimeString(),
            'created_by' => $userId ?? auth()->id(),
            'remarks'    => $remarks,
            'is_added'   => true,
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
            AuditLog::log(
                'fee_particular_added',
                $invoice,
                [],
                ['particular' => $pName, 'amount' => $amount, 'remarks' => $remarks],
                "অ্যাডমিন নতুন ফি '{$pName}' (৳{$amount}) ইনভয়েসে যুক্ত করেছেন।"
            );
        } catch (\Throwable $e) {}

        return [
            'success'     => true,
            'message'     => "✓ নতুন ফি '{$pName}' (৳" . number_format($amount, 0) . ") সফলভাবে যুক্ত করা হয়েছে।",
            'particular'  => [
                'name'      => $pName,
                'amount'    => $newAmt,
                'due'       => $newDue,
                'remarks'   => $remarks,
                'is_paid'   => false,
                'is_custom' => true,
                'is_added'  => true,
            ],
            'invoice_id'  => $invoice->id,
            'invoice_due' => $newDueAmt,
        ];
    }

    /**
     * Admin deletes a custom added fee particular from an invoice.
     */
    public function deleteParticular(int $invoiceId, string $particularName, ?int $userId = null): array
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $pName   = trim($particularName);

        $custom = $invoice->custom_particulars ?? [];
        if (!isset($custom[$pName])) {
            if ($invoice->paid_amount <= 0 && ($invoice->title === $pName || str_contains($invoice->title, $pName))) {
                $invoice->delete();
                return [
                    'success'     => true,
                    'message'     => "✓ '{$pName}' ফি সফলভাবে মুছে ফেলা হয়েছে।",
                    'invoice_id'  => $invoiceId,
                    'invoice_due' => 0,
                ];
            }
            return [
                'success' => false,
                'message' => 'এই ফি আইটেমটি পাওয়া যায়নি।',
            ];
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
            AuditLog::log(
                'fee_particular_deleted',
                $invoice,
                [],
                ['particular' => $pName],
                "অ্যাডমিন '{$pName}' ফি বাতিল/মুছে ফেলেছেন।"
            );
        } catch (\Throwable $e) {}

        return [
            'success'     => true,
            'message'     => "✓ '{$pName}' ফি সফলভাবে মুছে ফেলা হয়েছে।",
            'invoice_id'  => $invoice->id,
            'invoice_due' => $newDueAmt,
        ];
    }

    /**
     * Collect payment for one or multiple fee particulars directly via Admin Ledger.
     */
    public function collectParticularPayment(
        Invoice $invoice,
        array $particularNames,
        float $amount,
        string $paymentMethod = 'CASH',
        ?string $trxId = null,
        ?string $senderNumber = null,
        ?string $remarks = null,
        ?int $userId = null,
        array $particularDues = [],
        array $particularAmounts = [],
        array $particularInvoices = []
    ): array {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            return [
                'success' => false,
                'message' => 'পরিশোধের পরিমাণ অবশ্যই ০ টাকার বেশি হতে হবে।',
            ];
        }

        $allocationsByInvoice = [];
        $remainingPayment = $amount;

        foreach ($particularNames as $idx => $pName) {
            $pName = trim($pName);
            $targetInvId = $particularInvoices[$pName] ?? $invoice->id;
            $targetInv = ($targetInvId == $invoice->id) ? $invoice : (Invoice::find($targetInvId) ?? $invoice);

            if (!isset($allocationsByInvoice[$targetInv->id])) {
                $allocationsByInvoice[$targetInv->id] = [
                    'invoice'   => $targetInv,
                    'allocated' => 0.0,
                    'items'     => [],
                ];
            }

            $itemCustom = $targetInv->custom_particulars ?? [];
            if (isset($itemCustom[$pName]['due'])) {
                $itemDue = (float) $itemCustom[$pName]['due'];
            } elseif (isset($particularDues[$pName])) {
                $itemDue = (float) $particularDues[$pName];
            } else {
                $itemDue = (float) $targetInv->due_amount ?: $amount;
            }

            if (isset($itemCustom[$pName]['amount'])) {
                $itemAmount = (float) $itemCustom[$pName]['amount'];
            } elseif (isset($particularAmounts[$pName])) {
                $itemAmount = (float) $particularAmounts[$pName];
            } else {
                $itemAmount = $itemDue;
            }

            $currentPaid = isset($itemCustom[$pName]['paid_amt'])
                ? (float) $itemCustom[$pName]['paid_amt']
                : max(0, $itemAmount - $itemDue);

            $isLast = ($idx === count($particularNames) - 1);
            $allocated = min($remainingPayment, $itemDue);
            if ($isLast && $remainingPayment > $itemDue) {
                $allocated = $remainingPayment;
            }
            $allocated = max(0, $allocated);

            $newPaid = $currentPaid + $allocated;
            $newDue  = max(0, $itemAmount - $newPaid);
            $isPaid  = ($newDue <= 0);

            $allocationsByInvoice[$targetInv->id]['allocated'] += $allocated;
            $allocationsByInvoice[$targetInv->id]['items'][$pName] = [
                'name'        => $pName,
                'amount'      => $itemAmount,
                'paid_amt'    => $newPaid,
                'due'         => $newDue,
                'is_paid'     => $isPaid,
                'adjusted_at' => now()->toDateTimeString(),
                'adjusted_by' => $userId ?? auth()->id(),
                'remarks'     => $isPaid
                    ? "পরিশোধ সম্পন্ন ({$paymentMethod})"
                    : "আংশিক পরিশোধ: ৳" . number_format($allocated, 0) . " ({$paymentMethod})",
            ];

            $remainingPayment -= $allocated;
        }

        $lastPayment = null;
        foreach ($allocationsByInvoice as $invData) {
            $curInv = $invData['invoice'];
            $invAllocated = (float) $invData['allocated'];

            if ($invAllocated > 0) {
                $curPaymentAmt = min($invAllocated, (float) $curInv->due_amount);
                if ($curPaymentAmt <= 0) {
                    $curPaymentAmt = $invAllocated;
                }

                $p = AccountingService::receivePayment(
                    $curInv,
                    $curPaymentAmt,
                    strtoupper($paymentMethod),
                    $trxId,
                    $remarks ?: ('ফি আদায়: ' . implode(', ', array_keys($invData['items']))),
                    $senderNumber
                );
                $lastPayment = $p;
            }

            $curCustom = $curInv->custom_particulars ?? [];
            foreach ($invData['items'] as $itName => $itVals) {
                $curCustom[$itName] = $itVals;
            }
            $curInv->update([
                'custom_particulars' => $curCustom,
            ]);
        }

        $paymentNo = $lastPayment ? $lastPayment->payment_no : 'N/A';

        return [
            'success'     => true,
            'message'     => "✓ নির্বাচিত ফি (৳" . number_format($amount, 2) . ") সফলভাবে আদায় করা হয়েছে! মানি রসিদ নং: {$paymentNo}",
            'payment'     => $lastPayment,
            'invoice'     => $invoice->fresh(),
        ];
    }
}
