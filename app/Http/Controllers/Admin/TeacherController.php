<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Subject;
use App\Models\SubjectTeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'password'                  => 'nullable|string|min:6',
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

        $teacher = Teacher::create(array_merge($validated, [
            'employee_id' => $nextEmpId,
            'is_active'   => $request->boolean('is_active', true),
        ]));

        // ── AUTO-CREATE USER ACCOUNT FOR TEACHER ─────────────────────────
        $loginEmail   = !empty($validated['email']) ? $validated['email'] : ($nextEmpId . '@iom.teacher');
        $customPass   = $request->input('password');
        $tempPassword = !empty($customPass) ? $customPass : (!empty($validated['phone']) ? $validated['phone'] : 'iom@1234');

        if (User::where('email', $loginEmail)->exists()) {
            $loginEmail = strtolower(str_replace([' ', '-'], '.', $nextEmpId)) . '@iom.teacher';
        }

        $user = User::create([
            'name'     => $teacher->name,
            'email'    => $loginEmail,
            'password' => Hash::make($tempPassword),
            'role'     => 'teacher',
        ]);

        $teacher->update(['user_id' => $user->id]);
        // ─────────────────────────────────────────────────────────────────

        return back()->with('success', "Teacher added successfully. 🔑 Login: {$loginEmail} | Password: {$tempPassword}");
    }

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'name'                       => 'required|string|max:200',
            'email'                      => 'nullable|email|unique:teachers,email,' . $teacher->id,
            'phone'                      => 'nullable|string|max:30',
            'password'                   => 'nullable|string|min:6',
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

        if ($teacher->user) {
            $userUpdate = ['name' => $teacher->name];
            if (!empty($validated['email'])) {
                $userUpdate['email'] = $validated['email'];
            }
            if ($request->filled('password')) {
                $userUpdate['password'] = Hash::make($request->input('password'));
            }
            $teacher->user->update($userUpdate);
        }

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher profile updated successfully.');
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
