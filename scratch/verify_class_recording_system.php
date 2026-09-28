<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Batch;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\ClassSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

echo "=== STARTING CLASS RECORDING SYSTEM VERIFICATION ===\n\n";

// 1. Check Database Columns
echo "[Test 1] Verifying Database Columns...\n";
$requiredColumns = ['recording_url', 'recording_file', 'recording_embed', 'recorded_videos', 'has_recording'];
foreach ($requiredColumns as $col) {
    if (!Schema::hasColumn('class_sessions', $col)) {
        echo "FAIL: Column {$col} missing in class_sessions table!\n";
        exit(1);
    }
}
echo "PASS: All recording columns exist in class_sessions.\n\n";

// 2. Prepare Entities
$batch = Batch::first();
$subject = Subject::first();
$teacher = Teacher::first();
$student = Student::first();
$adminUser = User::where('role', 'admin')->first() ?? User::first();
$teacherUser = User::where('id', $teacher?->user_id)->first() ?? User::first();
$studentUser = User::where('id', $student?->user_id)->first() ?? User::first();

if (!$batch || !$subject || !$teacher || !$student) {
    echo "FAIL: Required seed entities (batch, subject, teacher, student) not found!\n";
    exit(1);
}

// Ensure student is enrolled in batch
$enrollment = Enrollment::firstOrCreate(
    ['student_id' => $student->id, 'batch_id' => $batch->id],
    ['status' => 'ACTIVE', 'group_tag' => 'ALL']
);

// 3. Create Class Session for Testing
echo "[Test 2] Creating Class Session...\n";
$session = ClassSession::create([
    'batch_id'        => $batch->id,
    'subject_id'      => $subject->id,
    'teacher_id'      => $teacher->id,
    'session_date'    => now()->toDateString(),
    'start_time'      => '14:00:00',
    'status'          => 'SCHEDULED',
    'group_tag'       => 'ALL',
    'has_recording'   => false,
]);

// 4. Test Admin Upload Recording
echo "[Test 3] Testing Admin updateRecording()...\n";
Auth::login($adminUser);
$adminReq = Request::create(route('admin.classes.recording', $session), 'POST', [
    'videos' => [
        [
            'title' => 'লেকচার ১: প্রারম্ভিক আলোচনা',
            'url'   => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ],
        [
            'title' => 'লেকচার ২: প্রশ্নোত্তর পর্ব',
            'url'   => 'https://vimeo.com/76979871',
        ],
    ]
]);

$adminController = app(\App\Http\Controllers\Admin\ClassSessionController::class);
$response = $adminController->updateRecording($adminReq, $session);

$session->refresh();

if (!$session->has_recording) {
    echo "FAIL: session has_recording is not true after admin update!\n";
    exit(1);
}
if ($session->video_count !== 2) {
    echo "FAIL: Expected 2 videos, got {$session->video_count}!\n";
    exit(1);
}

$videos = $session->videos;
if ($videos[0]['type'] !== 'youtube' || empty($videos[0]['embed_url'])) {
    echo "FAIL: Video 1 should be youtube type with embed_url!\n";
    exit(1);
}
if ($videos[1]['type'] !== 'vimeo' || empty($videos[1]['embed_url'])) {
    echo "FAIL: Video 2 should be vimeo type with embed_url!\n";
    exit(1);
}
echo "PASS: Admin recorded videos saved, normalized, and accessors verified.\n\n";

// 5. Test Teacher Upload Recording
echo "[Test 4] Testing Teacher updateRecording()...\n";
Auth::login($teacherUser);
$teacherReq = Request::create(route('teacher.classes.recording', $session), 'POST', [
    'videos' => [
        [
            'title' => 'শিক্ষকের গুগল ড্রাইভ ক্লাস রেকর্ড',
            'url'   => 'https://drive.google.com/file/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs/view?usp=sharing',
        ],
    ]
]);

$teacherController = app(\App\Http\Controllers\Teacher\ClassController::class);
$teacherResponse = $teacherController->updateRecording($teacherReq, $session);

$session->refresh();
$videos = $session->videos;
if (empty($videos) || $videos[0]['type'] !== 'drive' || !str_contains($videos[0]['embed_url'], 'preview')) {
    echo "FAIL: Teacher drive recording not formatted correctly! Video: " . json_encode($videos) . "\n";
    exit(1);
}
echo "PASS: Teacher recording updated successfully with Drive preview formatting.\n\n";

// 6. Test Query Scopes
echo "[Test 5] Testing scopeWithRecording()...\n";
$recordedSessions = ClassSession::withRecording()->where('id', $session->id)->count();
if ($recordedSessions !== 1) {
    echo "FAIL: scopeWithRecording failed to retrieve session!\n";
    exit(1);
}
echo "PASS: scopeWithRecording() correctly finds recorded session.\n\n";

// 7. Test Student ClassRecordingController & Views
echo "[Test 6] Testing Student ClassRecordingController & Views...\n";
Auth::login($studentUser);

$studentRecController = app(\App\Http\Controllers\Student\ClassRecordingController::class);

// Index
$indexReq = new Request();
$indexView = $studentRecController->index($indexReq);
$renderedIndex = $indexView->render();
if (!str_contains($renderedIndex, 'ক্লাস রেকর্ড (Class Records)') || !str_contains($renderedIndex, $subject->name)) {
    echo "FAIL: Student recordings index does not contain expected subject and title!\n";
    exit(1);
}
echo "PASS: Student ClassRecordingController::index rendered (" . strlen($renderedIndex) . " bytes).\n";

// Show Video Player
$showReq = new Request();
$showView = $studentRecController->show($showReq, $session);
$renderedShow = $showView->render();
if (!str_contains($renderedShow, 'drive.google.com/file/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs/preview')) {
    echo "FAIL: Video player does not contain expected Drive preview iframe embed URL!\n";
    exit(1);
}
echo "PASS: Student ClassRecordingController::show rendered video player (" . strlen($renderedShow) . " bytes).\n";

// 8. Test Student Classes Show with recording prompt
echo "[Test 7] Testing Student Classes Show View...\n";
$studentClassController = app(\App\Http\Controllers\Student\ClassController::class);
$classShowView = $studentClassController->show($session);
$renderedClassShow = $classShowView->render();
if (!str_contains($renderedClassShow, 'এই ক্লাসের রেকর্ডিং ভিডিও উপলব্ধ আছে')) {
    echo "FAIL: Student classes show does not display recording availability prompt!\n";
    exit(1);
}
echo "PASS: Student classes show displays recording watch prompt.\n\n";

// 9. Verify Student Sidebar
echo "[Test 8] Checking Student Sidebar Navigation...\n";
$sidebarHtml = view('student.layouts.app', ['slot' => 'test'])->render();
if (str_contains($sidebarHtml, 'আমার বিষয়সমূহ (Subjects)')) {
    echo "FAIL: 'আমার বিষয়সমূহ (Subjects)' was NOT removed from student sidebar!\n";
    exit(1);
}
if (!str_contains($sidebarHtml, 'ক্লাস রেকর্ড (Class Records)')) {
    echo "FAIL: 'ক্লাস রেকর্ড (Class Records)' missing from student sidebar!\n";
    exit(1);
}
echo "PASS: Student sidebar verified - 'আমার বিষয়' removed, 'ক্লাস রেকর্ড' added.\n\n";

// Clean up
$session->delete();
echo "Test cleanup complete.\n";

echo "=== ALL CLASS RECORDING SYSTEM TESTS PASSED SUCCESSFULLY (Exit Code 0) ===\n";
exit(0);
