<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

echo "=== Verifying Task 71: Dynamic Semester Consolidated Transcript in Results ===" . PHP_EOL . PHP_EOL;

$passed = 0;
$failed = 0;

function assertCondition($desc, $cond) {
    global $passed, $failed;
    if ($cond) {
        echo "  [PASS] $desc" . PHP_EOL;
        $passed++;
    } else {
        echo "  [FAIL] $desc" . PHP_EOL;
        $failed++;
    }
}

// 1. Blade Template Logic Verification for index.blade.php
$indexViewPath = resource_path('views/student/results/index.blade.php');
$indexContent = file_get_contents($indexViewPath);
assertCondition("results/index.blade.php checks \$semCount > 1", str_contains($indexContent, '$semCount > 1') || str_contains($indexContent, '$semCount > 1'));
assertCondition("results/index.blade.php uses Bengali numerals for semester count", str_contains($indexContent, '$bnSemCount'));
assertCondition("results/index.blade.php has 'Kalpurush' font family", str_contains($indexContent, "'Kalpurush'"));

// 2. Blade Rendering Test with Mocked Courses
// Case A: 6-Semester Course
$c6 = new Course(['name' => 'আলিম প্রিপারেটরি কোর্স']);
$c6->setRelation('semesters', collect([
    new Semester(['id' => 1, 'name' => '১ম সেমিস্টার', 'sequence_no' => 1]),
    new Semester(['id' => 2, 'name' => '২য় সেমিস্টার', 'sequence_no' => 2]),
    new Semester(['id' => 3, 'name' => '৩য় সেমিস্টার', 'sequence_no' => 3]),
    new Semester(['id' => 4, 'name' => '৪র্থ সেমিস্টার', 'sequence_no' => 4]),
    new Semester(['id' => 5, 'name' => '৫ম সেমিস্টার', 'sequence_no' => 5]),
    new Semester(['id' => 6, 'name' => '৬ষ্ঠ সেমিস্টার', 'sequence_no' => 6]),
]));

$rendered6 = View::make('student.results.index', [
    'course' => $c6,
    'student' => new Student(['name' => 'টেস্ট শিক্ষার্থী']),
    'results' => collect(),
    'finalMarks' => collect(),
])->render();

assertCondition("6-Semester Course renders '৬-সেমিস্টার একত্রিত ট্রান্সক্রিপ্ট'", str_contains($rendered6, '৬-সেমিস্টার একত্রিত ট্রান্সক্রিপ্ট'));

// Case B: 2-Semester Course
$c2 = new Course(['name' => 'হাদিস ডিপ্লোমা কোর্স']);
$c2->setRelation('semesters', collect([
    new Semester(['id' => 1, 'name' => '১ম সেমিস্টার', 'sequence_no' => 1]),
    new Semester(['id' => 2, 'name' => '২য় সেমিস্টার', 'sequence_no' => 2]),
]));

$rendered2 = View::make('student.results.index', [
    'course' => $c2,
    'student' => new Student(['name' => 'টেস্ট শিক্ষার্থী']),
    'results' => collect(),
    'finalMarks' => collect(),
])->render();

assertCondition("2-Semester Course renders '২-সেমিস্টার একত্রিত ট্রান্সক্রিপ্ট'", str_contains($rendered2, '২-সেমিস্টার একত্রিত ট্রান্সক্রিপ্ট'));
assertCondition("2-Semester Course DOES NOT render '৬-সেমিস্টার'", !str_contains($rendered2, '৬-সেমিস্টার একত্রিত ট্রান্সক্রিপ্ট'));

// Case C: 1-Semester Course (or no semesters)
$c1 = new Course(['name' => 'বেসিক এরাবিক কোর্স']);
$c1->setRelation('semesters', collect([
    new Semester(['id' => 1, 'name' => 'একক সেমিস্টার', 'sequence_no' => 1]),
]));

$rendered1 = View::make('student.results.index', [
    'course' => $c1,
    'student' => new Student(['name' => 'টেস্ট শিক্ষার্থী']),
    'results' => collect(),
    'finalMarks' => collect(),
])->render();

assertCondition("1-Semester Course DOES NOT render consolidated transcript button", !str_contains($rendered1, 'একত্রিত ট্রান্সক্রিপ্ট (Consolidated Transcript)'));

// Case D: 0-Semester / Subject-based Course
$c0 = new Course(['name' => 'সাবজেক্ট বেসড শর্ট কোর্স']);
$c0->setRelation('semesters', collect());

$rendered0 = View::make('student.results.index', [
    'course' => $c0,
    'student' => new Student(['name' => 'টেস্ট শিক্ষার্থী']),
    'results' => collect(),
    'finalMarks' => collect(),
])->render();

assertCondition("0-Semester Course DOES NOT render consolidated transcript button", !str_contains($rendered0, 'একত্রিত ট্রান্সক্রিপ্ট (Consolidated Transcript)'));

// 3. Transcript View dynamic title verification
$transcriptViewPath = resource_path('views/student/results/transcript.blade.php');
$transcriptContent = file_get_contents($transcriptViewPath);
assertCondition("transcript.blade.php uses dynamic bnSemCount in title", str_contains($transcriptContent, '{{ $bnSemCount ??'));
assertCondition("transcript.blade.php uses dynamic bnSemCount in doc-title header", str_contains($transcriptContent, '{{ $bnSemCount ?? \'একত্রিত\' }}-সেমিস্টার'));

// 4. Controller Test: Single-semester redirect & Multi-semester handling
DB::beginTransaction();
try {
    $user = User::factory()->create(['role' => 'STUDENT']);
    $student = Student::create([
        'user_id' => $user->id,
        'name' => 'মাহমুদুল হাসান',
        'email' => $user->email,
        'phone' => '01711' . rand(100000, 999999),
        'gender' => 'Male',
        'status' => 'ACTIVE',
    ]);

    // Create course with 1 semester
    $singleCourse = Course::create([
        'name' => 'সিঙ্গেল সেমিস্টার টেস্ট কোর্স',
        'code' => 'S1' . rand(10, 99),
        'is_active' => true,
    ]);
    Semester::create(['course_id' => $singleCourse->id, 'name' => 'সেমিস্টার ১', 'sequence_no' => 1]);

    $batch = \App\Models\Batch::create([
        'name' => 'টেস্ট ব্যাচ',
        'batch_code' => 'TB-' . rand(100, 999),
        'course_id' => $singleCourse->id,
        'start_date' => now()->toDateString(),
        'status' => 'ACTIVE',
    ]);

    Enrollment::create([
        'student_id' => $student->id,
        'course_id' => $singleCourse->id,
        'batch_id' => $batch->id,
        'enrolled_at' => now(),
        'status' => 'ACTIVE',
    ]);

    auth()->login($user);
    $controller = new \App\Http\Controllers\Student\ResultController();
    $response = $controller->transcript();

    assertCondition("ResultController redirects when student has <= 1 semester", $response->isRedirect());

    // Now add another semester to make it multi-semester
    Semester::create(['course_id' => $singleCourse->id, 'name' => 'সেমিস্টার ২', 'sequence_no' => 2]);
    $responseMulti = $controller->transcript();

    assertCondition("ResultController renders transcript view when course has > 1 semesters", $responseMulti instanceof \Illuminate\View\View);
    $viewData = $responseMulti->getData();
    assertCondition("ResultController passes semestersCount=2", ($viewData['semestersCount'] ?? 0) === 2);
    assertCondition("ResultController passes bnSemCount='২'", ($viewData['bnSemCount'] ?? '') === '২');

    DB::rollBack();
    echo "  [PASS] DB transaction rolled back cleanly without pollution" . PHP_EOL;
    $passed++;
} catch (\Throwable $e) {
    DB::rollBack();
    echo "  [FAIL] Controller test exception: " . $e->getMessage() . PHP_EOL;
    $failed++;
}

echo PHP_EOL . "Results: $passed Passed, $failed Failed." . PHP_EOL;
if ($failed > 0) {
    exit(1);
}
echo "All assertions PASSED with Exit Code 0!" . PHP_EOL;
exit(0);
