<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware('role:admin,super_admin') / 'role:teacher' / 'role:student'
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $userRole = strtolower(trim($user->role ?? ''));

        // Handle alias role matches
        if ($userRole === 'super_admin' && (in_array('admin', $roles) || in_array('super_admin', $roles))) {
            return $next($request);
        }
        if ($userRole === 'support_agent' && (in_array('support', $roles) || in_array('support_agent', $roles))) {
            return $next($request);
        }

        if (!in_array($userRole, $roles)) {
            // Redirect unauthorized users to their respective panel dashboard
            $redirectRoute = match($userRole) {
                'teacher'       => route('teacher.dashboard'),
                'student'       => route('student.dashboard'),
                'support',
                'support_agent' => route('support.dashboard'),
                'admin',
                'super_admin'   => route('admin.dashboard'),
                default         => route('login'),
            };

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Your role does not have permission for this section.'
                ], 403);
            }

            return redirect($redirectRoute)->with('error', '⛔ Access Denied! You do not have permission to access that area.');
        }

        return $next($request);
    }
}
