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
    public function handle(Request $request, Closure $next, ...$modules): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        $allModules = [];
        foreach ($modules as $m) {
            foreach (preg_split('/[,|]/', (string)$m) as $part) {
                $trimmed = trim($part);
                if ($trimmed !== '') {
                    $allModules[] = $trimmed;
                }
            }
        }

        $hasAccess = false;
        foreach ($allModules as $m) {
            if ($user->canAccess($m)) {
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

            $firstModule = $allModules[0] ?? 'general';
            $moduleName = User::adminModules()[$firstModule]['name'] ?? $firstModule;
            return redirect()->route('admin.dashboard')
                ->with('error', "⛔ অ্যাক্সেস অস্বীকৃত! আপনার '{$moduleName}' মডিউলে প্রবেশের অনুমতি নেই।");
        }

        return $next($request);
    }
}
