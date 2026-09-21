<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Student;

class EnsureCourseAccess
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

        // Always allow dashboard, profile, fees/payment, support, and logout
        $allowedPrefixes = [
            'student.profile',
            'student.dashboard',
            'student.fees',
            'student.support',
            'logout',
        ];

        foreach ($allowedPrefixes as $prefix) {
            if ($request->route() && method_exists($request->route(), 'named')) {
                if ($request->routeIs($prefix) || $request->routeIs($prefix . '.*')) {
                    return $next($request);
                }
            }
        }

        // Fallback check on request path
        $path = trim($request->path(), '/');
        if ($path === 'student' || $path === 'student/dashboard' || str_starts_with($path, 'student/profile') || str_starts_with($path, 'student/fees') || str_starts_with($path, 'student/support')) {
            return $next($request);
        }

        $student = Student::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$student) {
            return $next($request);
        }

        if (isset($student->has_course_access) && !$student->has_course_access) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'কোর্স অ্যাক্সেস নিষ্ক্রিয় রয়েছে।'
                ], 403);
            }

            return redirect()->route('student.dashboard')
                ->with('error', '⚠️ আপনার কোর্সের অ্যাক্সেস বর্তমানে সাময়িকভাবে স্থগিত বা নিষ্ক্রিয় করা হয়েছে। অনুগ্রহ করে সাপোর্ট বা অ্যাকাডেমিক বিভাগে যোগাযোগ করুন।');
        }

        return $next($request);
    }
}
