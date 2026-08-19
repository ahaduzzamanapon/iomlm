<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\SubjectTeacherAssignment;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();
        $assignments = SubjectTeacherAssignment::with(['subject.modules' => fn($q) => $q->orderBy('sequence_no')])
            ->where('teacher_id', $teacher?->id)
            ->get();

        return view('teacher.subjects.index', compact('assignments'));
    }

    public function show($subjectId)
    {
        $subject = \App\Models\Subject::with(['modules' => fn($q) => $q->orderBy('sequence_no')])->findOrFail($subjectId);
        return view('teacher.subjects.show', compact('subject'));
    }
}
