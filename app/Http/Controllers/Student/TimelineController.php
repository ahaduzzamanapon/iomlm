<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\ClassSession;
use App\Models\Attendance;

class TimelineController extends Controller
{
    public function index()
    {
        $student   = Student::where('user_id', auth()->id())->first();
        $studentId = $student?->id;

        $batchIds = Enrollment::where('student_id', $studentId)
            ->where('status', 'ACTIVE')
            ->pluck('batch_id');

        $sessions = ClassSession::with(['subject', 'batch', 'routineEntry.slot', 'moduleCovered', 'attendances'])
            ->whereIn('batch_id', $batchIds)
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->get();

        // Attendance map: session_id => status
        $attendanceMap = Attendance::where('student_id', $studentId)
            ->pluck('status', 'class_session_id');

        return view('student.timeline.index', compact('sessions', 'attendanceMap'));
    }
}
