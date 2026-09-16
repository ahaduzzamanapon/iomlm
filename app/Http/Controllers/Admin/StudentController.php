<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        if (!empty($validated['email']) && $student->user) {
            $student->user->update(['email' => $validated['email']]);
        }

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

    /**
     * Impersonate Student (Login directly as student)
     */
    public function impersonate(Student $student)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'অননুমোদিত অনুরোধ। শুধুমাত্র অ্যাডমিন এই সুবিধা ব্যবহার করতে পারবেন।');
        }

        $adminId = Auth::id();

        // Get or create associated User
        $user = $student->user;
        if (!$user) {
            $loginEmail = $student->email ?: ($student->student_code . '@iom.student');
            if (User::where('email', $loginEmail)->exists()) {
                $user = User::where('email', $loginEmail)->first();
            } else {
                $user = User::create([
                    'name'     => $student->name,
                    'email'    => $loginEmail,
                    'password' => Hash::make($student->phone ?: 'iom@1234'),
                    'role'     => 'student',
                ]);
            }
            $student->user_id = $user->id;
            $student->save();
        }

        // Save admin user ID to session
        session()->put('admin_impersonator_id', $adminId);

        // Login as the student
        Auth::login($user);

        return redirect()->route('student.dashboard')
            ->with('success', "আপনি শিক্ষার্থী '{$student->name}' (আইডি: {$student->student_code}) হিসেবে সরাসরি প্রবেশ করেছেন।");
    }

    /**
     * Return back to Admin panel from impersonated student session
     */
    public function leaveImpersonation()
    {
        if (!session()->has('admin_impersonator_id')) {
            return redirect()->route('student.dashboard');
        }

        $adminId = session()->pull('admin_impersonator_id');
        $adminUser = User::find($adminId);

        if ($adminUser && $adminUser->isAdmin()) {
            Auth::login($adminUser);
            return redirect()->route('admin.dashboard')
                ->with('success', 'অ্যাডমিন প্যানেলে সফলভাবে ফিরে এসেছেন।');
        }

        return redirect()->route('login');
    }
}
