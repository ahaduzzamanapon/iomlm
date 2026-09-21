<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Teacher;
use App\Models\SubjectTeacherAssignment;
use App\Models\Subject;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    private function teacher(): ?Teacher
    {
        return Teacher::where('user_id', auth()->id())->first();
    }

    public function index(Request $request)
    {
        $teacher = $this->teacher();
        $assignedSubjectIds = SubjectTeacherAssignment::where('teacher_id', $teacher?->id)->pluck('subject_id');

        if ($assignedSubjectIds->isEmpty()) {
            $assignedSubjectIds = Subject::where('is_active', true)->pluck('id');
        }

        $subjectId = $request->query('subject_id');
        $batchId   = $request->query('batch_id');

        $query = Assignment::with(['subject', 'batch', 'submissions.student'])
            ->whereIn('subject_id', $assignedSubjectIds)
            ->latest();

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        $assignments = $query->paginate(20);
        $subjects    = Subject::whereIn('id', $assignedSubjectIds)->orderBy('name')->get();
        $batches     = Batch::where('status', 'ACTIVE')->orderBy('name')->get();

        return view('teacher.assignments.index', compact('assignments', 'subjects', 'batches', 'subjectId', 'batchId'));
    }

    public function store(Request $request)
    {
        $teacher = $this->teacher();
        $assignedSubjectIds = SubjectTeacherAssignment::where('teacher_id', $teacher?->id)->pluck('subject_id');

        if ($assignedSubjectIds->isEmpty()) {
            $assignedSubjectIds = Subject::where('is_active', true)->pluck('id');
        }

        $validated = $request->validate([
            'subject_id'     => 'required|in:' . $assignedSubjectIds->implode(','),
            'batch_id'       => 'nullable|exists:batches,id',
            'title'          => 'required|string|max:250',
            'instructions'   => 'nullable|string',
            'total_marks'    => 'required|numeric|min:1',
            'start_datetime' => 'nullable|date',
            'due_datetime'   => 'required|date',
            'attachment'     => 'nullable|file|mimes:pdf,doc,docx,zip,jpg,jpeg,png|max:51200',
        ]);

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('assignments/questions', 'public');
        }

        Assignment::create([
            'subject_id'     => $validated['subject_id'],
            'batch_id'       => $validated['batch_id'] ?? null,
            'teacher_id'     => $teacher?->id,
            'title'          => $validated['title'],
            'instructions'   => $validated['instructions'] ?? null,
            'file_path'      => $filePath,
            'total_marks'    => $validated['total_marks'],
            'start_datetime' => $validated['start_datetime'] ?? now(),
            'due_datetime'   => $validated['due_datetime'],
            'status'         => 'PUBLISHED',
        ]);

        return back()->with('success', 'অ্যাসাইনমেন্ট সফলভাবে তৈরি করা হয়েছে।');
    }

    public function show(Assignment $assignment)
    {
        $assignment->load(['subject', 'batch', 'teacher', 'submissions.student']);
        return view('teacher.assignments.show', compact('assignment'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'subject_id'     => 'required|exists:subjects,id',
            'batch_id'       => 'nullable|exists:batches,id',
            'title'          => 'required|string|max:250',
            'instructions'   => 'nullable|string',
            'total_marks'    => 'required|numeric|min:1',
            'start_datetime' => 'nullable|date',
            'due_datetime'   => 'required|date',
            'attachment'     => 'nullable|file|mimes:pdf,doc,docx,zip,jpg,jpeg,png|max:51200',
        ]);

        $filePath = $assignment->file_path;
        if ($request->hasFile('attachment')) {
            if ($assignment->file_path && Storage::disk('public')->exists($assignment->file_path)) {
                Storage::disk('public')->delete($assignment->file_path);
            }
            $filePath = $request->file('attachment')->store('assignments/questions', 'public');
        }

        $assignment->update([
            'subject_id'     => $validated['subject_id'],
            'batch_id'       => $validated['batch_id'] ?? null,
            'title'          => $validated['title'],
            'instructions'   => $validated['instructions'] ?? null,
            'file_path'      => $filePath,
            'total_marks'    => $validated['total_marks'],
            'start_datetime' => $validated['start_datetime'] ?? $assignment->start_datetime,
            'due_datetime'   => $validated['due_datetime'],
        ]);

        return back()->with('success', 'অ্যাসাইনমেন্ট সফলভাবে আপডেট করা হয়েছে।');
    }

    public function destroy(Assignment $assignment)
    {
        if ($assignment->file_path && Storage::disk('public')->exists($assignment->file_path)) {
            Storage::disk('public')->delete($assignment->file_path);
        }

        foreach ($assignment->submissions as $sub) {
            if ($sub->submission_file && Storage::disk('public')->exists($sub->submission_file)) {
                Storage::disk('public')->delete($sub->submission_file);
            }
            $sub->delete();
        }

        $assignment->delete();
        return redirect()->route('teacher.assignments.index')->with('success', 'অ্যাসাইনমেন্ট সফলভাবে মুছে ফেলা হয়েছে।');
    }

    public function gradeSubmission(Request $request, AssignmentSubmission $submission)
    {
        $validated = $request->validate([
            'obtained_marks'   => 'required|numeric|min:0|max:' . $submission->assignment->total_marks,
            'teacher_feedback' => 'nullable|string|max:1000',
        ]);

        $submission->update([
            'obtained_marks'   => $validated['obtained_marks'],
            'teacher_feedback' => $validated['teacher_feedback'] ?? null,
            'status'           => 'GRADED',
        ]);

        return back()->with('success', "শিক্ষার্থীর খাতা সফলভাবে মূল্যায়ন করা হয়েছে।");
    }

    public function overrideSubmission(Request $request, AssignmentSubmission $submission)
    {
        $validated = $request->validate([
            'obtained_marks'   => 'nullable|numeric|min:0|max:' . $submission->assignment->total_marks,
            'teacher_feedback' => 'nullable|string|max:1000',
            'student_note'     => 'nullable|string|max:1000',
            'status'           => 'required|in:SUBMITTED,GRADED,LATE,REJECTED',
            'submission_file'  => 'nullable|file|mimes:pdf,doc,docx,zip,jpg,jpeg,png|max:51200',
        ]);

        $filePath = $submission->submission_file;
        if ($request->hasFile('submission_file')) {
            if ($submission->submission_file && Storage::disk('public')->exists($submission->submission_file)) {
                Storage::disk('public')->delete($submission->submission_file);
            }
            $filePath = $request->file('submission_file')->store('assignments/submissions', 'public');
        }

        $submission->update([
            'submission_file'  => $filePath,
            'student_note'     => $validated['student_note'] ?? $submission->student_note,
            'obtained_marks'   => $validated['obtained_marks'] ?? $submission->obtained_marks,
            'teacher_feedback' => $validated['teacher_feedback'] ?? $submission->teacher_feedback,
            'status'           => $validated['status'],
        ]);

        return back()->with('success', "শিক্ষার্থীর সাবমিশন সফলভাবে ওভাররাইড করা হয়েছে।");
    }
}
