<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Result;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $student   = Student::where('user_id', auth()->id())->first();
        $studentId = $student?->id;
        $today     = Carbon::today();

        $batchIds = Enrollment::where('student_id', $studentId)
            ->where('status', 'ACTIVE')->pluck('batch_id');

        // Stats
        $stats = [
            'enrolled_courses'   => Enrollment::where('student_id', $studentId)->where('status', 'ACTIVE')->count(),
            'upcoming_classes'   => ClassSession::whereIn('batch_id', $batchIds)->where('status', 'SCHEDULED')->whereDate('session_date', '>=', $today)->count(),
            'attendance_percent' => $this->calcAttendance($studentId),
            'upcoming_exams'     => Exam::where('status', 'SCHEDULED')->whereHas('attendees', fn($q) => $q->where('student_id', $studentId))->count(),
        ];

        // Recent sessions (replaces timeline-based currentModules)
        $currentModules = ClassSession::with(['subject', 'batch', 'routineEntry.slot', 'moduleCovered'])
            ->whereIn('batch_id', $batchIds)
            ->orderByDesc('session_date')
            ->take(6)
            ->get();

        // Upcoming sessions this week
        $upcomingClasses = ClassSession::with(['subject', 'batch', 'teacher', 'routineEntry.slot'])
            ->whereIn('batch_id', $batchIds)
            ->where('status', 'SCHEDULED')
            ->whereDate('session_date', '>=', $today)
            ->orderBy('session_date')
            ->take(5)
            ->get();

        // Recent exam results
        $recentResults = Result::with(['exam.subject'])
            ->where('student_id', $studentId)
            ->latest()
            ->take(5)
            ->get();

        // Upcoming exams for this student
        $upcomingExamsList = Exam::with('subject')
            ->whereHas('attendees', fn($q) => $q->where('student_id', $studentId))
            ->where('status', 'SCHEDULED')
            ->orderBy('exam_date')
            ->take(5)
            ->get();

        // Notices for students
        $notices = \App\Models\Notice::where('is_published', true)
            ->whereIn('target_audience', ['ALL', 'STUDENTS'])
            ->where(function ($q) use ($batchIds) {
                $q->whereIn('batch_id', $batchIds)
                  ->orWhereNull('batch_id');
            })
            ->latest()
            ->take(5)
            ->get();

        // Running semester fee status & monthly breakdown for dashboard
        $activeEnrollment = Enrollment::with(['course.semesters', 'batch.semesterPosition.currentSemester'])
            ->where('student_id', $studentId)
            ->where('status', 'ACTIVE')
            ->first();

        $runningSemester = $activeEnrollment?->batch?->semesterPosition?->currentSemester;
        $runningSemesterName = $runningSemester?->name ?? 'চলতি সেমিস্টার';
        $runningSemesterInvoices = collect();
        if ($runningSemester && $studentId) {
            $runningSemesterInvoices = \App\Models\Invoice::where('student_id', $studentId)
                ->where('status', '!=', 'CANCELLED')
                ->where(function($q) use ($runningSemester) {
                    $q->where('source_id', $runningSemester->id)
                      ->orWhere('title', 'like', "%{$runningSemester->name}%");
                })->get();
        }

        $runningSemPayable = (float) $runningSemesterInvoices->sum('payable_amount');
        $runningSemPaid    = (float) $runningSemesterInvoices->sum('paid_amount');
        $runningSemDue     = (float) $runningSemesterInvoices->sum('due_amount');

        $monthlyRate = $runningSemPayable > 0 ? round($runningSemPayable / 6, 2) : 500;
        $pool = $runningSemPaid;
        $dashboardMonthly = [];
        $bnDigits = ['0'=>'০','1'=>'১','2'=>'২','3'=>'৩','4'=>'৪','5'=>'৫','6'=>'৬','7'=>'৭','8'=>'৮','9'=>'৯'];
        for ($m = 1; $m <= 6; $m++) {
            $mBn = strtr((string)$m, $bnDigits);
            if ($pool >= $monthlyRate) {
                $status = 'PAID';
                $pool -= $monthlyRate;
            } elseif ($pool > 0) {
                $status = 'PARTIAL';
                $pool = 0;
            } else {
                $status = 'UNPAID';
            }
            $dashboardMonthly[] = [
                'month_no' => $m,
                'name'     => "{$mBn}ম মাস",
                'rate'     => $monthlyRate,
                'status'   => $status,
            ];
        }

        // Distinct Due, Paid & Voucher collections for Student Dashboard
        $allStudentInvoices = \App\Models\Invoice::where('student_id', $studentId)
            ->where('status', '!=', 'CANCELLED')
            ->latest()
            ->get();

        $dueInvoices       = $allStudentInvoices->where('due_amount', '>', 0);
        $paidInvoices      = $allStudentInvoices->where('due_amount', '<=', 0);
        $totalOverallDue   = (float) $allStudentInvoices->sum('due_amount');
        $totalOverallPaid  = (float) $allStudentInvoices->sum('paid_amount');

        $recentVoucherPayments = \App\Models\Payment::with('invoice')
            ->where('student_id', $studentId)
            ->latest('paid_at')
            ->take(5)
            ->get();

        return view('student.dashboard', compact(
            'student', 'stats', 'currentModules', 'upcomingClasses',
            'recentResults', 'upcomingExamsList', 'notices',
            'dashboardMonthly', 'runningSemesterName', 'runningSemDue', 'runningSemPaid',
            'dueInvoices', 'paidInvoices', 'totalOverallDue', 'totalOverallPaid', 'recentVoucherPayments'
        ));
    }

    private function calcAttendance(?int $studentId): int
    {
        if (!$studentId) return 0;
        $total   = Attendance::where('student_id', $studentId)->count();
        $present = Attendance::where('student_id', $studentId)->whereIn('status', ['PRESENT', 'LATE'])->count();
        return $total > 0 ? (int) round($present / $total * 100) : 0;
    }
}
