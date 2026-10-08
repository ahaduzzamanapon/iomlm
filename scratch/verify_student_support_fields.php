<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\User;
use App\Models\SupportDepartment;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

echo "--- VERIFYING TASK 66: STUDENT SUPPORT PORTAL EMAIL & ROLL NO ---\n\n";

// 1. Setup a test student with dummy login email and real email
$testEmail = 'test_student_support_' . Str::random(6) . '@gmail.com';
$dummyLogin = 'dummy_' . Str::random(6) . '@iom.student';
$testCode = '27-01-01-1-0999';
$cleanCode = '27010110999';

$user = User::create([
    'name' => 'Support Test Student',
    'email' => $dummyLogin,
    'password' => bcrypt('secret123'),
    'role' => 'student',
]);

$student = Student::create([
    'user_id' => $user->id,
    'student_code' => $testCode,
    'name' => 'Support Test Student',
    'email' => $testEmail,
    'phone' => '01700000999',
    'gender' => 'MALE',
    'status' => 'ACTIVE',
]);

echo "1. Created test student:\n";
echo "   User email: {$user->email}\n";
echo "   Student real email: {$student->email}\n";
echo "   Student code: {$student->student_code}\n\n";

// 2. Test Student Support Page render
echo "2. Testing StudentSupportController::index view render:\n";
Auth::login($user);

$controller = app(\App\Http\Controllers\Student\StudentSupportController::class);
$response = $controller->index();
$renderedHtml = $response->render();

// Check email input value in HTML
preg_match('/<input[^>]*name=["\']email["\'][^>]*value=["\']([^"\']*)["\']/', $renderedHtml, $emailMatches);
$renderedEmail = $emailMatches[1] ?? '';
echo "   Rendered email in form: '{$renderedEmail}'\n";
if ($renderedEmail !== $testEmail) {
    echo "   [FAIL] Expected email to be '{$testEmail}', but got '{$renderedEmail}'\n";
    exit(1);
}
if (strpos($renderedEmail, '@iom.student') !== false) {
    echo "   [FAIL] Internal dummy email was rendered in form!\n";
    exit(1);
}
echo "   [PASS] Student's real email rendered correctly in form!\n";

// Check student_id input value in HTML
preg_match('/<input[^>]*name=["\']student_id["\'][^>]*value=["\']([^"\']*)["\']/', $renderedHtml, $idMatches);
$renderedId = $idMatches[1] ?? '';
echo "   Rendered student_id in form: '{$renderedId}'\n";
if ($renderedId !== $cleanCode) {
    echo "   [FAIL] Expected student_id to be '{$cleanCode}', but got '{$renderedId}'\n";
    exit(1);
}
echo "   [PASS] Student's clean student_code ID rendered correctly in form!\n\n";

// 3. Test submitting a ticket
echo "3. Testing ticket creation and display in student ticket list:\n";
$dept = SupportDepartment::first();
if (!$dept) {
    $dept = SupportDepartment::create(['name' => 'General Support', 'is_active' => true]);
}

$requestData = [
    'department_id' => $dept->id,
    'name' => $student->name,
    'email' => $student->email,
    'phone' => $student->phone,
    'gender' => 'MALE',
    'student_id' => $cleanCode,
    'subject' => 'Need help with course video playback',
    'problem_details' => 'I cannot load the class recorded lectures on mobile.',
];

$onlineController = app(\App\Http\Controllers\Public\OnlineSupportController::class);
$storeRequest = \Illuminate\Http\Request::create('/online-support', 'POST', $requestData);
$storeResponse = $onlineController->store($storeRequest);

$createdTicket = SupportTicket::where('student_id', $cleanCode)->latest()->first();
if (!$createdTicket) {
    echo "   [FAIL] Support ticket was not created!\n";
    exit(1);
}
echo "   [PASS] Ticket created: #{$createdTicket->ticket_no} with email={$createdTicket->email} and student_id={$createdTicket->student_id}\n";

// Test that ticket shows up in Student Support ticket list
$responseAfter = $controller->index();
$tickets = $responseAfter->getData()['myTickets'];
$ticketFound = $tickets->contains('id', $createdTicket->id);
if (!$ticketFound) {
    echo "   [FAIL] Created ticket not found in student's myTickets collection!\n";
    exit(1);
}
echo "   [PASS] Ticket is visible in student's support history table!\n\n";

// Cleanup
$createdTicket->messages()->delete();
$createdTicket->delete();
$student->delete();
$user->delete();

echo "ALL TASK 66 CHECKS PASSED SUCCESSFULLY (Exit Code 0)!\n";
exit(0);
