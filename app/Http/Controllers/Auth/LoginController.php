<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($request->input('email'));
        $password   = (string) $request->input('password');

        $user = null;

        // 1. Direct email match
        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $user = \App\Models\User::where('email', $loginInput)->first();
            if (!$user) {
                $teacher = \App\Models\Teacher::where('email', $loginInput)->first();
                if ($teacher && $teacher->user) {
                    $user = $teacher->user;
                }
            }
        }

        // 2. Non-email match (Employee ID, Student Code, or Phone Number)
        if (!$user) {
            $cleanCode = preg_replace('/[^a-zA-Z0-9]/', '', $loginInput);

            // A. Check Teacher by Employee ID
            $teacher = \App\Models\Teacher::where('employee_id', $loginInput)
                ->orWhereRaw("REPLACE(REPLACE(employee_id, '-', ''), ' ', '') = ?", [$cleanCode])
                ->first();

            // B. Check Teacher by Phone Number
            if (!$teacher) {
                $phoneDigits = preg_replace('/[^0-9]/', '', $loginInput);
                if (strlen($phoneDigits) >= 10) {
                    $last10 = substr($phoneDigits, -10);
                    $teacher = \App\Models\Teacher::where('phone', 'like', "%{$last10}")->first();
                }
            }

            // Auto-heal teacher user account if missing
            if ($teacher) {
                if ($teacher->user) {
                    $user = $teacher->user;
                } else {
                    $loginEmail = !empty($teacher->email) ? $teacher->email : ($teacher->employee_id . '@iom.teacher');
                    $user = \App\Models\User::firstOrCreate(
                        ['email' => $loginEmail],
                        [
                            'name'        => $teacher->name,
                            'password'    => $teacher->password ?: \Illuminate\Support\Facades\Hash::make($password),
                            'role'        => 'teacher',
                            'gender'      => $teacher->gender,
                            'employee_id' => $teacher->employee_id,
                        ]
                    );
                    $teacher->update(['user_id' => $user->id]);
                }
            }

            // C. Check User directly by Employee ID
            if (!$user) {
                $user = \App\Models\User::where('employee_id', $loginInput)
                    ->orWhereRaw("REPLACE(REPLACE(employee_id, '-', ''), ' ', '') = ?", [$cleanCode])
                    ->first();
            }

            // D. Check Student by student_code
            if (!$user) {
                $student = \App\Models\Student::where('student_code', $loginInput)
                    ->orWhereRaw("REPLACE(REPLACE(student_code, '-', ''), ' ', '') = ?", [$cleanCode])
                    ->first();

                // E. Check Student by Phone Number
                if (!$student) {
                    $phoneDigits = preg_replace('/[^0-9]/', '', $loginInput);
                    if (strlen($phoneDigits) >= 10) {
                        $last10 = substr($phoneDigits, -10);
                        $student = \App\Models\Student::where('phone', 'like', "%{$last10}")->first();
                    }
                }

                if ($student && $student->user) {
                    $user = $student->user;
                }
            }
        }

        $credentials = [
            'email'    => $user ? $user->email : $loginInput,
            'password' => $password,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            \App\Models\LoginHistory::recordLogin(Auth::user());
            return $this->redirectByRole();
        }

        return back()->withErrors([
            'email' => 'প্রদত্ত তথ্যসমূহ সঠিক নয় বা পাসওয়ার্ড ভুল হয়েছে।',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function redirectByRole()
    {
        $role = Auth::user()->role ?? 'admin';
        return match($role) {
            'teacher'       => redirect()->intended(route('teacher.dashboard')),
            'student'       => redirect()->intended(route('student.dashboard')),
            'support_agent',
            'support'       => redirect()->intended(route('support.dashboard')),
            default         => redirect()->intended(route('admin.dashboard')),
        };
    }
}
