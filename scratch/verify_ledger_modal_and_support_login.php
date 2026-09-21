<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

echo "=== VERIFYING TASK 24: LEDGER MODAL DESIGN FIX & SUPPORT DIRECT STUDENT LOGIN ===\n";

$assertions = 0;

function assertCondition($cond, $msg) {
    global $assertions;
    if (!$cond) {
        echo "❌ FAILED: $msg\n";
        exit(1);
    }
    echo "✅ PASSED: $msg\n";
    $assertions++;
}

// 1. Check modal-overlay CSS in admin layout
$adminLayoutContent = file_get_contents(__DIR__ . '/../resources/views/admin/layouts/app.blade.php');
assertCondition(strpos($adminLayoutContent, '.modal-overlay') !== false, 'Admin layout contains .modal-overlay CSS');
assertCondition(strpos($adminLayoutContent, 'position: fixed !important;') !== false, 'Admin layout contains fixed modal overlay rules');
assertCondition(strpos($adminLayoutContent, '.modal-overlay.open') !== false, 'Admin layout contains .modal-overlay.open rule');

// 2. Check all 5 modals in student_ledger.blade.php
$ledgerContent = file_get_contents(__DIR__ . '/../resources/views/admin/accounts/student_ledger.blade.php');
$expectedModals = ['addCustomFeeModal', 'addExtraFeeModal', 'editInvoiceModal', 'statusModal', 'collectModal'];
foreach ($expectedModals as $modalId) {
    $hasOverlay = preg_match('/<div[^>]*class=["\'][^"\']*modal-overlay[^"\']*["\'][^>]*id=["\']' . $modalId . '["\']/', $ledgerContent);
    assertCondition($hasOverlay === 1, "Ledger contains modal-overlay container for modal ID: $modalId");
}

// 3. Check routes
assertCondition(Route::has('admin.students.impersonate'), 'Route admin.students.impersonate exists');
assertCondition(Route::has('support.students.impersonate'), 'Route support.students.impersonate exists');

// 4. Check Support Chat View has direct login button
$supportChatContent = file_get_contents(__DIR__ . '/../resources/views/support/chat.blade.php');
assertCondition(strpos($supportChatContent, 'support.students.impersonate') !== false, 'Support chat view contains direct student login button');
assertCondition(strpos($supportChatContent, 'শিক্ষার্থী হিসেবে সরাসরি লগইন করুন') !== false || strpos($supportChatContent, 'লগইন') !== false, 'Support chat button has login text/title');

// 5. Check Support Profile Modal has spmImpersonateLink
$modalBladeContent = file_get_contents(__DIR__ . '/../resources/views/support/partials/student_profile_modal.blade.php');
assertCondition(strpos($modalBladeContent, 'id="spmImpersonateLink"') !== false, 'Profile modal contains spmImpersonateLink element');
assertCondition(strpos($modalBladeContent, 'শিক্ষার্থী হিসেবে লগইন') !== false, 'Profile modal button contains Bengali login label');
assertCondition(strpos($modalBladeContent, 's.admin_urls.impersonate') !== false, 'Profile modal JS sets impersonate URL dynamically');

// 6. Test Support Lookup API returns impersonate url
$sampleStudent = Student::first();
if (!$sampleStudent) {
    $sampleStudent = Student::create([
        'student_code' => '26010110001',
        'name' => 'Sample Test Student',
        'gender' => 'MALE',
        'phone' => '01700000000',
        'status' => 'ACTIVE',
    ]);
}

$agentUser = User::where('role', 'support')->orWhere('role', 'support_agent')->orWhere('can_provide_support', true)->first();
if (!$agentUser) {
    $agentUser = User::create([
        'name' => 'Support Agent Test',
        'email' => 'support_test_agent@iom.edu.bd',
        'password' => bcrypt('secret123'),
        'role' => 'support_agent',
        'can_provide_support' => true,
    ]);
}

Auth::login($agentUser);
$agentController = new \App\Http\Controllers\Support\SupportAgentController();
$req = Request::create('/support/api/student-lookup', 'GET', ['code' => $sampleStudent->student_code]);
$res = $agentController->studentLookupApi($req);
$data = $res->getData(true);

assertCondition(isset($data['success']) && $data['success'] === true, 'Student lookup API returned success');
assertCondition(isset($data['student']['admin_urls']['impersonate']), 'Student lookup API includes admin_urls.impersonate');
assertCondition(strpos($data['student']['admin_urls']['impersonate'], '/support/students/' . $sampleStudent->id . '/impersonate') !== false, 'Impersonate URL targets support impersonate route');

// 7. Test StudentController::impersonate for Support Agent
$studentController = new \App\Http\Controllers\Admin\StudentController();
$impersonateResponse = $studentController->impersonate($sampleStudent);

assertCondition($impersonateResponse->isRedirect(), 'Impersonate returns redirect');
assertCondition(session()->has('admin_impersonator_id'), 'Session has admin_impersonator_id');
assertCondition(session('admin_impersonator_id') === $agentUser->id, 'Session has correct impersonator agent user id');
assertCondition(Auth::id() === $sampleStudent->user_id, 'Auth user switched to student user id');

// 8. Test StudentController::leaveImpersonation redirects back to support dashboard for agent
$leaveResponse = $studentController->leaveImpersonation();
assertCondition($leaveResponse->isRedirect(), 'Leave impersonation returns redirect');
assertCondition(Auth::id() === $agentUser->id, 'Auth user returned to agent user');
assertCondition(strpos($leaveResponse->getTargetUrl(), 'support/dashboard') !== false, 'Support agent is redirected back to support/dashboard');

echo "\n🎉 ALL $assertions ASSERTIONS PASSED! EXIT CODE 0\n";
exit(0);
