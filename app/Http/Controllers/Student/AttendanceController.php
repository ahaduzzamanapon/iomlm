<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function index()
    {
        $student = Student::where('email', auth()->user()->email)->first();

        $attendances = Attendance::with(['classSession.subject', 'classSession.batch', 'classSession.routineEntry.slot'])
            ->where('student_id', $student?->id)
            ->orderByDesc('created_at')
            ->get();

        $total      = $attendances->count();
        $present    = $attendances->where('status', 'PRESENT')->count();
        $absent     = $attendances->where('status', 'ABSENT')->count();
        $late       = $attendances->where('status', 'LATE')->count();
        $percentage = $total > 0 ? round(($present / $total) * 100, 1) : 100;

        return view('student.attendance.index', compact('attendances', 'total', 'present', 'absent', 'late', 'percentage'));
    }
}
