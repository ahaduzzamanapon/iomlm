<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "======================================================\n";
echo "VERIFYING TASK 36: SPECIAL COURSES COMMON USER ACCOUNT\n";
echo "======================================================\n";

$assertions = 0;
$passed = 0;

function assertCondition($desc, $cond) {
    global $assertions, $passed;
    $assertions++;
    if ($cond) {
        $passed++;
        echo " [PASS] $desc\n";
    } else {
        echo " [FAIL] $desc\n";
    }
}

// 1. Database Schema
assertCondition(
    "Column 'is_common_account' exists on 'users' table",
    Schema::hasColumn('users', 'is_common_account')
);

assertCondition(
    "Column 'is_common_account' exists on 'students' table",
    Schema::hasColumn('students', 'is_common_account')
);

// 2. Models and Casts
$user = new User();
assertCondition(
    "User model casts 'is_common_account' to boolean",
    isset($user->getCasts()['is_common_account']) && $user->getCasts()['is_common_account'] === 'boolean'
);

$student = new Student();
assertCondition(
    "Student model casts 'is_common_account' to boolean",
    isset($student->getCasts()['is_common_account']) && $student->getCasts()['is_common_account'] === 'boolean'
);

// 3. Test Student::isProfileCompleted() for empty profile with is_common_account = true
$dummyStudent = new Student([
    'name' => 'Special Shared Student',
    'is_common_account' => true,
    'profile_completed_percent' => 0,
]);
assertCondition(
    "Student::isProfileCompleted() returns true for common account even if percent is 0",
    $dummyStudent->isProfileCompleted() === true
);

// 4. Test EnsureProfileCompleted middleware bypass
$middleware = new \App\Http\Middleware\EnsureProfileCompleted();
$commonUser = new User([
    'name' => 'Shared Test User',
    'role' => 'student',
    'is_common_account' => true,
]);
$request = Request::create('/student/dashboard', 'GET');
$request->setUserResolver(fn() => $commonUser);

$passedMiddleware = false;
$response = $middleware->handle($request, function ($req) use (&$passedMiddleware) {
    $passedMiddleware = true;
    return new \Illuminate\Http\Response('OK');
});

assertCondition(
    "EnsureProfileCompleted middleware allows common user to pass without redirection",
    $passedMiddleware === true
);

// 5. Test Student ProfileController::update blocking
$dbUser = User::create([
    'name' => 'Common Student User',
    'email' => 'common_test_' . uniqid() . '@example.com',
    'password' => bcrypt('secret123'),
    'role' => 'student',
    'is_common_account' => true,
]);

$dbStudent = Student::create([
    'user_id' => $dbUser->id,
    'name' => 'Common Test Student',
    'email' => $dbUser->email,
    'phone' => '01711' . rand(100000, 999999),
    'is_common_account' => true,
    'status' => 'ACTIVE',
]);

Auth::login($dbUser);

$profileController = new \App\Http\Controllers\Student\ProfileController();
$updateReq = Request::create('/student/profile', 'POST', [
    'nationality' => 'Bangladeshi',
]);
$updateReq->setUserResolver(fn() => $dbUser);

$updateResp = $profileController->update($updateReq);
assertCondition(
    "ProfileController::update redirects with error message for common account",
    $updateResp->isRedirect() && session()->has('error')
);

// 6. Test JSON request blocking
$updateReqJson = Request::create('/student/profile', 'POST', [
    'nationality' => 'Bangladeshi',
], [], [], ['HTTP_ACCEPT' => 'application/json']);
$updateReqJson->setUserResolver(fn() => $dbUser);

$updateRespJson = $profileController->update($updateReqJson);
assertCondition(
    "ProfileController::update returns 403 JSON response when requested by common account",
    $updateRespJson->getStatusCode() === 403
);

// 7. Test CourseTransferController::store blocking
$courseTransferController = new \App\Http\Controllers\Student\CourseTransferController();
$transferReq = Request::create('/student/course-transfers', 'POST', [
    'to_course_id' => 1,
]);
$transferReq->setUserResolver(fn() => $dbUser);
$transferResp = $courseTransferController->store($transferReq);

assertCondition(
    "CourseTransferController::store blocks application for common account",
    $transferResp->isRedirect() && session()->has('error')
);

// 8. Test ReadmissionController::store blocking
$readmissionController = new \App\Http\Controllers\Student\ReadmissionController();
$readmissionReq = Request::create('/student/readmissions', 'POST', [
    'to_batch_id' => 1,
]);
$readmissionReq->setUserResolver(fn() => $dbUser);
$readmissionResp = $readmissionController->store($readmissionReq);

assertCondition(
    "ReadmissionController::store blocks application for common account",
    $readmissionResp->isRedirect() && session()->has('error')
);

// 9. Test Admin StudentController::update sets is_common_account
$adminController = new \App\Http\Controllers\Admin\StudentController();
$adminUser = User::where('role', 'admin')->orWhere('role', 'super_admin')->first();
if (!$adminUser) {
    $adminUser = User::create([
        'name' => 'Super Admin',
        'email' => 'admin_test_' . uniqid() . '@example.com',
        'password' => bcrypt('secret123'),
        'role' => 'admin',
    ]);
}
Auth::login($adminUser);

$adminUpdateReq = Request::create("/admin/students/{$dbStudent->id}", 'POST', [
    '_method' => 'PUT',
    'name' => 'Updated Common Student Name',
    'phone' => $dbStudent->phone,
    'email' => $dbStudent->email,
    'status' => 'ACTIVE',
    'is_common_account' => '1',
]);

$adminResp = $adminController->update($adminUpdateReq, $dbStudent);
$dbStudent->refresh();
$dbUser->refresh();

assertCondition(
    "Admin StudentController::update successfully updates is_common_account on student",
    $dbStudent->is_common_account === true
);

assertCondition(
    "Admin StudentController::update syncs is_common_account on user model",
    $dbUser->is_common_account === true
);

// 10. Check blade views for common account markup
$showView = file_get_contents(resource_path('views/admin/students/show.blade.php'));
assertCondition(
    "admin/students/show.blade.php has 'is_common_account' badge",
    str_contains($showView, '$student->is_common_account') && str_contains($showView, 'কমন শেয়ার্ড অ্যাকাউন্ট')
);

$editView = file_get_contents(resource_path('views/admin/students/edit.blade.php'));
assertCondition(
    "admin/students/edit.blade.php has 'is_common_account' checkbox",
    str_contains($editView, 'name="is_common_account"') && str_contains($editView, 'স্পেশাল কোর্স কমন/শেয়ার্ড অ্যাকাউন্ট')
);

$indexView = file_get_contents(resource_path('views/admin/students/index.blade.php'));
assertCondition(
    "admin/students/index.blade.php has 'is_common_account' indicator",
    str_contains($indexView, '$st->is_common_account')
);

$studentProfileView = file_get_contents(resource_path('views/student/profile/index.blade.php'));
assertCondition(
    "student/profile/index.blade.php displays common account warning and disabled lock button",
    str_contains($studentProfileView, 'কমন / শেয়ার্ড স্টুডেন্ট অ্যাকাউন্ট') && str_contains($studentProfileView, 'কমন অ্যাকাউন্টে প্রোফাইল তথ্য লক করা')
);

// Clean up test records
$dbStudent->forceDelete();
$dbUser->forceDelete();

echo "======================================================\n";
echo "SUMMARY: $passed / $assertions assertions passed.\n";
echo "======================================================\n";

if ($passed === $assertions) {
    exit(0);
} else {
    exit(1);
}
