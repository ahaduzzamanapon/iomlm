<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Teacher;
use App\Models\Attendance;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $teacher = Teacher::where('email', auth()->user()->email)->first();
        $teacherId = $teacher?->id;

        $classes = ClassSession::with(['timeline.subject', 'timeline.module', 'timeline.batch'])
            ->where('teacher_id', $teacherId)
            ->latest()
            ->get();

        return view('teacher.classes.index', compact('classes'));
    }

    public function conduct(ClassSession $class)
    {
        $class->load(['timeline.subject', 'timeline.module', 'timeline.batch', 'attendances.student']);
        
        // Fetch enrolled students in the batch if attendance not created yet
        $batchStudents = \App\Models\Enrollment::with('student')
            ->where('batch_id', $class->timeline->batch_id)
            ->where('status', 'ACTIVE')
            ->get();

        return view('teacher.classes.conduct', compact('class', 'batchStudents'));
    }

    public function markComplete(Request $request, ClassSession $class)
    {
        $request->validate([
            'attendance' => 'nullable|array',
        ]);

        $class->update([
            'teacher_present' => true,
            'class_conducted' => true,
            'status'          => 'COMPLETED',
            'ended_at'        => now(),
            'notes'           => $request->input('notes'),
        ]);

        // Save attendance for each student
        $attendanceData = $request->input('attendance', []);
        foreach ($attendanceData as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'class_session_id' => $class->id,
                    'student_id'       => $studentId,
                ],
                [
                    'status' => $status,
                ]
            );
        }

        return redirect()->route('teacher.classes.index')
            ->with('success', 'Class completed and attendance marked successfully!');
    }

    public function markCancelled(Request $request, ClassSession $class)
    {
        $class->update([
            'teacher_present' => false,
            'class_conducted' => false,
            'status'          => 'CANCELLED',
            'notes'           => $request->input('reason', 'Teacher absent / class cancelled'),
        ]);

        // Trigger Reschedule: append a new Timeline slot per §6.2
        $oldTimeline = $class->timeline;
        if ($oldTimeline) {
            $newDate = \Carbon\Carbon::parse($oldTimeline->scheduled_date)->addDays(7)->toDateString();
            
            $newTimeline = \App\Models\Timeline::create([
                'batch_id'           => $oldTimeline->batch_id,
                'subject_id'         => $oldTimeline->subject_id,
                'module_id'          => $oldTimeline->module_id,
                'scheduled_date'     => $newDate,
                'status'             => 'SCHEDULED',
                'parent_timeline_id' => $oldTimeline->id,
                'reschedule_count'   => $oldTimeline->reschedule_count + 1,
            ]);

            $meetCode = strtolower(\Illuminate\Support\Str::random(3) . '-' . \Illuminate\Support\Str::random(4) . '-' . \Illuminate\Support\Str::random(3));
            ClassSession::create([
                'timeline_id'  => $newTimeline->id,
                'teacher_id'   => $class->teacher_id,
                'meeting_link' => "https://meet.google.com/{$meetCode}",
                'status'       => 'SCHEDULED',
            ]);
        }

        return redirect()->route('teacher.classes.index')
            ->with('success', 'Class marked as CANCELLED. Automatic reschedule timeline slot created for next week.');
    }
}
