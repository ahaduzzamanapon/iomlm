<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\Student;
use App\Models\FinalMark;
use App\Models\User;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ResultBookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

echo "=== STARTING VERIFICATION: FACEBOOK LIVE CLASS APPROVAL & 10% ATTENDANCE ===\n";

DB::beginTransaction();

try {
    // 1. Check Schema
    echo "[TEST 1] Verifying database schema on students table...\n";
    $columns = ['facebook_live_approved', 'facebook_live_approved_at', 'facebook_live_approved_by', 'facebook_live_notes'];
    foreach ($columns as $col) {
        if (!Schema::hasColumn('students', $col)) {
            throw new \Exception("Column {$col} not found in students table!");
        }
    }
    echo "  -> Schema columns verified successfully.\n";

    // 2. Check Route
    echo "[TEST 2] Verifying toggle-facebook-live route...\n";
    if (!Route::has('admin.students.toggle-facebook-live')) {
        throw new \Exception("Route admin.students.toggle-facebook-live is not registered!");
    }
    echo "  -> Route verified successfully.\n";

    // 3. Prepare Test Data
    echo "[TEST 3] Preparing test Student, Batch, Subject, and Course...\n";
    $course = Course::first() ?? Course::create([
        'name' => 'Alim Course Test',
        'code' => 'ACT-101',
        'status' => 'ACTIVE',
    ]);

    $batch = Batch::first() ?? Batch::create([
        'course_id' => $course->id,
        'name' => 'Batch 2026 Test',
        'status' => 'ACTIVE',
    ]);

    $subject = Subject::first() ?? Subject::create([
        'name' => 'Quran Studies',
        'code' => 'QS-101',
        'credit' => 3,
    ]);

    $admin = User::first() ?? User::create([
        'name' => 'Admin Test User',
        'email' => 'admin_fb_' . time() . '@iom.edu.bd',
        'password' => bcrypt('password'),
    ]);
    auth()->login($admin);

    $student = Student::create([
        'student_code' => 'TEST' . time(),
        'name' => 'Abdul Karim FB Student',
        'phone' => '01711111111',
        'email' => 'karim_fb_' . time() . '@example.com',
        'status' => 'ACTIVE',
        'facebook_live_approved' => false,
    ]);

    echo "  -> Test Student created (ID: {$student->id}, Approved: " . ($student->facebook_live_approved ? 'YES' : 'NO') . ")\n";

    // 4. Test toggleFacebookLive controller action
    echo "[TEST 4] Testing StudentController::toggleFacebookLive activation...\n";
    $studentController = app(StudentController::class);
    $req = new Request(['facebook_live_notes' => 'Approved via support ticket #12345']);

    $response = $studentController->toggleFacebookLive($req, $student);
    $student->refresh();

    if (!$student->facebook_live_approved) {
        throw new \Exception("Student facebook_live_approved should be true after toggle!");
    }
    if ($student->facebook_live_approved_at === null) {
        throw new \Exception("Student facebook_live_approved_at was not set!");
    }
    if ($student->facebook_live_approved_by !== $admin->id) {
        throw new \Exception("Student facebook_live_approved_by expected {$admin->id}, got: {$student->facebook_live_approved_by}");
    }
    if ($student->facebook_live_notes !== 'Approved via support ticket #12345') {
        throw new \Exception("Student facebook_live_notes not saved!");
    }
    echo "  -> Toggle ON successful. Student is now Facebook Live Approved.\n";

    // 5. Test 10% Attendance Math: 100 -> 10, 90 -> 9, 85 -> 8.5, 70 -> 7
    echo "[TEST 5] Testing attendance mark calculation rules (100 -> 10, 90 -> 9, 85 -> 8.5)...\n";

    // Case 5.1: 100 out of 100
    $fm1 = FinalMark::create([
        'batch_id' => $batch->id,
        'subject_id' => $subject->id,
        'student_id' => $student->id,
        'final_obtained' => 100,
        'final_converted' => 40,
        'midterm_converted' => 30,
        'class_test_converted' => 20, // Converted sum = 90 out of 90 (100%)
    ]);
    $fm1->recalculate();
    $fm1->refresh();

    echo "  -> Case 100% Score: Attendance Converted = {$fm1->attendance_converted} (Expected: 10.0), Total = {$fm1->total_mark}\n";
    if ((float)$fm1->attendance_converted !== 10.0) {
        throw new \Exception("100% score should yield 10.0 attendance mark! Got: {$fm1->attendance_converted}");
    }
    if ((float)$fm1->total_mark !== 100.0) {
        throw new \Exception("Total mark should be 100.0 (90 exams + 10 attendance)! Got: {$fm1->total_mark}");
    }

    // Case 5.2: 90 out of 100
    $fm1->recalculate([
        'final_obtained' => 90,
        'final_converted' => 36, // 90% of 40 = 36
        'midterm_converted' => 27, // 90% of 30 = 27
        'class_test_converted' => 18, // 90% of 20 = 18. Sum = 81 out of 90 (90%)
    ]);
    $fm1->refresh();

    echo "  -> Case 90% Score: Attendance Converted = {$fm1->attendance_converted} (Expected: 9.0), Total = {$fm1->total_mark}\n";
    if ((float)$fm1->attendance_converted !== 9.0) {
        throw new \Exception("90% score should yield 9.0 attendance mark! Got: {$fm1->attendance_converted}");
    }
    if ((float)$fm1->total_mark !== 90.0) {
        throw new \Exception("Total mark should be 90.0 (81 exams + 9 attendance)! Got: {$fm1->total_mark}");
    }

    // Case 5.3: 85 out of 100
    $fm1->class_test_converted = null;
    $fm1->midterm_converted = null;
    $fm1->recalculate([
        'final_obtained' => 85,
        'final_converted' => null,
        'class_test_converted' => null,
        'midterm_converted' => null,
    ]);
    $fm1->refresh();

    echo "  -> Case 85% Score: Attendance Converted = {$fm1->attendance_converted} (Expected: 8.5)\n";
    if ((float)$fm1->attendance_converted !== 8.5) {
        throw new \Exception("85% score should yield 8.5 attendance mark! Got: {$fm1->attendance_converted}");
    }

    // Case 5.4: 70 out of 100
    $fm1->recalculate([
        'final_obtained' => 70,
        'final_converted' => null,
        'class_test_converted' => null,
        'midterm_converted' => null,
    ]);
    $fm1->refresh();

    echo "  -> Case 70% Score: Attendance Converted = {$fm1->attendance_converted} (Expected: 7.0)\n";
    if ((float)$fm1->attendance_converted !== 7.0) {
        throw new \Exception("70% score should yield 7.0 attendance mark! Got: {$fm1->attendance_converted}");
    }

    // 6. Test syncFacebookLiveAttendance helper
    echo "[TEST 6] Testing Student::syncFacebookLiveAttendance()...\n";
    $synced = $student->syncFacebookLiveAttendance();
    echo "  -> Synced {$synced} final mark records successfully.\n";

    // 7. Test toggle OFF
    echo "[TEST 7] Testing StudentController::toggleFacebookLive deactivation...\n";
    $studentController->toggleFacebookLive(new Request(), $student);
    $student->refresh();
    if ($student->facebook_live_approved) {
        throw new \Exception("Student should be deactivated after second toggle!");
    }
    echo "  -> Toggle OFF successful.\n";

    // Re-enable for view testing
    $student->update(['facebook_live_approved' => true, 'facebook_live_approved_at' => now()]);

    // 8. Test Blade View Rendering
    echo "[TEST 8] Testing show.blade.php rendering with Facebook Live elements...\n";
    $student->load(['enrollments.batch.course', 'auditLogs', 'loginHistories', 'finalMarks.subject', 'finalMarks.semester']);
    $view = view('admin.students.show', [
        'student' => $student,
        'feePackages' => collect(),
        'currentCourse' => $course,
        'divisions' => collect(),
        'districts' => collect(),
    ])->render();

    if (!str_contains($view, 'toggle-facebook-live')) {
        throw new \Exception("show.blade.php does not have toggle-facebook-live action!");
    }
    if (!str_contains($view, 'এফবি লাইভ') && !str_contains($view, 'ফেসবুক লাইভ')) {
        throw new \Exception("show.blade.php does not render Facebook Live button or badge!");
    }
    if (!str_contains($view, '১০% অটো') && !str_contains($view, '১০% এটেন্ডেন্স')) {
        throw new \Exception("show.blade.php does not display 10% auto attendance text!");
    }
    echo "  -> show.blade.php rendered Facebook Live badges and button successfully.\n";

    echo "[TEST 9] Testing students index.blade.php rendering with FB Live badge...\n";
    $indexView = view('admin.students.index', [
        'students' => new \Illuminate\Pagination\LengthAwarePaginator([$student], 1, 15),
        'courses' => collect(),
        'batches' => collect(),
        'semesters' => collect(),
        'districts' => collect(),
        'status' => null,
        'totalCount' => 1,
        'activeCount' => 1,
        'pendingCount' => 0,
        'graduatedCount' => 0,
        'search' => null,
        'hasFilters' => false,
    ])->render();

    if (!str_contains($indexView, 'FB Live')) {
        throw new \Exception("index.blade.php does not contain FB Live badge!");
    }
    echo "  -> index.blade.php rendered FB Live badge successfully.\n";

    DB::rollBack();
    echo "\n=== ALL TESTS PASSED SUCCESSFULLY! EXIT CODE 0 ===\n";
    exit(0);

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n[ERROR]: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
