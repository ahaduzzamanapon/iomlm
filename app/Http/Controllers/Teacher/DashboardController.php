<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Teacher linked to auth user
        $teacher = null;
        try {
            $teacher = \App\Models\Teacher::where('email', auth()->user()->email)->first();
        } catch (\Exception) {}

        $teacherId = $teacher?->id;

        $stats = [
            'today_classes'   => $this->safeCount(\App\Models\ClassSession::class, ['teacher_id' => $teacherId, 'status' => 'SCHEDULED']),
            'total_students'  => 0, // resolved via batch enrollments
            'total_subjects'  => $teacherId ? \App\Models\SubjectTeacherAssignment::where('teacher_id', $teacherId)->where('is_active', true)->count() : 0,
            'pending_results' => 0,
        ];

        // Today's classes for this teacher
        $todayClasses = collect();
        try {
            $todayClasses = \App\Models\ClassSession::with(['timeline.subject', 'timeline.module'])
                ->where('teacher_id', $teacherId)
                ->whereIn('status', ['SCHEDULED', 'RUNNING', 'COMPLETED'])
                ->latest()
                ->take(6)
                ->get();
        } catch (\Exception) {}

        // Upcoming exams for teacher's subjects
        $upcomingExams = collect();
        try {
            $subjectIds = \App\Models\SubjectTeacherAssignment::where('teacher_id', $teacherId)
                ->pluck('subject_id');
            $upcomingExams = \App\Models\Exam::with('subject')
                ->whereIn('subject_id', $subjectIds)
                ->where('status', 'SCHEDULED')
                ->orderBy('exam_date')
                ->take(5)
                ->get();
        } catch (\Exception) {}

        // Classes where attendance not marked (completed but no attendance records)
        $attendancePending = collect();
        try {
            $attendancePending = \App\Models\ClassSession::with(['timeline.subject', 'timeline'])
                ->where('teacher_id', $teacherId)
                ->where('class_conducted', true)
                ->whereDoesntHave('attendances')
                ->take(5)
                ->get();
        } catch (\Exception) {}

        // Exams completed but results not entered
        $pendingResults = collect();
        try {
            $pendingResults = \App\Models\Exam::with('subject')
                ->whereIn('subject_id', $subjectIds ?? [])
                ->where('status', 'COMPLETED')
                ->whereDoesntHave('results')
                ->take(5)
                ->get();
        } catch (\Exception) {}

        return view('teacher.dashboard', compact(
            'stats', 'todayClasses', 'upcomingExams', 'attendancePending', 'pendingResults'
        ));
    }

    private function safeCount(string $model, array $where = []): int
    {
        try {
            $q = $model::query();
            foreach ($where as $col => $val) {
                if ($val !== null) $q->where($col, $val);
            }
            return $q->count();
        } catch (\Exception) {
            return 0;
        }
    }
}
