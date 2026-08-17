<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\SubjectTeacherAssignment;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::with('assignments.subject')->latest()->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        return view('admin.teachers.index', compact('teachers', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                      => 'required|string|max:200',
            'email'                     => 'nullable|email|unique:teachers,email',
            'phone'                     => 'nullable|string|max:30',
            'date_of_birth'             => 'nullable|date',
            'gender'                    => 'nullable|string|max:20',
            'marital_status'            => 'nullable|string|max:20',
            'national_id'               => 'nullable|string|max:50',
            'religion'                  => 'nullable|string|max:50',
            'designation'               => 'nullable|string|max:100',
            'department'                => 'nullable|string|max:100',
            'qualification'             => 'nullable|string|max:200',
            'employment_type'           => 'nullable|string|max:30',
            'salary'                    => 'nullable|numeric|min:0',
            'joining_date'              => 'nullable|date',
            'bio'                       => 'nullable|string',
            'emergency_contact_name'    => 'nullable|string|max:200',
            'emergency_contact_phone'   => 'nullable|string|max:30',
            'emergency_contact_relation'=> 'nullable|string|max:50',
            'present_house'             => 'nullable|string|max:300',
            'present_post_office'       => 'nullable|string|max:100',
            'present_police_station'    => 'nullable|string|max:100',
            'present_district_id'       => 'nullable|exists:districts,id',
            'present_division_id'       => 'nullable|exists:divisions,id',
            'permanent_house'           => 'nullable|string|max:300',
            'permanent_post_office'     => 'nullable|string|max:100',
            'permanent_police_station'  => 'nullable|string|max:100',
            'permanent_district_id'     => 'nullable|exists:districts,id',
            'permanent_division_id'     => 'nullable|exists:divisions,id',
        ]);

        $maxId    = Teacher::max('id') ?? 0;
        $nextEmpId = 'EMP-' . str_pad($maxId + 1, 3, '0', STR_PAD_LEFT);

        Teacher::create(array_merge($validated, [
            'employee_id' => $nextEmpId,
            'is_active'   => $request->boolean('is_active', true),
        ]));

        return back()->with('success', 'Teacher added successfully.');
    }

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'name'                       => 'required|string|max:200',
            'email'                      => 'nullable|email|unique:teachers,email,' . $teacher->id,
            'phone'                      => 'nullable|string|max:30',
            'date_of_birth'              => 'nullable|date',
            'gender'                     => 'nullable|string|max:20',
            'marital_status'             => 'nullable|string|max:20',
            'national_id'                => 'nullable|string|max:50',
            'religion'                   => 'nullable|string|max:50',
            'designation'                => 'nullable|string|max:100',
            'department'                 => 'nullable|string|max:100',
            'qualification'              => 'nullable|string|max:200',
            'employment_type'            => 'nullable|string|max:30',
            'salary'                     => 'nullable|numeric|min:0',
            'joining_date'               => 'nullable|date',
            'bio'                        => 'nullable|string',
            'emergency_contact_name'     => 'nullable|string|max:200',
            'emergency_contact_phone'    => 'nullable|string|max:30',
            'emergency_contact_relation' => 'nullable|string|max:50',
        ]);

        $teacher->update(array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
        ]));

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher updated successfully.');
    }

    public function assignSubject(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        SubjectTeacherAssignment::firstOrCreate([
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $teacher->id,
            'batch_id'   => null, // Global assignment per §3
        ]);

        return back()->with('success', 'Subject assigned to teacher.');
    }

    public function removeSubject(Teacher $teacher, SubjectTeacherAssignment $assignment)
    {
        $assignment->delete();
        return back()->with('success', 'Subject assignment removed.');
    }

    public function printIdCard(Teacher $teacher)
    {
        $teacher->load('assignments.subject');
        return view('admin.teachers.id_card', compact('teacher'));
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();
        return back()->with('success', 'Teacher profile deleted.');
    }
}
