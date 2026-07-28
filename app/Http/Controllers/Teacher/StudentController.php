<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with(['enrollments.batch.course'])->where('status', 'ACTIVE')->get();
        return view('teacher.students.index', compact('students'));
    }

    public function show(Student $student)
    {
        $student->load(['enrollments.batch.course', 'attendances.classSession.timeline.subject', 'results.exam.subject']);
        return view('teacher.students.show', compact('student'));
    }
}
