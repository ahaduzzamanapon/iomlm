<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Student;

class EnsureProfileCompleted
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?: auth()->user();

        if (!$user || !in_array(strtolower($user->role ?? ''), ['student'])) {
            return $next($request);
        }

        // Allow profile routes and logout
        if ($request->routeIs('student.profile.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        $student = Student::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$student) {
            return $next($request);
        }

        if (!$student->isProfileCompleted()) {
            return redirect()->route('student.profile.index')
                ->with('error', '⚠️ অনুগ্রহ করে প্রথমে আপনার প্রোফাইল অন্তত ৯৫% সম্পন্ন করুন। প্রোফাইল ৯৫% পূর্ণ না হওয়া পর্যন্ত ড্যাশবোর্ড ও ক্লাসের অন্যান্য ফিচার লক থাকবে।');
        }

        return $next($request);
    }
}
