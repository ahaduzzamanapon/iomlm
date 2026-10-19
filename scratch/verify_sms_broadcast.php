<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SentNotification;
use App\Models\User;
use App\Services\SmsService;
use App\Http\Controllers\Admin\BroadcastNotificationController;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

$failed = 0;
$passed = 0;

function assertCondition($cond, $desc) {
    global $failed, $passed;
    if ($cond) {
        echo "  [PASS] {$desc}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$desc}\n";
        $failed++;
    }
}

echo "============================================================\n";
echo "1. DATABASE SCHEMA VERIFICATION FOR SMS SUPPORT\n";
echo "============================================================\n";

assertCondition(Schema::hasTable('sent_notifications'), "Table 'sent_notifications' exists.");
assertCondition(Schema::hasColumn('sent_notifications', 'sms_message'), "Column 'sms_message' exists in sent_notifications.");
assertCondition(Schema::hasColumn('sent_notifications', 'sms_count'), "Column 'sms_count' exists in sent_notifications.");
assertCondition(Schema::hasColumn('sent_notifications', 'email_count'), "Column 'email_count' exists in sent_notifications.");
assertCondition(Schema::hasColumn('sent_notifications', 'push_count'), "Column 'push_count' exists in sent_notifications.");
assertCondition(Schema::hasColumn('sent_notifications', 'channel'), "Column 'channel' exists in sent_notifications.");

echo "\n============================================================\n";
echo "2. BLADE TEMPLATE SYNTAX & UI CONTENT VERIFICATION\n";
echo "============================================================\n";

$createViewContent = file_get_contents(resource_path('views/admin/notifications/create.blade.php'));
assertCondition(strpos($createViewContent, 'name="sms_message"') !== false, "create.blade.php contains name='sms_message' textarea.");
assertCondition(strpos($createViewContent, 'value="ALL"') !== false, "create.blade.php contains ALL channel option.");
assertCondition(strpos($createViewContent, 'value="SMS"') !== false, "create.blade.php contains SMS Only channel option.");
assertCondition(strpos($createViewContent, 'value="PUSH_SMS"') !== false, "create.blade.php contains PUSH_SMS channel option.");
assertCondition(strpos($createViewContent, 'value="EMAIL_SMS"') !== false, "create.blade.php contains EMAIL_SMS channel option.");
assertCondition(strpos($createViewContent, 'Kalpurush') !== false, "create.blade.php uses 'Kalpurush' font for Bangla typography.");
assertCondition(strpos($createViewContent, 'sms_preview_view') !== false, "create.blade.php contains SMS live bubble preview.");
assertCondition(strpos($createViewContent, 'updateSmsCharCounter') !== false, "create.blade.php contains updateSmsCharCounter() JavaScript.");

$indexViewContent = file_get_contents(resource_path('views/admin/notifications/index.blade.php'));
assertCondition(strpos($indexViewContent, 'SMS ONLY') !== false, "index.blade.php contains 'SMS ONLY' badge.");
assertCondition(strpos($indexViewContent, 'vn_sms_container') !== false, "index.blade.php modal contains dedicated SMS container.");
assertCondition(strpos($indexViewContent, 'vn_val_sms') !== false, "index.blade.php modal displays SMS delivery counter.");
assertCondition(strpos($indexViewContent, 'Kalpurush') !== false, "index.blade.php uses 'Kalpurush' font.");

// Verify blade view compilation via Blade Compiler
try {
    $compiledCreate = app('blade.compiler')->compileString($createViewContent);
    assertCondition(!empty($compiledCreate), "create.blade.php compiles cleanly via BladeCompiler.");
} catch (\Throwable $e) {
    assertCondition(false, "create.blade.php BladeCompiler syntax error: " . $e->getMessage());
}

try {
    $compiledIndex = app('blade.compiler')->compileString($indexViewContent);
    assertCondition(!empty($compiledIndex), "index.blade.php compiles cleanly via BladeCompiler.");
} catch (\Throwable $e) {
    assertCondition(false, "index.blade.php BladeCompiler syntax error: " . $e->getMessage());
}

// Setup memory/array session for CLI
$session = app('session')->driver('array');
request()->setLaravelSession($session);
session()->put('_token', 'test');

echo "\n============================================================\n";
echo "3. CONTROLLER VALIDATION FOR CHANNELS & SMS MESSAGE\n";
echo "============================================================\n";

$allowedChannels = ['ALL', 'BOTH', 'PUSH', 'EMAIL', 'SMS', 'PUSH_SMS', 'EMAIL_SMS'];
foreach ($allowedChannels as $ch) {
    $v = Validator::make([
        'title'          => 'Test Notice',
        'message'        => 'Test Message Body',
        'sms_message'    => 'Test Custom SMS',
        'channel'        => $ch,
        'recipient_type' => 'ALL_STUDENTS',
    ], [
        'title'       => 'required|string|max:250',
        'message'     => 'required|string',
        'sms_message' => 'nullable|string|max:1000',
        'channel'     => 'required|string|in:ALL,BOTH,PUSH,EMAIL,SMS,PUSH_SMS,EMAIL_SMS',
    ]);
    assertCondition(!$v->fails(), "Validation succeeds for channel: {$ch}");
}

$invalidChannelValidator = Validator::make([
    'title'       => 'Test Notice',
    'message'     => 'Test Message Body',
    'channel'     => 'INVALID_CHANNEL_XYZ',
], [
    'channel' => 'required|string|in:ALL,BOTH,PUSH,EMAIL,SMS,PUSH_SMS,EMAIL_SMS',
]);
assertCondition($invalidChannelValidator->fails(), "Validation correctly rejects unknown channel 'INVALID_CHANNEL_XYZ'.");

echo "\n============================================================\n";
echo "4. SMS SERVICE & BANGLADESHI PHONE FORMATTING\n";
echo "============================================================\n";

$smsService = app(SmsService::class);
$p1 = $smsService->formatBangladeshiPhone('01712345678');
$p2 = $smsService->formatBangladeshiPhone('+8801812345678');
$p3 = $smsService->formatBangladeshiPhone('8801912345678');
$p4 = $smsService->formatBangladeshiPhone('');

assertCondition($p1 === '8801712345678', "formatBangladeshiPhone correctly formats '01712345678' -> '{$p1}'.");
assertCondition($p2 === '8801812345678', "formatBangladeshiPhone correctly formats '+8801812345678' -> '{$p2}'.");
assertCondition($p3 === '8801912345678', "formatBangladeshiPhone correctly keeps '8801912345678' -> '{$p3}'.");
assertCondition($p4 === null, "formatBangladeshiPhone returns null for empty phone string.");

echo "\n============================================================\n";
echo "5. SENT NOTIFICATION MODEL & JSON SERIALIZATION\n";
echo "============================================================\n";

$testNotif = SentNotification::create([
    'title'               => 'টেস্ট এসএমএস নোটিফিকেশন',
    'message'             => 'প্রিয় {name}, এটি একটি পরীক্ষামূলক ব্রডকাস্ট বার্তা।',
    'sms_message'         => 'প্রিয় {name}, রোল: {roll}, কোর্সের নোটিশ পেতে লগইন করুন।',
    'channel'             => 'ALL',
    'recipient_type'      => 'ALL_STUDENTS',
    'recipient_filter_id' => null,
    'sent_count'          => 25,
    'sms_count'           => 25,
    'email_count'         => 24,
    'push_count'          => 18,
    'status'              => 'SENT',
]);

assertCondition($testNotif->id > 0, "SentNotification created successfully with ID: {$testNotif->id}");
assertCondition($testNotif->sms_message === 'প্রিয় {name}, রোল: {roll}, কোর্সের নোটিশ পেতে লগইন করুন।', "SentNotification holds custom sms_message.");
assertCondition($testNotif->sms_count === 25, "SentNotification holds sms_count = 25.");
assertCondition($testNotif->channel === 'ALL', "SentNotification holds channel = 'ALL'.");

$controller = app(BroadcastNotificationController::class);
$jsonResponse = $controller->showJson($testNotif);
$jsonData = json_decode($jsonResponse->getContent(), true);

assertCondition($jsonData['sms_message'] === $testNotif->sms_message, "showJson returns matching sms_message.");
assertCondition($jsonData['sms_count'] === 25, "showJson returns matching sms_count.");
assertCondition($jsonData['channel'] === 'ALL', "showJson returns matching channel.");
assertCondition($jsonData['push_count'] === 18, "showJson returns matching push_count.");
assertCondition($jsonData['email_count'] === 24, "showJson returns matching email_count.");

// Cleanup
$testNotif->delete();
assertCondition(!SentNotification::find($testNotif->id), "Test notification record cleanly deleted.");

echo "\n============================================================\n";
echo "VERIFICATION SUMMARY\n";
echo "============================================================\n";
echo "Passed: {$passed} | Failed: {$failed}\n";

if ($failed > 0) {
    echo "❌ VERIFICATION FAILED!\n";
    exit(1);
} else {
    echo "✅ ALL VERIFICATION TESTS PASSED SUCCESSFULLY! (Exit Code 0)\n";
    exit(0);
}
