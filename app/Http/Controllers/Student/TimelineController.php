<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Timeline;
use App\Models\Enrollment;
use App\Models\Attendance;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    public function index()
    {
        $student = Student::where('email', auth()->user()->email)->first();
        $studentId = $student?->id;

        $batchIds = Enrollment::where('student_id', $studentId)
            ->where('status', 'ACTIVE')
            ->pluck('batch_id');

        $timelines = Timeline::with(['subject', 'module', 'batch'])
            ->whereIn('batch_id', $batchIds)
            ->orderBy('scheduled_date')
            ->get();

        // Fetch attendance map for this student
        $attendances = Attendance::where('student_id', $studentId)
            ->pluck('status', 'class_session_id');

        return view('student.timeline.index', compact('timelines', 'attendances'));
    }
}
