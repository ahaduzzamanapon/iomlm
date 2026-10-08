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

        $activeEnrollments = Enrollment::where('student_id', $studentId)
            ->where('status', 'ACTIVE')
            ->with(['batch.course', 'course'])
            ->get();

        $courseIds = $activeEnrollments->map(fn($e) => $e->course_id ?? $e->batch?->course_id)->filter()->unique()->values();
        $batchIds = $activeEnrollments->pluck('batch_id')->filter()->unique()->values();
        $semesterIds = $activeEnrollments->map(fn($e) => $e->semester_id ?? $e->batch?->semesterPosition?->current_semester_id)->filter()->unique()->values();

        $enrolledSubjectIds = \App\Models\CourseSubjectMap::whereIn('course_id', $courseIds)
            ->when($semesterIds->isNotEmpty(), function ($q) use ($semesterIds) {
                $q->where(function ($sq) use ($semesterIds) {
                    $sq->whereIn('semester_id', $semesterIds)
                       ->orWhereNull('semester_id');
                });
            })
            ->pluck('subject_id')
            ->filter()
            ->unique()
            ->values();

        $attendeeExamIds = \App\Models\ExamAttendee::where('student_id', $studentId)->pluck('exam_id')->values();

        $examScopeClosure = function ($sub) use ($attendeeExamIds, $enrolledSubjectIds, $semesterIds) {
            $hasCond = false;
            if ($attendeeExamIds->isNotEmpty()) {
                $sub->whereIn('id', $attendeeExamIds);
                $hasCond = true;
            }
            if ($enrolledSubjectIds->isNotEmpty()) {
                $method = $hasCond ? 'orWhere' : 'where';
                $sub->$method(function ($sq) use ($enrolledSubjectIds, $semesterIds) {
                    $sq->whereIn('subject_id', $enrolledSubjectIds);
                    if ($semesterIds->isNotEmpty()) {
                        $sq->where(function ($semQ) use ($semesterIds) {
                            $semQ->whereIn('semester_id', $semesterIds)
                                 ->orWhereNull('semester_id');
                        });
                    }
                });
            }
        };

        $canAccessExams = ($courseIds->isNotEmpty() || $attendeeExamIds->isNotEmpty());

        // Stats
        $stats = [
            'enrolled_courses'   => Enrollment::where('student_id', $studentId)->where('status', 'ACTIVE')->count(),
            'upcoming_classes'   => ClassSession::whereIn('batch_id', $batchIds)->where('status', 'SCHEDULED')->whereDate('session_date', '>=', $today)->count(),
            'attendance_percent' => $this->calcAttendance($studentId),
            'upcoming_exams'     => $canAccessExams
                ? Exam::where('status', 'SCHEDULED')
                    ->where(function($q) use ($today) {
                        $q->whereDate('exam_date', '>=', $today)
                          ->orWhere('end_datetime', '>=', now());
                    })
                    ->where($examScopeClosure)
                    ->count()
                : 0,
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

        // Upcoming exams for this student (strictly isolated to enrolled course/subjects)
        $upcomingExamsList = $canAccessExams
            ? Exam::with('subject')
                ->where('status', 'SCHEDULED')
                ->where(function($q) use ($today) {
                    $q->whereDate('exam_date', '>=', $today)
                      ->orWhere('end_datetime', '>=', now());
                })
                ->where($examScopeClosure)
                ->orderBy('exam_date')
                ->take(5)
                ->get()
            : collect();

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

        // Multi-Course Support: fetch all student enrollments & unique courses
        $studentEnrollments = Enrollment::with(['course.semesters', 'batch.semesterPosition.currentSemester', 'semester'])
            ->where('student_id', $studentId)
            ->where('status', 'ACTIVE')
            ->get();

        if ($studentEnrollments->isEmpty()) {
            $studentEnrollments = Enrollment::with(['course.semesters', 'batch.semesterPosition.currentSemester', 'semester'])
                ->where('student_id', $studentId)
                ->get();
        }

        $studentCourses = $studentEnrollments->map(fn($e) => $e->course)->filter()->unique('id')->values();

        $selectedCourseId = request('course_id');
        $activeEnrollment = null;
        if ($selectedCourseId) {
            $activeEnrollment = $studentEnrollments->where('course_id', $selectedCourseId)->first();
        }
        if (!$activeEnrollment) {
            $activeEnrollment = $studentEnrollments->first();
        }

        $selectedCourse = $activeEnrollment?->course;

        // Current Running Semester resolved matching FeeController
        $runningSemester = $activeEnrollment?->batch?->semesterPosition?->currentSemester
            ?? $activeEnrollment?->semester
            ?? ($selectedCourse ? $selectedCourse->semesters->first() : null);

        $runningSemesterName = $runningSemester?->name ?? ($selectedCourse ? ($selectedCourse->name . ' - চলতি সেমিস্টার') : 'চলতি সেমিস্টার');

        $runningSemesterInvoices = collect();
        if ($studentId && ($runningSemester || $selectedCourse)) {
            $runningSemesterInvoices = \App\Models\Invoice::where('student_id', $studentId)
                ->where('status', '!=', 'CANCELLED')
                ->where(function($q) use ($runningSemester, $activeEnrollment, $selectedCourse) {
                    if ($activeEnrollment) {
                        $q->where('enrollment_id', $activeEnrollment->id);
                    }
                    if ($selectedCourse) {
                        $q->orWhereHas('enrollment', fn($q2) => $q2->where('course_id', $selectedCourse->id));
                    }
                    if ($runningSemester) {
                        $q->orWhere('source_id', $runningSemester->id)
                          ->orWhere('title', 'like', "%{$runningSemester->name}%");
                    }
                })->get();
        }

        $runningSemPayable = (float) $runningSemesterInvoices->sum('payable_amount');
        $runningSemPaid    = (float) $runningSemesterInvoices->sum('paid_amount');
        $runningSemDue     = (float) $runningSemesterInvoices->sum('due_amount');
        $hasRunningSemesterInvoices = $runningSemesterInvoices->isNotEmpty();

        $monthlyRate = $runningSemPayable > 0 ? round($runningSemPayable / 6, 2) : 0;
        $pool = $runningSemPaid;
        $dashboardMonthly = [];
        $bnDigits = ['0'=>'০','1'=>'১','2'=>'২','3'=>'৩','4'=>'৪','5'=>'৫','6'=>'৬','7'=>'৭','8'=>'৮','9'=>'৯'];

        if ($hasRunningSemesterInvoices) {
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

        // Learning Resources for student dashboard (from subject modules & learning resources)
        $latestResources = collect();
        if ($enrolledSubjectIds->isNotEmpty()) {
            $recentModules = \App\Models\SubjectModule::with(['subject', 'learningResources'])
                ->whereIn('subject_id', $enrolledSubjectIds)
                ->where('is_hidden', false)
                ->where(function ($q) {
                    $q->whereNotNull('file_path')
                      ->orWhereNotNull('drive_link')
                      ->orWhereNotNull('recorded_videos')
                      ->orWhereHas('learningResources');
                })
                ->latest()
                ->take(6)
                ->get();

            foreach ($recentModules as $mod) {
                if ($mod->file_path) {
                    $ext = strtolower(pathinfo($mod->file_path, PATHINFO_EXTENSION));
                    $latestResources->push([
                        'id'           => 'mod_file_' . $mod->id,
                        'title'        => $mod->title,
                        'subject_name' => $mod->subject?->name ?? '—',
                        'type'         => $ext === 'pdf' ? 'PDF' : 'ATTACHMENT',
                        'url'          => asset('storage/' . $mod->file_path),
                        'created_at'   => $mod->created_at,
                    ]);
                }
                if ($mod->drive_link) {
                    $latestResources->push([
                        'id'           => 'mod_drive_' . $mod->id,
                        'title'        => $mod->title . ' (Google Drive)',
                        'subject_name' => $mod->subject?->name ?? '—',
                        'type'         => 'DRIVE',
                        'url'          => $mod->drive_link,
                        'created_at'   => $mod->created_at,
                    ]);
                }
                if (!empty($mod->videos)) {
                    $firstVid = $mod->videos[0] ?? null;
                    $vUrl = $firstVid['url'] ?? ($firstVid['file_path'] ? asset('storage/' . $firstVid['file_path']) : null);
                    if ($vUrl) {
                        $latestResources->push([
                            'id'           => 'mod_vid_' . $mod->id,
                            'title'        => $firstVid['title'] ?? ($mod->title . ' - ভিডিও'),
                            'subject_name' => $mod->subject?->name ?? '—',
                            'type'         => 'VIDEO',
                            'url'          => $vUrl,
                            'created_at'   => $mod->created_at,
                        ]);
                    }
                }
                foreach ($mod->learningResources as $lr) {
                    $latestResources->push([
                        'id'           => 'lr_' . $lr->id,
                        'title'        => $lr->title,
                        'subject_name' => $mod->subject?->name ?? '—',
                        'type'         => $lr->type,
                        'url'          => $lr->url,
                        'created_at'   => $lr->created_at,
                    ]);
                }
            }
            $latestResources = $latestResources->sortByDesc('created_at')->take(5)->values();
        }

        return view('student.dashboard', compact(
            'student', 'stats', 'currentModules', 'upcomingClasses',
            'recentResults', 'upcomingExamsList', 'notices',
            'dashboardMonthly', 'runningSemesterName', 'runningSemDue', 'runningSemPaid',
            'dueInvoices', 'paidInvoices', 'totalOverallDue', 'totalOverallPaid', 'recentVoucherPayments',
            'studentCourses', 'selectedCourse', 'hasRunningSemesterInvoices', 'latestResources'
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
