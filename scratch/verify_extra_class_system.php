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
use App\Models\RoutineEntry;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

echo "=== STARTING EXTRA CLASS SYSTEM VERIFICATION ===\n\n";

// 1. Verify Database Schema
echo "[Test 1] Checking Database Schema...\n";
$columns = ['is_extra_class', 'title', 'end_time', 'reason'];
foreach ($columns as $col) {
    if (!Schema::hasColumn('class_sessions', $col)) {
        echo "FAIL: Column {$col} missing from class_sessions table!\n";
        exit(1);
    }
}
echo "PASS: All extra class columns exist in class_sessions.\n\n";

// 2. Prepare test data
$batch = Batch::first();
$subject = Subject::first();
$teacher = Teacher::first();
$student = Student::first();

if (!$batch || !$subject || !$teacher) {
    echo "FAIL: Need at least one batch, subject, and teacher in DB to run test.\n";
    exit(1);
}

// 3. Test Admin Extra Class Creation
echo "[Test 2] Creating Admin Extra Class Session...\n";
$adminSession = ClassSession::create([
    'batch_id'         => $batch->id,
    'subject_id'       => $subject->id,
    'teacher_id'       => $teacher->id,
    'session_date'     => now()->addDays(2)->toDateString(),
    'start_time'       => '15:30:00',
    'end_time'         => '17:00:00',
    'title'            => 'টেস্ট এক্সট্রা ক্লাস - ব্যাকরণ সেশন',
    'reason'           => 'সিলেবাস দ্রুত শেষ করার লক্ষ্যে বিশেষ অতিরিক্ত ক্লাস',
    'group_tag'        => 'ALL',
    'meeting_link'     => 'https://meet.google.com/test-extra-admin',
    'notes'            => 'সকল শিক্ষার্থীদের যথাসময়ে উপস্থিত থাকার অনুরোধ করা হচ্ছে।',
    'is_extra_class'   => true,
    'routine_entry_id' => null,
    'status'           => 'SCHEDULED',
    'teacher_present'  => false,
    'class_conducted'  => false,
]);

if (!$adminSession || !$adminSession->id) {
    echo "FAIL: Could not create admin extra class session!\n";
    exit(1);
}

echo "Created Session ID: {$adminSession->id}\n";
echo "is_extra accessor: " . ($adminSession->is_extra ? 'TRUE' : 'FALSE') . "\n";
echo "class_type_label: " . $adminSession->class_type_label . "\n";

if (!$adminSession->is_extra || $adminSession->class_type_label !== 'এক্সট্রা ক্লাস') {
    echo "FAIL: Accessor or label mismatch!\n";
    exit(1);
}
echo "PASS: Admin Extra Class created and accessors verified.\n\n";

// 4. Test Teacher Extra Class Creation
echo "[Test 3] Creating Teacher Extra Class Session...\n";
$teacherSession = ClassSession::create([
    'batch_id'         => $batch->id,
    'subject_id'       => $subject->id,
    'teacher_id'       => $teacher->id,
    'session_date'     => now()->addDays(3)->toDateString(),
    'start_time'       => '10:00:00',
    'end_time'         => '11:30:00',
    'title'            => 'টিচার্স স্পেশাল রিভিশন ক্লাস',
    'reason'           => 'পরীক্ষা পূর্ববর্তী রিভিশন',
    'group_tag'        => 'MALE',
    'meeting_link'     => 'https://meet.google.com/test-teacher-extra',
    'notes'            => 'ভাইদের জন্য বিশেষ অতিরিক্ত ক্লাস',
    'is_extra_class'   => true,
    'routine_entry_id' => null,
    'status'           => 'SCHEDULED',
    'teacher_present'  => false,
    'class_conducted'  => false,
]);

if (!$teacherSession || !$teacherSession->id || !$teacherSession->is_extra) {
    echo "FAIL: Could not create teacher extra class session!\n";
    exit(1);
}
echo "PASS: Teacher Extra Class created successfully.\n\n";

// 5. Test Query Scopes
echo "[Test 4] Testing extra() and regular() Query Scopes...\n";
$extraCount = ClassSession::extra()->whereIn('id', [$adminSession->id, $teacherSession->id])->count();
if ($extraCount !== 2) {
    echo "FAIL: extra() scope failed to find both sessions! Found: {$extraCount}\n";
    exit(1);
}

$regularCount = ClassSession::regular()->whereIn('id', [$adminSession->id, $teacherSession->id])->count();
if ($regularCount !== 0) {
    echo "FAIL: regular() scope included extra sessions! Found: {$regularCount}\n";
    exit(1);
}
echo "PASS: Scopes extra() and regular() work properly.\n\n";

// 6. Test Controller Query Logic
echo "[Test 5] Testing Admin, Teacher, and Student Routine Queries...\n";

// Admin Routine query
$adminUpcoming = ClassSession::with(['subject', 'batch', 'teacher'])
    ->extra()
    ->whereDate('session_date', '>=', today())
    ->whereIn('id', [$adminSession->id, $teacherSession->id])
    ->get();
if ($adminUpcoming->count() < 2) {
    echo "FAIL: Admin routine query did not retrieve both extra sessions!\n";
    exit(1);
}
echo "PASS: Admin routine query retrieved {$adminUpcoming->count()} upcoming extra classes.\n";

// Teacher Routine query
$teacherUpcoming = ClassSession::with(['subject', 'batch'])
    ->where('teacher_id', $teacher->id)
    ->extra()
    ->whereDate('session_date', '>=', today())
    ->whereIn('id', [$adminSession->id, $teacherSession->id])
    ->get();
if ($teacherUpcoming->count() < 2) {
    echo "FAIL: Teacher routine query failed to retrieve teacher's extra classes!\n";
    exit(1);
}
echo "PASS: Teacher routine query retrieved {$teacherUpcoming->count()} upcoming extra classes.\n";

// Student Routine query
$studentUpcoming = ClassSession::with(['subject', 'batch', 'teacher'])
    ->where('batch_id', $batch->id)
    ->extra()
    ->whereDate('session_date', '>=', today())
    ->whereIn('id', [$adminSession->id])
    ->get();
if ($studentUpcoming->isEmpty()) {
    echo "FAIL: Student routine query did not find the extra class for batch {$batch->id}!\n";
    exit(1);
}
echo "PASS: Student routine query retrieved extra classes for batch.\n\n";

// 7. Test Controllers & View Rendering
echo "[Test 6] Testing Controllers and Blade Views End-to-End...\n";

// A. Admin ClassSessionController & RoutineController
$adminUser = User::where('role', 'admin')->first() ?? User::where('email', 'like', '%admin%')->first() ?? User::first();
Auth::login($adminUser);

$adminClassResponse = app(\App\Http\Controllers\Admin\ClassSessionController::class)->index(new \Illuminate\Http\Request());
$rendered = $adminClassResponse->render();
echo "PASS: Admin ClassSessionController::index rendered (" . strlen($rendered) . " bytes).\n";

// B. Admin RoutineController index
$adminRoutineResponse = app(\App\Http\Controllers\Admin\RoutineController::class)->index(new \Illuminate\Http\Request());
$rendered = $adminRoutineResponse->render();
echo "PASS: Admin RoutineController::index rendered (" . strlen($rendered) . " bytes).\n";

// C. Teacher Routine & Classes
$teacherUser = User::where('id', $teacher->user_id)->first() ?? User::first();
Auth::login($teacherUser);
$teacherRoutineResponse = app(\App\Http\Controllers\Teacher\RoutineController::class)->index();
$rendered = $teacherRoutineResponse->render();
echo "PASS: Teacher RoutineController::index rendered (" . strlen($rendered) . " bytes).\n";

$teacherClassResponse = app(\App\Http\Controllers\Teacher\ClassController::class)->index();
$rendered = $teacherClassResponse->render();
echo "PASS: Teacher ClassController::index rendered (" . strlen($rendered) . " bytes).\n";

// D. Student Routine & Classes
$studentUser = User::where('id', $student->user_id)->first() ?? User::first();
Auth::login($studentUser);
$studentRoutineResponse = app(\App\Http\Controllers\Student\RoutineController::class)->index();
$rendered = $studentRoutineResponse->render();
echo "PASS: Student RoutineController::index rendered (" . strlen($rendered) . " bytes).\n";

$studentTodayResponse = app(\App\Http\Controllers\Student\ClassController::class)->today();
$rendered = $studentTodayResponse->render();
echo "PASS: Student ClassController::today rendered (" . strlen($rendered) . " bytes).\n";

$studentIndexResponse = app(\App\Http\Controllers\Student\ClassController::class)->index();
$rendered = $studentIndexResponse->render();
echo "PASS: Student ClassController::index rendered (" . strlen($rendered) . " bytes).\n";

// 8. Clean up test records
$adminSession->delete();
$teacherSession->delete();
echo "\nTest cleanup complete.\n";

echo "\n=== ALL EXTRA CLASS SYSTEM TESTS PASSED SUCCESSFULLY (Exit Code 0) ===\n";
exit(0);
