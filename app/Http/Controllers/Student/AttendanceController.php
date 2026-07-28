<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $student = Student::where('email', auth()->user()->email)->first();
        $attendances = Attendance::with('classSession.timeline.subject', 'classSession.timeline.module')
            ->where('student_id', $student?->id)
            ->latest()
            ->get();

        $total = $attendances->count();
        $present = $attendances->where('status', 'PRESENT')->count();
        $percentage = $total > 0 ? round(($present / $total) * 100, 1) : 100;

        return view('student.attendance.index', compact('attendances', 'total', 'present', 'percentage'));
    }
}
