<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware('role:admin') / 'role:teacher' / 'role:student'
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role ?? 'admin'; // default admin until roles fully wired

        if (!in_array($userRole, $roles)) {
            abort(403, 'Unauthorized — insufficient role.');
        }

        return $next($request);
    }
}
