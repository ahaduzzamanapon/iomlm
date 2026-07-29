<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Teacher;
use App\Models\Timeline;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClassSessionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = ClassSession::with(['timeline.subject', 'timeline.module', 'timeline.batch', 'teacher', 'mergedGroups.batch']);

        if ($status) {
            $query->where('status', $status);
        }

        $classes = $query->latest()->get();
        $teachers = Teacher::where('is_active', true)->orderBy('name')->get();

        return view('admin.classes.index', compact('classes', 'teachers', 'status'));
    }

    public function show(ClassSession $class)
    {
        $class->load(['timeline.subject', 'timeline.module', 'timeline.batch', 'teacher', 'attendances.student', 'mergedGroups.batch']);
        $teachers = Teacher::where('is_active', true)->orderBy('name')->get();

        return view('admin.classes.show', compact('class', 'teachers'));
    }

    public function updateSchedule(Request $request, ClassSession $class)
    {
        $validated = $request->validate([
            'scheduled_date' => 'required|date',
            'start_time'     => 'nullable|string',
            'teacher_id'     => 'nullable|exists:teachers,id',
            'meeting_link'   => 'nullable|string',
        ]);

        $class->timeline->update([
            'scheduled_date' => $validated['scheduled_date'],
            'status'         => 'SCHEDULED',
        ]);

        $meetLink = $validated['meeting_link'];
        if (!$meetLink) {
            $meetCode = strtolower(Str::random(3) . '-' . Str::random(4) . '-' . Str::random(3));
            $meetLink = "https://meet.google.com/{$meetCode}";
        }

        $class->update([
            'teacher_id'   => $validated['teacher_id'] ?? $class->teacher_id,
            'start_time'   => $validated['start_time'] ?? null,
            'meeting_link' => $meetLink,
            'status'       => 'SCHEDULED',
        ]);

        return back()->with('success', 'Class schedule, date, start time & meeting link updated successfully!');
    }
}
