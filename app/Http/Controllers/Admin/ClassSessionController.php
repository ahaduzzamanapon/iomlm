<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\ClassSession;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\SubjectModule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClassSessionController extends Controller
{
    public function index(Request $request)
    {
        $status     = $request->query('status');
        $batchId    = $request->query('batch_id');
        $dateFilter = $request->query('date');

        $query = ClassSession::with(['subject', 'batch', 'teacher', 'routineEntry.slot', 'moduleCovered', 'attendances'])
            ->orderBy('session_date', 'desc')
            ->orderBy('start_time', 'asc');

        if ($status)     $query->where('status', $status);
        if ($batchId)    $query->where('batch_id', $batchId);
        if ($dateFilter) $query->whereDate('session_date', $dateFilter);

        $classes  = $query->get();
        $teachers = Teacher::where('is_active', true)->orderBy('name')->get();
        $batches  = Batch::orderBy('name')->get();

        return view('admin.classes.index', compact('classes', 'teachers', 'status', 'batches', 'batchId', 'dateFilter'));
    }

    public function show(ClassSession $class)
    {
        $class->load(['subject', 'batch', 'teacher', 'routineEntry.slot', 'moduleCovered', 'attendances.student', 'attendances.enrollment']);

        // All active students enrolled in this batch
        $batchStudents = \App\Models\Enrollment::with('student')
            ->where('batch_id', $class->batch_id)
            ->where('status', 'ACTIVE')
            ->get();

        $teachers = Teacher::where('is_active', true)->orderBy('name')->get();
        $modules  = SubjectModule::where('subject_id', $class->subject_id)->orderBy('sequence_no')->get();

        return view('admin.classes.show', compact('class', 'batchStudents', 'teachers', 'modules'));
    }

    /**
     * Update session date, meeting link, teacher for a specific class session.
     */
    public function updateSchedule(Request $request, ClassSession $class)
    {
        $validated = $request->validate([
            'session_date'      => 'required|date',
            'start_time'        => 'nullable|string',
            'teacher_id'        => 'nullable|exists:teachers,id',
            'meeting_link'      => 'nullable|string|max:500',
            'module_covered_id' => 'nullable|exists:subject_modules,id',
        ]);

        $class->update([
            'session_date'      => $validated['session_date'],
            'start_time'        => $validated['start_time'] ?? $class->start_time,
            'teacher_id'        => $validated['teacher_id'] ?? $class->teacher_id,
            'meeting_link'      => $validated['meeting_link'] ?? $class->meeting_link,
            'module_covered_id' => $validated['module_covered_id'] ?? $class->module_covered_id,
            'status'            => 'SCHEDULED',
        ]);

        return back()->with('success', 'Class session updated successfully.');
    }

    /**
     * Auto-generate a real Zoom Meeting link via Server-to-Server OAuth Zoom API
     */
    public function generateZoomLink(ClassSession $class)
    {
        $class->load(['subject', 'batch']);

        $topic = "{$class->subject->name} ({$class->batch->name})";
        $dateStr = $class->session_date instanceof Carbon ? $class->session_date->format('Y-m-d') : Carbon::parse($class->session_date)->format('Y-m-d');
        $startTime = Carbon::parse($dateStr . ' ' . ($class->start_time ?? '10:00:00'))->toIso8601String();

        try {
            $meetingSvc = new \App\Services\MeetingService();
            $result = $meetingSvc->generate($topic, $startTime);

            if ($result && isset($result['join_url'])) {
                $class->update([
                    'meeting_link' => $result['join_url'],
                    'meeting_id'   => $result['meeting_id'] ?? null,
                    'status'       => 'SCHEDULED',
                ]);
                return back()->with('success', "Real Zoom Meeting generated successfully! Join Link: {$result['join_url']}");
            }

            return back()->with('error', 'Meeting provider is not configured for Zoom in Settings → Meeting Platform.');
        } catch (\Exception $e) {
            return back()->with('error', 'Zoom API Error: ' . $e->getMessage());
        }
    }

    /**
     * Mark a session as completed and save attendance.
     */
    public function markComplete(Request $request, ClassSession $class)
    {
        $request->validate([
            'attendance'        => 'nullable|array',
            'attendance.*'      => 'in:PRESENT,ABSENT,LATE,EXCUSED',
            'module_covered_id' => 'nullable|exists:subject_modules,id',
            'notes'             => 'nullable|string',
        ]);

        $class->update([
            'teacher_present'   => true,
            'class_conducted'   => true,
            'status'            => 'COMPLETED',
            'ended_at'          => now(),
            'module_covered_id' => $request->input('module_covered_id'),
            'notes'             => $request->input('notes'),
        ]);

        foreach ($request->input('attendance', []) as $studentId => $status) {
            $enrollment = \App\Models\Enrollment::where('student_id', $studentId)
                ->where('batch_id', $class->batch_id)
                ->first();
            \App\Models\Attendance::updateOrCreate(
                ['class_session_id' => $class->id, 'student_id' => $studentId],
                ['status' => $status, 'enrollment_id' => $enrollment?->id]
            );
        }

        return back()->with('success', 'Session marked complete. Attendance saved.');
    }

    /**
     * Cancel a session (teacher absent etc.)
     */
    public function markCancelled(Request $request, ClassSession $class)
    {
        $class->update([
            'teacher_present' => false,
            'class_conducted' => false,
            'status'          => 'CANCELLED',
            'notes'           => $request->input('reason', 'Class cancelled'),
        ]);

        return back()->with('success', 'Session marked as cancelled.');
    }
}
