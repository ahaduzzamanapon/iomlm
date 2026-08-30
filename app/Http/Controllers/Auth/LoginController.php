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
        $password   = $request->input('password');

        // Check if login input is email or student_code
        $credentials = ['email' => $loginInput, 'password' => $password];

        if (!filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            // Find student by student_code
            $student = \App\Models\Student::where('student_code', $loginInput)->first();
            if ($student && $student->user) {
                $credentials = ['email' => $student->user->email, 'password' => $password];
            }
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return $this->redirectByRole();
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
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
