<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $teacher = Teacher::where('email', auth()->user()->email)->first();
        $classes = ClassSession::with(['timeline.subject', 'timeline.module', 'attendances.student'])
            ->where('teacher_id', $teacher?->id)
            ->where('class_conducted', true)
            ->latest()
            ->get();

        return view('teacher.attendance.index', compact('classes'));
    }

    public function mark(ClassSession $class)
    {
        $class->load(['timeline.subject', 'timeline.module', 'timeline.batch', 'attendances.student']);

        $batchStudents = Enrollment::with('student')
            ->where('batch_id', $class->timeline->batch_id)
            ->where('status', 'ACTIVE')
            ->get();

        return view('teacher.classes.conduct', compact('class', 'batchStudents'));
    }

    public function save(Request $request, ClassSession $class)
    {
        $request->validate([
            'attendance'   => 'nullable|array',
            'attendance.*' => 'in:PRESENT,ABSENT,LATE,EXCUSED',
        ]);

        foreach ($request->input('attendance', []) as $studentId => $status) {
            Attendance::updateOrCreate(
                ['class_session_id' => $class->id, 'student_id' => $studentId],
                ['status' => $status]
            );
        }

        return redirect()->route('teacher.attendance.index')
            ->with('success', 'Attendance saved successfully.');
    }
}
