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
        $batches = Batch::where('status', 'ACTIVE')->with('course')->orderBy('name')->get();
        $days    = ['SAT', 'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI'];
        $weekends = $this->weekends();

        $selectedBatchId = $request->query('batch_id');

        // Load entries, optionally filtered by batch
        $query = RoutineEntry::with(['batch.course', 'slot', 'subject', 'teacher', 'classSession']);
        if ($selectedBatchId) {
            $query->where('batch_id', $selectedBatchId);
        }
        $entries = $query->get()->groupBy(['slot_id', 'day_of_week']);

        // Assign a color per batch (index-based)
        $batchColors = [];
        foreach ($batches as $i => $b) {
            $batchColors[$b->id] = RoutineEntry::BATCH_COLORS[$i % count(RoutineEntry::BATCH_COLORS)];
        }

        $subjects  = Subject::where('is_active', true)->orderBy('name')->get();
        $teachers  = Teacher::where('is_active', true)->orderBy('name')->get();
        $holidays  = HolidayCalendar::pluck('date')->toArray();

        return view('admin.routine.index', compact(
            'slots', 'batches', 'days', 'entries', 'weekends',
            'batchColors', 'subjects', 'teachers', 'selectedBatchId', 'holidays'
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

        RoutineSlot::create([
            'name'       => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time'   => $validated['end_time'],
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

        $slot->update($validated);
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
            'class_session_id' => 'nullable|exists:class_sessions,id',
            'title'            => 'nullable|string|max:200',
            'color'            => 'nullable|string|max:20',
        ]);

        // Teacher conflict detection
        $isOverride = false;
        if (!empty($validated['teacher_id'])) {
            $conflict = RoutineEntry::where('slot_id', $validated['slot_id'])
                ->where('day_of_week', $validated['day_of_week'])
                ->where('teacher_id', $validated['teacher_id'])
                ->where('batch_id', '!=', $validated['batch_id'])
                ->exists();

            $isOverride = $conflict;
        }

        RoutineEntry::create(array_merge($validated, ['is_override' => $isOverride]));

        if ($isOverride) {
            return back()->with('success', 'Entry added — ⚠️ Teacher conflict detected! Marked as OVERRIDE (shown in red).');
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
            'class_session_id' => 'nullable|exists:class_sessions,id',
            'title'            => 'nullable|string|max:200',
            'color'            => 'nullable|string|max:20',
        ]);

        // Re-check conflict
        $isOverride = false;
        if (!empty($validated['teacher_id'])) {
            $conflict = RoutineEntry::where('slot_id', $validated['slot_id'])
                ->where('day_of_week', $validated['day_of_week'])
                ->where('teacher_id', $validated['teacher_id'])
                ->where('batch_id', '!=', $validated['batch_id'])
                ->where('id', '!=', $entry->id)
                ->exists();
            $isOverride = $conflict;
        }

        $entry->update(array_merge($validated, ['is_override' => $isOverride]));

        $msg = 'Routine entry updated.' . ($isOverride ? ' ⚠️ Teacher conflict — marked RED.' : '');

        // AJAX (drag-drop) → return JSON
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'is_override' => $isOverride,
                'message'     => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }


    public function destroy(RoutineEntry $entry)
    {
        $entry->delete();
        return back()->with('success', 'Routine entry removed.');
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

        $slots    = RoutineSlot::orderBy('sort_order')->get();
        $subjects = $batch->course->subjects()->with('modules')->get();

        if ($slots->isEmpty() || $subjects->isEmpty()) {
            return back()->with('error', 'Please create at least one time slot and ensure the course has subjects before auto-generating.');
        }

        $slot    = $slots->first(); // Use first slot as default
        $dayIdx  = 0;
        $created = 0;

        foreach ($subjects as $subject) {
            $day = $activeDays[$dayIdx % count($activeDays)];

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

            // Avoid duplicate entry for same batch+slot+day+subject
            RoutineEntry::firstOrCreate(
                [
                    'batch_id'    => $batch->id,
                    'slot_id'     => $slot->id,
                    'day_of_week' => $day,
                    'subject_id'  => $subject->id,
                ],
                [
                    'teacher_id'  => $assignment?->teacher_id,
                    'is_override' => $isOverride,
                    'title'       => $subject->code . ': ' . $subject->name,
                ]
            );

            $created++;
            $dayIdx++;
        }

        return back()->with('success', "Auto-generated {$created} routine entries for '{$batch->name}'. Review and edit as needed.");
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
