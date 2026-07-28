<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\Course;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    public function index()
    {
        $semesters = Semester::with('course')->orderBy('course_id')->orderBy('sequence_no')->get();
        $courses = Course::where('type', 'SEMESTER_BASED')->where('is_active', true)->orderBy('name')->get();
        return view('admin.semesters.index', compact('semesters', 'courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id'   => 'required|exists:courses,id',
            'name'        => 'required|string|max:100',
            'sequence_no' => 'required|integer|min:1',
        ]);

        Semester::create($validated);
        return back()->with('success', 'Semester created successfully.');
    }

    public function destroy(Semester $semester)
    {
        $semester->delete();
        return back()->with('success', 'Semester deleted.');
    }
}
