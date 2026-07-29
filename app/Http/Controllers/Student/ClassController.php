<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\ClassSession;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $student = Student::where('email', auth()->user()->email)->first();
        $batchIds = Enrollment::where('student_id', $student?->id)->where('status', 'ACTIVE')->pluck('batch_id');

        $classes = ClassSession::with(['timeline.subject', 'timeline.module', 'timeline.batch', 'teacher'])
            ->whereHas('timeline', fn($q) => $q->whereIn('batch_id', $batchIds))
            ->latest()
            ->get();

        return view('student.classes.index', compact('classes'));
    }

    public function calendar()
    {
        $student = Student::where('email', auth()->user()->email)->first();
        $batchIds = Enrollment::where('student_id', $student?->id)->where('status', 'ACTIVE')->pluck('batch_id');

        $classes = ClassSession::with(['timeline.subject', 'timeline.module', 'timeline.batch', 'teacher'])
            ->whereHas('timeline', fn($q) => $q->whereIn('batch_id', $batchIds))
            ->get();

        $events = $classes->map(function($c) {
            return [
                'id'             => $c->id,
                'title'          => ($c->timeline->subject->name ?? 'Class') . ' (Module ' . ($c->timeline->module->sequence_no ?? 1) . ')',
                'subject_name'   => $c->timeline->subject->name ?? '—',
                'module_title'   => $c->timeline->module->title ?? '—',
                'teacher_name'   => $c->teacher->name ?? 'Faculty',
                'scheduled_date' => $c->timeline->scheduled_date,
                'start_time'     => $c->start_time ? \Carbon\Carbon::parse($c->start_time)->format('h:i A') : 'TBA',
                'meeting_link'   => $c->meeting_link,
                'status'         => $c->status,
            ];
        });

        return view('student.calendar.index', compact('events', 'classes'));
    }
}
