<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Exam;
use App\Models\ExamAttendee;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $student = Student::where('email', auth()->user()->email)->first();
        $attendees = ExamAttendee::with(['exam.subject'])
            ->where('student_id', $student?->id)
            ->get();

        return view('student.exams.index', compact('attendees'));
    }
}
