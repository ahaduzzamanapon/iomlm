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

// ── Impersonation Leave Route ─────────────────────────────────────────
Route::get('/impersonate/leave', [\App\Http\Controllers\Admin\StudentController::class, 'leaveImpersonation'])->name('admin.impersonate.leave');

// ── Google OAuth Routes ──────────────────────────────────────────────
Route::get('/auth/google/redirect', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// ── Panel Routes ─────────────────────────────────────────────────────
require __DIR__ . '/admin.php';
require __DIR__ . '/teacher.php';
require __DIR__ . '/student.php';

// ── Public Admission Form (No Auth) ──────────────────────────────────
Route::get('/apply', [\App\Http\Controllers\Public\AdmissionFormController::class, 'show'])->name('apply.show');
Route::post('/apply', [\App\Http\Controllers\Public\AdmissionFormController::class, 'store'])->name('apply.store');
Route::get('/apply/payment/{applicationNo}', [\App\Http\Controllers\Public\AdmissionFormController::class, 'paymentView'])->name('apply.payment');
Route::post('/apply/payment/{applicationNo}', [\App\Http\Controllers\Public\AdmissionFormController::class, 'processPayment'])->name('apply.payment.process');
Route::get('/apply/success/{applicationNo}', [\App\Http\Controllers\Public\AdmissionFormController::class, 'success'])->name('apply.success');
Route::get('/api/districts', [\App\Http\Controllers\Public\AdmissionFormController::class, 'districts'])->name('api.districts');

// ── Public Applicant Tracker ──────────────────────────────────────────
Route::get('/admission/status', [\App\Http\Controllers\Public\AdmissionFormController::class, 'trackStatus'])->name('admission.status');
Route::post('/admission/status', [\App\Http\Controllers\Public\AdmissionFormController::class, 'trackStatusLookup'])->name('admission.status.lookup');

// ── Payment Gateway Callbacks & Verification ──────────────────────────
Route::get('/apply/payment-status/{tranId}', [\App\Http\Controllers\Public\PaymentCallbackController::class, 'status'])->name('payment.status');
Route::get('/apply/payment-status-ajax/{tranId}', [\App\Http\Controllers\Public\PaymentCallbackController::class, 'checkStatusAjax'])->name('payment.status.ajax');

// SSLCommerz Callbacks
Route::match(['get', 'post'], '/payment/callback/sslcommerz/success', [\App\Http\Controllers\Public\PaymentCallbackController::class, 'sslcommerzSuccess'])->name('payment.callback.sslcommerz.success');
Route::match(['get', 'post'], '/payment/callback/sslcommerz/fail', [\App\Http\Controllers\Public\PaymentCallbackController::class, 'sslcommerzFail'])->name('payment.callback.sslcommerz.fail');
Route::match(['get', 'post'], '/payment/callback/sslcommerz/cancel', [\App\Http\Controllers\Public\PaymentCallbackController::class, 'sslcommerzCancel'])->name('payment.callback.sslcommerz.cancel');
Route::match(['get', 'post'], '/api/payment/sslcommerz/ipn', [\App\Http\Controllers\Public\PaymentCallbackController::class, 'sslcommerzIpn'])->name('payment.callback.sslcommerz.ipn');

// bKash Callbacks
Route::match(['get', 'post'], '/payment/callback/bkash', [\App\Http\Controllers\Public\PaymentCallbackController::class, 'bkashCallback'])->name('payment.callback.bkash');
Route::match(['get', 'post'], '/api/payment/bkash/callback', [\App\Http\Controllers\Public\PaymentCallbackController::class, 'bkashCallback']);


// ── Public Special Discount / Waiver (SD / PF) Form & Status Tracker ──────
// Primary routes under /sd (Special Discount)
Route::get('/sd/status', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'trackStatus'])->name('poor_fund.status');
Route::post('/sd/status', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'trackStatusLookup'])->name('poor_fund.status.lookup');
Route::get('/sd/track', fn() => redirect()->route('poor_fund.status'));
Route::get('/sd', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'show'])->name('poor_fund.show');
Route::get('/sd/admission', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'showAdmission'])->name('poor_fund.admission');
Route::get('/sd/tuition', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'showTuition'])->name('poor_fund.tuition');
Route::get('/sd/monthly', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'showTuition'])->name('poor_fund.monthly');
Route::get('/sd/both', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'showBoth'])->name('poor_fund.both');
Route::post('/sd', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'store'])->name('poor_fund.store');
Route::get('/sd/success/{applicationNo}', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'success'])->name('poor_fund.success');

// Short Alias routes under /pf (PF)
Route::get('/pf/status', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'trackStatus'])->name('pf.status');
Route::post('/pf/status', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'trackStatusLookup'])->name('pf.status.lookup');
Route::get('/pf/track', fn() => redirect()->route('poor_fund.status'));
Route::get('/pf', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'show'])->name('pf.show');
Route::get('/pf/admission', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'showAdmission'])->name('pf.admission');
Route::get('/pf/tuition', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'showTuition'])->name('pf.tuition');
Route::get('/pf/monthly', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'showTuition'])->name('pf.monthly');
Route::get('/pf/both', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'showBoth'])->name('pf.both');
Route::post('/pf', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'store'])->name('pf.store');
Route::get('/pf/success/{applicationNo}', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'success'])->name('pf.success');

// Backward Compatibility redirects for legacy /poor-fund URLs
Route::get('/poor-fund/status', fn() => redirect()->route('poor_fund.status'));
Route::post('/poor-fund/status', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'trackStatusLookup']);
Route::get('/poor-fund/track', fn() => redirect()->route('poor_fund.status'));
Route::get('/poor-fund', fn() => redirect()->route('poor_fund.show'));
Route::get('/poor-fund/admission', fn() => redirect()->route('poor_fund.admission'));
Route::get('/poor-fund/tuition', fn() => redirect()->route('poor_fund.tuition'));
Route::get('/poor-fund/monthly', fn() => redirect()->route('poor_fund.tuition'));
Route::get('/poor-fund/both', fn() => redirect()->route('poor_fund.both'));
Route::post('/poor-fund', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'store']);
Route::get('/poor-fund/success/{applicationNo}', fn($no) => redirect()->route('poor_fund.success', $no));

Route::get('/api/waiver-lookup', [\App\Http\Controllers\Public\WaiverApplicationController::class, 'lookup'])->name('api.waiver-lookup');

// ── Public Online Support Form & Chat ─────────────────────────────
Route::get('/online-support', [\App\Http\Controllers\Public\OnlineSupportController::class, 'index'])->name('online-support.index');
Route::post('/online-support', [\App\Http\Controllers\Public\OnlineSupportController::class, 'store'])->name('online-support.store');
Route::get('/online-support/search', [\App\Http\Controllers\Public\OnlineSupportController::class, 'searchStatus'])->name('online-support.search');
Route::get('/online-support/chat/{uuid}', [\App\Http\Controllers\Public\OnlineSupportController::class, 'chatView'])->name('online-support.chat');
Route::get('/online-support/messages/{uuid}', [\App\Http\Controllers\Public\OnlineSupportController::class, 'getMessages'])->name('online-support.messages');
Route::post('/online-support/chat/{uuid}/message', [\App\Http\Controllers\Public\OnlineSupportController::class, 'sendMessage'])->name('online-support.chat.send');
Route::post('/online-support/rate/{uuid}', [\App\Http\Controllers\Public\OnlineSupportController::class, 'submitRating'])->name('online-support.rate');

// ── Public Dynamic Survey Forms (No Auth Required) ─────────────────────
Route::get('/surveys/{slug}', [\App\Http\Controllers\Public\SurveyPublicController::class, 'show'])->name('public.survey.show');
Route::post('/surveys/{slug}', [\App\Http\Controllers\Public\SurveyPublicController::class, 'submit'])->name('public.survey.submit');
Route::get('/surveys/{slug}/success', [\App\Http\Controllers\Public\SurveyPublicController::class, 'success'])->name('public.survey.success');

// ── Support Agent Panel Routes (Auth) ─────────────────────────────────
Route::middleware(['auth', 'role:support,support_agent,admin,super_admin'])->prefix('support')->name('support.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Support\SupportAgentController::class, 'dashboard'])->name('dashboard');
    Route::get('/api/queue', [\App\Http\Controllers\Support\SupportAgentController::class, 'queueApi'])->name('api.queue');
    Route::post('/tickets/{uuid}/accept', [\App\Http\Controllers\Support\SupportAgentController::class, 'acceptTicket'])->name('tickets.accept');
    Route::post('/tickets/{uuid}/transfer', [\App\Http\Controllers\Support\SupportAgentController::class, 'transferTicket'])->name('tickets.transfer');
    Route::get('/chat/{uuid}', [\App\Http\Controllers\Support\SupportAgentController::class, 'agentChat'])->name('chat');
    Route::post('/tickets/{uuid}/message', [\App\Http\Controllers\Support\SupportAgentController::class, 'sendMessage'])->name('tickets.message');
    Route::post('/tickets/{uuid}/close', [\App\Http\Controllers\Support\SupportAgentController::class, 'closeTicket'])->name('tickets.close');
    Route::post('/tickets/{uuid}/link-student', [\App\Http\Controllers\Support\SupportAgentController::class, 'linkStudent'])->name('tickets.link-student');

    // Student Lookup API & Direct Login for Support Agents
    Route::get('/api/student-lookup', [\App\Http\Controllers\Support\SupportAgentController::class, 'studentLookupApi'])->name('api.student-lookup');
    Route::get('/students/{student}/impersonate', [\App\Http\Controllers\Admin\StudentController::class, 'impersonate'])->name('students.impersonate');

    // Canned Messages (Quick Replies)
    Route::get('/canned-messages', [\App\Http\Controllers\Support\SupportAgentController::class, 'cannedMessagesIndex'])->name('canned-messages.index');
    Route::post('/canned-messages', [\App\Http\Controllers\Support\SupportAgentController::class, 'cannedMessagesStore'])->name('canned-messages.store');
    Route::put('/canned-messages/{cannedMessage}', [\App\Http\Controllers\Support\SupportAgentController::class, 'cannedMessagesUpdate'])->name('canned-messages.update');
    Route::delete('/canned-messages/{cannedMessage}', [\App\Http\Controllers\Support\SupportAgentController::class, 'cannedMessagesDestroy'])->name('canned-messages.destroy');
});

// ── Hidden Artisan Command Runner Route (/command) ────────────────────
Route::get('/command', [\App\Http\Controllers\Admin\CommandRunnerController::class, 'index'])->middleware(['auth', 'role:admin,super_admin'])->name('command.index');
Route::post('/command', [\App\Http\Controllers\Admin\CommandRunnerController::class, 'run'])->middleware(['auth', 'role:admin,super_admin'])->name('command.run');

// ── FCM Device Token Save Route (Auth Users) ──────────────────────────
Route::post('/user/fcm-token', [\App\Http\Controllers\UserFcmTokenController::class, 'store'])->middleware('auth')->name('user.fcm-token');
