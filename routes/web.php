<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root redirect → login (Breeze default) or admin dashboard
Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role ?? 'admin';
        return match($role) {
            'teacher' => redirect()->route('teacher.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default   => redirect()->route('admin.dashboard'),
        };
    }
    return redirect()->route('login');
});

// ── Auth routes (login/logout only — no registration for now) ─────────
Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// ── Panel Routes ─────────────────────────────────────────────────────
require __DIR__.'/admin.php';
require __DIR__.'/teacher.php';
require __DIR__.'/student.php';
