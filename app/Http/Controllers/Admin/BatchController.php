<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\AcademicYear;
use App\Models\HolidayCalendar;
use App\Models\RoutineEntry;
use App\Models\SubjectTeacherAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BatchController extends Controller
{
    public function index()
    {
        $batches = Batch::with(['course', 'academicYear'])->withCount('classSessions')->latest()->get();
        $courses = Course::where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::where('is_active', true)->orderBy('name')->get();
        return view('admin.batches.index', compact('batches', 'courses', 'academicYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:150',
            'course_id'        => 'required|exists:courses,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'start_date'       => 'required|date',
            'admission_fee'    => 'nullable|numeric|min:0',
            'monthly_fee'      => 'nullable|numeric|min:0',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $nextCode = strtoupper(substr($course->name, 0, 3)) . '-' . date('Y') . '-' . str_pad(Batch::count() + 1, 2, '0', STR_PAD_LEFT);

        $batch = Batch::create([
            'name'                     => $validated['name'],
            'batch_code'               => $nextCode,
            'course_id'                => $validated['course_id'],
            'academic_year_id'         => $validated['academic_year_id'] ?? null,
            'start_date'               => $validated['start_date'],
            'admission_fee'            => $validated['admission_fee'] ?? 0.00,
            'monthly_fee'              => $validated['monthly_fee'] ?? 0.00,
            'status'                   => 'ACTIVE',
            'is_admission_open'        => $request->boolean('is_admission_open', true),
            'subject_version_snapshot' => 1,
        ]);

        // Auto-generate class sessions from routine (4 weeks ahead)
        $count = $this->generateSessionsFromRoutine($batch, 4);

        return back()->with('success', "Batch '{$batch->name}' created! Generated {$count} class sessions from routine.");
    }

    public function update(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:150',
            'course_id'         => 'required|exists:courses,id',
            'academic_year_id'  => 'nullable|exists:academic_years,id',
            'start_date'        => 'required|date',
            'admission_fee'     => 'nullable|numeric|min:0',
            'monthly_fee'       => 'nullable|numeric|min:0',
            'status'            => 'required|in:PLANNED,ACTIVE,COMPLETED,CANCELLED',
        ]);

        $batch->update(array_merge($validated, [
            'admission_fee'     => $validated['admission_fee'] ?? 0.00,
            'monthly_fee'       => $validated['monthly_fee'] ?? 0.00,
            'is_admission_open' => $request->boolean('is_admission_open'),
        ]));

        return back()->with('success', "Batch '{$batch->name}' updated successfully.");
    }

    public function show(Batch $batch)
    {
        $batch->load(['course', 'enrollments.student']);

        // Upcoming + past sessions ordered by date
        $sessions = ClassSession::with(['subject', 'teacher', 'routineEntry.slot', 'moduleCovered', 'attendances'])
            ->where('batch_id', $batch->id)
            ->orderBy('session_date')
            ->get();

        // Curriculum: subjects with modules (for progress tracking)
        $subjects = $batch->course->subjects()->with([
            'modules' => fn($q) => $q->orderBy('sequence_no')
        ])->get();

        // Which modules have been covered (via class sessions)
        $coveredModuleIds = ClassSession::where('batch_id', $batch->id)
            ->whereNotNull('module_covered_id')
            ->pluck('module_covered_id')
            ->unique()
            ->toArray();

        return view('admin.batches.show', compact('batch', 'sessions', 'subjects', 'coveredModuleIds'));
    }

    public function generateTimeline(Batch $batch)
    {
        $count = $this->generateSessionsFromRoutine($batch, 8);
        return back()->with('success', "Generated {$count} class sessions for '{$batch->name}' (8 weeks from today).");
    }

    public function destroy(Batch $batch)
    {
        $batch->delete();
        return redirect()->route('admin.batches.index')->with('success', 'Batch deleted.');
    }

    // ─────────────────────────────────────────────────────────────
    // Generate date-based class sessions from routine entries
    // One session per routine_entry per week (for $weeks weeks ahead)
    // Skips holidays. Does not duplicate existing sessions.
    // ─────────────────────────────────────────────────────────────
    public function generateSessionsFromRoutine(Batch $batch, int $weeks = 4): int
    {
        $holidays = HolidayCalendar::pluck('date')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();

        // Day string → Carbon dayOfWeek (Sun=0 ... Sat=6)
        $dayMap = ['SUN' => 0, 'MON' => 1, 'TUE' => 2, 'WED' => 3, 'THU' => 4, 'FRI' => 5, 'SAT' => 6];

        $routineEntries = RoutineEntry::with(['slot', 'subject'])
            ->where('batch_id', $batch->id)
            ->get();

        if ($routineEntries->isEmpty()) {
            return 0;
        }

        $today    = Carbon::today();
        $baseDate = Carbon::parse($batch->start_date)->max($today); // start from today or batch start
        $endDate  = $baseDate->copy()->addWeeks($weeks);
        $created  = 0;

        foreach ($routineEntries as $entry) {
            if (!isset($dayMap[$entry->day_of_week])) continue;

            $targetDow = $dayMap[$entry->day_of_week];

            // Find first occurrence of this day on/after baseDate
            $sessionDate = $baseDate->copy();
            while ($sessionDate->dayOfWeek !== $targetDow) {
                $sessionDate->addDay();
            }

            // Walk week by week
            while ($sessionDate <= $endDate) {
                $dateStr = $sessionDate->toDateString();

                // Skip holidays
                if (!in_array($dateStr, $holidays)) {
                    // Don't duplicate
                    $exists = ClassSession::where('routine_entry_id', $entry->id)
                        ->where('session_date', $dateStr)
                        ->exists();

                    if (!$exists) {
                        ClassSession::create([
                            'routine_entry_id' => $entry->id,
                            'batch_id'         => $batch->id,
                            'subject_id'       => $entry->subject_id,
                            'teacher_id'       => $entry->teacher_id,
                            'session_date'     => $dateStr,
                            'start_time'       => $entry->slot?->start_time,
                            'status'           => $sessionDate->isPast() ? 'COMPLETED' : 'SCHEDULED',
                        ]);
                        $created++;
                    }
                }

                $sessionDate->addWeek();
            }
        }

        return $created;
    }
}
