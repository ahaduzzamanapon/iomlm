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
    private function teacher(): ?Teacher
    {
        return Teacher::where('email', auth()->user()->email)->first();
    }

    /**
     * Sessions that have been conducted (or are today) — teacher can manage attendance.
     */
    public function index()
    {
        $teacher = $this->teacher();

        $sessions = ClassSession::with(['subject', 'batch', 'routineEntry.slot', 'moduleCovered', 'attendances.student'])
            ->where('teacher_id', $teacher?->id)
            ->whereIn('status', ['SCHEDULED', 'COMPLETED'])
            ->orderBy('session_date', 'desc')
            ->get();

        return view('teacher.attendance.index', compact('sessions'));
    }

    /**
     * Show attendance form for a specific session.
     */
    public function mark(ClassSession $class)
    {
        $class->load(['subject', 'batch', 'routineEntry.slot', 'attendances.student', 'moduleCovered']);

        $batchStudents = Enrollment::with('student')
            ->where('batch_id', $class->batch_id)
            ->where('status', 'ACTIVE')
            ->get();

        $existingAttendance = $class->attendances->keyBy('student_id');

        return view('teacher.attendance.mark', compact('class', 'batchStudents', 'existingAttendance'));
    }

    /**
     * Save attendance for a session.
     */
    public function save(Request $request, ClassSession $class)
    {
        $request->validate([
            'attendance'   => 'nullable|array',
            'attendance.*' => 'in:PRESENT,ABSENT,LATE,EXCUSED',
        ]);

        foreach ($request->input('attendance', []) as $studentId => $status) {
            $enrollment = Enrollment::where('student_id', $studentId)
                ->where('batch_id', $class->batch_id)
                ->first();

            Attendance::updateOrCreate(
                ['class_session_id' => $class->id, 'student_id' => $studentId],
                [
                    'status'        => $status,
                    'enrollment_id' => $enrollment?->id,
                ]
            );
        }

        // Mark session as completed if all attendance done
        if ($class->status === 'SCHEDULED') {
            $class->update([
                'class_conducted' => true,
                'teacher_present' => true,
                'status'          => 'COMPLETED',
                'ended_at'        => now(),
            ]);
        }

        return redirect()->route('teacher.attendance.index')
            ->with('success', 'Attendance saved successfully.');
    }
}
