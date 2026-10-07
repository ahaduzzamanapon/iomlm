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
    public function index(Request $request)
    {
        $courseId       = $request->query('course_id');
        $academicYearId = $request->query('academic_year_id');
        $status         = $request->query('status');
        $search         = $request->query('search');

        $query = Batch::with(['course', 'academicYear'])->withCount('classSessions')->latest();

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($status) {
            $query->where('status', strtoupper($status));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('batch_code', 'like', "%{$search}%");
            });
        }

        $batches       = $query->get();
        $courses       = Course::where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::where('is_active', true)->orderBy('name')->get();
        $months        = Course::monthsList();

        return view('admin.batches.index', compact(
            'batches', 'courses', 'academicYears', 'courseId', 'academicYearId', 'status', 'search', 'months'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => [
                'required', 'string', 'max:150',
                \Illuminate\Validation\Rule::unique('batches', 'name')->where('course_id', $request->input('course_id'))
            ],
            'course_id'        => 'required|exists:courses,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'start_date'       => 'required|date',
            'expected_end_date'=> 'nullable|date|after_or_equal:start_date',
            'start_month'      => 'nullable|string|max:20',
            'end_month'        => 'nullable|string|max:20',
            'fee_start_month'  => 'nullable|string|max:20',
            'fee_end_month'    => 'nullable|string|max:20',
            'admission_fee'    => 'nullable|numeric|min:0',
            'monthly_fee'      => 'nullable|numeric|min:0',
        ], [
            'name.unique' => 'একই কোর্সের অধীনে ব্যাচের নাম ইউনিক হতে হবে। এই কোর্সে এই নামের একটি ব্যাচ ইতিমধ্যে বিদ্যমান।',
            'expected_end_date.after_or_equal' => 'ব্যাচ সমাপ্তির তারিখ অবশ্যই শুরু হওয়ার তারিখের সমান বা পরবর্তী হতে হবে।',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $startDate = $validated['start_date'];
        $startYear = date('Y', strtotime($startDate));
        $startMonth = (int) date('m', strtotime($startDate));
        $monthName = date('F', strtotime($startDate));
        $inputStartMonth = $validated['start_month'] ?? null;

        // Validation: একই কোর্সে একই তারিখে বা একই মাসে একাধিক ব্যাচ তৈরি করা যাবে না
        $existingBatch = Batch::where('course_id', $course->id)
            ->where(function ($q) use ($startDate, $startYear, $startMonth, $monthName, $inputStartMonth) {
                $q->whereDate('start_date', $startDate)
                  ->orWhere(function ($mQ) use ($startYear, $startMonth, $monthName, $inputStartMonth) {
                      $mQ->whereYear('start_date', $startYear)
                         ->where(function ($subQ) use ($startMonth, $monthName, $inputStartMonth) {
                             $subQ->whereMonth('start_date', $startMonth);
                             if (!empty($inputStartMonth)) {
                                 $subQ->orWhere('start_month', $inputStartMonth);
                             }
                             if (!empty($monthName)) {
                                 $subQ->orWhere('start_month', $monthName);
                             }
                         });
                  });
            })
            ->first();

        if ($existingBatch) {
            $existingMonth = date('F Y', strtotime($existingBatch->start_date));
            $existingDate = date('d-m-Y', strtotime($existingBatch->start_date));
            throw \Illuminate\Validation\ValidationException::withMessages([
                'start_date' => ["'{$course->name}' কোর্সে ইতিমধ্যে একই তারিখে ({$existingDate}) বা একই মাসে ({$existingMonth}) একটি ব্যাচ ('{$existingBatch->name}') বিদ্যমান। একই কোর্সে একই মাসে একাধিক ব্যাচ তৈরি করা যাবে না।"],
            ]);
        }

        // Bulletproof unique batch_code generation
        $rawPrefix = preg_replace('/[^A-Za-z0-9]/', '', $course->code ?: $course->name);
        $prefix = strtoupper(substr($rawPrefix, 0, 3));
        if (strlen($prefix) < 2) {
            $prefix = 'BAT';
        }

        $year = date('Y', strtotime($startDate));
        $baseCode = $prefix . '-' . $year . '-';

        $existingCodes = Batch::where('batch_code', 'like', $baseCode . '%')->pluck('batch_code');
        $maxSeq = 0;
        foreach ($existingCodes as $c) {
            $parts = explode('-', $c);
            $lastPart = end($parts);
            if (is_numeric($lastPart)) {
                $num = (int) $lastPart;
                if ($num > $maxSeq) {
                    $maxSeq = $num;
                }
            }
        }

        $seq = $maxSeq + 1;
        $nextCode = $baseCode . str_pad($seq, 2, '0', STR_PAD_LEFT);
        while (Batch::where('batch_code', $nextCode)->exists()) {
            $seq++;
            $nextCode = $baseCode . str_pad($seq, 2, '0', STR_PAD_LEFT);
        }

        $startDateMonth = date('F', strtotime($validated['start_date']));
        $endDateMonth = !empty($validated['expected_end_date']) ? date('F', strtotime($validated['expected_end_date'])) : null;

        $batch = Batch::create([
            'name'                     => $validated['name'],
            'batch_code'               => $nextCode,
            'course_id'                => $validated['course_id'],
            'academic_year_id'         => $validated['academic_year_id'] ?? null,
            'start_date'               => $validated['start_date'],
            'expected_end_date'        => $validated['expected_end_date'] ?? null,
            'start_month'              => $validated['start_month'] ?? $startDateMonth,
            'end_month'                => $validated['end_month'] ?? $endDateMonth,
            'fee_start_month'          => $validated['fee_start_month'] ?? $startDateMonth,
            'fee_end_month'            => $validated['fee_end_month'] ?? $endDateMonth,
            'admission_fee'            => $validated['admission_fee'] ?? 0.00,
            'monthly_fee'              => $validated['monthly_fee'] ?? 0.00,
            'status'                   => 'ACTIVE',
            'is_admission_open'        => $request->boolean('is_admission_open'),
            'subject_version_snapshot' => 1,
        ]);

        // Auto-generate class sessions from routine (4 weeks ahead)
        $count = $this->generateSessionsFromRoutine($batch, 4);

        return back()->with('success', "Batch '{$batch->name}' created! Generated {$count} class sessions from routine.");
    }

    public function update(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'name'              => [
                'required', 'string', 'max:150',
                \Illuminate\Validation\Rule::unique('batches', 'name')
                    ->where('course_id', $request->input('course_id') ?: $batch->course_id)
                    ->ignore($batch->id)
            ],
            'course_id'         => 'required|exists:courses,id',
            'academic_year_id'  => 'nullable|exists:academic_years,id',
            'start_date'        => 'required|date',
            'expected_end_date' => 'nullable|date|after_or_equal:start_date',
            'start_month'       => 'nullable|string|max:20',
            'end_month'         => 'nullable|string|max:20',
            'fee_start_month'   => 'nullable|string|max:20',
            'fee_end_month'     => 'nullable|string|max:20',
            'admission_fee'     => 'nullable|numeric|min:0',
            'monthly_fee'       => 'nullable|numeric|min:0',
            'status'            => 'required|in:PLANNED,ACTIVE,COMPLETED,CANCELLED,SUSPENDED',
        ], [
            'name.unique' => 'একই কোর্সের অধীনে ব্যাচের নাম ইউনিক হতে হবে। এই কোর্সে এই নামের একটি ব্যাচ ইতিমধ্যে বিদ্যমান।',
            'expected_end_date.after_or_equal' => 'ব্যাচ সমাপ্তির তারিখ অবশ্যই শুরু হওয়ার তারিখের সমান বা পরবর্তী হতে হবে।',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $startDate = $validated['start_date'];
        $startYear = date('Y', strtotime($startDate));
        $startMonth = (int) date('m', strtotime($startDate));
        $monthName = date('F', strtotime($startDate));
        $inputStartMonth = $validated['start_month'] ?? null;

        // Validation: একই কোর্সে একই তারিখে বা একই মাসে একাধিক ব্যাচ থাকতে পারবে না
        $existingBatch = Batch::where('course_id', $course->id)
            ->where('id', '!=', $batch->id)
            ->where(function ($q) use ($startDate, $startYear, $startMonth, $monthName, $inputStartMonth) {
                $q->whereDate('start_date', $startDate)
                  ->orWhere(function ($mQ) use ($startYear, $startMonth, $monthName, $inputStartMonth) {
                      $mQ->whereYear('start_date', $startYear)
                         ->where(function ($subQ) use ($startMonth, $monthName, $inputStartMonth) {
                             $subQ->whereMonth('start_date', $startMonth);
                             if (!empty($inputStartMonth)) {
                                 $subQ->orWhere('start_month', $inputStartMonth);
                             }
                             if (!empty($monthName)) {
                                 $subQ->orWhere('start_month', $monthName);
                             }
                         });
                  });
            })
            ->first();

        if ($existingBatch) {
            $existingMonth = date('F Y', strtotime($existingBatch->start_date));
            $existingDate = date('d-m-Y', strtotime($existingBatch->start_date));
            throw \Illuminate\Validation\ValidationException::withMessages([
                'start_date' => ["'{$course->name}' কোর্সে ইতিমধ্যে একই তারিখে ({$existingDate}) বা একই মাসে ({$existingMonth}) একটি ব্যাচ ('{$existingBatch->name}') বিদ্যমান। একই কোর্সে একই মাসে একাধিক ব্যাচ তৈরি করা যাবে না।"],
            ]);
        }

        $startDateMonth = date('F', strtotime($validated['start_date']));
        $endDateMonth = !empty($validated['expected_end_date']) ? date('F', strtotime($validated['expected_end_date'])) : null;

        $batch->update(array_merge($validated, [
            'expected_end_date' => $validated['expected_end_date'] ?? null,
            'start_month'       => $validated['start_month'] ?? $startDateMonth,
            'end_month'         => $validated['end_month'] ?? $endDateMonth,
            'fee_start_month'   => $validated['fee_start_month'] ?? $startDateMonth,
            'fee_end_month'     => $validated['fee_end_month'] ?? $endDateMonth,
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
        $holidays = HolidayCalendar::all();

        // Check if a given date is a holiday (exact date OR yearly recurring)
        $isHoliday = function(Carbon $date) use ($holidays) {
            foreach ($holidays as $h) {
                $hDate = Carbon::parse($h->date);
                if ($hDate->isSameDay($date)) {
                    return true;
                }
                if ($h->is_recurring_yearly && $hDate->month === $date->month && $hDate->day === $date->day) {
                    return true;
                }
            }
            return false;
        };

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

                // Skip holidays (both exact and yearly recurring)
                if (!$isHoliday($sessionDate)) {
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
                            'group_tag'        => $entry->group_tag ?? 'ALL',
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

    /**
     * Auto-split active students in a batch 50/50 into Group A and Group B.
     */
    public function autoSplitStudents(Request $request, Batch $batch)
    {
        $enrollments = $batch->enrollments()
            ->where('status', 'ACTIVE')
            ->orderBy('id')
            ->get();

        $groupNames = ['GROUP_A', 'GROUP_B'];
        foreach ($enrollments as $index => $enr) {
            $assignedGroup = $groupNames[$index % 2];
            $enr->update(['group_tag' => $assignedGroup]);
        }

        return back()->with('success', "{$enrollments->count()} জন শিক্ষার্থীকে সফলভাবে গ্রুপ ক এবং গ্রুপ খ-তে ৫০/৫০ বিভাজন করা হয়েছে।");
    }

    /**
     * Set group tag for an enrollment.
     */
    public function setStudentGroup(Request $request, \App\Models\Enrollment $enrollment)
    {
        $validated = $request->validate([
            'group_tag' => 'nullable|string|max:30',
        ]);

        $enrollment->update(['group_tag' => $validated['group_tag'] ?: null]);

        return back()->with('success', 'শিক্ষার্থীর গ্রুপ আপডেট করা হয়েছে।');
    }
}
