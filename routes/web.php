<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root Welcome Page (Public Landing)
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

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

// ── Public Online Support Form & Live Chat ─────────────────────────────
Route::get('/online-support', [\App\Http\Controllers\Public\OnlineSupportController::class, 'index'])->name('online-support.index');
Route::post('/online-support', [\App\Http\Controllers\Public\OnlineSupportController::class, 'store'])->name('online-support.store');
Route::get('/online-support/search', [\App\Http\Controllers\Public\OnlineSupportController::class, 'searchStatus'])->name('online-support.search');
Route::get('/online-support/chat/{uuid}', [\App\Http\Controllers\Public\OnlineSupportController::class, 'chatView'])->name('online-support.chat');
Route::get('/online-support/messages/{uuid}', [\App\Http\Controllers\Public\OnlineSupportController::class, 'getMessages'])->name('online-support.messages');
Route::post('/online-support/chat/{uuid}/message', [\App\Http\Controllers\Public\OnlineSupportController::class, 'sendMessage'])->name('online-support.chat.send');
Route::post('/online-support/rate/{uuid}', [\App\Http\Controllers\Public\OnlineSupportController::class, 'submitRating'])->name('online-support.rate');

// ── Support Agent Panel Routes (Auth) ─────────────────────────────────
Route::middleware(['auth'])->prefix('support')->name('support.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Support\SupportAgentController::class, 'dashboard'])->name('dashboard');
    Route::get('/api/queue', [\App\Http\Controllers\Support\SupportAgentController::class, 'queueApi'])->name('api.queue');
    Route::post('/tickets/{uuid}/accept', [\App\Http\Controllers\Support\SupportAgentController::class, 'acceptTicket'])->name('tickets.accept');
    Route::post('/tickets/{uuid}/transfer', [\App\Http\Controllers\Support\SupportAgentController::class, 'transferTicket'])->name('tickets.transfer');
    Route::get('/chat/{uuid}', [\App\Http\Controllers\Support\SupportAgentController::class, 'agentChat'])->name('chat');
    Route::post('/tickets/{uuid}/message', [\App\Http\Controllers\Support\SupportAgentController::class, 'sendMessage'])->name('tickets.message');
    Route::post('/tickets/{uuid}/close', [\App\Http\Controllers\Support\SupportAgentController::class, 'closeTicket'])->name('tickets.close');

    // Canned Messages (Quick Replies)
    Route::get('/canned-messages', [\App\Http\Controllers\Support\SupportAgentController::class, 'cannedMessagesIndex'])->name('canned-messages.index');
    Route::post('/canned-messages', [\App\Http\Controllers\Support\SupportAgentController::class, 'cannedMessagesStore'])->name('canned-messages.store');
    Route::put('/canned-messages/{cannedMessage}', [\App\Http\Controllers\Support\SupportAgentController::class, 'cannedMessagesUpdate'])->name('canned-messages.update');
    Route::delete('/canned-messages/{cannedMessage}', [\App\Http\Controllers\Support\SupportAgentController::class, 'cannedMessagesDestroy'])->name('canned-messages.destroy');
});

// ── Hidden Artisan Command Runner Route (/command) ────────────────────
Route::get('/command', [\App\Http\Controllers\Admin\CommandRunnerController::class, 'index'])->middleware(['auth'])->name('command.index');
Route::post('/command', [\App\Http\Controllers\Admin\CommandRunnerController::class, 'run'])->middleware(['auth'])->name('command.run');
