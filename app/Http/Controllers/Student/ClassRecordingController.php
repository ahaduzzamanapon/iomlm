<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class ClassRecordingController extends Controller
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

    /**
     * Display all recorded classes for student's enrolled batches.
     */
    public function index(Request $request)
    {
        $student = $this->student();
        $enrollments = Enrollment::where('student_id', $student?->id)
            ->where('status', 'ACTIVE')
            ->with('batch.course')
            ->get();
        $batchIds = $enrollments->pluck('batch_id')->filter()->unique();

        $subjectId = $request->query('subject_id');
        $batchId   = $request->query('batch_id');
        $search    = trim($request->query('search', ''));

        $query = ClassSession::with(['subject', 'batch', 'teacher', 'moduleCovered'])
            ->withRecording()
            ->whereIn('batch_id', $batchIds);

        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('recorded_videos', 'like', "%{$search}%")
                  ->orWhereHas('subject', fn($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                  ->orWhereHas('teacher', fn($tq) => $tq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('moduleCovered', fn($mq) => $mq->where('title', 'like', "%{$search}%"));
            });
        }

        $classes = $this->applyStudentGroupScope($query, $student)
            ->orderBy('session_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(12)
            ->withQueryString();

        $enrolledBatches = Batch::whereIn('id', $batchIds)->with('course')->orderBy('name')->get();

        $enrolledSubjectIds = ClassSession::withRecording()
            ->whereIn('batch_id', $batchIds)
            ->pluck('subject_id')
            ->unique();
        $subjects = Subject::whereIn('id', $enrolledSubjectIds)->orderBy('name')->get();

        return view('student.recordings.index', compact(
            'classes', 'subjects', 'enrolledBatches', 'subjectId', 'batchId', 'search', 'student'
        ));
    }

    /**
     * Watch a recorded class session with the in-portal video player.
     */
    public function show(Request $request, ClassSession $class)
    {
        $student = $this->student();

        // 1. Enrollment check
        $enrolled = Enrollment::where('student_id', $student?->id)
            ->where('batch_id', $class->batch_id)
            ->where('status', 'ACTIVE')
            ->exists();

        if (!$enrolled && auth()->user()->role !== 'admin' && auth()->user()->role !== 'super_admin') {
            return redirect()->route('student.recordings.index')
                ->with('error', 'এই ক্লাস রেকর্ডিংটি দেখার অনুমতি আপনার নেই।');
        }

        // 2. Group eligibility check
        if ($class->group_tag && $class->group_tag !== 'ALL') {
            $studentGender = strtoupper($student?->gender ?? '');
            $enr = Enrollment::where('student_id', $student?->id)
                ->where('batch_id', $class->batch_id)
                ->where('status', 'ACTIVE')
                ->first();

            $allowed = false;
            if ($class->group_tag === 'MALE' && $studentGender === 'MALE') $allowed = true;
            elseif ($class->group_tag === 'FEMALE' && $studentGender === 'FEMALE') $allowed = true;
            elseif ($enr && $enr->group_tag === $class->group_tag) $allowed = true;

            if (!$allowed) {
                return redirect()->route('student.recordings.index')
                    ->with('error', 'এই ক্লাস সেশনটি আপনার নির্ধারিত গ্রুপের জন্য নয়।');
            }
        }

        // 3. Check for recorded videos
        if (!$class->has_recorded_videos) {
            return redirect()->route('student.recordings.index')
                ->with('error', 'এই ক্লাসের কোনো রেকর্ডিং পাওয়া যায়নি।');
        }

        $class->load(['subject', 'batch', 'teacher', 'moduleCovered']);

        $videos = $class->videos;
        $part = (int) $request->query('part', 0);
        if ($part < 0 || $part >= count($videos)) {
            $part = 0;
        }

        $activeVideo = $videos[$part] ?? ($videos[0] ?? null);

        return view('student.recordings.show', compact('class', 'videos', 'activeVideo', 'part'));
    }
}
