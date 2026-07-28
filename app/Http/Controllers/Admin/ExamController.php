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
        $exams = Exam::with(['subject', 'attendees.student'])->latest()->get();
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
            'duration_minutes' => 'nullable|integer|min:15',
            'full_marks'       => 'required|integer|min:10',
            'pass_marks'       => 'required|integer|min:1',
        ]);

        Exam::create([
            'subject_id'       => $validated['subject_id'],
            'title'            => $validated['title'],
            'type'             => $validated['type'],
            'exam_date'        => $validated['exam_date'],
            'duration_minutes' => $validated['duration_minutes'] ?? 90,
            'full_marks'       => $validated['full_marks'],
            'pass_marks'       => $validated['pass_marks'],
            'status'           => 'SCHEDULED',
        ]);

        return back()->with('success', 'Exam scheduled successfully.');
    }

    public function show(Exam $exam)
    {
        $exam->load(['subject', 'attendees.student', 'results.student']);
        return view('admin.exams.show', compact('exam'));
    }
}
