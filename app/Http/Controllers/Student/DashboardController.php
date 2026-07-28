<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Student linked to auth user
        $student = null;
        try {
            $student = \App\Models\Student::where('email', auth()->user()->email)->first();
        } catch (\Exception) {}

        $studentId = $student?->id;

        // Enrollments
        $enrollmentIds = collect();
        try {
            $enrollmentIds = \App\Models\Enrollment::where('student_id', $studentId)
                ->where('status', 'ACTIVE')
                ->pluck('id');
        } catch (\Exception) {}

        // Stats
        $stats = [
            'enrolled_courses'  => $this->safe(fn() => \App\Models\Enrollment::where('student_id', $studentId)->where('status', 'ACTIVE')->count()),
            'upcoming_classes'  => $this->safe(fn() => \App\Models\ClassSession::where('status', 'SCHEDULED')->count()),
            'attendance_percent'=> $this->calcAttendance($studentId),
            'upcoming_exams'    => $this->safe(fn() => \App\Models\Exam::where('status', 'SCHEDULED')->count()),
        ];

        // Current module timeline
        $currentModules = collect();
        try {
            $batchIds = \App\Models\Enrollment::where('student_id', $studentId)
                ->where('status', 'ACTIVE')->pluck('batch_id');
            $currentModules = \App\Models\Timeline::with(['subject', 'module'])
                ->whereIn('batch_id', $batchIds)
                ->whereIn('status', ['UPCOMING', 'SCHEDULED', 'RUNNING'])
                ->orderBy('scheduled_date')
                ->take(6)
                ->get();
        } catch (\Exception) {}

        // Upcoming classes
        $upcomingClasses = collect();
        try {
            $batchIds = $batchIds ?? collect();
            $upcomingClasses = \App\Models\ClassSession::with(['timeline.subject', 'timeline.module', 'teacher'])
                ->whereHas('timeline', fn($q) => $q->whereIn('batch_id', $batchIds))
                ->where('status', 'SCHEDULED')
                ->take(5)
                ->get();
        } catch (\Exception) {}

        // Recent results
        $recentResults = collect();
        try {
            $recentResults = \App\Models\Result::with(['exam.subject'])
                ->where('student_id', $studentId)
                ->latest()
                ->take(5)
                ->get();
        } catch (\Exception) {}

        // Upcoming exams
        $upcomingExamsList = collect();
        try {
            $upcomingExamsList = \App\Models\Exam::with('subject')
                ->whereHas('attendees', fn($q) => $q->where('student_id', $studentId))
                ->where('status', 'SCHEDULED')
                ->orderBy('exam_date')
                ->take(5)
                ->get();
        } catch (\Exception) {}

        return view('student.dashboard', compact(
            'stats', 'currentModules', 'upcomingClasses', 'recentResults', 'upcomingExamsList'
        ));
    }

    private function safe(callable $fn, mixed $default = 0): mixed
    {
        try { return $fn(); } catch (\Exception) { return $default; }
    }

    private function calcAttendance(?int $studentId): int
    {
        try {
            if (!$studentId) return 0;
            $total   = \App\Models\Attendance::where('student_id', $studentId)->count();
            $present = \App\Models\Attendance::where('student_id', $studentId)->whereIn('status', ['PRESENT', 'LATE'])->count();
            return $total > 0 ? (int) round($present / $total * 100) : 0;
        } catch (\Exception) {
            return 0;
        }
    }
}
