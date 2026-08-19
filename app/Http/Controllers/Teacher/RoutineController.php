<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\RoutineEntry;
use App\Models\RoutineSlot;
use App\Models\Teacher;

class RoutineController extends Controller
{
    public function index()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        $slots   = RoutineSlot::orderBy('sort_order')->get();
        $days    = ['SAT', 'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI'];
        $weekends = ['FRI', 'SAT'];

        $entries = RoutineEntry::with(['batch.course', 'slot', 'subject'])
            ->where('teacher_id', $teacher?->id)
            ->get()
            ->groupBy(['slot_id', 'day_of_week']);

        // Today's sessions keyed by routine_entry_id — for live meeting link
        $todaySessions = ClassSession::where('teacher_id', $teacher?->id)
            ->whereDate('session_date', today())
            ->whereNotNull('meeting_link')
            ->get()
            ->keyBy('routine_entry_id');

        return view('teacher.routine.index', compact('slots', 'days', 'entries', 'weekends', 'teacher', 'todaySessions'));
    }
}
