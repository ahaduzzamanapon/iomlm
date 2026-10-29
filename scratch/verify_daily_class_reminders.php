<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Batch;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\SentNotification;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $title, ?string $extra = null) {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$title}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$title}" . ($extra ? " ({$extra})" : "") . "\n";
    }
}

echo "========================================================\n";
echo "  DAILY CLASS REMINDERS VERIFICATION SUITE\n";
echo "========================================================\n\n";

// 1. Verify Command & Schedule
echo "Test Group 1: Command & Console Schedule Registration\n";
$scheduleCommands = [];
foreach (app()->make(\Illuminate\Console\Scheduling\Schedule::class)->events() as $event) {
    $scheduleCommands[] = $event->command;
}
$isScheduled = false;
foreach ($scheduleCommands as $cmd) {
    if (str_contains($cmd, 'classes:send-daily-reminders')) {
        $isScheduled = true;
        break;
    }
}
assertTest($isScheduled, "Command 'classes:send-daily-reminders' is scheduled in console schedule");

// Verify Routes
assertTest(Route::has('admin.classes.send-reminders'), "Route 'admin.classes.send-reminders' exists");
assertTest(Route::has('student.notifications.index'), "Route 'student.notifications.index' exists");
assertTest(Route::has('student.notifications.read'), "Route 'student.notifications.read' exists");
assertTest(Route::has('student.notifications.read-all'), "Route 'student.notifications.read-all' exists");

echo "\nTest Group 2: Bengali Formatting Helpers\n";
$commandInstance = new \App\Console\Commands\SendDailyClassReminders();
$banglaNum = $commandInstance->toBanglaNumber(12345);
assertTest($banglaNum === '১২৩৪৫', "toBanglaNumber converts 12345 to ১২৩৪৫", "Got: {$banglaNum}");

$banglaDate = $commandInstance->formatDateBangla(Carbon::parse('2026-10-29'));
assertTest(str_contains($banglaDate, '২৯ অক্টোবর ২০২৬'), "formatDateBangla produces correct date string", "Got: {$banglaDate}");

$banglaTime = $commandInstance->formatSingleTimeBangla('10:30:00');
assertTest(str_contains($banglaTime, '১০:৩০ টা') && str_contains($banglaTime, 'সকাল'), "formatSingleTimeBangla formats morning time correctly", "Got: {$banglaTime}");

$banglaTimeEve = $commandInstance->formatSingleTimeBangla('19:45:00');
assertTest(str_contains($banglaTimeEve, '০৭:৪৫ টা') && str_contains($banglaTimeEve, 'সন্ধ্যা'), "formatSingleTimeBangla formats evening time correctly", "Got: {$banglaTimeEve}");

// 3. Execution & Notification Pipeline Test
echo "\nTest Group 3: Session Detection, Notification & Email Generation\n";

DB::beginTransaction();

try {
    // Create test fixtures
    $testUser = User::create([
        'name'     => 'পরীক্ষামূলক শিক্ষার্থী',
        'email'    => 'test_student_' . uniqid() . '@example.com',
        'password' => bcrypt('password123'),
        'role'     => 'student',
    ]);

    $testStudent = Student::create([
        'user_id'           => $testUser->id,
        'student_code'      => 'TEST' . rand(1000, 9999),
        'name'              => $testUser->name,
        'email'             => $testUser->email,
        'gender'            => 'MALE',
        'has_course_access' => true,
        'status'            => 'ACTIVE',
    ]);

    $testCourse = Course::first();
    if (!$testCourse) {
        $testCourse = Course::create([
            'name' => 'ইসলামিক স্টাডিজ ডিপ্লোমা',
            'code' => 'ISD_' . rand(100, 999),
            'type' => 'SEMESTER_BASED',
        ]);
    }

    $testBatch = Batch::first();
    if (!$testBatch) {
        $testBatch = Batch::create([
            'name'       => 'ব্যাচ ২০২৬-ক',
            'course_id'  => $testCourse->id,
            'start_date' => '2026-01-01',
            'status'     => 'ACTIVE',
        ]);
    }

    $testEnrollment = Enrollment::create([
        'student_id'  => $testStudent->id,
        'batch_id'    => $testBatch->id,
        'course_id'   => $testCourse->id,
        'status'      => 'ACTIVE',
        'enrolled_at' => now(),
    ]);

    $testSubject = Subject::first();
    if (!$testSubject) {
        $testSubject = Subject::create([
            'name' => 'আরবি ব্যাকরণ ও সাহিত্য (নাহু-সরফ)',
            'code' => 'ARB_' . rand(100, 999),
        ]);
    }

    $testTeacher = Teacher::first();
    if (!$testTeacher) {
        $testTeacher = Teacher::create([
            'name'     => 'মাওলানা মোহাম্মাদুল্লাহ',
            'email'    => 'teacher_' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
            'phone'    => '01711' . rand(100000, 999999),
        ]);
    }

    $todayStr = Carbon::today()->toDateString();

    $testSession = ClassSession::create([
        'batch_id'        => $testBatch->id,
        'subject_id'      => $testSubject->id,
        'teacher_id'      => $testTeacher->id,
        'session_date'    => $todayStr,
        'start_time'      => '10:00:00',
        'end_time'        => '11:30:00',
        'title'           => 'পাঠ্য বই: পৃষ্ঠা ১৫-১৮ আলোচনা',
        'meeting_link'    => 'https://us04web.zoom.us/j/1234567890',
        'status'          => 'SCHEDULED',
        'teacher_present' => false,
        'class_conducted' => false,
    ]);

    assertTest($testSession->exists, "Test class session created for today");

    // Run Command for this specific student
    $exitCode = Artisan::call('classes:send-daily-reminders', [
        '--date'    => $todayStr,
        '--student' => $testStudent->id,
        '--force'   => true,
    ]);
    assertTest($exitCode === 0, "Command classes:send-daily-reminders returned Exit Code 0");

    // Check SentNotification was created
    $notif = SentNotification::where('recipient_type', 'SPECIFIC_STUDENT')
        ->where('recipient_filter_id', $testUser->id)
        ->latest()
        ->first();

    assertTest(!empty($notif), "SentNotification record created for student");
    assertTest(str_contains($notif->title, 'আজকের ক্লাসের রিমাইন্ডার'), "Notification title contains Bengali reminder header");
    assertTest(str_contains($notif->message, 'আরবি ব্যাকরণ ও সাহিত্য') || str_contains($notif->message, $testSubject->name), "Notification message mentions subject name");
    assertTest(str_contains($notif->message, 'সকাল ১০:০০ টা'), "Notification message mentions class time");
    assertTest(str_contains($notif->message, 'মাওলানা মোহাম্মাদুল্লাহ') || str_contains($notif->message, $testTeacher->name), "Notification message mentions teacher name");
    assertTest(str_contains($notif->message, 'লাইভ ক্লাসে জয়েন করার নিয়ম ও পদ্ধতি'), "Notification message contains step-by-step join instructions");
    assertTest(str_contains($notif->message, "Today's Classes"), "Notification message explains Today's Classes page navigation");

    // Verify scopeForUser
    $userNotifs = SentNotification::forUser($testUser)->get();
    assertTest($userNotifs->contains('id', $notif->id), "SentNotification::forUser scope retrieves student notification");

    // Test Duplicate prevention
    $initialCount = SentNotification::where('recipient_type', 'SPECIFIC_STUDENT')->where('recipient_filter_id', $testUser->id)->count();
    Artisan::call('classes:send-daily-reminders', [
        '--date'    => $todayStr,
        '--student' => $testStudent->id,
        // no --force
    ]);
    $afterCount = SentNotification::where('recipient_type', 'SPECIFIC_STUDENT')->where('recipient_filter_id', $testUser->id)->count();
    assertTest($initialCount === $afterCount, "Duplicate reminder skipped when --force is omitted");

    // Test Email Template Rendering
    $emailHtml = view('emails.daily_class_reminder', [
        'studentName'   => $testStudent->name,
        'dateBangla'    => $banglaDate,
        'classes'       => [
            [
                'subject_name' => $testSubject->name,
                'class_title'  => $testSession->title,
                'batch_name'   => $testBatch->name,
                'course_name'  => $testCourse->name,
                'time_range'   => 'সকাল ১০:০০ টা হতে সকাল ১১:৩০ টা',
                'slot_name'    => 'সকাল ১ম স্লট',
                'teacher_name' => $testTeacher->name,
                'join_url'     => route('student.classes.join', $testSession),
                'is_extra'     => false,
            ]
        ],
        'portalUrl'     => url('/student/classes/today'),
        'instituteName' => 'ইসলামিক অনলাইন মাদ্রাসা',
    ])->render();

    assertTest(str_contains($emailHtml, 'Kalpurush'), "Email HTML utilizes 'Kalpurush' font");
    assertTest(str_contains($emailHtml, 'আজকের ক্লাসের রিমাইন্ডার'), "Email HTML has Bengali title");
    assertTest(str_contains($emailHtml, $testSubject->name), "Email HTML contains subject name");
    assertTest(str_contains($emailHtml, 'সকাল ১০:০০ টা'), "Email HTML contains class time");
    assertTest(str_contains($emailHtml, 'মাওলানা মোহাম্মাদুল্লাহ'), "Email HTML contains teacher name");
    assertTest(str_contains($emailHtml, 'লাইভ ক্লাসে জয়েন করার নিয়ম ও পদ্ধতি'), "Email HTML contains join instructions");
    assertTest(str_contains($emailHtml, 'Join Live Class'), "Email HTML specifies Join Live Class button step");

    // Test Student Notification Controller (Index, Read, Read-All)
    auth()->login($testUser);
    $notifController = new \App\Http\Controllers\Student\NotificationController();

    $request = Request::create('/student/notifications', 'GET');
    $request->headers->set('Accept', 'application/json');
    $jsonResponse = $notifController->index($request);
    $data = $jsonResponse->getData(true);

    assertTest(!empty($data['success']) && count($data['notifications']) >= 1, "NotificationController@index returns student notifications via JSON");
    assertTest($data['unread_count'] >= 1, "Unread notifications count is correctly calculated");

    // Mark single as read
    $markReadRes = $notifController->markRead(new Request(), $notif);
    $markReadData = $markReadRes->getData(true);
    assertTest(!empty($markReadData['success']), "NotificationController@markRead succeeds");
    assertTest($testUser->isNotificationRead($notif->id), "User::isNotificationRead returns true after markRead");

    // Admin trigger
    $adminController = new \App\Http\Controllers\Admin\ClassSessionController();
    $adminReq = Request::create('/admin/classes/send-reminders', 'POST', [
        'date' => $todayStr,
        'batch_id' => $testBatch->id,
    ]);
    $adminRes = $adminController->sendDailyReminders($adminReq);
    assertTest($adminRes->isRedirect(), "Admin ClassSessionController@sendDailyReminders executes and redirects with flash message");

} finally {
    // Rollback all test data
    DB::rollBack();
}

echo "\n========================================================\n";
echo "  RESULTS: {$passed} Passed, {$failed} Failed\n";
echo "========================================================\n";

exit($failed === 0 ? 0 : 1);
