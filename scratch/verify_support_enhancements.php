<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SupportDepartment;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "========================================================================\n";
echo "VERIFICATION: SUPPORT TICKET ENHANCEMENTS & STATUS LIFECYCLE (TASK 17)\n";
echo "========================================================================\n\n";

$passedTests = 0;
$totalTests = 0;

function assertTest($description, $condition) {
    global $passedTests, $totalTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo " [PASS] {$description}\n";
    } else {
        echo " [FAIL] {$description}\n";
        throw new \Exception("Assertion failed: {$description}");
    }
}

DB::beginTransaction();

try {
    // -----------------------------------------------------------------
    // TEST 1: Blade View Inspection
    // -----------------------------------------------------------------
    echo "\n--- TEST 1: Blade View Inspection ---\n";

    $publicChatView = file_get_contents(resource_path('views/public/support_chat.blade.php'));
    $agentChatView  = file_get_contents(resource_path('views/support/chat.blade.php'));
    $studentIndex   = file_get_contents(resource_path('views/student/support/index.blade.php'));
    $agentDashView  = file_get_contents(resource_path('views/support/dashboard.blade.php'));
    $adminTickets   = file_get_contents(resource_path('views/admin/support/tickets.blade.php'));
    $adminShow      = file_get_contents(resource_path('views/admin/support/show.blade.php'));

    // Check attachment icons
    assertTest("Public chat contains fa-paperclip icon in attachment label", str_contains($publicChatView, 'fa-paperclip'));
    assertTest("Agent chat contains fa-paperclip icon in attachment label", str_contains($agentChatView, 'fa-paperclip'));
    assertTest("Public chat non-image file preview has PDF/file icon", str_contains($publicChatView, 'fa-file-pdf'));

    // Check rating & feedback elements
    assertTest("Public chat has interactive star items with setRating function", str_contains($publicChatView, 'star-item') && str_contains($publicChatView, 'setRating('));
    assertTest("Public chat has hidden rating score input with default value 5", str_contains($publicChatView, 'id="ratingScoreInput"') && str_contains($publicChatView, 'value="5"'));
    assertTest("Public chat has submit button for rating and feedback", str_contains($publicChatView, 'রেটিং ও ফিডব্যাক জমা দিন'));
    assertTest("Public chat renders FontAwesome stars when rating exists", str_contains($publicChatView, 'fa-star'));

    // Check SOLVED status display
    assertTest("Public chat displays SOLVED status badge", str_contains($publicChatView, '✓ সমস্যার সমাধান প্রদান করা হয়েছে (Solved)'));
    assertTest("Public chat JS updateBadgeAndInput handles SOLVED status", str_contains($publicChatView, "data.status === 'SOLVED'"));
    assertTest("Student support index displays SOLVED badge", str_contains($studentIndex, '✓ সলভড (Solved)'));
    assertTest("Support agent dashboard displays SOLVED tab and badge", str_contains($agentDashView, "status' => 'SOLVED'") && str_contains($agentDashView, '✓ Solved'));
    assertTest("Admin support tickets view displays SOLVED in stats, filter and table", str_contains($adminTickets, 'value="SOLVED"') && str_contains($adminTickets, 'Solved'));
    assertTest("Admin support show view displays SOLVED badge", str_contains($adminShow, '✓ Solved'));

    // -----------------------------------------------------------------
    // TEST 2: Database Schema & Lifecycle Setup
    // -----------------------------------------------------------------
    echo "\n--- TEST 2: Support Ticket Lifecycle Setup ---\n";

    $dept = SupportDepartment::first();
    if (!$dept) {
        $dept = SupportDepartment::create(['name' => 'Academic Support', 'is_active' => true]);
    }

    $agent = User::firstOrCreate(
        ['email' => 'agent_test_' . Str::random(5) . '@iom.test'],
        ['name' => 'Test Agent', 'password' => bcrypt('password'), 'role' => 'support_agent']
    );

    $studentUser = User::firstOrCreate(
        ['email' => 'student_test_' . Str::random(5) . '@iom.test'],
        ['name' => 'Test Student', 'password' => bcrypt('password'), 'role' => 'student']
    );

    $ticket = SupportTicket::create([
        'ticket_no'         => 'TCK-TEST-' . rand(10000, 99999),
        'uuid'              => (string) Str::uuid(),
        'department_id'     => $dept->id,
        'user_id'           => $studentUser->id,
        'name'              => 'Test Student',
        'email'             => $studentUser->email,
        'phone'             => '01711000000',
        'gender'            => 'MALE',
        'student_id'        => 'STDTEST001',
        'subject'           => 'Exam routine issue',
        'problem_details'   => 'Cannot see tomorrow exam routine',
        'status'            => 'PENDING',
        'assigned_agent_id' => $agent->id,
    ]);

    assertTest("Initial ticket status is PENDING", $ticket->status === 'PENDING');

    // -----------------------------------------------------------------
    // TEST 3: Admin / Agent Replies -> Ticket Becomes SOLVED
    // -----------------------------------------------------------------
    echo "\n--- TEST 3: Agent / Admin Reply Sets Status to SOLVED ---\n";

    // Simulate Agent sending message
    $agentController = new \App\Http\Controllers\Support\SupportAgentController();
    \Illuminate\Support\Facades\Auth::login($agent);

    $agentReq = new Request([
        'message' => 'আপনার রুটিন আপডেট করা হয়েছে। চেক করে দেখুন।'
    ]);

    $response = $agentController->sendMessage($agentReq, $ticket->uuid);
    $ticket->refresh();

    assertTest("Agent message returned success JSON", $response->getStatusCode() === 200 && ($response->getData()->success ?? false) === true);
    assertTest("After agent reply, ticket status is now SOLVED (no longer PENDING)", $ticket->status === 'SOLVED');

    // -----------------------------------------------------------------
    // TEST 4: Student Replies Again -> Ticket Returns to PENDING
    // -----------------------------------------------------------------
    echo "\n--- TEST 4: Student Reply Reverts Status to PENDING ---\n";

    $onlineController = new \App\Http\Controllers\Public\OnlineSupportController();
    \Illuminate\Support\Facades\Auth::login($studentUser);

    $studentReq = new Request([
        'message' => 'ধন্যবাদ, তবে পিডিএফ ডাউনলোড হচ্ছে না।'
    ]);

    $response2 = $onlineController->sendMessage($studentReq, $ticket->uuid);
    $ticket->refresh();

    assertTest("Student message returned success JSON", $response2->getStatusCode() === 200 && ($response2->getData()->success ?? false) === true);
    assertTest("After student reply, ticket status is now PENDING again", $ticket->status === 'PENDING');

    // -----------------------------------------------------------------
    // TEST 5: Agent Closes Ticket -> Student Can No Longer Reply
    // -----------------------------------------------------------------
    echo "\n--- TEST 5: Closing Ticket Rejects Subsequent Student Replies ---\n";

    \Illuminate\Support\Facades\Auth::login($agent);
    $agentController->closeTicket($ticket->uuid);
    $ticket->refresh();

    assertTest("Ticket status is now CLOSED", $ticket->status === 'CLOSED');
    assertTest("Ticket closed_at timestamp is set", !is_null($ticket->closed_at));

    // Student attempts to reply
    \Illuminate\Support\Facades\Auth::login($studentUser);
    $blockedReq = new Request([
        'message' => 'আমি কি এখনো প্রশ্ন করতে পারি?'
    ]);

    $responseBlocked = $onlineController->sendMessage($blockedReq, $ticket->uuid);
    assertTest("Student reply on CLOSED ticket is rejected with 422 HTTP status", $responseBlocked->getStatusCode() === 422);

    $respData = json_decode($responseBlocked->getContent(), true);
    assertTest("Rejection error message informs user that ticket is closed", str_contains($respData['error'] ?? '', 'বন্ধ'));

    // -----------------------------------------------------------------
    // TEST 6: Student Submits Feedback & Rating on Closed Ticket
    // -----------------------------------------------------------------
    echo "\n--- TEST 6: Student Rating & Feedback Submission on Closed Ticket ---\n";

    $rateReq = new Request([
        'rating'   => 5,
        'feedback' => 'অনেক দ্রুত ও চমৎকার সমাধান পেয়েছি, জাযাকাল্লাহু খাইরান।'
    ]);

    $rateResp = $onlineController->submitRating($rateReq, $ticket->uuid);
    $ticket->refresh();

    assertTest("Rating is successfully updated to 5", $ticket->rating === 5);
    assertTest("Feedback is successfully saved", str_contains($ticket->feedback, 'চমৎকার সমাধান'));

    // Also test optional rating defaults to 5 if empty
    $rateReq2 = new Request([
        'rating'   => null,
        'feedback' => 'আলহামদুলিল্লাহ'
    ]);
    $onlineController->submitRating($rateReq2, $ticket->uuid);
    $ticket->refresh();
    assertTest("Empty rating defaults smoothly to 5 stars without failing", $ticket->rating === 5);

    echo "\n========================================================================\n";
    echo "ALL TESTS PASSED: {$passedTests} / {$totalTests} (Exit Code 0)\n";
    echo "========================================================================\n";

} finally {
    DB::rollBack();
}
