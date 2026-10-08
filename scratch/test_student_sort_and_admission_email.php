<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\Batch;
use App\Models\Course;
use App\Models\AdmissionForm;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "=======================================================\n";
echo "=== TESTING 1: Student List Ascending Serial Order ===\n";
echo "=======================================================\n";

// 1. Create two temporary students where Roll 1 is created AFTER Roll 2
$tempCourse = Course::first();
$tempBatch = Batch::first();

// Create Roll 2 first
$code2 = '99999910002';
$code1 = '99999910001';

// Clean up any leftovers
Student::whereIn('student_code', [$code1, $code2])->forceDelete();
User::whereIn('email', ['test_roll1@example.com', 'test_roll2@example.com', $code1 . '@iom.student', $code2 . '@iom.student'])->forceDelete();

$user2 = User::create([
    'name' => 'Test Roll 2',
    'email' => 'test_roll2@example.com',
    'password' => Hash::make('password123'),
    'role' => 'student',
]);

$st2 = Student::create([
    'name' => 'Roll 2 Student',
    'student_code' => $code2,
    'user_id' => $user2->id,
    'email' => 'test_roll2@example.com',
    'phone' => '01700000002',
    'status' => 'ACTIVE',
    'created_at' => now()->subMinutes(10), // Created earlier
]);

sleep(1);

$user1 = User::create([
    'name' => 'Test Roll 1',
    'email' => 'test_roll1@example.com',
    'password' => Hash::make('password123'),
    'role' => 'student',
]);

$st1 = Student::create([
    'name' => 'Roll 1 Student',
    'student_code' => $code1,
    'user_id' => $user1->id,
    'email' => 'test_roll1@example.com',
    'phone' => '01700000001',
    'status' => 'ACTIVE',
    'created_at' => now(), // Created later
]);

// Test query sorting
$sortedStudents = Student::whereIn('student_code', [$code1, $code2])
    ->orderByRaw('CASE WHEN student_code IS NULL OR student_code = "" THEN 1 ELSE 0 END, LENGTH(student_code) ASC, student_code ASC, id ASC')
    ->get();

echo "Queried count: " . $sortedStudents->count() . "\n";
echo "Position 0: " . $sortedStudents[0]->student_code . " (" . $sortedStudents[0]->name . ")\n";
echo "Position 1: " . $sortedStudents[1]->student_code . " (" . $sortedStudents[1]->name . ")\n";

if ($sortedStudents[0]->student_code !== $code1) {
    echo "FAILED: Expected Roll 1 ($code1) at position 0, but got " . $sortedStudents[0]->student_code . "\n";
    exit(1);
}
if ($sortedStudents[1]->student_code !== $code2) {
    echo "FAILED: Expected Roll 2 ($code2) at position 1, but got " . $sortedStudents[1]->student_code . "\n";
    exit(1);
}
echo "SUCCESS: Roll 1 is placed at the top (ascending order verified)!\n\n";

echo "=======================================================\n";
echo "=== TESTING 2: Admission Approval Real Email Setup ===\n";
echo "=======================================================\n";

$testRealEmail = 'new_student_unique_' . uniqid() . '@gmail.com';
$adminUser = User::where('role', 'admin')->first() ?: User::first();

// Create a pending admission with real email
$admStudent = Student::create([
    'name' => 'Pending Verification Student',
    'email' => $testRealEmail,
    'phone' => '01711223344',
    'status' => 'PENDING',
]);

$form = AdmissionForm::create([
    'application_no' => 'APP-TEST-' . uniqid(),
    'student_id' => $admStudent->id,
    'interested_course_id' => $tempCourse->id,
    'batch_id' => $tempBatch->id,
    'status' => 'PENDING',
    'gender' => 'Male',
]);

// Simulate AdmissionController approval logic
$request = new \Illuminate\Http\Request([
    'approved_batch_id' => $tempBatch->id,
    'custom_password' => 'pass1234',
    'send_email' => false,
    'send_sms' => false,
]);

// Run approval logic matching controller
$cleanCode = preg_replace('/\D/', '', (string)($admStudent->student_code ?? ''));
$expectedPrefix = Student::resolveAcademicYearCode($tempBatch)
    . Student::resolveBatchNumberCode($tempBatch)
    . Student::resolveCourseCode($tempCourse, $tempBatch->course_id)
    . Student::resolveGenderCode('Male');

if (empty($admStudent->student_code) || !str_starts_with($admStudent->student_code, $expectedPrefix)) {
    $admStudent->student_code = Student::generateStudentCode($tempBatch, $tempCourse, 'Male');
}
$admStudent->status = 'ACTIVE';
$admStudent->save();

$realEmail = $admStudent->email;
$existingUserWithRealEmail = $realEmail ? User::where('email', $realEmail)->first() : null;
$canUseRealEmail = $realEmail && (!$existingUserWithRealEmail || !Student::where('user_id', $existingUserWithRealEmail->id)->where('id', '!=', $admStudent->id)->exists());

if ($canUseRealEmail) {
    $loginEmail = $realEmail;
    $createdUser = $existingUserWithRealEmail ?: User::create([
        'name' => $admStudent->name,
        'email' => $loginEmail,
        'password' => Hash::make('pass1234'),
        'role' => 'student',
    ]);
} else {
    $loginEmail = $admStudent->student_code . '@iom.student';
    $createdUser = User::create([
        'name' => $admStudent->name,
        'email' => $loginEmail,
        'password' => Hash::make('pass1234'),
        'role' => 'student',
    ]);
}
$admStudent->user_id = $createdUser->id;
$admStudent->temporary_password = 'pass1234';
$admStudent->save();

$form->update([
    'status' => 'APPROVED',
    'reviewed_by' => $adminUser->id,
    'reviewed_at' => now(),
]);

echo "Created User ID: {$createdUser->id}, Email: {$createdUser->email}\n";
if ($createdUser->email !== $testRealEmail) {
    echo "FAILED: Expected user email to be {$testRealEmail}, got {$createdUser->email}\n";
    exit(1);
}
echo "SUCCESS: Student user account created with real email ({$testRealEmail})!\n\n";

echo "=======================================================\n";
echo "=== TESTING 3: Admission Show View Real Email Display ===\n";
echo "=======================================================\n";

// Refresh form with relations
$form->refresh();
$form->load(['student.user', 'reviewer']);

$html = view('admin.admissions.show', [
    'admission' => $form,
    'student' => $form->student,
    'courses' => Course::all(),
    'batches' => Batch::all(),
    'semesters' => collect(),
    'bloodGroups' => collect(),
    'districts' => collect(),
    'divisions' => collect(),
    'sessions' => collect(),
    'emailTemplates' => collect(),
    'accountingEntries' => collect(),
    'invoices' => collect(),
])->render();

if (!str_contains($html, $testRealEmail)) {
    echo "FAILED: Rendered admin.admissions.show does NOT contain real email {$testRealEmail}\n";
    exit(1);
}
echo "SUCCESS: Rendered HTML contains real email ({$testRealEmail}) in Student Login Account section!\n";

// Also test fallback display if user had placeholder
$createdUser->email = $admStudent->student_code . '@iom.student';
$createdUser->save();
$form->refresh();
$form->load(['student.user', 'reviewer']);

$htmlFallback = view('admin.admissions.show', [
    'admission' => $form,
    'student' => $form->student,
    'courses' => Course::all(),
    'batches' => Batch::all(),
    'semesters' => collect(),
    'bloodGroups' => collect(),
    'districts' => collect(),
    'divisions' => collect(),
    'sessions' => collect(),
    'emailTemplates' => collect(),
    'accountingEntries' => collect(),
    'invoices' => collect(),
])->render();

if (!str_contains($htmlFallback, $testRealEmail)) {
    echo "FAILED: Fallback display in admin.admissions.show did NOT show student real email {$testRealEmail}\n";
    exit(1);
}
echo "SUCCESS: Fallback display correctly shows student real email ({$testRealEmail}) even if user.email was @iom.student!\n\n";

// CLEANUP
echo "Cleaning up temporary test records...\n";
$form->forceDelete();
$admStudent->forceDelete();
$createdUser->forceDelete();
$st1->forceDelete();
$st2->forceDelete();
$user1->forceDelete();
$user2->forceDelete();
echo "Cleanup completed successfully!\n";
echo "ALL TESTS PASSED WITH EXIT CODE 0!\n";
exit(0);
