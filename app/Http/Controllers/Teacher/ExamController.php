<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\SubjectTeacherAssignment;
use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $teacher = Teacher::where('email', auth()->user()->email)->first();
        $subjectIds = SubjectTeacherAssignment::where('teacher_id', $teacher?->id)->pluck('subject_id');

        $exams = Exam::with(['subject', 'attendees.student'])
            ->whereIn('subject_id', $subjectIds)
            ->latest()
            ->get();

        return view('teacher.exams.index', compact('exams'));
    }
}
