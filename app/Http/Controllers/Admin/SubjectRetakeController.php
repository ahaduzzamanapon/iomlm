<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubjectRetake;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectRetakeController extends Controller
{
    public function index()
    {
        $retakes = SubjectRetake::with(['student', 'subject'])->latest()->get();
        $students = Student::where('status', 'ACTIVE')->orderBy('name')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        return view('admin.retakes.index', compact('retakes', 'students', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'  => 'required|exists:students,id',
            'subject_id'  => 'required|exists:subjects,id',
            'retake_type' => 'required|in:EXAM_ONLY,CLASS_EXAM,FULL_RESTART',
            'notes'       => 'nullable|string',
        ]);

        SubjectRetake::create([
            'student_id'  => $validated['student_id'],
            'subject_id'  => $validated['subject_id'],
            'retake_type' => $validated['retake_type'],
            'status'      => 'ACTIVE',
            'notes'       => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Subject retake registered successfully.');
    }
}
