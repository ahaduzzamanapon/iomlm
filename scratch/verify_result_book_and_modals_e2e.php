<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\Semester;
use App\Models\Exam;
use App\Models\FinalMark;
use Illuminate\Http\Request;

echo "=== E2E TESTING: RESULT BOOK CONTROLLER & BLADE RENDERING ===\n";

// Authenticate as Admin
$admin = User::where('role', 'SUPER_ADMIN')->orWhere('role', 'ADMIN')->first();
if (!$admin) {
    echo "  [FAIL] No admin user found!\n";
    exit(1);
}
auth()->guard()->setUser($admin);
echo "  [PASS] Authenticated as Admin: {$admin->email}\n";

$controller = app(\App\Http\Controllers\Admin\ResultBookController::class);

// 1. Test Tab 1: Tabulation Sheet
$batch = Batch::first();
$subject = Subject::first();
$semester = Semester::first();

echo "\n1. Testing Tab 1: Tabulation Sheet...\n";
$req1 = Request::create(route('admin.result-book.index'), 'GET', [
    'tab' => 'tabulation',
    'batch_id' => $batch?->id,
    'semester_id' => $semester?->id,
    'subject_id' => $subject?->id,
]);
$resp1 = $controller->index($req1);
$content1 = $resp1->render();
echo "  [PASS] Tabulation Sheet rendered. Length: " . strlen($content1) . " bytes.\n";

// 2. Test Tab 2: Manual Marking (All-Exams / Semester Mode)
echo "\n2. Testing Tab 2: Manual Marking (All-Exams Mode)...\n";
$req2 = Request::create(route('admin.result-book.index'), 'GET', [
    'tab' => 'manual_marking',
    'batch_id' => $batch?->id,
    'semester_id' => $semester?->id,
    'subject_id' => $subject?->id,
]);
$resp2 = $controller->index($req2);
$content2 = $resp2->render();
echo "  [PASS] Manual Marking (All-Exams) rendered. Length: " . strlen($content2) . " bytes.\n";

// 3. Test Tab 2: Manual Marking (Specific Exam Mode)
$exam = Exam::where('subject_id', $subject?->id)->first();
if ($exam) {
    echo "\n3. Testing Tab 2: Manual Marking (Single Exam Mode: {$exam->title})...\n";
    $req3 = Request::create(route('admin.result-book.index'), 'GET', [
        'tab' => 'manual_marking',
        'batch_id' => $batch?->id,
        'semester_id' => $semester?->id,
        'subject_id' => $subject?->id,
        'exam_id' => $exam->id,
    ]);
    $resp3 = $controller->index($req3);
    $content3 = $resp3->render();
    echo "  [PASS] Manual Marking (Single Exam) rendered. Length: " . strlen($content3) . " bytes.\n";
} else {
    echo "\n3. [SKIP] No exam found for subject {$subject?->name}.\n";
}

// 4. Test Student Results Portal
echo "\n4. Testing Student Results Portal...\n";
$studentUser = User::where('role', 'STUDENT')->first();
if ($studentUser) {
    auth()->login($studentUser);
    $studentController = app(\App\Http\Controllers\Student\ResultController::class);
    $respStudent = $studentController->index();
    $contentStudent = $respStudent->render();
    echo "  [PASS] Student Results Page rendered for {$studentUser->email}. Length: " . strlen($contentStudent) . " bytes.\n";
} else {
    echo "  [INFO] No student user found for direct portal render test.\n";
}

echo "\n=== ALL E2E RENDERING TESTS PASSED (Exit Code 0) ===\n";
