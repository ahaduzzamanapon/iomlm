<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Batch;
use App\Models\Course;
use App\Models\AcademicYear;
use App\Models\Student;

echo "=== VERIFICATION TEST: BATCH CODE FORMAT OVERHAUL ===\n\n";

$pass = 0;
$fail = 0;

function assertEqual($actual, $expected, $message) {
    global $pass, $fail;
    if ($actual === $expected) {
        echo " [PASS] {$message} (Result: '{$actual}')\n";
        $pass++;
    } else {
        echo " [FAIL] {$message} (Expected: '{$expected}', Got: '{$actual}')\n";
        $fail++;
    }
}

// 1. Test User Example 1: Alim Preparatory Course (2027, Batch 01) -> APC-2701
$courseAPC = Course::where('name', 'like', '%Alim Preparatory Course%')->first();
if (!$courseAPC) {
    $courseAPC = Course::create(['name' => 'Alim Preparatory Course', 'type' => 'semester']);
}
$ay2027 = AcademicYear::where('name', 'like', '%2027%')->first();
if (!$ay2027) {
    $ay2027 = AcademicYear::create(['name' => 'Academic Year 2027', 'year' => '2027']);
}

$batch22 = Batch::find(22);
$excludeId = $batch22 ? $batch22->id : null;
$code1 = Batch::generateBatchCode($courseAPC, $ay2027, '01', '2027-01-01', $excludeId);
assertEqual($code1, 'APC-2701', 'Example 1: 2027 Alim Preparatory Course 01 batch generates APC-2701');

// 2. Test User Example 2: School Maktab Nazera (2027, Batch 12) -> SMN-2712
$courseSMN = Course::where('name', 'like', '%School Maktab Nazera%')->first();
if (!$courseSMN) {
    $courseSMN = Course::create(['name' => 'School Maktab Nazera', 'type' => 'semester']);
}

$code2 = Batch::generateBatchCode($courseSMN, $ay2027, '12', '2027-01-01', 999999);
assertEqual($code2, 'SMN-2712', 'Example 2: 2027 School Maktab Nazera 12 batch generates SMN-2712');

// 3. Test Course Prefix resolution
assertEqual(Batch::resolveCoursePrefix('Alim Preparatory Course'), 'APC', 'Prefix for Alim Preparatory Course is APC');
assertEqual(Batch::resolveCoursePrefix('School Maktab Nazera'), 'SMN', 'Prefix for School Maktab Nazera is SMN');
assertEqual(Batch::resolveCoursePrefix('School Maktab Nazera (Bangla)'), 'SMN', 'Prefix for School Maktab Nazera (Bangla) ignores parentheses and gives SMN');
assertEqual(Batch::resolveCoursePrefix('School Maktab'), 'SM', 'Prefix for School Maktab is SM');
assertEqual(Batch::resolveCoursePrefix('Ruqyah Nazera Course'), 'RNC', 'Prefix for Ruqyah Nazera Course is RNC');

// 4. Test Academic Year Digits resolution
assertEqual(Batch::resolveAcademicYearDigits($ay2027), '27', 'Academic Year 2027 resolves to 27');
assertEqual(Batch::resolveAcademicYearDigits(null, '2028-06-15'), '28', 'Start date 2028-06-15 resolves to 28');

// 5. Test Batch Number Digits resolution
assertEqual(Batch::resolveBatchNumberDigits('01'), '01', 'Batch name 01 resolves to 01');
assertEqual(Batch::resolveBatchNumberDigits('০১'), '01', 'Bengali batch name ০১ resolves to 01');
assertEqual(Batch::resolveBatchNumberDigits('12'), '12', 'Batch name 12 resolves to 12');
assertEqual(Batch::resolveBatchNumberDigits('১২'), '12', 'Bengali batch name ১২ resolves to 12');
assertEqual(Batch::resolveBatchNumberDigits('Alim 2717'), '17', 'Batch name Alim 2717 resolves to 17');
assertEqual(Batch::resolveBatchNumberDigits('Alim 2819'), '19', 'Batch name Alim 2819 resolves to 19');

// 6. Test Batch in DB (Batch 22 from user screenshot)
$batch22 = Batch::find(22);
if ($batch22) {
    assertEqual($batch22->batch_code, 'APC-2701', 'Batch ID 22 in DB has batch_code APC-2701');
    assertEqual(Student::resolveBatchNumberCode($batch22), '01', 'Student::resolveBatchNumberCode for Batch 22 resolves 01');
    assertEqual(Student::resolveAcademicYearCode($batch22), '27', 'Student::resolveAcademicYearCode for Batch 22 resolves 27');
}

// 7. Test Batch 19, 18, 17 in DB
$batch19 = Batch::find(19);
if ($batch19) {
    assertEqual($batch19->batch_code, 'APC-2819', 'Batch ID 19 in DB has batch_code APC-2819');
}
$batch18 = Batch::find(18);
if ($batch18) {
    assertEqual($batch18->batch_code, 'APC-2718', 'Batch ID 18 in DB has batch_code APC-2718');
}
$batch17 = Batch::find(17);
if ($batch17) {
    assertEqual($batch17->batch_code, 'APC-2717', 'Batch ID 17 in DB has batch_code APC-2717');
}

echo "\nSummary: {$pass} Passed, {$fail} Failed.\n";

if ($fail > 0) {
    exit(1);
}

exit(0);
