<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SupportTicket;
use App\Models\SupportDepartment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

echo "=== STARTING SUPPORT SECTION CORRECTION VERIFICATION ===\n\n";

$mandatoryMessage = 'আপনার সমস্যার সমাধান করে ২৪ ঘণ্টার মধ্যে রিপ্লাই দেওয়া হবে। অনুগ্রহ করে অপেক্ষা করুন।';

// 1. Check support/chat.blade.php
echo "1. Checking support/chat.blade.php...\n";
$agentChatContent = file_get_contents(resource_path('views/support/chat.blade.php'));
if (stripos($agentChatContent, 'Live Chat') !== false) {
    echo "FAILED: support/chat.blade.php still contains 'Live Chat'!\n";
    exit(1);
}
assert(str_contains($agentChatContent, $mandatoryMessage), "support/chat.blade.php missing mandatory instructive message");
echo "  [OK] 'Live Chat' removed and mandatory 24-hour instructive message present in support/chat.blade.php.\n";

// 2. Check public/support_chat.blade.php
echo "2. Checking public/support_chat.blade.php...\n";
$publicChatContent = file_get_contents(resource_path('views/public/support_chat.blade.php'));
if (stripos($publicChatContent, 'Live Chat') !== false) {
    echo "FAILED: public/support_chat.blade.php still contains 'Live Chat'!\n";
    exit(1);
}
assert(str_contains($publicChatContent, $mandatoryMessage), "public/support_chat.blade.php missing mandatory instructive message");
echo "  [OK] 'Live Chat' removed and mandatory 24-hour instructive message present in public/support_chat.blade.php.\n";

// 3. Check OnlineSupportController initial greeting
echo "3. Checking OnlineSupportController initial greeting message...\n";
$controllerContent = file_get_contents(app_path('Http/Controllers/Public/OnlineSupportController.php'));
assert(str_contains($controllerContent, $mandatoryMessage), "OnlineSupportController missing mandatory instructive message in greeting");
if (stripos($controllerContent, 'Live Chat') !== false) {
    echo "FAILED: OnlineSupportController.php still contains 'Live Chat'!\n";
    exit(1);
}
echo "  [OK] OnlineSupportController verified with mandatory 24h message and no 'Live Chat'.\n";

// 4. Check other views for absence of "Live Chat"
echo "4. Checking other relevant views for absence of 'Live Chat'...\n";
$viewsToCheck = [
    'resources/views/support/dashboard.blade.php',
    'resources/views/support/layouts/app.blade.php',
    'resources/views/student/support/index.blade.php',
    'resources/views/admin/support/tickets.blade.php',
    'resources/views/admin/layouts/app.blade.php',
    'resources/views/welcome.blade.php',
    'resources/views/admin/users/create.blade.php',
    'resources/views/admin/users/edit.blade.php',
];

foreach ($viewsToCheck as $relPath) {
    $fullPath = base_path($relPath);
    if (!file_exists($fullPath)) continue;
    $content = file_get_contents($fullPath);
    if (stripos($content, 'Live Chat') !== false) {
        echo "FAILED: {$relPath} contains 'Live Chat'!\n";
        exit(1);
    }
    if (str_contains($content, 'লাইভ চ্যাট')) {
        echo "FAILED: {$relPath} contains 'লাইভ চ্যাট'!\n";
        exit(1);
    }
    echo "  [OK] {$relPath} is free of 'Live Chat'.\n";
}

// 5. Test Blade View Rendering
echo "5. Testing Blade View Rendering for both chat views...\n";
$admin = User::first();
Auth::login($admin);

$ticket = SupportTicket::with('department', 'assignedAgent', 'messages.sender')->first();
if (!$ticket) {
    $dept = SupportDepartment::first() ?? SupportDepartment::create(['name' => 'General', 'code' => 'GEN', 'is_active' => true]);
    $ticket = SupportTicket::create([
        'ticket_no' => 'SUP-TEST-001',
        'department_id' => $dept->id,
        'name' => 'Test User',
        'phone' => '01700000000',
        'email' => 'test@example.com',
        'gender' => 'MALE',
        'subject' => 'Test Subject',
        'problem_details' => 'Test details',
        'status' => 'IN_PROGRESS',
        'assigned_agent_id' => $admin->id,
    ]);
}

$renderedPublic = View::make('public.support_chat', ['ticket' => $ticket])->render();
assert(str_contains($renderedPublic, $mandatoryMessage), "Rendered public chat view missing mandatory message");
assert(stripos($renderedPublic, 'Live Chat') === false, "Rendered public chat contains Live Chat");
echo "  [OK] public.support_chat rendered successfully with mandatory message banner.\n";

$renderedAgent = View::make('support.chat', [
    'ticket' => $ticket,
    'departments' => SupportDepartment::all(),
    'cannedMessages' => collect(),
])->render();
assert(str_contains($renderedAgent, $mandatoryMessage), "Rendered agent chat view missing mandatory message");
assert(stripos($renderedAgent, 'Live Chat') === false, "Rendered agent chat contains Live Chat");
echo "  [OK] support.chat rendered successfully with mandatory message banner.\n";

echo "\n====================================================\n";
echo ">>> ALL SUPPORT SECTION CORRECTION TESTS PASSED! <<<\n";
echo "====================================================\n";
exit(0);
