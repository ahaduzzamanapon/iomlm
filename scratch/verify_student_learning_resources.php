<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\SubjectModule;
use App\Models\CourseSubjectMap;
use App\Models\LearningResource;
use App\Models\Student;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

echo "--- VERIFYING TASK 68: STUDENT LEARNING RESOURCES INTEGRATION ---\n\n";

$uniq = Str::random(5);
$course = Course::create(['name' => "Resource Course {$uniq}", 'code' => "RC{$uniq}", 'is_active' => true]);
$batch = Batch::create(['course_id' => $course->id, 'name' => "Resource Batch {$uniq}", 'status' => 'ACTIVE', 'start_date' => now()->toDateString()]);
$subject = Subject::create(['name' => "Fiqh of Transactions {$uniq}", 'code' => "FT{$uniq}", 'is_active' => true]);
CourseSubjectMap::create(['course_id' => $course->id, 'subject_id' => $subject->id]);

// Create SubjectModule with file attachment, drive link, and recorded videos
$module = SubjectModule::create([
    'subject_id'      => $subject->id,
    'title'           => "Module 1: Basic Principles {$uniq}",
    'sequence_no'     => 1,
    'description'     => 'Introduction and fundamental rules of exchange',
    'file_path'       => 'modules/attachments/sample_' . $uniq . '.pdf',
    'drive_link'      => 'https://drive.google.com/file/d/sample_' . $uniq,
    'recorded_videos' => [
        [
            'id'    => 'vid_1',
            'title' => "Class 1 Lecture Video {$uniq}",
            'url'   => 'https://youtube.com/watch?v=sample_' . $uniq,
        ]
    ],
    'is_hidden'       => false,
    'is_active'       => true,
]);

// Also create child LearningResource
$learningRes = LearningResource::create([
    'module_id' => $module->id,
    'title'     => "Supplementary Hadith Notes {$uniq}",
    'type'      => 'NOTES',
    'url'       => 'https://example.com/hadith_notes_' . $uniq,
]);

// Create Enrolled Student
$user = User::create(['name' => "Res Student {$uniq}", 'email' => "res_{$uniq}@example.com", 'password' => bcrypt('123'), 'role' => 'student']);
$student = Student::create(['user_id' => $user->id, 'name' => "Res Student {$uniq}", 'student_code' => "ST-RES-{$uniq}", 'phone' => '01733333333', 'status' => 'ACTIVE']);
Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'batch_id' => $batch->id, 'status' => 'ACTIVE', 'enrolled_at' => now()]);

echo "1. Fixtures created:\n";
echo "   Course: {$course->name}, Subject: {$subject->name}, Module: {$module->title}\n\n";

// 2. Test Student/LearningResourceController::index
Auth::login($user);
$resController = app(\App\Http\Controllers\Student\LearningResourceController::class);
$request = \Illuminate\Http\Request::create('/student/resources', 'GET');
$response = $resController->index($request);
$resources = $response->getData()['resources'];

echo "2. Checking Student Learning Resources Aggregation:\n";
echo "   Total resources aggregated: " . $resources->count() . "\n";

$hasPdf = $resources->contains(fn($r) => strpos($r['title'], $module->title) !== false && $r['type'] === 'PDF');
$hasDrive = $resources->contains(fn($r) => strpos($r['title'], 'Google Drive') !== false && $r['type'] === 'DRIVE');
$hasVideo = $resources->contains(fn($r) => strpos($r['title'], "Class 1 Lecture Video {$uniq}") !== false && $r['type'] === 'VIDEO');
$hasNotes = $resources->contains(fn($r) => strpos($r['title'], "Supplementary Hadith Notes {$uniq}") !== false && $r['type'] === 'NOTES');

if (!$hasPdf) {
    echo "   [FAIL] Missing PDF attachment from SubjectModule!\n";
    exit(1);
}
echo "   [PASS] PDF attachment included!\n";

if (!$hasDrive) {
    echo "   [FAIL] Missing Google Drive link from SubjectModule!\n";
    exit(1);
}
echo "   [PASS] Google Drive link included!\n";

if (!$hasVideo) {
    echo "   [FAIL] Missing recorded video from SubjectModule!\n";
    exit(1);
}
echo "   [PASS] Recorded video included!\n";

if (!$hasNotes) {
    echo "   [FAIL] Missing child LearningResource notes!\n";
    exit(1);
}
echo "   [PASS] Child LearningResource notes included!\n";

// Check rendered HTML
$renderedResHtml = $response->render();
if (strpos($renderedResHtml, 'লার্নিং রিসোর্স ও স্টাডি ম্যাটেরিয়াল') === false) {
    echo "   [FAIL] Rendered resources view missing Bengali header!\n";
    exit(1);
}
echo "   [PASS] Rendered resources page contains Bengali headers and styled table!\n\n";

// 3. Test Student Dashboard Learning Resources widget
echo "3. Checking Student Dashboard Learning Resources widget:\n";
$dashController = app(\App\Http\Controllers\Student\DashboardController::class);
$dashResponse = $dashController->index();
$dashResources = $dashResponse->getData()['latestResources'];

if ($dashResources->isEmpty()) {
    echo "   [FAIL] Dashboard latestResources is empty for enrolled student!\n";
    exit(1);
}
echo "   [PASS] Dashboard fetched {$dashResources->count()} latest resources for student's subjects!\n";

$renderedDashHtml = $dashResponse->render();
if (strpos($renderedDashHtml, 'লার্নিং রিসোর্স ও স্টাডি ম্যাটেরিয়াল') === false) {
    echo "   [FAIL] Rendered dashboard HTML missing learning resources widget!\n";
    exit(1);
}
echo "   [PASS] Rendered dashboard HTML contains learning resources widget!\n\n";

// 4. Cleanup
$learningRes->delete();
$module->delete();
CourseSubjectMap::where('course_id', $course->id)->delete();
Enrollment::where('student_id', $student->id)->delete();
$student->delete();
$user->delete();
$batch->delete();
$subject->delete();
$course->delete();

echo "ALL TASK 68 CHECKS PASSED SUCCESSFULLY (Exit Code 0)!\n";
exit(0);
