<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminModuleAccessMiddleware
{
    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware('admin.module:academic')
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        $modules = preg_split('/[,|]/', $module);
        $hasAccess = false;
        foreach ($modules as $m) {
            $trimmed = trim($m);
            if ($trimmed !== '' && $user->canAccess($trimmed)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'অনুমতি নেই: আপনার এই মডিউলে প্রবেশের অধিকার সংরক্ষিত নেই।'
                ], 403);
            }

            $firstModule = trim($modules[0]);
            $moduleName = User::adminModules()[$firstModule]['name'] ?? $firstModule;
            return redirect()->route('admin.dashboard')
                ->with('error', "⛔ অ্যাক্সেস অস্বীকৃত! আপনার '{$moduleName}' মডিউলে প্রবেশের অনুমতি নেই।");
        }

        return $next($request);
    }
}
