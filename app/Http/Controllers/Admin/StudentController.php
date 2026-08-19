<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = Student::with(['enrollments.batch.course']);

        if ($status) {
            $query->where('status', $status);
        }

        $students = $query->latest()->get();
        return view('admin.students.index', compact('students', 'status'));
    }

    public function show(Student $student)
    {
        $student->load([
            'enrollments.batch.course',
            'enrollments.semester',
            'admissions.interestedCourse',
            'invoices',
        ]);
        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:200',
            'phone'         => 'required|string|max:30',
            'email'         => 'nullable|email|unique:students,email,' . $student->id,
            'blood_group'   => 'nullable|string',
            'national_id'   => 'nullable|string|max:50',
            'address'       => 'nullable|string',
            'guardian_name' => 'nullable|string|max:200',
            'guardian_phone'=> 'nullable|string|max:30',
            'status'        => 'required|in:LEAD,PENDING,ACTIVE,ABSENT,DROPPED,CANCELLED,TRANSFERRED,COMPLETED,GRADUATED',
        ]);

        $student->update($validated);
        return redirect()->route('admin.students.show', $student)->with('success', 'Student details updated.');
    }

    public function printGradeSheet(Student $student)
    {
        $student->load(['enrollments.batch.course', 'results.exam.subject', 'attendances']);
        return view('admin.students.grade_sheet', compact('student'));
    }

    public function printCertificate(Student $student)
    {
        $student->load(['enrollments.batch.course']);
        return view('admin.students.certificate', compact('student'));
    }

    public function printIdCard(Student $student)
    {
        $student->load(['enrollments.batch.course']);
        return view('admin.students.id_card', compact('student'));
    }
}
