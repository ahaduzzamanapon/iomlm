<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\RoutineEntry;
use App\Models\RoutineSlot;
use App\Models\Student;

class RoutineController extends Controller
{
    public function index()
    {
        $student  = Student::where('user_id', auth()->id())->first();
        $enrollments = Enrollment::where('student_id', $student?->id)
            ->where('status', 'ACTIVE')
            ->get();
        $batchIds = $enrollments->pluck('batch_id');
        $studentGender = strtoupper($student?->gender ?? '');
        $enrollmentGroups = $enrollments->pluck('group_tag')->filter()->unique()->toArray();

        $slots   = RoutineSlot::orderBy('sort_order')->get();
        $days    = ['SAT', 'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI'];
        $weekends = ['FRI', 'SAT'];

        $entries = RoutineEntry::with(['batch.course', 'slot', 'subject', 'teacher'])
            ->whereIn('batch_id', $batchIds)
            ->where(function ($q) use ($studentGender, $enrollmentGroups) {
                $q->whereNull('group_tag')
                  ->orWhere('group_tag', 'ALL');
                if ($studentGender) {
                    $q->orWhere('group_tag', $studentGender);
                }
                if (!empty($enrollmentGroups)) {
                    $q->orWhereIn('group_tag', $enrollmentGroups);
                }
            })
            ->get()
            ->groupBy(['slot_id', 'day_of_week']);

        // Today's sessions keyed by routine_entry_id — for live meeting link (filtered by group)
        $todaySessions = ClassSession::whereIn('batch_id', $batchIds)
            ->where(function ($q) use ($studentGender, $enrollmentGroups) {
                $q->whereNull('group_tag')
                  ->orWhere('group_tag', 'ALL');
                if ($studentGender) {
                    $q->orWhere('group_tag', $studentGender);
                }
                if (!empty($enrollmentGroups)) {
                    $q->orWhereIn('group_tag', $enrollmentGroups);
                }
            })
            ->whereDate('session_date', today())
            ->whereNotNull('meeting_link')
            ->get()
            ->keyBy('routine_entry_id');

        return view('student.routine.index', compact('slots', 'days', 'entries', 'weekends', 'todaySessions'));
    }
}
