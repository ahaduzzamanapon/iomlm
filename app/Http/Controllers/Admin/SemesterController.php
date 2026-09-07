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
            'has_groups'  => 'nullable|boolean',
            'group_type'  => 'nullable|in:NONE,GENDER,SPLIT',
            'split_count' => 'nullable|integer|min:2|max:10',
        ]);

        $hasGroups = $request->boolean('has_groups');
        $groupType = $hasGroups ? ($request->input('group_type', 'GENDER')) : 'NONE';

        Semester::create([
            'course_id'   => $validated['course_id'],
            'name'        => $validated['name'],
            'sequence_no' => $validated['sequence_no'],
            'has_groups'  => $hasGroups,
            'group_type'  => $groupType,
            'split_count' => $request->input('split_count', 2),
        ]);

        return back()->with('success', 'Semester created successfully.');
    }

    public function update(Request $request, Semester $semester)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'sequence_no' => 'required|integer|min:1',
            'has_groups'  => 'nullable|boolean',
            'group_type'  => 'nullable|in:NONE,GENDER,SPLIT',
            'split_count' => 'nullable|integer|min:2|max:10',
        ]);

        $hasGroups = $request->boolean('has_groups');
        $groupType = $hasGroups ? ($request->input('group_type', 'GENDER')) : 'NONE';

        $semester->update([
            'name'        => $validated['name'],
            'sequence_no' => $validated['sequence_no'],
            'has_groups'  => $hasGroups,
            'group_type'  => $groupType,
            'split_count' => $request->input('split_count', 2),
        ]);

        return back()->with('success', 'Semester configuration updated.');
    }

    public function destroy(Semester $semester)
    {
        $semester->delete();
        return back()->with('success', 'Semester deleted.');
    }
}
