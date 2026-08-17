<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\ClassSession;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    private function student(): ?Student
    {
        return Student::where('email', auth()->user()->email)->first();
    }

    public function today()
    {
        $student  = $this->student();
        $batchIds = Enrollment::where('student_id', $student?->id)->where('status', 'ACTIVE')->pluck('batch_id');

        $sessions = ClassSession::with(['subject', 'batch', 'teacher', 'routineEntry.slot', 'moduleCovered'])
            ->whereIn('batch_id', $batchIds)
            ->whereDate('session_date', today())
            ->orderBy('start_time')
            ->get();

        $today = \Carbon\Carbon::today();

        return view('student.classes.today', compact('sessions', 'today'));
    }

    public function index()
    {
        $student  = $this->student();
        $batchIds = Enrollment::where('student_id', $student?->id)->where('status', 'ACTIVE')->pluck('batch_id');

        $classes = ClassSession::with(['subject', 'batch', 'teacher', 'routineEntry.slot', 'moduleCovered'])
            ->whereIn('batch_id', $batchIds)
            ->orderBy('session_date', 'desc')
            ->get();

        return view('student.classes.index', compact('classes'));
    }

    public function show(ClassSession $class)
    {
        $student = $this->student();

        if ($student) {
            $guard = \App\Services\EnforcementService::canJoinClass($student);
            if (!$guard['allowed']) {
                return redirect()->route('student.fees.index')->with('error', $guard['reason']);
            }
        }

        $class->load(['subject', 'batch', 'teacher', 'routineEntry.slot', 'moduleCovered']);
        $attendance = $class->attendances()->where('student_id', $student?->id)->first();

        return view('student.classes.show', compact('class', 'attendance'));
    }

    public function calendar()
    {
        $student  = $this->student();
        $batchIds = Enrollment::where('student_id', $student?->id)->where('status', 'ACTIVE')->pluck('batch_id');

        $classes = ClassSession::with(['subject', 'batch', 'teacher', 'routineEntry.slot'])
            ->whereIn('batch_id', $batchIds)
            ->whereNotNull('session_date')
            ->get();

        $events = $classes->map(fn($c) => [
            'id'           => $c->id,
            'title'        => ($c->subject?->name ?? 'Class') . ' — ' . ($c->batch?->name ?? ''),
            'subject_name' => $c->subject?->name ?? '—',
            'batch_name'   => $c->batch?->name ?? '—',
            'slot_name'    => $c->routineEntry?->slot?->name ?? '',
            'teacher_name' => $c->teacher?->name ?? 'Faculty',
            'date'         => $c->session_date?->toDateString(),
            'start_time'   => $c->start_time ? \Carbon\Carbon::parse($c->start_time)->format('h:i A') : 'TBA',
            'meeting_link' => $c->meeting_link,
            'status'       => $c->status,
        ]);

        return view('student.calendar.index', compact('events', 'classes'));
    }
}
