<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RoutineEntry;
use App\Models\RoutineSlot;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Admin\RoutineController as AdminRoutineController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

echo "======================================================\n";
echo "VERIFYING TASK 35: TEACHER ROUTINE & ADMIN COPY/EDIT\n";
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
$copyRoute = Route::getRoutes()->getByName('admin.routine.entries.copy');
assertCheck($copyRoute !== null, "Route 'admin.routine.entries.copy' is registered.");
if ($copyRoute) {
    assertCheck(in_array('POST', $copyRoute->methods()), "Copy route accepts POST method.");
}

$updateRoute = Route::getRoutes()->getByName('admin.routine.entries.update');
assertCheck($updateRoute !== null, "Route 'admin.routine.entries.update' is registered.");

// 2. Setup Test Data (Teacher, Slot, Batch, Subject)
$teacherUser = User::where('role', 'TEACHER')->first();
if (!$teacherUser) {
    $teacherUser = User::create([
        'name'     => 'Test Teacher User',
        'email'    => 'test.teacher.routine@example.com',
        'password' => bcrypt('password123'),
        'role'     => 'TEACHER',
    ]);
}

$teacher = Teacher::where('user_id', $teacherUser->id)->first();
if (!$teacher) {
    $teacher = Teacher::create([
        'user_id' => $teacherUser->id,
        'name'    => 'মাওলানা টেস্ট শিক্ষক',
        'email'   => 'test.teacher.routine@example.com',
        'phone'   => '01811002233',
        'status'  => 'ACTIVE',
    ]);
}

$slot = RoutineSlot::first();
if (!$slot) {
    $slot = RoutineSlot::create([
        'name'       => 'সকাল ১ম স্লট',
        'start_time' => '09:00:00',
        'end_time'   => '10:00:00',
        'sort_order' => 1,
    ]);
}

$batch = Batch::first();
$subject = Subject::first();

// Create source routine entry for Monday
$sourceEntry = RoutineEntry::create([
    'batch_id'    => $batch ? $batch->id : 1,
    'slot_id'     => $slot->id,
    'day_of_week' => 'MON',
    'subject_id'  => $subject ? $subject->id : null,
    'teacher_id'  => $teacher->id,
    'group_tag'   => 'ALL',
    'title'       => 'ফিকহ ও উসূলে ফিকহ ক্লাস',
    'color'       => '#0284c7',
    'is_override' => false,
]);

assertCheck($sourceEntry->exists, "Source routine entry created for Monday (MON).");

// 3. Test Teacher Dashboard Weekly Routine Loading
Auth::login($teacherUser);
$teacherDashboardController = new TeacherDashboardController();
$dashboardView = $teacherDashboardController->index();
$viewData = $dashboardView->getData();

assertCheck(isset($viewData['weeklyRoutine']), "Teacher dashboard view contains 'weeklyRoutine'.");
assertCheck(isset($viewData['daysOfWeek']), "Teacher dashboard view contains 'daysOfWeek'.");

$weeklyRoutine = $viewData['weeklyRoutine'];
assertCheck($weeklyRoutine->has('MON'), "Weekly routine contains Monday (MON) classes for teacher.");
$monClasses = $weeklyRoutine->get('MON');
assertCheck($monClasses->contains('id', $sourceEntry->id), "Monday classes include the created routine entry.");

// 4. Test Admin Routine Entry Copy Action (Duplicate into Wednesday - WED)
$adminUser = User::where('role', 'SUPER_ADMIN')->orWhere('role', 'ADMIN')->first() ?? $teacherUser;
Auth::login($adminUser);

$adminRoutineController = new AdminRoutineController();
$copyReq = Request::create("/admin/routine/entries/{$sourceEntry->id}/copy", 'POST', [
    'target_day'     => 'WED',
    'target_slot_id' => $slot->id,
]);

$copyRes = $adminRoutineController->copyEntry($copyReq, $sourceEntry);
assertCheck($copyRes->isRedirection(), "copyEntry returned redirect response.");

$copiedEntry = RoutineEntry::where('teacher_id', $teacher->id)
    ->where('day_of_week', 'WED')
    ->where('slot_id', $slot->id)
    ->latest()
    ->first();

assertCheck($copiedEntry !== null, "Copied entry exists in database for Wednesday (WED).");
if ($copiedEntry) {
    assertCheck($copiedEntry->batch_id === $sourceEntry->batch_id, "Copied entry preserves batch_id.");
    assertCheck($copiedEntry->subject_id === $sourceEntry->subject_id, "Copied entry preserves subject_id.");
    assertCheck($copiedEntry->teacher_id === $sourceEntry->teacher_id, "Copied entry preserves teacher_id.");
    assertCheck($copiedEntry->day_of_week === 'WED', "Copied entry day is WED.");
    
    // Clean up copied entry
    $copiedEntry->delete();
}

// Clean up source entry
$sourceEntry->delete();

echo "======================================================\n";
echo "SUMMARY: $passed / $total assertions passed.\n";
echo "======================================================\n";

if ($passed === $total) {
    exit(0);
} else {
    exit(1);
}
