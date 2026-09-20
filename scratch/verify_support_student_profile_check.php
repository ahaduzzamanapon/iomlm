<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\SupportTicket;
use App\Models\Student;
use App\Models\User;
use App\Http\Controllers\Support\SupportAgentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

echo "=======================================================\n";
echo "VERIFYING TASK 21: Support Panel Student Profile Check\n";
echo "=======================================================\n\n";

$passed = 0;
$failed = 0;

function assertCondition($desc, $cond) {
    global $passed, $failed;
    if ($cond) {
        echo " [PASS] $desc\n";
        $passed++;
    } else {
        echo "![FAIL] $desc\n";
        $failed++;
    }
}

// 1. Authenticate Admin/Support Agent
$admin = User::find(1);
Auth::login($admin);
assertCondition("Admin/Support Agent authenticated", Auth::check() && Auth::user()->isAdmin());

// 2. Test SupportTicket Auto-Resolution
$baniAminStudent = Student::where('email', 'aminmd783@gmail.com')->first();
assertCondition("Student Bani Amin exists in database", $baniAminStudent !== null);

$ticket4 = SupportTicket::find(4);
if ($ticket4) {
    $resolved = $ticket4->resolved_student;
    assertCondition("Ticket #4 resolves student via email/user (Bani Amin)", $resolved && $resolved->id === $baniAminStudent->id);
}

// 3. Test SupportAgentController studentLookupApi with clean code
$controller = new SupportAgentController();
$req1 = Request::create('/support/api/student-lookup', 'GET', ['code' => $baniAminStudent->student_code]);
$res1 = $controller->studentLookupApi($req1);
assertCondition("studentLookupApi returns HTTP 200 for student code {$baniAminStudent->student_code}", $res1->getStatusCode() === 200);

$data1 = json_decode($res1->getContent(), true);
assertCondition("API response success = true", isset($data1['success']) && $data1['success'] === true);
assertCondition("API response contains student name", isset($data1['student']['name']) && $data1['student']['name'] === $baniAminStudent->name);
assertCondition("API response contains student_code without dashes", isset($data1['student']['student_code']) && strpos($data1['student']['student_code'], '-') === false);
assertCondition("API response contains financials structure", isset($data1['student']['financials']['total_billed']) && isset($data1['student']['financials']['total_due']));
assertCondition("API response contains attendance structure", isset($data1['student']['attendance']['total']));
assertCondition("API response contains results structure", isset($data1['student']['results']));

// 4. Test studentLookupApi with dashed query
$dashedCode = 'STD-2026-001';
$reqDashed = Request::create('/support/api/student-lookup', 'GET', ['code' => $dashedCode]);
$resDashed = $controller->studentLookupApi($reqDashed);
$dataDashed = json_decode($resDashed->getContent(), true);
assertCondition("API handles dashed queries (e.g. STD-2026-001) successfully", $resDashed->getStatusCode() === 200 && $dataDashed['success'] === true);

// 5. Test studentLookupApi with invalid code
$reqInvalid = Request::create('/support/api/student-lookup', 'GET', ['code' => 'INVALID_CODE_99999']);
$resInvalid = $controller->studentLookupApi($reqInvalid);
assertCondition("API returns HTTP 404 for non-existent student code", $resInvalid->getStatusCode() === 404);

// 6. Test linkStudent method
$targetTicket = SupportTicket::first();
$linkReq = Request::create("/support/tickets/{$targetTicket->uuid}/link-student", 'POST', [
    'student_code' => $baniAminStudent->student_code,
]);
$linkReq->headers->set('Accept', 'application/json');
$linkRes = $controller->linkStudent($linkReq, $targetTicket->uuid);
assertCondition("linkStudent endpoint returns HTTP 200", $linkRes->getStatusCode() === 200);
$targetTicket->refresh();
assertCondition("Ticket student_id successfully updated to student code", $targetTicket->student_id === $baniAminStudent->student_code);

// 7. Verify chat.blade.php template content
$chatContent = file_get_contents(resource_path('views/support/chat.blade.php'));
assertCondition("chat.blade.php has openStudentProfileModal trigger", strpos($chatContent, 'openStudentProfileModal(') !== false);
assertCondition("chat.blade.php defines window.currentTicketUuid", strpos($chatContent, 'window.currentTicketUuid') !== false);
assertCondition("chat.blade.php has 'প্রোফাইল চেক' or 'প্রোফাইল দেখুন' button", strpos($chatContent, 'প্রোফাইল দেখুন') !== false || strpos($chatContent, 'প্রোফাইল চেক') !== false);
assertCondition("chat.blade.php has student search input for unlinked tickets", strpos($chatContent, 'sidebarStudentSearchInput') !== false);

// 8. Verify support layout app.blade.php content
$layoutContent = file_get_contents(resource_path('views/support/layouts/app.blade.php'));
assertCondition("app.blade.php has topbar student search input", strpos($layoutContent, 'topbarStudentLookupInput') !== false);
assertCondition("app.blade.php has sidebar 'স্টুডেন্ট প্রোফাইল চেক' menu item", strpos($layoutContent, 'স্টুডেন্ট প্রোফাইল চেক') !== false);
assertCondition("app.blade.php includes support.partials.student_profile_modal", strpos($layoutContent, "support.partials.student_profile_modal") !== false);

// 9. Verify support modal partial template
$modalPartialContent = file_get_contents(resource_path('views/support/partials/student_profile_modal.blade.php'));
assertCondition("student_profile_modal.blade.php exists and has modal ID", strpos($modalPartialContent, 'id="supportStudentProfileModal"') !== false);
assertCondition("student_profile_modal.blade.php contains Kalpurush font", strpos($modalPartialContent, "'Kalpurush'") !== false);
assertCondition("student_profile_modal.blade.php has fetchStudentProfile function", strpos($modalPartialContent, 'function fetchStudentProfile') !== false);

// 10. Verify public online_support.blade.php student_code prefill
$onlineSupportContent = file_get_contents(resource_path('views/public/online_support.blade.php'));
assertCondition("online_support.blade.php pre-fills student_code", strpos($onlineSupportContent, 'student_code') !== false);

// 11. Verify dashboard.blade.php student ID modal link
$dashboardContent = file_get_contents(resource_path('views/support/dashboard.blade.php'));
assertCondition("dashboard.blade.php has openStudentProfileModal link", strpos($dashboardContent, 'openStudentProfileModal(') !== false);

// 12. Test View Renderings
try {
    $renderedChat = $controller->agentChat($targetTicket->uuid)->render();
    assertCondition("support.chat view renders with Exit Code 0 without blade exceptions", !empty($renderedChat));
} catch (\Throwable $e) {
    echo "! Rendering support.chat failed: " . $e->getMessage() . "\n";
    $failed++;
}

try {
    $dashReq = Request::create('/support/dashboard', 'GET');
    $renderedDash = $controller->dashboard($dashReq)->render();
    assertCondition("support.dashboard view renders without exceptions", !empty($renderedDash));
} catch (\Throwable $e) {
    echo "! Rendering support.dashboard failed: " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n=======================================================\n";
echo "SUMMARY: Passed: $passed, Failed: $failed\n";
echo "=======================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
