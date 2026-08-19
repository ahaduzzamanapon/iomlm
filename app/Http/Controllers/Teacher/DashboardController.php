<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Exam;
use App\Models\SubjectTeacherAssignment;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $teacher   = Teacher::where('user_id', auth()->id())->first();
        $teacherId = $teacher?->id;
        $today     = Carbon::today();

        // Today's classes for this teacher (by session_date)
        $todayClasses = ClassSession::with(['subject', 'batch', 'routineEntry.slot', 'attendances'])
            ->where('teacher_id', $teacherId)
            ->whereDate('session_date', $today)
            ->orderBy('start_time')
            ->get();

        // Upcoming sessions this week (not today)
        $upcomingSessions = ClassSession::with(['subject', 'batch', 'routineEntry.slot'])
            ->where('teacher_id', $teacherId)
            ->whereDate('session_date', '>', $today)
            ->whereDate('session_date', '<=', $today->copy()->addDays(6))
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // Sessions needing attendance (completed but no attendance)
        $attendancePending = ClassSession::with(['subject', 'batch'])
            ->where('teacher_id', $teacherId)
            ->where('status', 'COMPLETED')
            ->whereDoesntHave('attendances')
            ->orderByDesc('session_date')
            ->take(5)
            ->get();

        // Upcoming exams for teacher's subjects
        $subjectIds = SubjectTeacherAssignment::where('teacher_id', $teacherId)->pluck('subject_id');
        $upcomingExams = Exam::with('subject')
            ->whereIn('subject_id', $subjectIds)
            ->where('status', 'SCHEDULED')
            ->orderBy('exam_date')
            ->take(5)
            ->get();

        // Exams completed but results not entered
        $pendingResults = Exam::with('subject')
            ->whereIn('subject_id', $subjectIds)
            ->where('status', 'COMPLETED')
            ->whereDoesntHave('results')
            ->take(5)
            ->get();

        $stats = [
            'today_classes'   => $todayClasses->count(),
            'total_subjects'  => $subjectIds->count(),
            'pending_results' => $pendingResults->count(),
            'attendance_todo' => $attendancePending->count(),
        ];

        return view('teacher.dashboard', compact(
            'stats', 'todayClasses', 'upcomingSessions',
            'upcomingExams', 'attendancePending', 'pendingResults', 'today'
        ));
    }
}
