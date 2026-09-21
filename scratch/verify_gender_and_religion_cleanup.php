<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "======================================================\n";
echo "VERIFYING TASK 37: GENDER SIMPLIFICATION & RELIGION CLEANUP\n";
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

// 1. Student model profileFieldsDefinition does not contain religion
$fields = Student::profileFieldsDefinition();
assertCondition(
    "Student::profileFieldsDefinition() does not contain 'religion'",
    !array_key_exists('religion', $fields)
);

// 2. resources/views/student/profile/index.blade.php
$studentProfileView = file_get_contents(resource_path('views/student/profile/index.blade.php'));
assertCondition(
    "student/profile/index.blade.php does not contain name=\"religion\"",
    !str_contains($studentProfileView, 'name="religion"')
);
assertCondition(
    "student/profile/index.blade.php contains name=\"nationality\"",
    str_contains($studentProfileView, 'name="nationality"')
);

// 3. resources/views/public/poor_fund.blade.php
$poorFundView = file_get_contents(resource_path('views/public/poor_fund.blade.php'));
assertCondition(
    "public/poor_fund.blade.php gender select contains Male and Female",
    str_contains($poorFundView, 'value="Male"') && str_contains($poorFundView, 'value="Female"')
);
preg_match('/<select name="gender"[^>]*>(.*?)<\/select>/s', $poorFundView, $poorFundGenderMatch);
$poorFundGenderBlock = $poorFundGenderMatch[1] ?? '';
assertCondition(
    "public/poor_fund.blade.php does NOT contain value=\"Other\" for gender",
    !str_contains($poorFundGenderBlock, 'value="Other"')
);

// 4. resources/views/admin/students/index.blade.php
$studentsIndexView = file_get_contents(resource_path('views/admin/students/index.blade.php'));
assertCondition(
    "admin/students/index.blade.php gender filter contains MALE and FEMALE",
    str_contains($studentsIndexView, 'value="MALE"') && str_contains($studentsIndexView, 'value="FEMALE"')
);
assertCondition(
    "admin/students/index.blade.php does NOT contain OTHER in gender filter",
    !preg_match('/<select name="gender"[^>]*>[\s\S]*?<option[^>]*value="OTHER"[^>]*>[\s\S]*?<\/select>/i', $studentsIndexView)
);

// 5. resources/views/admin/teachers/index.blade.php
$teachersIndexView = file_get_contents(resource_path('views/admin/teachers/index.blade.php'));
assertCondition(
    "admin/teachers/index.blade.php Add Teacher modal does not contain 'Other' gender",
    !preg_match('/<select name="gender" class="form-control">[\s\S]*?<option value="Other">[\s\S]*?<\/select>/i', $teachersIndexView)
);
assertCondition(
    "admin/teachers/index.blade.php Edit Teacher modal does not contain 'Other' gender",
    !preg_match('/<select name="gender" id="et_gender"[^>]*>[\s\S]*?<option value="Other">[\s\S]*?<\/select>/i', $teachersIndexView)
);
assertCondition(
    "admin/teachers/index.blade.php does not contain name=\"religion\"",
    !str_contains($teachersIndexView, 'name="religion"')
);
assertCondition(
    "admin/teachers/index.blade.php does not contain id=\"et_religion\"",
    !str_contains($teachersIndexView, 'id="et_religion"')
);

// 6. resources/views/apply/index.blade.php
$applyIndexView = file_get_contents(resource_path('views/apply/index.blade.php'));
assertCondition(
    "apply/index.blade.php gender offers only Male and Female",
    str_contains($applyIndexView, 'value="Male"') &&
    str_contains($applyIndexView, 'value="Female"') &&
    !str_contains($applyIndexView, 'value="Other"')
);
assertCondition(
    "apply/index.blade.php does not contain name=\"religion\"",
    !str_contains($applyIndexView, 'name="religion"')
);

// 7. resources/views/admin/admissions/create.blade.php
$admissionCreateView = file_get_contents(resource_path('views/admin/admissions/create.blade.php'));
assertCondition(
    "admin/admissions/create.blade.php gender offers only Male and Female",
    str_contains($admissionCreateView, 'value="Male"') &&
    str_contains($admissionCreateView, 'value="Female"') &&
    !str_contains($admissionCreateView, 'value="Other"')
);

// 8. resources/views/admin/admissions/show.blade.php
$admissionShowView = file_get_contents(resource_path('views/admin/admissions/show.blade.php'));
assertCondition(
    "admin/admissions/show.blade.php does not display 'Religion / Nationality'",
    !str_contains($admissionShowView, 'Religion / Nationality:')
);
assertCondition(
    "admin/admissions/show.blade.php displays Nationality",
    str_contains($admissionShowView, 'Nationality (জাতীয়তা):')
);

// 9. ProfileController update execution without religion
$user = User::create([
    'name' => 'Gender Test User',
    'email' => 'gendertest_' . uniqid() . '@example.com',
    'password' => bcrypt('secret123'),
    'role' => 'student',
]);
$student = Student::create([
    'user_id' => $user->id,
    'name' => 'Gender Test Student',
    'email' => $user->email,
    'phone' => '01888' . rand(100000, 999999),
    'gender' => 'MALE',
    'status' => 'ACTIVE',
]);

Auth::login($user);
$controller = new \App\Http\Controllers\Student\ProfileController();
$req = Request::create('/student/profile', 'POST', [
    'nationality' => 'Bangladeshi',
    'national_id' => '19901234567890',
]);
$req->setUserResolver(fn() => $user);

$resp = $controller->update($req);
$student->refresh();

assertCondition(
    "Profile update saves nationality successfully without religion field",
    $student->nationality === 'Bangladeshi' && $student->national_id === '19901234567890'
);

$student->forceDelete();
$user->forceDelete();

echo "======================================================\n";
echo "SUMMARY: $passed / $assertions assertions passed.\n";
echo "======================================================\n";

if ($passed === $assertions) {
    exit(0);
} else {
    exit(1);
}
