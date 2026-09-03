<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SubjectTeacherAssignment;
use App\Models\RoutineEntry;
use App\Models\ClassSession;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        if ($teacher) {
            $assignedBatchIds = SubjectTeacherAssignment::where('teacher_id', $teacher->id)->pluck('batch_id')
                ->merge(RoutineEntry::where('teacher_id', $teacher->id)->pluck('batch_id'))
                ->merge(ClassSession::where('teacher_id', $teacher->id)->pluck('batch_id'))
                ->filter()->unique();

            $students = Student::with(['enrollments.batch.course'])
                ->where('status', 'ACTIVE')
                ->whereHas('enrollments', function($q) use ($assignedBatchIds) {
                    $q->whereIn('batch_id', $assignedBatchIds);
                })
                ->paginate(25);
        } else {
            $students = Student::with(['enrollments.batch.course'])->where('status', 'ACTIVE')->paginate(25);
        }

        return view('teacher.students.index', compact('students'));
    }

    public function show(Student $student)
    {
        $student->load(['enrollments.batch.course', 'attendances.classSession.timeline.subject', 'results.exam.subject']);
        return view('teacher.students.show', compact('student'));
    }
}
