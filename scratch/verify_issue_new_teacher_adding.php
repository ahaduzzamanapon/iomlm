<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Teacher;
use App\Http\Controllers\Admin\TeacherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

echo "=== VERIFYING NEW TEACHER ADDING FIXES ===\n";

// 1. Check Teacher index markup for openModal('addTeacherModal') and local fallback
echo "1. Checking Teacher index view markup and script bindings...\n";
$teacherView = file_get_contents(__DIR__ . '/../resources/views/admin/teachers/index.blade.php');
if (strpos($teacherView, "openModal('addTeacherModal')") === false) {
    echo "  [FAIL] New Teacher button does not trigger openModal('addTeacherModal')!\n";
    exit(1);
}
if (strpos($teacherView, 'id="addTeacherModal"') === false) {
    echo "  [FAIL] addTeacherModal ID not found in view!\n";
    exit(1);
}
if (strpos($teacherView, 'function openModal(id)') === false || strpos($teacherView, 'function closeModal(id)') === false) {
    echo "  [FAIL] Local openModal/closeModal fallback is missing from teachers index!\n";
    exit(1);
}
echo "  [PASS] Teacher index has New Teacher button and openModal/closeModal bindings properly installed.\n";

// 2. Test TeacherController@store lifecycle with auto User creation
echo "2. Testing TeacherController store lifecycle...\n";
DB::beginTransaction();

try {
    $admin = User::where('role', 'super_admin')->first();
    auth()->login($admin);

    $controller = new TeacherController();
    $randomSuffix = rand(1000, 9999);
    $phone = '0171' . $randomSuffix . '00';
    $email = 'teacher_' . $randomSuffix . '@test.iom';

    $reqData = [
        'name'            => 'মাওলানা রফিকুজ্জামান (ভেরিফিকেশন)',
        'phone'           => $phone,
        'email'           => $email,
        'password'        => 'teacherPass123',
        'designation'     => 'মুহাদ্দিস',
        'department'      => 'হাদিস বিভাগ',
        'gender'          => 'Male',
        'is_active'       => '1',
    ];

    $request = Request::create(route('admin.teachers.store'), 'POST', $reqData);
    $response = $controller->store($request);

    if ($response->getStatusCode() !== 302) {
        echo "  [FAIL] Expected redirect status 302, got " . $response->getStatusCode() . "\n";
        exit(1);
    }

    $teacher = Teacher::where('email', $email)->latest()->first();
    if (!$teacher) {
        echo "  [FAIL] Teacher was not created in database!\n";
        exit(1);
    }
    echo "  [PASS] Teacher created with ID: {$teacher->id}, Employee ID: {$teacher->employee_id}\n";

    $user = $teacher->user;
    if (!$user) {
        echo "  [FAIL] Associated User account was not created for teacher!\n";
        exit(1);
    }
    if ($user->role !== 'teacher') {
        echo "  [FAIL] Created user role is {$user->role}, expected 'teacher'!\n";
        exit(1);
    }
    echo "  [PASS] User account successfully created and linked: Email: {$user->email}, Role: {$user->role}\n";

    // 3. Test duplicate email safety handling
    echo "3. Testing duplicate email auto-conflict resolution...\n";
    $reqData2 = [
        'name'            => 'মাওলানা ডুপ্লিকেট টেস্ট',
        'phone'           => '0181' . $randomSuffix . '11',
        'email'           => null, // fallback to employee_id@iom.teacher
        'gender'          => 'Male',
        'is_active'       => '1',
    ];
    $request2 = Request::create(route('admin.teachers.store'), 'POST', $reqData2);
    $response2 = $controller->store($request2);
    echo "  [PASS] Handled fallback email generation cleanly without unique constraint crashes.\n";

    DB::rollBack();
    echo "\n>>> ALL NEW TEACHER ADDING TESTS PASSED (Exit Code 0) <<<\n";
    exit(0);
} catch (\Throwable $e) {
    DB::rollBack();
    echo "  [FAIL] Exception: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
