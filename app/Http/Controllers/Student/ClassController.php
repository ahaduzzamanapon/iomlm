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
        return Student::where('user_id', auth()->id())->first();
    }

    private function applyStudentGroupScope($query, ?Student $student)
    {
        if (!$student) return $query;
        $studentGender = strtoupper($student->gender ?? '');
        $enrollmentGroups = Enrollment::where('student_id', $student->id)
            ->where('status', 'ACTIVE')
            ->pluck('group_tag')
            ->filter()
            ->unique()
            ->toArray();

        return $query->where(function ($q) use ($studentGender, $enrollmentGroups) {
            $q->whereNull('group_tag')
              ->orWhere('group_tag', 'ALL');
            if ($studentGender) {
                $q->orWhere('group_tag', $studentGender);
            }
            if (!empty($enrollmentGroups)) {
                $q->orWhereIn('group_tag', $enrollmentGroups);
            }
        });
    }

    public function today()
    {
        $student  = $this->student();
        $batchIds = Enrollment::where('student_id', $student?->id)->where('status', 'ACTIVE')->pluck('batch_id');

        $query = ClassSession::with(['subject', 'batch', 'teacher', 'routineEntry.slot', 'moduleCovered'])
            ->whereIn('batch_id', $batchIds)
            ->whereDate('session_date', today());

        $sessions = $this->applyStudentGroupScope($query, $student)
            ->orderBy('start_time')
            ->get();

        $today = \Carbon\Carbon::today();

        return view('student.classes.today', compact('sessions', 'today'));
    }

    public function index()
    {
        $student  = $this->student();
        $batchIds = Enrollment::where('student_id', $student?->id)->where('status', 'ACTIVE')->pluck('batch_id');

        $query = ClassSession::with(['subject', 'batch', 'teacher', 'routineEntry.slot', 'moduleCovered'])
            ->whereIn('batch_id', $batchIds);

        $classes = $this->applyStudentGroupScope($query, $student)
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

            // Verify group eligibility
            if ($class->group_tag && $class->group_tag !== 'ALL') {
                $studentGender = strtoupper($student->gender ?? '');
                $enr = Enrollment::where('student_id', $student->id)
                    ->where('batch_id', $class->batch_id)
                    ->where('status', 'ACTIVE')
                    ->first();
                $allowed = false;
                if ($class->group_tag === 'MALE' && $studentGender === 'MALE') $allowed = true;
                elseif ($class->group_tag === 'FEMALE' && $studentGender === 'FEMALE') $allowed = true;
                elseif ($enr && $enr->group_tag === $class->group_tag) $allowed = true;

                if (!$allowed) {
                    return redirect()->route('student.classes.index')
                        ->with('error', 'এই ক্লাস সেশনটি আপনার নির্ধারিত গ্রুপের জন্য নয়।');
                }
            }
        }

        $class->load(['subject', 'batch', 'teacher', 'routineEntry.slot', 'moduleCovered']);
        $attendance = $class->attendances()->where('student_id', $student?->id)->first();

        return view('student.classes.show', compact('class', 'attendance'));
    }

    public function join(ClassSession $class)
    {
        $student = $this->student();

        if (!$student) {
            return redirect()->route('student.classes.index')
                ->with('error', 'শিক্ষার্থীর প্রোফাইল পাওয়া যায়নি।');
        }

        // 1. Fee enforcement check
        $guard = \App\Services\EnforcementService::canJoinClass($student);
        if (!$guard['allowed']) {
            return redirect()->route('student.fees.index')->with('error', $guard['reason']);
        }

        // 2. Batch enrollment check
        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('batch_id', $class->batch_id)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$enrollment) {
            return redirect()->route('student.classes.index')
                ->with('error', 'আপনি এই ব্যাচে সক্রিয়ভাবে অন্তর্ভুক্ত নন।');
        }

        // 3. Group eligibility check
        if ($class->group_tag && $class->group_tag !== 'ALL') {
            $studentGender = strtoupper($student->gender ?? '');
            $allowed = false;
            if ($class->group_tag === 'MALE' && $studentGender === 'MALE') $allowed = true;
            elseif ($class->group_tag === 'FEMALE' && $studentGender === 'FEMALE') $allowed = true;
            elseif ($enrollment->group_tag === $class->group_tag) $allowed = true;

            if (!$allowed) {
                return redirect()->route('student.classes.index')
                    ->with('error', 'এই ক্লাস সেশনটি আপনার নির্ধারিত গ্রুপের জন্য নয়।');
            }
        }

        // 4. Mark attendance as PRESENT automatically upon clicking Join
        \App\Models\Attendance::updateOrCreate(
            [
                'class_session_id' => $class->id,
                'student_id'       => $student->id,
            ],
            [
                'enrollment_id' => $enrollment->id,
                'status'        => 'PRESENT',
                'notes'         => 'Joined live class at ' . now()->format('h:i A, d M Y'),
            ]
        );

        // 5. Redirect to live class meeting link
        if ($class->meeting_link) {
            return redirect()->away($class->meeting_link);
        }

        return redirect()->route('student.classes.show', $class)
            ->with('info', 'উপস্থিতি (PRESENT) সফলভাবে রেকর্ড করা হয়েছে। তবে লাইভ ক্লাসের লিংক এখনও প্রদান করা হয়নি।');
    }

    public function calendar()
    {
        $student  = $this->student();
        $batchIds = Enrollment::where('student_id', $student?->id)->where('status', 'ACTIVE')->pluck('batch_id');

        $query = ClassSession::with(['subject', 'batch', 'teacher', 'routineEntry.slot'])
            ->whereIn('batch_id', $batchIds)
            ->whereNotNull('session_date');

        $classes = $this->applyStudentGroupScope($query, $student)->get();

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
            'join_url'     => route('student.classes.join', $c),
            'status'       => $c->status,
        ]);

        return view('student.calendar.index', compact('events', 'classes'));
    }
}
