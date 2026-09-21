<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Notice;
use App\Models\SentNotification;
use App\Models\User;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\BroadcastNotificationController;
use App\Services\DynamicMailService;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

echo "======================================================\n";
echo "VERIFYING TASK 34: NOTICE & NOTIFICATION ENHANCEMENTS\n";
echo "======================================================\n";

$passed = 0;
$total = 0;

function assertCheck($condition, $message) {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo " [PASS] $message\n";
    } else {
        echo " [FAIL] $message\n";
    }
}

// 1. Verify Route Registrations
$noticeUpdateRoute = Route::getRoutes()->getByName('admin.notices.update');
assertCheck($noticeUpdateRoute !== null, "Route 'admin.notices.update' is registered.");
if ($noticeUpdateRoute) {
    assertCheck(in_array('PUT', $noticeUpdateRoute->methods()) || in_array('PATCH', $noticeUpdateRoute->methods()), "Notice update accepts PUT/PATCH.");
}

$notifJsonRoute = Route::getRoutes()->getByName('admin.notifications.show-json');
assertCheck($notifJsonRoute !== null, "Route 'admin.notifications.show-json' is registered.");
if ($notifJsonRoute) {
    assertCheck(in_array('GET', $notifJsonRoute->methods()), "notifications.show-json accepts GET.");
}

// Login as admin
$adminUser = User::where('role', 'SUPER_ADMIN')->orWhere('role', 'ADMIN')->first() ?? User::first();
Auth::login($adminUser);

// 2. Test Notice Editing & Updating
$notice = Notice::create([
    'title'           => 'মূল নোটিশ শিরোনাম (Original Title)',
    'content'         => 'মূল নোটিশের বিবরণ (Original Content)',
    'target_audience' => 'ALL',
    'priority'        => 'NORMAL',
    'created_by'      => $adminUser->id,
    'is_published'    => true,
]);

assertCheck($notice->exists, "Test notice created successfully.");

$noticeController = new NoticeController();
$updateReq = Request::create("/admin/notices/{$notice->id}", 'PUT', [
    'title'           => 'সংশোধিত নোটিশের শিরোনাম (Updated Title)',
    'content'         => 'সংশোধিত নোটিশের বিবরণ (Updated Content)',
    'target_audience' => 'STUDENTS',
    'priority'        => 'URGENT',
]);

$updateRes = $noticeController->update($updateReq, $notice);
assertCheck($updateRes->isRedirection(), "NoticeController::update returned a redirect response.");

$notice->refresh();
assertCheck($notice->title === 'সংশোধিত নোটিশের শিরোনাম (Updated Title)', "Notice title was updated in database.");
assertCheck($notice->content === 'সংশোধিত নোটিশের বিবরণ (Updated Content)', "Notice content was updated in database.");
assertCheck($notice->target_audience === 'STUDENTS', "Notice target_audience updated to STUDENTS.");
assertCheck($notice->priority === 'URGENT', "Notice priority updated to URGENT.");

// Clean up notice
$notice->delete();

// 3. Test Notification Scheduling & showJson API
$futureDate = now()->addDays(3)->format('Y-m-d H:i:s');
$mailServiceMock = new class extends DynamicMailService {
    public function sendHtmlNotification(string $to, string $subject, string $body, ?string $img = null, ?string $url = null): bool {
        return true;
    }
};
$fcmServiceMock = new class extends FirebaseNotificationService {
    public function sendPushNotification(array $tokens, string $title, string $body, ?string $img = null, ?string $url = null): array {
        return ['sent' => count($tokens)];
    }
};

$broadcastController = new BroadcastNotificationController();
$scheduleReq = Request::create('/admin/notifications', 'POST', [
    'title'          => 'শিডিউল নোটিফিকেশন টেস্ট',
    'message'        => 'এই নোটিফিকেশনটি ভবিষ্যতে নির্দিষ্ট সময়ে স্বয়ংক্রিয়ভাবে যাবে।',
    'channel'        => 'PUSH',
    'recipient_type' => 'ALL_STUDENTS',
    'scheduled_at'   => $futureDate,
]);

$scheduleRes = $broadcastController->send($scheduleReq, $mailServiceMock, $fcmServiceMock);
assertCheck($scheduleRes->isRedirection(), "BroadcastNotificationController::send with scheduled_at returned redirect.");

$scheduledNotif = SentNotification::where('title', 'শিডিউল নোটিফিকেশন টেস্ট')->latest()->first();
assertCheck($scheduledNotif !== null, "Scheduled SentNotification record exists in database.");
assertCheck($scheduledNotif->status === 'SCHEDULED', "SentNotification status is 'SCHEDULED'.");
assertCheck(!empty($scheduledNotif->scheduled_at), "SentNotification scheduled_at is populated.");

// 4. Test showJson Details API
$jsonRes = $broadcastController->showJson($scheduledNotif);
$jsonData = json_decode($jsonRes->getContent(), true);

assertCheck(isset($jsonData['id']), "showJson returns 'id'.");
assertCheck($jsonData['title'] === 'শিডিউল নোটিফিকেশন টেস্ট', "showJson returns matching 'title'.");
assertCheck($jsonData['status'] === 'SCHEDULED', "showJson returns matching 'status'.");
assertCheck(isset($jsonData['message']), "showJson returns full 'message'.");
assertCheck(isset($jsonData['sender_name']), "showJson returns 'sender_name'.");

// Clean up test notification
$scheduledNotif->delete();

echo "======================================================\n";
echo "SUMMARY: $passed / $total assertions passed.\n";
echo "======================================================\n";

if ($passed === $total) {
    exit(0);
} else {
    exit(1);
}
