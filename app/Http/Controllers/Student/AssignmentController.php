<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    private function student(): ?Student
    {
        return Student::where('user_id', auth()->id())->first();
    }

    public function index(Request $request)
    {
        $student = $this->student();

        $activeEnrollments = Enrollment::with('course.subjects')
            ->where('student_id', $student?->id)
            ->where('status', 'ACTIVE')
            ->get();

        $subjectIds = $activeEnrollments->flatMap(fn($e) => $e->course?->subjects?->pluck('id') ?? collect())->unique();
        $batchIds = $activeEnrollments->pluck('batch_id')->filter()->unique();

        $assignments = Assignment::with(['subject', 'batch', 'submissions' => fn($q) => $q->where('student_id', $student?->id)])
            ->whereIn('subject_id', $subjectIds)
            ->where(function ($q) use ($batchIds) {
                $q->whereNull('batch_id')->orWhereIn('batch_id', $batchIds);
            })
            ->where('status', 'PUBLISHED')
            ->latest('due_datetime')
            ->get();

        return view('student.assignments.index', compact('assignments', 'student'));
    }

    public function show(Assignment $assignment)
    {
        $student = $this->student();
        $assignment->load(['subject', 'batch', 'teacher']);

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student?->id)
            ->first();

        $appeal = \App\Models\AssignmentAppeal::where('assignment_id', $assignment->id)
            ->where('student_id', $student?->id)
            ->latest()
            ->first();

        return view('student.assignments.show', compact('assignment', 'submission', 'student', 'appeal'));
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $student = $this->student();
        if (!$student) {
            return back()->with('error', 'শিক্ষার্থী প্রোফাইল পাওয়া যায়নি।');
        }

        $validated = $request->validate([
            'submission_file' => 'required|file|mimes:pdf,doc,docx,zip,jpg,jpeg,png|max:51200',
            'student_note'    => 'nullable|string|max:1000',
        ]);

        $filePath = $request->file('submission_file')->store('assignments/submissions', 'public');

        $isLate = $assignment->isExpired();
        $status = $isLate ? 'LATE' : 'SUBMITTED';

        $submission = AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $student->id],
            [
                'submission_file' => $filePath,
                'student_note'    => $validated['student_note'] ?? null,
                'status'          => $status,
                'submitted_at'    => now(),
            ]
        );

        $msg = $isLate
            ? 'বিলম্বিতভাবে (Late Submission) আপনার অ্যাসাইনমেন্ট সফলভাবে জমা নেওয়া হয়েছে।'
            : 'আপনার অ্যাসাইনমেন্ট সফলভাবে জমা দেওয়া হয়েছে।';

        return back()->with('success', $msg);
    }

    public function appeal(Request $request, Assignment $assignment)
    {
        $student = $this->student();
        if (!$student) {
            return back()->with('error', 'শিক্ষার্থী প্রোফাইল পাওয়া যায়নি।');
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ]);

        $existing = \App\Models\AssignmentAppeal::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->where('status', 'PENDING')
            ->first();

        if ($existing) {
            return back()->with('info', 'আপনার একটি আবেদন ইতিমধ্যে প্রক্রিয়াধীন রয়েছে। অনুগ্রহ করে অপেক্ষা করুন।');
        }

        \App\Models\AssignmentAppeal::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $student->id,
            'reason'        => $validated['reason'],
            'status'        => 'PENDING',
        ]);

        return back()->with('success', 'দেরিতে জমা দেওয়ার আবেদন সফলভাবে পাঠানো হয়েছে। কর্তৃপক্ষ অনুমোদন করলে আপনি কোনো ফি ছাড়াই জমা দিতে পারবেন।');
    }
}