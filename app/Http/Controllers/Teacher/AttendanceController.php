<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\ClassSession;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $teacher = Teacher::where('email', auth()->user()->email)->first();
        $classes = ClassSession::with(['timeline.subject', 'timeline.module', 'attendances.student'])
            ->where('teacher_id', $teacher?->id)
            ->where('class_conducted', true)
            ->latest()
            ->get();

        return view('teacher.attendance.index', compact('classes'));
    }
}
