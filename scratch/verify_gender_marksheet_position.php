<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Course;
use App\Models\Student;
use App\Models\Batch;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\Result;
use App\Models\FinalMark;
use App\Http\Controllers\Admin\ResultBookController;
use App\Http\Controllers\Admin\ExamController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "=== START VERIFICATION: Gender-based Merit Positions on Marksheet & Result Book ===\n\n";

$errors = [];

// 1. Authenticate an admin user so blade view authorization checks pass
$adminUser = User::first();
if ($adminUser) {
    Auth::login($adminUser);
    echo "✔ Authenticated as User ID: {$adminUser->id}\n";
} else {
    echo "⚠ No User found to authenticate; proceed with caution.\n";
}

// 2. Test Student model gender methods
echo "\n--- Step 1: Testing Student Model Gender Detection ---\n";

$brotherSample = new Student([
    'name' => 'Mohammad Abdullah (Test Brother)',
    'gender' => 'male',
    'student_code' => '25010110001' // 7th digit is '1' -> Male
]);

if ($brotherSample->getGenderGroup() !== 'MALE') {
    $errors[] = "Brother getGenderGroup() expected 'MALE', got '{$brotherSample->getGenderGroup()}'";
}
if (!$brotherSample->isBrother() || $brotherSample->isSister()) {
    $errors[] = "Brother isBrother()/isSister() check failed";
}
if ($brotherSample->getGenderLabel() !== 'ভাই') {
    $errors[] = "Brother getGenderLabel() expected 'ভাই', got '{$brotherSample->getGenderLabel()}'";
}
if ($brotherSample->getGenderBranchTitle() !== 'ভাই শাখা') {
    $errors[] = "Brother getGenderBranchTitle() expected 'ভাই শাখা', got '{$brotherSample->getGenderBranchTitle()}'";
}

$sisterSample = new Student([
    'name' => 'Fatima Khatun (Test Sister)',
    'gender' => 'female',
    'student_code' => '25010120002' // 7th digit is '2' -> Female
]);

if ($sisterSample->getGenderGroup() !== 'FEMALE') {
    $errors[] = "Sister getGenderGroup() expected 'FEMALE', got '{$sisterSample->getGenderGroup()}'";
}
if (!$sisterSample->isSister() || $sisterSample->isBrother()) {
    $errors[] = "Sister isSister()/isBrother() check failed";
}
if ($sisterSample->getGenderLabel() !== 'বোন') {
    $errors[] = "Sister getGenderLabel() expected 'বোন', got '{$sisterSample->getGenderLabel()}'";
}
if ($sisterSample->getGenderBranchTitle() !== 'বোন শাখা') {
    $errors[] = "Sister getGenderBranchTitle() expected 'বোন শাখা', got '{$sisterSample->getGenderBranchTitle()}'";
}

if (empty($errors)) {
    echo "✔ Student gender helper methods verified successfully (MALE = ভাই শাখা, FEMALE = বোন শাখা).\n";
}

// 3. Test in DB Transaction with seeded students & final marks
echo "\n--- Step 2: Testing Dual Merit Ranking in ResultBookController with Brother & Sister Students ---\n";

DB::beginTransaction();

try {
    // Use existing Batch, Semester, and Subject
    $batch = Batch::first();
    $course = $batch->course ?? Course::first();
    $semester = Semester::first();
    $subject = Subject::first();
    if (!$subject) {
        $subject = Subject::create([
            'name' => 'AQIDAH & FIQH',
            'code' => 'AQD-101',
            'full_marks' => 100,
            'pass_marks' => 40,
        ]);
    }

    // Create 2 Brother Students (MALE)
    // Brother 1: 90 marks (Overall 1st, Brothers 1st)
    $b1 = Student::create([
        'name' => 'Brother Amin',
        'gender' => 'male',
        'student_code' => '26010110001',
        'status' => 'ACTIVE',
    ]);
    FinalMark::create([
        'student_id' => $b1->id,
        'batch_id' => $batch->id,
        'semester_id' => $semester->id,
        'subject_id' => $subject->id,
        'total_mark' => 90,
        'attendance_converted' => 10,
        'gpa' => 5.0,
        'grade' => 'A+',
        'status' => 'PASS',
    ]);

    // Sister 1: 85 marks (Overall 2nd, Sisters 1st)
    $s1 = Student::create([
        'name' => 'Sister Maryam',
        'gender' => 'female',
        'student_code' => '26010120002',
        'status' => 'ACTIVE',
    ]);
    FinalMark::create([
        'student_id' => $s1->id,
        'batch_id' => $batch->id,
        'semester_id' => $semester->id,
        'subject_id' => $subject->id,
        'total_mark' => 85,
        'attendance_converted' => 10,
        'gpa' => 4.5,
        'grade' => 'A',
        'status' => 'PASS',
    ]);

    // Brother 2: 80 marks (Overall 3rd, Brothers 2nd)
    $b2 = Student::create([
        'name' => 'Brother Bilal',
        'gender' => 'male',
        'student_code' => '26010110003',
        'status' => 'ACTIVE',
    ]);
    FinalMark::create([
        'student_id' => $b2->id,
        'batch_id' => $batch->id,
        'semester_id' => $semester->id,
        'subject_id' => $subject->id,
        'total_mark' => 80,
        'attendance_converted' => 10,
        'gpa' => 4.0,
        'grade' => 'A-',
        'status' => 'PASS',
    ]);

    // Sister 2: 75 marks (Overall 4th, Sisters 2nd)
    $s2 = Student::create([
        'name' => 'Sister Aisha',
        'gender' => 'female',
        'student_code' => '26010120004',
        'status' => 'ACTIVE',
    ]);
    FinalMark::create([
        'student_id' => $s2->id,
        'batch_id' => $batch->id,
        'semester_id' => $semester->id,
        'subject_id' => $subject->id,
        'total_mark' => 75,
        'attendance_converted' => 10,
        'gpa' => 3.5,
        'grade' => 'B',
        'status' => 'PASS',
    ]);

    // Run ResultBookController::index with ALL genders
    $requestAll = new Request([
        'batch_id' => $batch->id,
        'semester_id' => $semester->id,
        'exam_type' => 'FINAL',
        'gender' => 'ALL'
    ]);

    $controller = new ResultBookController();
    $response = $controller->index($requestAll);
    $viewData = $response->getData();

    // Verify summary statistics
    $summary = $viewData['summary'] ?? [];
    if (($summary['total'] ?? 0) !== 4) {
        $errors[] = "Expected total students 4, got " . ($summary['total'] ?? 'null');
    }
    if (($summary['brother_count'] ?? 0) !== 2) {
        $errors[] = "Expected brother_count 2, got " . ($summary['brother_count'] ?? 'null');
    }
    if (($summary['sister_count'] ?? 0) !== 2) {
        $errors[] = "Expected sister_count 2, got " . ($summary['sister_count'] ?? 'null');
    }
    echo "✔ Summary KPI statistics correctly computed: Total: {$summary['total']}, Brothers: {$summary['brother_count']}, Sisters: {$summary['sister_count']}\n";

    // Verify rankings of rankedStudents
    $rankedStudents = $viewData['rankedStudents'] ?? collect();
    if ($rankedStudents->count() !== 4) {
        $errors[] = "Expected 4 ranked students, got " . $rankedStudents->count();
    }

    $rankedArray = $rankedStudents->keyBy('student_id')->toArray();

    // Verify Brother 1 (Amin): Overall 1st, Gender Rank 1st ("ভাইদের মধ্যে ১ম স্থান")
    $b1Rank = $rankedArray[$b1->id] ?? null;
    if (!$b1Rank) {
        $errors[] = "Brother 1 not found in rankedStudents";
    } else {
        echo "  [Rank 1] {$b1Rank['student_name']}: Overall Pos: {$b1Rank['overall_position']}, Gender Pos: {$b1Rank['gender_position']}, Text: '{$b1Rank['gender_merit_text']}'\n";
        if ($b1Rank['overall_position'] !== 1) $errors[] = "B1 overall_position expected 1, got {$b1Rank['overall_position']}";
        if ($b1Rank['gender_position'] !== 1) $errors[] = "B1 gender_position expected 1, got {$b1Rank['gender_position']}";
        if ($b1Rank['gender_rank_bengali'] !== '১ম') $errors[] = "B1 gender_rank_bengali expected '১ম', got {$b1Rank['gender_rank_bengali']}";
        if ($b1Rank['gender_merit_text'] !== 'ভাইদের মধ্যে ১ম স্থান') $errors[] = "B1 gender_merit_text expected 'ভাইদের মধ্যে ১ম স্থান', got {$b1Rank['gender_merit_text']}";
    }

    // Verify Sister 1 (Maryam): Overall 2nd, Gender Rank 1st ("বোনদের মধ্যে ১ম স্থান")
    $s1Rank = $rankedArray[$s1->id] ?? null;
    if (!$s1Rank) {
        $errors[] = "Sister 1 not found in rankedStudents";
    } else {
        echo "  [Rank 2] {$s1Rank['student_name']}: Overall Pos: {$s1Rank['overall_position']}, Gender Pos: {$s1Rank['gender_position']}, Text: '{$s1Rank['gender_merit_text']}'\n";
        if ($s1Rank['overall_position'] !== 2) $errors[] = "S1 overall_position expected 2, got {$s1Rank['overall_position']}";
        if ($s1Rank['gender_position'] !== 1) $errors[] = "S1 gender_position expected 1 (1st among sisters!), got {$s1Rank['gender_position']}";
        if ($s1Rank['gender_rank_bengali'] !== '১ম') $errors[] = "S1 gender_rank_bengali expected '১ম', got {$s1Rank['gender_rank_bengali']}";
        if ($s1Rank['gender_merit_text'] !== 'বোনদের মধ্যে ১ম স্থান') $errors[] = "S1 gender_merit_text expected 'বোনদের মধ্যে ১ম স্থান', got {$s1Rank['gender_merit_text']}";
    }

    // Verify Brother 2 (Bilal): Overall 3rd, Gender Rank 2nd ("ভাইদের মধ্যে ২য় স্থান")
    $b2Rank = $rankedArray[$b2->id] ?? null;
    if (!$b2Rank) {
        $errors[] = "Brother 2 not found in rankedStudents";
    } else {
        echo "  [Rank 3] {$b2Rank['student_name']}: Overall Pos: {$b2Rank['overall_position']}, Gender Pos: {$b2Rank['gender_position']}, Text: '{$b2Rank['gender_merit_text']}'\n";
        if ($b2Rank['overall_position'] !== 3) $errors[] = "B2 overall_position expected 3, got {$b2Rank['overall_position']}";
        if ($b2Rank['gender_position'] !== 2) $errors[] = "B2 gender_position expected 2 (2nd among brothers!), got {$b2Rank['gender_position']}";
        if ($b2Rank['gender_merit_text'] !== 'ভাইদের মধ্যে ২য় স্থান') $errors[] = "B2 gender_merit_text expected 'ভাইদের মধ্যে ২য় স্থান', got {$b2Rank['gender_merit_text']}";
    }

    // Verify Sister 2 (Aisha): Overall 4th, Gender Rank 2nd ("বোনদের মধ্যে ২য় স্থান")
    $s2Rank = $rankedArray[$s2->id] ?? null;
    if (!$s2Rank) {
        $errors[] = "Sister 2 not found in rankedStudents";
    } else {
        echo "  [Rank 4] {$s2Rank['student_name']}: Overall Pos: {$s2Rank['overall_position']}, Gender Pos: {$s2Rank['gender_position']}, Text: '{$s2Rank['gender_merit_text']}'\n";
        if ($s2Rank['overall_position'] !== 4) $errors[] = "S2 overall_position expected 4, got {$s2Rank['overall_position']}";
        if ($s2Rank['gender_position'] !== 2) $errors[] = "S2 gender_position expected 2 (2nd among sisters!), got {$s2Rank['gender_position']}";
        if ($s2Rank['gender_merit_text'] !== 'বোনদের মধ্যে ২য় স্থান') $errors[] = "S2 gender_merit_text expected 'বোনদের মধ্যে ২য় স্থান', got {$s2Rank['gender_merit_text']}";
    }

    // Test Gender Filter: Brothers Only (MALE)
    $requestMale = new Request([
        'batch_id' => $batch->id,
        'semester_id' => $semester->id,
        'exam_type' => 'FINAL',
        'gender' => 'MALE'
    ]);
    $resMale = $controller->index($requestMale);
    $tabMale = $resMale->getData()['rankedStudents'] ?? collect();
    if ($tabMale->count() !== 2) {
        $errors[] = "Expected 2 male students in MALE filter, got " . $tabMale->count();
    }
    foreach ($tabMale as $item) {
        if (($item['gender_group'] ?? '') !== 'MALE') {
            $errors[] = "Non-male found in MALE filter: " . $item['student_name'];
        }
    }
    echo "✔ MALE filter correctly returns only 2 brother students.\n";

    // Test Gender Filter: Sisters Only (FEMALE)
    $requestFemale = new Request([
        'batch_id' => $batch->id,
        'semester_id' => $semester->id,
        'exam_type' => 'FINAL',
        'gender' => 'FEMALE'
    ]);
    $resFemale = $controller->index($requestFemale);
    $tabFemale = $resFemale->getData()['rankedStudents'] ?? collect();
    if ($tabFemale->count() !== 2) {
        $errors[] = "Expected 2 female students in FEMALE filter, got " . $tabFemale->count();
    }
    foreach ($tabFemale as $item) {
        if (($item['gender_group'] ?? '') !== 'FEMALE') {
            $errors[] = "Non-female found in FEMALE filter: " . $item['student_name'];
        }
    }
    echo "✔ FEMALE filter correctly returns only 2 sister students.\n";

    // Test Blade view rendering
    $renderedHtml = $response->render();
    if (!str_contains($renderedHtml, 'name="gender"')) {
        $errors[] = "Result book view missing branch filter dropdown ('name=\"gender\"')";
    }
    if (!str_contains($renderedHtml, 'id="msStudentBranch"')) {
        $errors[] = "Marksheet modal missing student branch field ('#msStudentBranch')";
    }
    if (!str_contains($renderedHtml, 'id="msMeritPosition"')) {
        $errors[] = "Marksheet modal missing merit position field ('#msMeritPosition')";
    }
    if (!str_contains($renderedHtml, 'id="msOverallPosition"')) {
        $errors[] = "Marksheet modal missing overall position field ('#msOverallPosition')";
    }
    if (!str_contains($renderedHtml, 'Kalpurush')) {
        $errors[] = "Result book view missing Kalpurush font reference";
    }
    echo "✔ Result Book Blade view renders branch filter, separate ranks, and marksheet modal elements correctly.\n";

    // 4. Test ExamController Single Exam Merit & Marksheet
    echo "\n--- Step 3: Testing ExamController Single Exam Merit & Marksheet ---\n";

    $exam = Exam::create([
        'subject_id' => $subject->id,
        'semester_id' => $semester?->id,
        'title' => 'Aqidah Final Exam 2026',
        'type' => 'FINAL',
        'full_marks' => 100,
        'pass_marks' => 40,
        'exam_date' => now()->toDateString(),
        'status' => 'COMPLETED',
    ]);

    // Result 1: Brother 1 (95 marks) -> Brother Rank 1, Overall 1
    Result::create([
        'exam_id' => $exam->id,
        'student_id' => $b1->id,
        'marks' => 95,
        'mcq_marks' => 40,
        'written_marks' => 55,
        'grade' => 'A+',
        'status' => 'PASS',
    ]);

    // Result 2: Sister 1 (90 marks) -> Sister Rank 1, Overall 2
    Result::create([
        'exam_id' => $exam->id,
        'student_id' => $s1->id,
        'marks' => 90,
        'mcq_marks' => 38,
        'written_marks' => 52,
        'grade' => 'A+',
        'status' => 'PASS',
    ]);

    // Result 3: Brother 2 (85 marks) -> Brother Rank 2, Overall 3
    Result::create([
        'exam_id' => $exam->id,
        'student_id' => $b2->id,
        'marks' => 85,
        'mcq_marks' => 35,
        'written_marks' => 50,
        'grade' => 'A',
        'status' => 'PASS',
    ]);

    // Result 4: Sister 2 (80 marks) -> Sister Rank 2, Overall 4
    Result::create([
        'exam_id' => $exam->id,
        'student_id' => $s2->id,
        'marks' => 80,
        'mcq_marks' => 32,
        'written_marks' => 48,
        'grade' => 'A-',
        'status' => 'PASS',
    ]);

    $examController = new ExamController();
    $examResponse = $examController->show($exam);
    $examViewData = $examResponse->getData();
    $meritList = $examViewData['meritList'] ?? collect();

    if ($meritList->count() !== 4) {
        $errors[] = "Expected 4 exam merit list items, got " . $meritList->count();
    }

    $examRanked = $meritList->keyBy('student.id')->toArray();

    // Check Exam Brother 1
    $exB1 = $examRanked[$b1->id] ?? null;
    if ($exB1['gender_position'] !== 1 || $exB1['gender_merit_text'] !== 'ভাইদের মধ্যে ১ম স্থান') {
        $errors[] = "Exam Brother 1 rank mismatch: " . ($exB1['gender_merit_text'] ?? 'null');
    }
    // Check Exam Sister 1 (1st among sisters!)
    $exS1 = $examRanked[$s1->id] ?? null;
    if ($exS1['gender_position'] !== 1 || $exS1['gender_merit_text'] !== 'বোনদের মধ্যে ১ম স্থান') {
        $errors[] = "Exam Sister 1 rank mismatch: " . ($exS1['gender_merit_text'] ?? 'null');
    }
    // Check Exam Brother 2 (2nd among brothers!)
    $exB2 = $examRanked[$b2->id] ?? null;
    if ($exB2['gender_position'] !== 2 || $exB2['gender_merit_text'] !== 'ভাইদের মধ্যে ২য় স্থান') {
        $errors[] = "Exam Brother 2 rank mismatch: " . ($exB2['gender_merit_text'] ?? 'null');
    }
    // Check Exam Sister 2 (2nd among sisters!)
    $exS2 = $examRanked[$s2->id] ?? null;
    if ($exS2['gender_position'] !== 2 || $exS2['gender_merit_text'] !== 'বোনদের মধ্যে ২য় স্থান') {
        $errors[] = "Exam Sister 2 rank mismatch: " . ($exS2['gender_merit_text'] ?? 'null');
    }

    echo "✔ Single exam merit list correctly assigned separate brother and sister positions:\n";
    echo "  - Brother 1: {$exB1['gender_merit_text']} (Overall: {$exB1['overall_rank_bengali']})\n";
    echo "  - Sister 1:  {$exS1['gender_merit_text']} (Overall: {$exS1['overall_rank_bengali']})\n";
    echo "  - Brother 2: {$exB2['gender_merit_text']} (Overall: {$exB2['overall_rank_bengali']})\n";
    echo "  - Sister 2:  {$exS2['gender_merit_text']} (Overall: {$exS2['overall_rank_bengali']})\n";

    // Render single exam show blade
    $examHtml = $examResponse->render();
    if (!str_contains($examHtml, 'id="smStudentBranch"')) {
        $errors[] = "Single marksheet modal missing #smStudentBranch";
    }
    if (!str_contains($examHtml, 'id="smOverallRankBadge"')) {
        $errors[] = "Single marksheet modal missing #smOverallRankBadge";
    }
    if (!str_contains($examHtml, 'Kalpurush')) {
        $errors[] = "Single marksheet missing 'Kalpurush' font style";
    }
    echo "✔ Single Exam Show Blade view renders branch badge, separate ranks, and modal elements correctly.\n";

} catch (\Throwable $e) {
    echo "❌ EXCEPTION in test block: " . $e->getMessage() . "\n";
    echo "   at " . $e->getFile() . ":" . $e->getLine() . "\n";
    $errors[] = "Exception: " . $e->getMessage();
} finally {
    // Always rollback so the test does not pollute the DB
    DB::rollBack();
    echo "✔ Test database transaction rolled back cleanly.\n";
}

// 5. Check if any errors occurred
echo "\n=== VERIFICATION SUMMARY ===\n";
if (!empty($errors)) {
    echo "❌ FAILED with " . count($errors) . " error(s):\n";
    foreach ($errors as $i => $err) {
        echo "  " . ($i + 1) . ". {$err}\n";
    }
    exit(1);
} else {
    echo "✔ ALL TESTS PASSED! Exit Code: 0\n";
    echo "✔ Brothers and Sisters successfully have separate merit positions on Marksheets, Result Book, and Single Exam pages!\n";
    exit(0);
}
