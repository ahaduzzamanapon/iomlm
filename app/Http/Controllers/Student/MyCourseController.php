<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\FinalMark;
use App\Models\Result;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\ClassSession;

class MyCourseController extends Controller
{
    public function index()
    {
        $student = Student::where('user_id', auth()->id())->first();

        if (!$student) {
            return view('student.my-course.index', ['enrollments' => collect()]);
        }

        $enrollments = Enrollment::with([
            'course.semesters',
            'course.subjects',
            'batch.semesterPosition.currentSemester',
            'batch.academicYear',
            'semester',
        ])
        ->where('student_id', $student->id)
        ->orderByDesc('enrolled_at')
        ->get()
        ->map(function ($enrollment) use ($student) {
            $batchId   = $enrollment->batch_id;
            $courseId  = $enrollment->course_id;

            // Total subjects for this course
            $totalSubjects = $enrollment->course?->courseSubjectMaps()->count() ?? 0;

            // Current semester name from batch position
            $currentSemester = $enrollment->batch?->semesterPosition?->currentSemester ?? $enrollment->semester;

            // Attendance stats
            $classSessions = ClassSession::where('batch_id', $batchId)
                ->where('status', 'COMPLETED')
                ->pluck('id');
            $totalSessions  = $classSessions->count();
            $presentCount   = $totalSessions > 0
                ? Attendance::whereIn('class_session_id', $classSessions)
                    ->where('student_id', $student->id)
                    ->whereIn('status', ['PRESENT', 'LATE'])
                    ->count()
                : 0;
            $attendancePct = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100) : 0;

            // Exam results count
            $resultCount = Result::where('student_id', $student->id)
                ->whereHas('exam', fn($q) => $q->whereHas('subject', fn($q2) =>
                    $q2->whereHas('courseSubjectMaps', fn($q3) => $q3->where('course_id', $courseId))
                ))
                ->count();

            // Final marks (if any generated)
            $finalMarks = FinalMark::where('student_id', $student->id)
                ->where('batch_id', $batchId)
                ->get();

            $enrollment->_total_subjects    = $totalSubjects;
            $enrollment->_current_semester  = $currentSemester;
            $enrollment->_attendance_pct    = $attendancePct;
            $enrollment->_present_count     = $presentCount;
            $enrollment->_total_sessions    = $totalSessions;
            $enrollment->_result_count      = $resultCount;
            $enrollment->_final_marks       = $finalMarks;

            return $enrollment;
        });

        return view('student.my-course.index', compact('enrollments', 'student'));
    }
}
