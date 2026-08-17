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
        $student   = Student::where('email', auth()->user()->email)->first();
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

        return view('student.dashboard', compact(
            'student', 'stats', 'currentModules', 'upcomingClasses',
            'recentResults', 'upcomingExamsList', 'notices'
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
