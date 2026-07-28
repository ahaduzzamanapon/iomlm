<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SubjectModule;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::withCount('modules')->latest()->get();
        return view('admin.subjects.index', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:200',
            'code'       => 'required|string|max:30|unique:subjects,code',
            'credit'     => 'required|integer|min:1|max:10',
            'full_marks' => 'required|integer|min:10',
            'pass_marks' => 'required|integer|min:1',
        ]);

        Subject::create([
            'name'       => $validated['name'],
            'code'       => strtoupper($validated['code']),
            'credit'     => $validated['credit'],
            'full_marks' => $validated['full_marks'],
            'pass_marks' => $validated['pass_marks'],
            'version'    => 1,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Subject created successfully.');
    }

    public function show(Subject $subject)
    {
        $subject->load(['modules' => fn($q) => $q->orderBy('sequence_no')]);
        return view('admin.subjects.show', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:200',
            'code'       => 'required|string|max:30|unique:subjects,code,' . $subject->id,
            'credit'     => 'required|integer|min:1|max:10',
            'full_marks' => 'required|integer|min:10',
            'pass_marks' => 'required|integer|min:1',
        ]);

        $subject->update([
            'name'       => $validated['name'],
            'code'       => strtoupper($validated['code']),
            'credit'     => $validated['credit'],
            'full_marks' => $validated['full_marks'],
            'pass_marks' => $validated['pass_marks'],
            'is_active'  => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()->route('admin.subjects.index')->with('success', 'Subject deleted.');
    }
}
