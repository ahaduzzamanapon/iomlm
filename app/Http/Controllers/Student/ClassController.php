<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\ClassSession;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $student = Student::where('email', auth()->user()->email)->first();
        $batchIds = Enrollment::where('student_id', $student?->id)->where('status', 'ACTIVE')->pluck('batch_id');

        $classes = ClassSession::with(['timeline.subject', 'timeline.module', 'timeline.batch', 'teacher'])
            ->whereHas('timeline', fn($q) => $q->whereIn('batch_id', $batchIds))
            ->latest()
            ->get();

        return view('student.classes.index', compact('classes'));
    }
}
