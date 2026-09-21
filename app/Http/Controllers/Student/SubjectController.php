<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $student = Student::where('user_id', auth()->id())->first();
        $enrollments = Enrollment::with([
            'course.subjects.category',
            'course.subjects.modules' => fn($q) => $q->where('is_hidden', false)->orderBy('sequence_no'),
            'course.subjects.assignments' => fn($q) => $q->where('status', 'PUBLISHED')->latest(),
        ])
            ->where('student_id', $student?->id)
            ->where('status', 'ACTIVE')
            ->get();

        return view('student.subjects.index', compact('enrollments', 'student'));
    }

    public function show(Subject $subject)
    {
        $student = Student::where('user_id', auth()->id())->first();
        $subject->load([
            'category',
            'modules' => fn($q) => $q->where('is_hidden', false)->orderBy('sequence_no'),
            'assignments' => fn($q) => $q->where('status', 'PUBLISHED')->with(['submissions' => fn($sq) => $sq->where('student_id', $student?->id)]),
        ]);

        return view('student.subjects.show', compact('subject', 'student'));
    }
}
