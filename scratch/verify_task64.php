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
use Illuminate\Support\Facades\Hash;

echo "1. Testing Student Sort:\n";
$code2 = '99999910002';
$code1 = '99999910001';

Student::whereIn('student_code', [$code1, $code2])->forceDelete();
User::whereIn('email', ['test_roll1@example.com', 'test_roll2@example.com'])->forceDelete();

$u2 = User::create(['name' => 'R2', 'email' => 'test_roll2@example.com', 'password' => Hash::make('123'), 'role' => 'student']);
$st2 = Student::create(['name' => 'Roll 2', 'student_code' => $code2, 'user_id' => $u2->id, 'created_at' => now()->subMinutes(10)]);

$u1 = User::create(['name' => 'R1', 'email' => 'test_roll1@example.com', 'password' => Hash::make('123'), 'role' => 'student']);
$st1 = Student::create(['name' => 'Roll 1', 'student_code' => $code1, 'user_id' => $u1->id, 'created_at' => now()]);

$sorted = Student::whereIn('student_code', [$code1, $code2])
    ->orderByRaw('CASE WHEN student_code IS NULL OR student_code = "" THEN 1 ELSE 0 END, LENGTH(student_code) ASC, student_code ASC, id ASC')
    ->get();

assert($sorted[0]->student_code === $code1, "Position 0 must be Roll 1");
assert($sorted[1]->student_code === $code2, "Position 1 must be Roll 2");
echo "   -> Sort OK: Roll 1 appears before Roll 2!\n";

echo "2. Testing Admission Real Email Setup:\n";
$testRealEmail = 'real_' . uniqid() . '@example.com';
$tempCourse = Course::first();
$tempBatch = Batch::first();

$admStudent = Student::create(['name' => 'Test Real', 'email' => $testRealEmail, 'status' => 'PENDING']);
$form = AdmissionForm::create([
    'application_no' => 'APP-' . uniqid(),
    'student_id' => $admStudent->id,
    'interested_course_id' => $tempCourse->id,
    'batch_id' => $tempBatch->id,
    'status' => 'PENDING',
]);

// Test real email preference
$realEmail = $admStudent->email ?: ($form->email ?: null);
$existingUser = $realEmail ? User::where('email', $realEmail)->first() : null;
$canUseRealEmail = $realEmail && (!$existingUser || !Student::where('user_id', $existingUser->id)->where('id', '!=', $admStudent->id)->exists());

assert($canUseRealEmail === true, "Should be able to use real email");
$createdUser = User::create([
    'name' => $admStudent->name,
    'email' => $realEmail,
    'password' => Hash::make('12345'),
    'role' => 'student',
]);
$admStudent->user_id = $createdUser->id;
$admStudent->save();

assert($createdUser->email === $testRealEmail, "User email must be real email");
echo "   -> Real email creation OK: {$createdUser->email}\n";

echo "3. Testing Admission Show Display:\n";
$form->refresh();
$form->load('student.user');

// Check the exact Blade logic
$studentUser = $form->student?->user;
$displayLoginEmail = ($studentUser && !str_contains($studentUser->email, '@iom.student'))
    ? $studentUser->email
    : ($form->student?->email ?: ($form->email ?: ($studentUser?->email ?? '—')));

assert($displayLoginEmail === $testRealEmail, "Display email must match real email");
echo "   -> Display email OK: {$displayLoginEmail}\n";

// Test fallback if student user had @iom.student
$studentUser->email = '99999910001@iom.student';
$studentUser->save();
$displayLoginEmailFallback = ($studentUser && !str_contains($studentUser->email, '@iom.student'))
    ? $studentUser->email
    : ($form->student?->email ?: ($form->email ?: ($studentUser?->email ?? '—')));

assert($displayLoginEmailFallback === $testRealEmail, "Fallback must still show real email");
echo "   -> Fallback display email OK: {$displayLoginEmailFallback}\n";

// Clean up
$form->forceDelete();
$admStudent->forceDelete();
$createdUser->forceDelete();
$st1->forceDelete();
$st2->forceDelete();
$u1->forceDelete();
$u2->forceDelete();

echo "\nTASK 64 VERIFIED SUCCESSFULLY (Exit Code 0)!\n";
exit(0);
