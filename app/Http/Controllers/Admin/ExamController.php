<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Result;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $exams    = Exam::with(['subject', 'attendees.student'])->latest()->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        return view('admin.exams.index', compact('exams', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id'       => 'required|exists:subjects,id',
            'title'            => 'required|string|max:200',
            'type'             => 'required|in:MIDTERM,FINAL,RETAKE,QUIZ,PRACTICAL',
            'exam_date'        => 'required|date',
            'end_date'         => 'nullable|date|after_or_equal:exam_date',
            'start_time'       => 'nullable|string',
            'end_time'         => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:5|max:360',
            'full_marks'       => 'required|integer|min:1',
            'pass_marks'       => 'required|integer|min:1',
            'negative_marking' => 'nullable|numeric|min:0|max:5',
        ]);

        $examDate  = $validated['exam_date'];
        $endDate   = $validated['end_date'] ?? $examDate;
        $startTime = $validated['start_time'] ?? null;
        $endTime   = $validated['end_time'] ?? null;

        $startDatetime = $startTime ? "{$examDate} {$startTime}:00" : "{$examDate} 00:00:00";
        $endDatetime   = $endTime ? "{$endDate} {$endTime}:00" : "{$endDate} 23:59:59";

        Exam::create([
            'subject_id'       => $validated['subject_id'],
            'title'            => $validated['title'],
            'type'             => $validated['type'],
            'exam_date'        => $examDate,
            'end_date'         => $endDate,
            'start_time'       => $startTime,
            'end_time'         => $endTime,
            'start_datetime'   => $startDatetime,
            'end_datetime'     => $endDatetime,
            'duration_minutes' => $validated['duration_minutes'] ?? 90,
            'full_marks'       => $validated['full_marks'],
            'pass_marks'       => $validated['pass_marks'],
            'negative_marking' => $validated['negative_marking'] ?? 0.00,
            'status'           => 'SCHEDULED',
        ]);

        return back()->with('success', 'পরীক্ষা সফলভাবে শিডিউল করা হয়েছে।');
    }

    public function show(Exam $exam)
    {
        $exam->load(['subject', 'attendees.student', 'results.student', 'submissions.student', 'examQuestions.question']);
        return view('admin.exams.show', compact('exam'));
    }

    public function update(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'status' => 'required|in:SCHEDULED,ONGOING,COMPLETED,CANCELLED',
        ]);
        $exam->update($validated);
        return back()->with('success', 'পরীক্ষার স্ট্যাটাস আপডেট করা হয়েছে।');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return back()->with('success', 'পরীক্ষা মুছে ফেলা হয়েছে।');
    }

    public function resetSubmission(Exam $exam, \App\Models\ExamSubmission $submission)
    {
        \App\Models\ExamAnswer::where('submission_id', $submission->id)->delete();
        $studentName = $submission->student?->name ?? 'শিক্ষার্থী';
        $submission->delete();

        return back()->with('success', "{$studentName}-এর পরীক্ষার খাতা সফলভাবে রিসেট করা হয়েছে। শিক্ষার্থী পুনরায় পরীক্ষা দিতে পারবেন।");
    }
}
