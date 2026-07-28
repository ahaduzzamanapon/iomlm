<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Course;
use App\Models\AcademicYear;
use App\Models\Timeline;
use App\Models\ClassSession;
use App\Models\HolidayCalendar;
use App\Models\SubjectTeacherAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function index()
    {
        $batches = Batch::with(['course', 'academicYear', 'timelines'])->latest()->get();
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
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $nextCode = strtoupper(substr($course->name, 0, 3)) . '-' . date('Y') . '-' . str_pad(Batch::count() + 1, 2, '0', STR_PAD_LEFT);

        $batch = Batch::create([
            'name'                     => $validated['name'],
            'batch_code'               => $nextCode,
            'course_id'                => $validated['course_id'],
            'academic_year_id'         => $validated['academic_year_id'] ?? null,
            'start_date'               => $validated['start_date'],
            'status'                   => 'ACTIVE',
            'subject_version_snapshot' => 1,
        ]);

        // Automatically trigger timeline generation
        $this->generateTimelineForBatch($batch);

        return back()->with('success', "Batch '{$batch->name}' created and Timeline auto-generated!");
    }

    public function generateTimeline(Batch $batch)
    {
        $count = $this->generateTimelineForBatch($batch);
        return back()->with('success', "Generated {$count} timeline module slots for batch '{$batch->name}'.");
    }

    public function show(Batch $batch)
    {
        $batch->load(['course', 'timelines.subject', 'timelines.module', 'enrollments.student']);
        return view('admin.batches.show', compact('batch'));
    }

    public function destroy(Batch $batch)
    {
        $batch->delete();
        return redirect()->route('admin.batches.index')->with('success', 'Batch deleted.');
    }

    private function generateTimelineForBatch(Batch $batch): int
    {
        $course = $batch->course;
        $subjects = $course->subjects()->with(['modules' => fn($q) => $q->orderBy('sequence_no')])->get();
        $holidays = HolidayCalendar::pluck('date')->toArray();

        $currentDate = Carbon::parse($batch->start_date);
        $slotsCreated = 0;

        foreach ($subjects as $subject) {
            foreach ($subject->modules as $module) {

                // Skip holidays
                while (in_array($currentDate->toDateString(), $holidays)) {
                    $currentDate->addDay();
                }

                // Check by batch, subject, module (prevent duplicates!)
                $timeline = Timeline::firstOrCreate(
                    [
                        'batch_id'   => $batch->id,
                        'subject_id' => $subject->id,
                        'module_id'  => $module->id,
                    ],
                    [
                        'scheduled_date' => $currentDate->toDateString(),
                        'status'         => 'SCHEDULED',
                    ]
                );

                // Auto-create ClassSession for new timeline slot
                if ($timeline->wasRecentlyCreated) {
                    $teacherAssignment = SubjectTeacherAssignment::where('subject_id', $subject->id)
                        ->where(fn($q) => $q->where('batch_id', $batch->id)->orWhereNull('batch_id'))
                        ->orderByRaw('batch_id DESC')
                        ->first();

                    // Auto-generate unique Google Meet link for live classroom
                    $meetCode = strtolower(\Illuminate\Support\Str::random(3) . '-' . \Illuminate\Support\Str::random(4) . '-' . \Illuminate\Support\Str::random(3));

                    ClassSession::create([
                        'timeline_id'  => $timeline->id,
                        'teacher_id'   => $teacherAssignment?->teacher_id,
                        'meeting_link' => "https://meet.google.com/{$meetCode}",
                        'status'       => 'SCHEDULED',
                    ]);

                    $slotsCreated++;
                }

                // Move 7 days forward for next module slot
                $currentDate->addDays(7);
            }
        }

        return $slotsCreated;
    }
}
