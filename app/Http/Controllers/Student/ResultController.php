<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Result;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index()
    {
        $student = Student::where('user_id', auth()->id())->first();
        $results = Result::with(['subject', 'exam'])
            ->where('student_id', $student?->id)
            ->latest('attempt_no')
            ->get();

        return view('student.results.index', compact('results'));
    }
}
