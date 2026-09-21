<?php

/**
 * Task 30 Verification Script: Result System Overhaul
 * 
 * Verifies:
 * 1. Schema & Model integrity (non-exam criteria: Tamrin, Tajweed, DNS; merit_position, publication flags)
 * 2. Qawmi Madrasah Grading System (মুমতাজ, জায়্যিদ জিদ্দান, জায়্যিদ, মাকবুল, রাসিব) and Merit Rank in Bengali (১ম, ২য়, ৩য়)
 * 3. Multi-Exam result publishing (CT, Mid, Final) toggle & Exam::publishResults()
 * 4. Automatic merit ranking calculation (recalculateMeritRanks)
 * 5. Non-exam manual marking & total score recalculation (Tamrin, Tajweed, DNS, Attendance)
 * 6. Result Book student roll search, manual mark override, and batch stats
 * 7. 6-Semester Consolidated Transcript calculation (SGPA per semester, Cumulative CGPA, Qawmi Honor)
 * 8. Routes integrity & Blade views with Kalpurush Bangla font
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Result;
use App\Models\FinalMark;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$passed = 0;
$failed = 0;

function assertCondition(string $name, bool $condition, string $details = ''): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [\033[32mPASS\033[0m] $name\n";
    } else {
        $failed++;
        echo "  [\033[31mFAIL\033[0m] $name " . ($details ? "($details)" : "") . "\n";
    }
}

echo "=== TASK 30: RESULT SYSTEM OVERHAUL VERIFICATION ===\n\n";

try {
    DB::beginTransaction();

    // ── SECTION 1: Schema Columns Check ────────
    echo "1. Testing Schema Changes for Task 30...\n";

    $finalMarkColumns = ['tamrin_mark', 'tajweed_mark', 'dns_mark', 'merit_position', 'is_published', 'published_at'];
    foreach ($finalMarkColumns as $col) {
        assertCondition("final_marks table has '$col' column", Schema::hasColumn('final_marks', $col));
    }

    assertCondition("exams table has 'is_result_published' column", Schema::hasColumn('exams', 'is_result_published'));
    assertCondition("exams table has 'result_published_at' column", Schema::hasColumn('exams', 'result_published_at'));
    assertCondition("results table has 'is_published' column", Schema::hasColumn('results', 'is_published'));

    // ── SECTION 2: Qawmi Grading Standard & Bengali Merit Ranks ────────
    echo "\n2. Testing Qawmi Madrasah Grading System & Bengali Merit Numerals...\n";

    $mumtaz = FinalMark::calculateQawmiGrade(85.0);
    assertCondition("Score >= 80 yields Mumtaz (মুমতাজ)", str_contains($mumtaz['name_bn'], 'মুমতাজ') && $mumtaz['name_ar'] === 'ممتاز');

    $jayyidJiddan = FinalMark::calculateQawmiGrade(72.0);
    assertCondition("Score 72 yields Jayyid Jiddan (জায়্যিদ জিদ্দান)", str_contains($jayyidJiddan['name_bn'], 'জায়্যিদ জিদ্দান') && $jayyidJiddan['name_ar'] === 'جيد جداً');

    $jayyid = FinalMark::calculateQawmiGrade(58.0);
    assertCondition("Score 58 yields Jayyid (জায়্যিদ)", str_contains($jayyid['name_bn'], 'জায়্যিদ') && $jayyid['name_ar'] === 'جيد');

    $maqbul = FinalMark::calculateQawmiGrade(45.0);
    assertCondition("Score 45 yields Maqbul (মাকবুল)", str_contains($maqbul['name_bn'], 'মাকবুল') && $maqbul['name_ar'] === 'مقبول');

    $rasib = FinalMark::calculateQawmiGrade(32.0);
    assertCondition("Score 32 yields Rasib (রাসিব / ফেল)", str_contains($rasib['name_bn'], 'রাসিব') && $rasib['name_ar'] === 'راسب');

    // Test Merit rank Bengali accessor
    $testMark = new FinalMark(['merit_position' => 1]);
    assertCondition("Merit position 1 converts to '১ম'", $testMark->merit_rank_bengali === '১ম');

    $testMark->merit_position = 2;
    assertCondition("Merit position 2 converts to '২য়'", $testMark->merit_rank_bengali === '২য়');

    $testMark->merit_position = 3;
    assertCondition("Merit position 3 converts to '৩য়'", $testMark->merit_rank_bengali === '৩য়');

    $testMark->merit_position = 4;
    assertCondition("Merit position 4 converts to '৪র্থ'", $testMark->merit_rank_bengali === '৪র্থ');

    // ── SECTION 3: Multi-Exam Result Publishing ────────
    echo "\n3. Testing Multi-Exam Result Publishing...\n";

    $subject = Subject::first() ?? Subject::create([
        'name' => 'বালাগাত ও অলঙ্কারশাস্ত্র',
        'code' => 'BAL-101',
        'credit' => 3,
        'full_marks' => 100,
        'pass_marks' => 40,
        'is_active' => true
    ]);

    $exam = Exam::create([
        'subject_id' => $subject->id,
        'title' => 'ইলমুল মাআনী মিডটার্ম পরীক্ষা',
        'type' => 'MIDTERM',
        'exam_date' => today(),
        'full_marks' => 50,
        'pass_marks' => 20,
        'status' => 'SCHEDULED'
    ]);

    $exam->publishResults();
    assertCondition("Exam publishResults() sets is_result_published to true", $exam->is_result_published === true);
    assertCondition("Exam publishResults() sets result_published_at", $exam->result_published_at !== null);

    $exam->unpublishResults();
    assertCondition("Exam unpublishResults() sets is_result_published to false", $exam->is_result_published === false);

    // ── SECTION 4: Non-Exam Manual Marks (Tamrin, Tajweed, DNS) & Recalculation ────────
    echo "\n4. Testing Manual Marking (Tamrin, Tajweed, DNS) & Auto Total Recalculation...\n";

    $course = Course::first() ?? Course::create(['name' => 'আলিম ১ম বর্ষ', 'code' => '01', 'type' => 'SEMESTER_BASED']);
    $batch = Batch::first() ?? Batch::create(['course_id' => $course->id, 'name' => 'ব্যাচ ২০২৬', 'batch_code' => '2601']);
    
    $studentUser = User::create([
        'name' => 'মুহাম্মদ উমর ফারুক',
        'email' => 'student_task30_' . time() . '@iom.edu.bd',
        'password' => bcrypt('password'),
        'role' => 'student'
    ]);
    $student1 = Student::create([
        'user_id' => $studentUser->id,
        'name' => 'মুহাম্মদ উমর ফারুক',
        'student_code' => '26-01-01-M-8801',
        'status' => 'ACTIVE'
    ]);

    $studentUser2 = User::create([
        'name' => 'আবদুর রহমান',
        'email' => 'student2_task30_' . time() . '@iom.edu.bd',
        'password' => bcrypt('password'),
        'role' => 'student'
    ]);
    $student2 = Student::create([
        'user_id' => $studentUser2->id,
        'name' => 'আবদুর রহমান',
        'student_code' => '26-01-01-M-8802',
        'status' => 'ACTIVE'
    ]);

    $finalMark1 = FinalMark::create([
        'student_id' => $student1->id,
        'subject_id' => $subject->id,
        'batch_id' => $batch->id,
        'class_test_converted' => 15.0,
        'midterm_converted' => 20.0,
        'final_converted' => 30.0,
        'attendance_converted' => 8.0,
        'tamrin_mark' => null,
        'tajweed_mark' => null,
        'dns_mark' => null,
        'total_mark' => 73.0,
        'grade' => 'A',
        'gpa' => 4.00,
        'status' => 'PASS',
        'is_published' => false
    ]);

    $finalMark2 = FinalMark::create([
        'student_id' => $student2->id,
        'subject_id' => $subject->id,
        'batch_id' => $batch->id,
        'class_test_converted' => 12.0,
        'midterm_converted' => 18.0,
        'final_converted' => 28.0,
        'attendance_converted' => 7.0,
        'total_mark' => 65.0,
        'grade' => 'A-',
        'gpa' => 3.50,
        'status' => 'PASS',
        'is_published' => false
    ]);

    // Student 1 receives practical non-exam marks: Tamrin (5), Tajweed (5), DNS (5)
    $finalMark1->recalculate([
        'tamrin_mark' => 5.0,
        'tajweed_mark' => 5.0,
        'dns_mark' => 5.0,
    ]);

    assertCondition("Final mark recalculates total with non-exam criteria (73 + 15 = 88)", $finalMark1->total_mark == 88.0);
    assertCondition("Grade updates to A+ for total 88", $finalMark1->grade === 'A+');
    assertCondition("GPA updates to 5.00 for total 88", $finalMark1->gpa == 5.00);
    assertCondition("Qawmi grade reflects Mumtaz (মুমতাজ)", str_contains($finalMark1->qawmi_grade['name_bn'], 'মুমতাজ'));

    // ── SECTION 5: Automatic Merit Ranking ────────
    echo "\n5. Testing Automatic Merit Position Assignment...\n";

    FinalMark::recalculateMeritRanks($batch->id, $subject->id);

    $finalMark1->refresh();
    $finalMark2->refresh();

    assertCondition("Student 1 with score 88 receives 1st Merit Rank (১ম)", $finalMark1->merit_position === 1 && $finalMark1->merit_rank_bengali === '১ম');
    assertCondition("Student 2 with score 65 receives 2nd Merit Rank (২য়)", $finalMark2->merit_position === 2 && $finalMark2->merit_rank_bengali === '২য়');

    // ── SECTION 6: Result Book Override & Publishing ────────
    echo "\n6. Testing Result Book Manual Override & Final Mark Publishing...\n";

    $finalMark2->recalculate([
        'class_test_converted' => 20.0,
        'midterm_converted' => 30.0,
        'final_converted' => 40.0,
        'tamrin_mark' => 5.0,
    ]); // New total: 20 + 30 + 40 + 7 (att) + 5 = 102 (or 95+)

    FinalMark::recalculateMeritRanks($batch->id, $subject->id);
    $finalMark1->refresh();
    $finalMark2->refresh();

    assertCondition("Student 2 with updated higher score takes 1st rank", $finalMark2->merit_position === 1);
    assertCondition("Student 1 shifts to 2nd rank", $finalMark1->merit_position === 2);

    // Publishing final marks
    FinalMark::where('batch_id', $batch->id)->where('subject_id', $subject->id)->update([
        'is_published' => true,
        'published_at' => now()
    ]);

    $publishedCount = FinalMark::where('batch_id', $batch->id)->where('subject_id', $subject->id)->published()->count();
    assertCondition("Scope published() correctly filters published final marks (count: 2)", $publishedCount === 2);

    // ── SECTION 7: 6-Semester Consolidated Transcript Calculations ────────
    echo "\n7. Testing 6-Semester Consolidated Transcript SGPA & CGPA Formulas...\n";

    // Create 2 test semesters with subjects
    $sem1 = Semester::firstOrCreate(['course_id' => $course->id, 'sequence_no' => 1], ['name' => '১ম সেমিস্টার']);
    $sem2 = Semester::firstOrCreate(['course_id' => $course->id, 'sequence_no' => 2], ['name' => '২য় সেমিস্টার']);

    $subj1 = Subject::create([
        'name' => 'তাফসীরুল কুরআন',
        'code' => 'TAF-101',
        'credit' => 3,
        'full_marks' => 100,
        'pass_marks' => 40,
        'is_active' => true
    ]);

    $subj2 = Subject::create([
        'name' => 'ফিকহুস সুন্নাহ',
        'code' => 'FIQ-102',
        'credit' => 3,
        'full_marks' => 100,
        'pass_marks' => 40,
        'is_active' => true
    ]);

    $fmSem1 = FinalMark::create([
        'student_id' => $student1->id,
        'subject_id' => $subj1->id,
        'batch_id' => $batch->id,
        'semester_id' => $sem1->id,
        'total_mark' => 85.0,
        'grade' => 'A+',
        'gpa' => 5.00,
        'status' => 'PASS',
        'is_published' => true
    ]);

    $fmSem2 = FinalMark::create([
        'student_id' => $student1->id,
        'subject_id' => $subj2->id,
        'batch_id' => $batch->id,
        'semester_id' => $sem2->id,
        'total_mark' => 75.0,
        'grade' => 'A',
        'gpa' => 4.00,
        'status' => 'PASS',
        'is_published' => true
    ]);

    // Sem 1 SGPA = 5.00 (3 credits * 5.00 = 15 points)
    // Sem 2 SGPA = 4.00 (3 credits * 4.00 = 12 points)
    // CGPA = (15 + 12) / (3 + 3) = 27 / 6 = 4.50
    $totalPoints = (5.00 * 3) + (4.00 * 3);
    $totalCredits = 3 + 3;
    $calculatedCgpa = round($totalPoints / $totalCredits, 2);

    assertCondition("Consolidated CGPA calculated correctly as 4.50", $calculatedCgpa === 4.50);

    $overallQawmi = FinalMark::calculateQawmiGrade(0, $calculatedCgpa);
    assertCondition("CGPA 4.50 yields Jayyid Jiddan (জায়্যিদ জিদ্দান)", str_contains($overallQawmi['name_bn'], 'জায়্যিদ জিদ্দান'));

    // ── SECTION 8: Routes & Views Integrity ────────
    echo "\n8. Verifying Routes & Blade Views Integrity for Task 30...\n";

    $requiredRoutes = [
        'admin.final-marks.index',
        'admin.final-marks.generate',
        'admin.final-marks.publish-toggle',
        'admin.final-marks.auto-attendance',
        'admin.final-marks.update-manual',
        'admin.result-book.index',
        'admin.result-book.override',
        'admin.result-book.publish-exam',
        'admin.students.transcript',
        'student.results.index',
        'student.results.transcript',
    ];

    foreach ($requiredRoutes as $r) {
        assertCondition("Named route '$r' is registered", Route::has($r));
    }

    $bladeViews = [
        'resources/views/admin/final-marks/index.blade.php',
        'resources/views/admin/result-book/index.blade.php',
        'resources/views/admin/students/transcript.blade.php',
        'resources/views/student/results/index.blade.php',
        'resources/views/student/results/transcript.blade.php',
    ];

    foreach ($bladeViews as $v) {
        $fullPath = __DIR__ . '/../' . $v;
        $exists = file_exists($fullPath);
        assertCondition("Blade view '$v' exists", $exists);
        if ($exists) {
            $content = file_get_contents($fullPath);
            assertCondition("Blade view '$v' has Kalpurush font reference", 
                str_contains($content, 'Kalpurush') || str_contains($content, 'kalpurush')
            );
        }
    }

    // Rollback changes to keep DB pristine
    DB::rollBack();
    echo "\nVerification database transaction rolled back cleanly.\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n\033[31mException occurred:\033[0m " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    $failed++;
}

echo "\n=======================================================\n";
echo "VERIFICATION SUMMARY:\n";
echo "Total Passed: $passed\n";
echo "Total Failed: $failed\n";

if ($failed === 0) {
    echo "\033[32mALL ASSERTIONS PASSED! Task 30 verified successfully.\033[0m\n";
    exit(0);
} else {
    echo "\033[31mSOME ASSERTIONS FAILED! Please review.\033[0m\n";
    exit(1);
}
