<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\ClassSession;
use App\Models\HolidayCalendar;
use App\Models\RoutineEntry;
use App\Models\RoutineSlot;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\SubjectTeacherAssignment;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoutineController extends Controller
{
    // Weekend days (configurable via Settings, defaults: FRI, SAT)
    private function weekends(): array
    {
        try {
            $v = Setting::where('key', 'weekend_days')->value('value');
            return $v ? explode(',', $v) : ['FRI', 'SAT'];
        } catch (\Exception $e) {
            return ['FRI', 'SAT'];
        }
    }

    public function index(Request $request)
    {
        $slots   = RoutineSlot::orderBy('sort_order')->orderBy('start_time')->get();
        $batches = Batch::where('status', 'ACTIVE')
            ->with([
                'course.semesters',
                'course.courseSubjectMaps.subject',
                'semesterPosition.currentSemester',
            ])
            ->orderBy('name')
            ->get();
        $days    = ['SAT', 'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI'];
        $weekends = $this->weekends();

        $selectedBatchId = $request->query('batch_id');
        $selectedGroup   = $request->query('group'); // 'ALL', 'MALE', 'FEMALE', 'GROUP_A', 'GROUP_B'

        // Load all entries to accurately detect conflicts (Batch overlap & Teacher overlap)
        $allEntries = RoutineEntry::with(['batch.course', 'slot', 'subject', 'teacher', 'classSession'])->get();

        $teacherCounts = [];
        foreach ($allEntries as $e) {
            if (!empty($e->teacher_id)) {
                $tKey = $e->slot_id . '_' . $e->day_of_week . '_' . $e->teacher_id;
                $teacherCounts[$tKey] = ($teacherCounts[$tKey] ?? 0) + 1;
            }
        }

        // Group-aware conflict detection:
        // Two entries in the same slot & day for the same batch conflict ONLY if:
        // either entry is 'ALL' (or null), OR both entries have the identical group_tag.
        // If one is 'MALE' and the other is 'FEMALE', they DO NOT conflict.
        foreach ($allEntries as $e) {
            $eGroup = $e->group_tag ?: 'ALL';
            $tKey   = !empty($e->teacher_id) ? ($e->slot_id . '_' . $e->day_of_week . '_' . $e->teacher_id) : null;

            $hasBatchConflict = $allEntries->contains(function ($other) use ($e, $eGroup) {
                if ($other->id === $e->id) return false;
                if ($other->slot_id !== $e->slot_id || $other->day_of_week !== $e->day_of_week || $other->batch_id !== $e->batch_id) {
                    return false;
                }
                $oGroup = $other->group_tag ?: 'ALL';
                return $eGroup === 'ALL' || $oGroup === 'ALL' || $eGroup === $oGroup;
            });

            $hasTeacherConflict = $tKey && (($teacherCounts[$tKey] ?? 0) > 1);

            if ($hasBatchConflict || $hasTeacherConflict) {
                $e->is_override = true;
                $e->conflict_type = $hasBatchConflict && $hasTeacherConflict
                    ? 'Batch & Teacher Overlap'
                    : ($hasBatchConflict ? 'Batch Overlap' : 'Teacher Overlap');
            }
        }

        $filteredEntries = $allEntries;
        if ($selectedBatchId) {
            $filteredEntries = $filteredEntries->where('batch_id', $selectedBatchId);
        }
        if ($selectedGroup) {
            $filteredEntries = $filteredEntries->filter(function($e) use ($selectedGroup) {
                $gt = $e->group_tag ?: 'ALL';
                return $gt === $selectedGroup || ($selectedGroup !== 'ALL' && $gt === 'ALL');
            });
        }

        $entries = $filteredEntries->groupBy(['slot_id', 'day_of_week']);

        // Assign a color per batch (index-based)
        $batchColors = [];
        foreach ($batches as $i => $b) {
            $batchColors[$b->id] = RoutineEntry::BATCH_COLORS[$i % count(RoutineEntry::BATCH_COLORS)];
        }

        $subjects  = Subject::where('is_active', true)->orderBy('name')->get();
        $teachers  = Teacher::where('is_active', true)->orderBy('name')->get();
        $holidays  = HolidayCalendar::pluck('date')->toArray();

        $subjectTeachers = \App\Models\SubjectTeacherAssignment::with('teacher')
            ->get()
            ->groupBy('subject_id')
            ->map(function ($assignments) {
                return $assignments->map(fn($a) => [
                    'id'   => $a->teacher_id,
                    'name' => $a->teacher?->name,
                ])->filter(fn($t) => !empty($t['id']) && !empty($t['name']))->unique('id')->values()->all();
            })->all();

        $batchData = $batches->mapWithKeys(function($b) {
            $runningSem = $b->semesterPosition?->currentSemester
                ?? $b->course?->semesters->sortBy('sequence_no')->first();
            $runningSemId = $runningSem?->id;

            return [$b->id => [
                'id'                  => $b->id,
                'name'                => $b->name,
                'course_type'         => $b->course?->type ?? 'SEMESTER_BASED',
                'current_semester_id' => $runningSemId,
                'has_groups'          => (bool) ($runningSem?->has_groups ?? false),
                'group_type'          => $runningSem?->group_type ?? 'NONE',
                'split_count'         => $runningSem?->split_count ?? 2,
                'semesters'           => $b->course?->semesters->map(fn($s) => [
                    'id'          => $s->id,
                    'name'        => $s->name,
                    'has_groups'  => (bool) $s->has_groups,
                    'group_type'  => $s->group_type ?? 'NONE',
                    'split_count' => $s->split_count ?? 2,
                ])->values()->all() ?? [],
                'subject_maps'        => $b->course?->courseSubjectMaps->map(fn($m) => [
                    'subject_id'  => $m->subject_id,
                    'semester_id' => $m->semester_id,
                    'group_mode'  => $m->group_mode ?? 'INHERIT',
                    'code'        => $m->subject->code ?? '',
                    'name'        => $m->subject->name ?? '',
                ])->values()->all() ?? [],
            ]];
        })->all();

        return view('admin.routine.index', compact(
            'slots', 'batches', 'batchData', 'days', 'entries', 'weekends',
            'batchColors', 'subjects', 'teachers', 'selectedBatchId', 'selectedGroup', 'holidays', 'subjectTeachers'
        ));
    }

    public function storeSlot(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'start_time' => 'required',
            'end_time'   => 'required',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $startTime = Carbon::parse($validated['start_time'])->format('H:i:s');
        } catch (\Exception $e) {
            $startTime = $validated['start_time'];
        }

        try {
            $endTime = Carbon::parse($validated['end_time'])->format('H:i:s');
        } catch (\Exception $e) {
            $endTime = $validated['end_time'];
        }

        RoutineSlot::create([
            'name'       => $validated['name'],
            'start_time' => $startTime,
            'end_time'   => $endTime,
            'sort_order' => $validated['sort_order'] ?? RoutineSlot::count(),
        ]);

        return back()->with('success', "Time slot '{$validated['name']}' created.");
    }

    public function updateSlot(Request $request, RoutineSlot $slot)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'start_time' => 'required',
            'end_time'   => 'required',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $startTime = Carbon::parse($validated['start_time'])->format('H:i:s');
        } catch (\Exception $e) {
            $startTime = $validated['start_time'];
        }

        try {
            $endTime = Carbon::parse($validated['end_time'])->format('H:i:s');
        } catch (\Exception $e) {
            $endTime = $validated['end_time'];
        }

        $slot->update([
            'name'       => $validated['name'],
            'start_time' => $startTime,
            'end_time'   => $endTime,
            'sort_order' => $validated['sort_order'] ?? $slot->sort_order,
        ]);
        return back()->with('success', 'Time slot updated.');
    }

    public function destroySlot(RoutineSlot $slot)
    {
        $slot->delete();
        return back()->with('success', 'Time slot deleted.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_id'         => 'required|exists:batches,id',
            'slot_id'          => 'required|exists:routine_slots,id',
            'day_of_week'      => 'required|in:SAT,SUN,MON,TUE,WED,THU,FRI',
            'subject_id'       => 'nullable|exists:subjects,id',
            'teacher_id'       => 'nullable|exists:teachers,id',
            'group_tag'        => 'nullable|string|max:30',
            'class_session_id' => 'nullable|exists:class_sessions,id',
            'title'            => 'nullable|string|max:200',
            'color'            => 'nullable|string|max:20',
        ]);

        $groupTag = !empty($validated['group_tag']) ? $validated['group_tag'] : 'ALL';
        $validated['group_tag'] = $groupTag;

        // Group-aware conflict detection (Batch overlap & Teacher overlap)
        // Two entries in same batch+slot+day conflict ONLY if either is 'ALL' or both share same group_tag.
        $batchConflict = RoutineEntry::where('slot_id', $validated['slot_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where('batch_id', $validated['batch_id'])
            ->where(function($q) use ($groupTag) {
                if ($groupTag !== 'ALL') {
                    $q->whereNull('group_tag')
                      ->orWhere('group_tag', 'ALL')
                      ->orWhere('group_tag', $groupTag);
                }
            })
            ->exists();

        $teacherConflict = false;
        if (!empty($validated['teacher_id'])) {
            $teacherConflict = RoutineEntry::where('slot_id', $validated['slot_id'])
                ->where('day_of_week', $validated['day_of_week'])
                ->where('teacher_id', $validated['teacher_id'])
                ->exists();
        }

        $isOverride = $batchConflict || $teacherConflict;

        $entry = RoutineEntry::create(array_merge($validated, ['is_override' => $isOverride]));
        $this->syncFutureSessionsForEntry($entry);

        if ($batchConflict && $teacherConflict) {
            return back()->with('warning', '⚠️ Conflict detected: Both Batch and Teacher are already scheduled in this slot! Marked in RED.');
        } elseif ($batchConflict) {
            return back()->with('warning', '⚠️ Batch Conflict: This batch already has a class in this time slot for this group! Marked in RED.');
        } elseif ($teacherConflict) {
            return back()->with('warning', '⚠️ Teacher Conflict: Teacher is already teaching another class in this time slot! Marked in RED.');
        }

        return back()->with('success', 'Routine entry added.');
    }

    public function update(Request $request, RoutineEntry $entry)
    {
        $validated = $request->validate([
            'batch_id'         => 'required|exists:batches,id',
            'slot_id'          => 'required|exists:routine_slots,id',
            'day_of_week'      => 'required|in:SAT,SUN,MON,TUE,WED,THU,FRI',
            'subject_id'       => 'nullable|exists:subjects,id',
            'teacher_id'       => 'nullable|exists:teachers,id',
            'group_tag'        => 'nullable|string|max:30',
            'class_session_id' => 'nullable|exists:class_sessions,id',
            'title'            => 'nullable|string|max:200',
            'color'            => 'nullable|string|max:20',
        ]);

        $groupTag = !empty($validated['group_tag']) ? $validated['group_tag'] : ($entry->group_tag ?: 'ALL');
        $validated['group_tag'] = $groupTag;

        $oldDayOfWeek = $entry->day_of_week;

        // Group-aware conflict detection (Batch overlap & Teacher overlap)
        $batchConflict = RoutineEntry::where('slot_id', $validated['slot_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where('batch_id', $validated['batch_id'])
            ->where('id', '!=', $entry->id)
            ->where(function($q) use ($groupTag) {
                if ($groupTag !== 'ALL') {
                    $q->whereNull('group_tag')
                      ->orWhere('group_tag', 'ALL')
                      ->orWhere('group_tag', $groupTag);
                }
            })
            ->exists();

        $teacherConflict = false;
        if (!empty($validated['teacher_id'])) {
            $teacherConflict = RoutineEntry::where('slot_id', $validated['slot_id'])
                ->where('day_of_week', $validated['day_of_week'])
                ->where('teacher_id', $validated['teacher_id'])
                ->where('id', '!=', $entry->id)
                ->exists();
        }

        $isOverride = $batchConflict || $teacherConflict;

        $entry->update(array_merge($validated, ['is_override' => $isOverride]));
        $this->syncFutureSessionsForEntry($entry, $oldDayOfWeek);

        $msg = 'Routine entry updated.';
        if ($batchConflict && $teacherConflict) {
            $msg .= ' ⚠️ Batch & Teacher conflict — marked in RED.';
        } elseif ($batchConflict) {
            $msg .= ' ⚠️ Batch conflict (2 classes in same slot for this group) — marked in RED.';
        } elseif ($teacherConflict) {
            $msg .= ' ⚠️ Teacher conflict — marked in RED.';
        }

        // AJAX (drag-drop) → return JSON
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'is_override'      => $isOverride,
                'batch_conflict'   => $batchConflict,
                'teacher_conflict' => $teacherConflict,
                'message'          => $msg,
            ]);
        }

        return back()->with($isOverride ? 'warning' : 'success', $msg);
    }

    public function destroy(RoutineEntry $entry)
    {
        // Delete future scheduled sessions that haven't been conducted yet
        ClassSession::where('routine_entry_id', $entry->id)
            ->whereDate('session_date', '>=', Carbon::today())
            ->where('status', 'SCHEDULED')
            ->doesntHave('attendances')
            ->delete();

        $entry->delete();
        return back()->with('success', 'Routine entry removed.');
    }

    /**
     * Synchronize upcoming class sessions when a routine entry is created or updated.
     */
    private function syncFutureSessionsForEntry(RoutineEntry $entry, ?string $oldDayOfWeek = null): void
    {
        $today = Carbon::today();
        $entry->load(['slot', 'batch']);

        $futureSessions = ClassSession::where('routine_entry_id', $entry->id)
            ->whereDate('session_date', '>=', $today)
            ->where('status', 'SCHEDULED')
            ->get();

        $dayMap = ['SUN' => 0, 'MON' => 1, 'TUE' => 2, 'WED' => 3, 'THU' => 4, 'FRI' => 5, 'SAT' => 6];
        $dayChanged = ($oldDayOfWeek && $oldDayOfWeek !== $entry->day_of_week);

        if ($futureSessions->isNotEmpty()) {
            foreach ($futureSessions as $session) {
                $sessionDate = Carbon::parse($session->session_date);
                $newDateStr  = $sessionDate->toDateString();

                if ($dayChanged && isset($dayMap[$entry->day_of_week])) {
                    $targetDow   = $dayMap[$entry->day_of_week];
                    $startOfWeek = $sessionDate->copy()->startOfWeek(Carbon::SATURDAY);
                    $newDate     = $startOfWeek->copy()->addDays(($targetDow + 1) % 7);
                    if ($newDate->lt($today)) {
                        $newDate->addWeek();
                    }
                    $newDateStr = $newDate->toDateString();
                }

                $session->update([
                    'subject_id'   => $entry->subject_id,
                    'teacher_id'   => $entry->teacher_id,
                    'start_time'   => $entry->slot?->start_time,
                    'session_date' => $newDateStr,
                    'group_tag'    => $entry->group_tag ?? 'ALL',
                ]);
            }
        } elseif ($entry->batch) {
            (new BatchController())->generateSessionsFromRoutine($entry->batch, 4);
        }
    }

    /**
     * Auto-generate routine for a batch.
     * Distributes subjects across days, skipping weekends.
     */
    public function autoGenerate(Batch $batch)
    {
        $weekends = $this->weekends();
        $allDays  = ['SAT', 'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI'];
        $activeDays = array_values(array_filter($allDays, fn($d) => !in_array($d, $weekends)));

        $slots = RoutineSlot::orderBy('sort_order')->get();

        if (!$batch->course) {
            return back()->with('error', 'Batch course not found.');
        }

        // Determine running semester for the batch
        $runningSemesterId = $batch->semesterPosition?->current_semester_id;
        if (!$runningSemesterId && $batch->course->semesters()->exists()) {
            $runningSemesterId = $batch->course->semesters()->orderBy('sequence_no')->orderBy('id')->first()?->id;
        }

        $subjectsQuery = $batch->course->subjects()->with('modules');
        if ($runningSemesterId) {
            $subjectsQuery->wherePivot('semester_id', $runningSemesterId);
        }
        $subjects = $subjectsQuery->get();

        // If no subjects mapped to the running semester, fallback to course subjects
        if ($subjects->isEmpty()) {
            $subjects = $batch->course->subjects()->with('modules')->get();
        }

        if ($slots->isEmpty() || $subjects->isEmpty()) {
            return back()->with('error', 'Please create at least one time slot and ensure the running semester has subjects mapped before auto-generating.');
        }

        // Check semester grouping configuration
        $runningSemester = $runningSemesterId ? \App\Models\Semester::find($runningSemesterId) : null;
        $semesterGroups = ['ALL'];
        if ($runningSemester && $runningSemester->has_groups) {
            if ($runningSemester->group_type === 'GENDER') {
                $semesterGroups = ['MALE', 'FEMALE'];
            } elseif ($runningSemester->group_type === 'SPLIT') {
                $semesterGroups = ['GROUP_A', 'GROUP_B'];
            }
        }

        $dayIdx  = 0;
        $created = 0;

        foreach ($subjects as $subject) {
            // Check subject-level override if any
            $subjectMap = \App\Models\CourseSubjectMap::where('course_id', $batch->course_id)
                ->where('subject_id', $subject->id)
                ->where('semester_id', $runningSemesterId)
                ->first();

            $groupMode = $subjectMap?->group_mode ?? 'INHERIT';
            $targetGroups = $semesterGroups;
            if ($groupMode === 'NONE') {
                $targetGroups = ['ALL'];
            } elseif ($groupMode === 'GENDER') {
                $targetGroups = ['MALE', 'FEMALE'];
            } elseif ($groupMode === 'SPLIT') {
                $targetGroups = ['GROUP_A', 'GROUP_B'];
            }

            foreach ($targetGroups as $grp) {
                $day = $activeDays[$dayIdx % count($activeDays)];
                $slotIndex = intdiv($dayIdx, count($activeDays)) % count($slots);
                $slot = $slots[$slotIndex] ?? $slots->first();

                // Get assigned teacher
                $assignment = SubjectTeacherAssignment::where('subject_id', $subject->id)->first();

                // Check teacher conflict
                $isOverride = false;
                if ($assignment) {
                    $isOverride = RoutineEntry::where('slot_id', $slot->id)
                        ->where('day_of_week', $day)
                        ->where('teacher_id', $assignment->teacher_id)
                        ->where('batch_id', '!=', $batch->id)
                        ->exists();
                }

                $titleSuffix = match($grp) {
                    'MALE'    => ' (ভাই শাখা)',
                    'FEMALE'  => ' (বোন শাখা)',
                    'GROUP_A' => ' (গ্রুপ ক)',
                    'GROUP_B' => ' (গ্রুপ খ)',
                    default   => '',
                };

                // Avoid duplicate entry for same batch+slot+day+subject+group
                RoutineEntry::firstOrCreate(
                    [
                        'batch_id'    => $batch->id,
                        'slot_id'     => $slot->id,
                        'day_of_week' => $day,
                        'subject_id'  => $subject->id,
                        'group_tag'   => $grp,
                    ],
                    [
                        'teacher_id'  => $assignment?->teacher_id,
                        'is_override' => $isOverride,
                        'title'       => $subject->code . ': ' . $subject->name . $titleSuffix,
                    ]
                );

                $created++;
                $dayIdx++;
            }
        }

        return back()->with('success', "Auto-generated {$created} routine entries for running semester of '{$batch->name}'. Review and edit as needed.");
    }

    /**
     * Show unassigned class sessions for a batch (no routine entry yet).
     */
    public function unassigned(Request $request)
    {
        $batchId = $request->query('batch_id');
        $batches = Batch::where('status', 'ACTIVE')->get();

        $unassigned = collect();
        if ($batchId) {
            // Sessions that exist for this batch but have no routine_entry_id assigned yet
            $unassigned = ClassSession::with(['subject', 'teacher'])
                ->where('batch_id', $batchId)
                ->whereNull('routine_entry_id')
                ->where('status', '!=', 'COMPLETED')
                ->orderBy('session_date')
                ->get();
        }

        $slots = RoutineSlot::orderBy('sort_order')->get();
        $days  = ['SAT', 'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI'];

        return view('admin.routine.unassigned', compact('batches', 'unassigned', 'batchId', 'slots', 'days'));
    }
}
