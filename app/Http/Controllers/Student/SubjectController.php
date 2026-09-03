<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $student = Student::where('user_id', auth()->id())->first();
        $enrollments = Enrollment::with(['course.subjects.modules' => fn($q) => $q->orderBy('sequence_no')])
            ->where('student_id', $student?->id)
            ->where('status', 'ACTIVE')
            ->get();

        return view('student.subjects.index', compact('enrollments'));
    }
}
