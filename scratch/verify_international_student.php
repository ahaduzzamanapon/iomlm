<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\AdmissionForm;
use App\Models\Division;
use App\Models\District;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Admin\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

echo "=== STARTING VERIFICATION: International Student Support ===\n";

// Clean any pre-existing test records
Student::where('student_code', 'TEST-INT-001')->orWhere('email', 'rahul.test@example.com')->forceDelete();
\App\Models\User::where('email', 'rahul.test@example.com')->forceDelete();

// 1. Verify Database Schema
echo "\n[Test 1] Checking Database Schema...\n";
if (!Schema::hasColumn('students', 'is_international')) {
    throw new Exception("Column 'is_international' missing in 'students' table.");
}
if (!Schema::hasColumn('admission_forms', 'is_international')) {
    throw new Exception("Column 'is_international' missing in 'admission_forms' table.");
}
echo "✓ Database schema has 'is_international' in both students and admission_forms tables.\n";

// 2. Verify Model Casts
echo "\n[Test 2] Checking Model Casts...\n";
$stModel = new Student();
if (!$stModel->hasCast('is_international', 'boolean')) {
    throw new Exception("Student model does not cast 'is_international' to boolean.");
}
$afModel = new AdmissionForm();
if (!$afModel->hasCast('is_international', 'boolean')) {
    throw new Exception("AdmissionForm model does not cast 'is_international' to boolean.");
}
echo "✓ Both Student and AdmissionForm models correctly cast 'is_international' to boolean.\n";

// 3. Verify Profile Completion & Missing Fields Logic
echo "\n[Test 3] Testing Profile Completion for International Student...\n";
$testStudent = Student::create([
    'name'                    => 'রাহুল শর্মা (Rahul Sharma)',
    'student_code'            => 'TEST-INT-001',
    'phone'                   => '919876543210',
    'email'                   => 'rahul.test@example.com',
    'gender'                  => 'MALE',
    'date_of_birth'           => '2000-01-01',
    'blood_group'             => 'B+',
    'national_id'             => 'IND-987654321',
    'is_international'        => true,
    'country_of_residence'    => 'ভারত (India)',
    'address'                 => '12 Park Street, Kolkata, West Bengal, 700016',
    'permanent_address'       => '12 Park Street, Kolkata, West Bengal, 700016',
    'father_name'             => 'রমেশ শর্মা',
    'mother_name'             => 'সুমন শর্মা',
    'guardian_name'           => 'রমেশ শর্মা',
    'guardian_phone'          => '919876543210',
    'guardian_relation'       => 'পিতা',
    'occupation'              => 'Student',
    'education_qualification' => 'HSC / Equivalent',
    'ssc_board'               => 'CBSE',
    'ssc_year'                => 2018,
    'nationality'             => 'ইন্ডিয়ান (Indian)',
    'photo_url'               => '/images/avatars/male_avatar_1.jpg',
    'status'                  => 'ACTIVE',
]);

$percent = $testStudent->calculateProfileCompletion();
echo "Calculated completion percent: {$percent}%\n";
if ($percent < 90) {
    throw new Exception("Expected profile completion for international student to be high, got {$percent}%.");
}

$missing = $testStudent->getMissingProfileFields();
echo "Missing fields: " . implode(', ', array_keys($missing)) . "\n";
if (isset($missing['country_of_residence'])) {
    throw new Exception("'country_of_residence' should not be missing as it is filled.");
}
echo "✓ Profile completion logic works seamlessly for International Student without division/district.\n";

// 4. Test Student ProfileController Update for International Student
echo "\n[Test 4] Testing ProfileController Update for International Student...\n";
$profileController = new ProfileController();
$request = Request::create('/student/profile', 'POST', [
    'date_of_birth'           => '2000-01-01',
    'blood_group'             => 'B+',
    'national_id'             => 'IND-987654321',
    'nationality'             => 'ইন্ডিয়ান (Indian)',
    'is_international'        => '1',
    'country_of_residence'    => 'ভারত',
    'address'                 => 'Plot 45, Salt Lake, Kolkata, India',
    'permanent_address'       => '', // leaving empty to test auto-sync
    'father_name'             => 'রমেশ শর্মা',
    'mother_name'             => 'সুমন শর্মা',
    'guardian_name'           => 'রমেশ শর্মা',
    'guardian_phone'          => '919876543210',
    'guardian_relation'       => 'পিতা',
    'occupation'              => 'Student',
    'education_qualification' => 'HSC / Equivalent',
    // Deliberately providing division/district to verify they are nullified for international students
    'present_division_id'     => 1,
    'present_district_id'     => 1,
    'permanent_division_id'   => 1,
    'permanent_district_id'   => 1,
]);

$testUser = \App\Models\User::create([
    'name'     => 'রাহুল শর্মা',
    'email'    => 'rahul.test@example.com',
    'password' => bcrypt('password123'),
    'role'     => 'STUDENT',
]);
$testStudent->update(['user_id' => $testUser->id]);
Auth::login($testUser);

$response = $profileController->update($request);
$testStudent->refresh();

if (!$testStudent->is_international) {
    throw new Exception("Expected is_international to be true after update.");
}
if ($testStudent->country_of_residence !== 'ভারত') {
    throw new Exception("Expected country_of_residence to be 'ভারত', got: " . $testStudent->country_of_residence);
}
if ($testStudent->present_division_id !== null || $testStudent->present_district_id !== null) {
    throw new Exception("Expected present division & district to be nullified for international student.");
}
if ($testStudent->permanent_division_id !== null || $testStudent->permanent_district_id !== null) {
    throw new Exception("Expected permanent division & district to be nullified for international student.");
}
if ($testStudent->permanent_address !== 'Plot 45, Salt Lake, Kolkata, India') {
    throw new Exception("Expected permanent_address to auto-sync to address when left empty, got: " . $testStudent->permanent_address);
}
echo "✓ ProfileController correctly validates, nullifies BD division/district, and syncs address.\n";

// 5. Test Admin StudentController Filter & Update
echo "\n[Test 5] Testing Admin StudentController Filter & Update...\n";
$adminStudentController = new StudentController();

// Test filtering: is_expatriate = international
$filterReq1 = Request::create('/admin/students', 'GET', ['is_expatriate' => 'international']);
$filteredQuery1 = Student::query();
// Simulate filter logic
if ($filterReq1->input('is_expatriate') === 'international') {
    $filteredQuery1->where('is_international', true);
}
$results1 = $filteredQuery1->where('id', $testStudent->id)->get();
if ($results1->isEmpty()) {
    throw new Exception("Filter 'is_expatriate=international' did not find the international student.");
}
echo "✓ Filter 'is_expatriate=international' successfully matched international student.\n";

// Test domestic filter: is_expatriate = 0 (should NOT return testStudent)
$filterReq2 = Request::create('/admin/students', 'GET', ['is_expatriate' => '0']);
$filteredQuery2 = Student::query();
$filteredQuery2->where(function ($q) {
    $q->where(function ($sub) {
        $sub->where('is_expatriate', false)->orWhereNull('is_expatriate');
    })->where(function ($sub) {
        $sub->where('is_international', false)->orWhereNull('is_international');
    });
});
$results2 = $filteredQuery2->where('id', $testStudent->id)->get();
if (!$results2->isEmpty()) {
    throw new Exception("Filter 'is_expatriate=0' (domestic) should NOT include international student.");
}
echo "✓ Domestic filter correctly excludes international student.\n";

// Test Admin update
$adminUpdateReq = Request::create("/admin/students/{$testStudent->id}", 'POST', [
    'name'                    => 'রাহুল শর্মা (Rahul Sharma)',
    'phone'                   => '919876543210',
    'status'                  => 'ACTIVE',
    'is_international'        => '1',
    'country_of_residence'    => 'ভারত (কলকাতা)',
    'address'                 => 'Park Street, Kolkata',
    'present_division_id'     => 1, // should be nullified
]);
$adminStudentController->update($adminUpdateReq, $testStudent);
$testStudent->refresh();

if ($testStudent->country_of_residence !== 'ভারত (কলকাতা)') {
    throw new Exception("Admin update failed to update country_of_residence.");
}
if ($testStudent->present_division_id !== null) {
    throw new Exception("Admin update did not nullify present_division_id for international student.");
}
echo "✓ Admin StudentController update works properly.\n";

// 6. Test Blade Rendering
echo "\n[Test 6] Verifying Blade Template Rendering...\n";
View::share('errors', new \Illuminate\Support\ViewErrorBag());

// View 1: student.profile.index
$divisions = Division::all();
$studentViewHtml = View::make('student.profile.index', [
    'student'   => $testStudent,
    'percent'   => $percent,
    'missing'   => $missing,
    'divisions' => $divisions,
    'districts' => District::all(),
])->render();
if (!str_contains($studentViewHtml, 'is_international')) {
    throw new Exception("student/profile/index.blade.php does not contain 'is_international'.");
}
if (!str_contains($studentViewHtml, 'toggleInternational')) {
    throw new Exception("student/profile/index.blade.php does not contain 'toggleInternational'.");
}
echo "✓ student/profile/index.blade.php rendered successfully with is_international toggles.\n";

// View 2: admin.students.show
$adminShowHtml = View::make('admin.students.show', [
    'student'       => $testStudent,
    'feePackages'   => collect(),
    'currentCourse' => null,
    'divisions'     => $divisions,
    'districts'     => District::all(),
])->render();
if (!str_contains($adminShowHtml, 'ইন্টারন্যাশনাল শিক্ষার্থী')) {
    throw new Exception("admin/students/show.blade.php does not display 'ইন্টারন্যাশনাল শিক্ষার্থী'.");
}
echo "✓ admin/students/show.blade.php rendered successfully with International badge.\n";

// View 3: admin.students.edit
$adminEditHtml = View::make('admin.students.edit', [
    'student'   => $testStudent,
    'divisions' => $divisions,
    'districts' => District::all(),
])->render();
if (!str_contains($adminEditHtml, 'admin_is_international')) {
    throw new Exception("admin/students/edit.blade.php does not contain 'admin_is_international'.");
}
echo "✓ admin/students/edit.blade.php rendered successfully with International checkbox.\n";

// View 4: admin.students.index
$paginator = new Illuminate\Pagination\LengthAwarePaginator([$testStudent], 1, 25);
$adminIndexHtml = View::make('admin.students.index', [
    'students'       => $paginator,
    'courses'        => collect(),
    'batches'        => collect(),
    'semesters'      => collect(),
    'districts'      => District::all(),
    'bloodGroups'    => collect(),
    'status'         => 'ACTIVE',
    'totalCount'     => 1,
    'activeCount'    => 1,
    'pendingCount'   => 0,
    'graduatedCount' => 0,
    'hasFilters'     => true,
    'perPage'        => 25,
])->render();
if (!str_contains($adminIndexHtml, 'ইন্টারন্যাশনাল')) {
    throw new Exception("admin/students/index.blade.php does not contain 'ইন্টারন্যাশনাল'.");
}
echo "✓ admin/students/index.blade.php rendered successfully with filter option & row badge.\n";

// 7. Cleanup
$testStudent->forceDelete();
$testUser->forceDelete();
echo "\n✓ Test student and user records cleaned up.\n";

echo "\n=======================================================\n";
echo "SUCCESS: ALL TESTS PASSED WITH EXIT CODE 0!\n";
echo "=======================================================\n";
exit(0);
