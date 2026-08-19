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

// ── Google OAuth Routes ──────────────────────────────────────────────
Route::get('/auth/google/redirect', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// ── Panel Routes ─────────────────────────────────────────────────────
require __DIR__.'/admin.php';
require __DIR__.'/teacher.php';
require __DIR__.'/student.php';

// ── Public Admission Form (No Auth) ──────────────────────────────────
Route::get('/apply', [\App\Http\Controllers\Public\AdmissionFormController::class, 'show'])->name('apply.show');
Route::post('/apply', [\App\Http\Controllers\Public\AdmissionFormController::class, 'store'])->name('apply.store');
Route::get('/apply/success/{applicationNo}', [\App\Http\Controllers\Public\AdmissionFormController::class, 'success'])->name('apply.success');
Route::get('/api/districts', [\App\Http\Controllers\Public\AdmissionFormController::class, 'districts'])->name('api.districts');

// ── Public Poor Fund / Waiver Form ────────────────────────────────────
Route::get('/poor-fund', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'show'])->name('poor_fund.show');
Route::post('/poor-fund', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'store'])->name('poor_fund.store');
Route::get('/poor-fund/success/{applicationNo}', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'success'])->name('poor_fund.success');
Route::get('/api/waiver-lookup', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'lookup'])->name('api.waiver-lookup');
