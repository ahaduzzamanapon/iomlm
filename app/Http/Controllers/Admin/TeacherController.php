<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\SubjectTeacherAssignment;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::with('assignments.subject')->latest()->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        return view('admin.teachers.index', compact('teachers', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:200',
            'email'         => 'nullable|email|unique:teachers,email',
            'phone'         => 'nullable|string|max:30',
            'designation'   => 'nullable|string|max:100',
            'qualification' => 'nullable|string|max:200',
        ]);

        $nextEmpId = 'EMP-' . str_pad(Teacher::count() + 1, 3, '0', STR_PAD_LEFT);

        Teacher::create([
            'employee_id'   => $nextEmpId,
            'name'          => $validated['name'],
            'email'         => $validated['email'] ?? null,
            'phone'         => $validated['phone'] ?? null,
            'designation'   => $validated['designation'] ?? null,
            'qualification' => $validated['qualification'] ?? null,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Teacher added successfully.');
    }

    public function assignSubject(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        SubjectTeacherAssignment::firstOrCreate([
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $teacher->id,
            'batch_id'   => null, // Global assignment per §3
        ]);

        return back()->with('success', 'Subject assigned to teacher.');
    }

    public function removeSubject(Teacher $teacher, SubjectTeacherAssignment $assignment)
    {
        $assignment->delete();
        return back()->with('success', 'Subject assignment removed.');
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();
        return back()->with('success', 'Teacher profile deleted.');
    }
}
