<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== Verifying Avatar Shift: Removed from Admission, Added to Student Profile ===" . PHP_EOL . PHP_EOL;

$passed = 0;
$failed = 0;

function assertCondition($desc, $cond) {
    global $passed, $failed;
    if ($cond) {
        echo "  [PASS] $desc" . PHP_EOL;
        $passed++;
    } else {
        echo "  [FAIL] $desc" . PHP_EOL;
        $failed++;
    }
}

// 1. Check admin admissions create view: avatar modal removed, clean photo field present
$adminAdmissionContent = file_get_contents(resource_path('views/admin/admissions/create.blade.php'));
assertCondition("admin/admissions/create.blade.php DOES NOT contain adminAvatarModal", !str_contains($adminAdmissionContent, 'id="adminAvatarModal"'));
assertCondition("admin/admissions/create.blade.php DOES NOT contain avatar_preview_img", !str_contains($adminAdmissionContent, 'id="avatar_preview_img"'));
assertCondition("admin/admissions/create.blade.php has optional photo file input", str_contains($adminAdmissionContent, '<input type="file" name="photo"'));

// 2. Check public apply view: no avatar modal
$publicApplyContent = file_get_contents(resource_path('views/apply/index.blade.php'));
assertCondition("apply/index.blade.php DOES NOT contain avatarModal", !str_contains($publicApplyContent, 'avatarModal'));

// 3. Check student profile view: rich avatar and photo UI present
$studentProfileContent = file_get_contents(resource_path('views/student/profile/index.blade.php'));
assertCondition("student/profile/index.blade.php has #profile_avatar_preview", str_contains($studentProfileContent, 'id="profile_avatar_preview"'));
assertCondition("student/profile/index.blade.php has #studentAvatarModal", str_contains($studentProfileContent, 'id="studentAvatarModal"'));
assertCondition("student/profile/index.blade.php has #student_avatar_preset", str_contains($studentProfileContent, 'id="student_avatar_preset"'));
assertCondition("student/profile/index.blade.php has female avatars", str_contains($studentProfileContent, 'female_avatar_1.jpg') && str_contains($studentProfileContent, 'female_avatar_2.jpg'));
assertCondition("student/profile/index.blade.php has male avatars", str_contains($studentProfileContent, 'male_avatar_1.png') && str_contains($studentProfileContent, 'male_avatar_2.jpg'));
assertCondition("student/profile/index.blade.php has photo upload option", str_contains($studentProfileContent, 'id="profile_photo_file_input"'));
assertCondition("student/profile/index.blade.php has reset to default button", str_contains($studentProfileContent, 'resetStudentAvatarToDefault()'));

// 4. Test Student Profile Avatar update flow in DB
DB::beginTransaction();
try {
    $user = User::factory()->create([
        'email' => 'test_avatar_profile_' . uniqid() . '@example.com',
        'role' => 'STUDENT',
    ]);

    $student = Student::create([
        'user_id' => $user->id,
        'name' => 'আয়েশা সিদ্দিকা',
        'email' => $user->email,
        'phone' => '01700' . rand(100000, 999999),
        'gender' => 'Female',
        'status' => 'ACTIVE',
    ]);

    // Initial photo_url should resolve to female default
    assertCondition("Female student with null photo_url defaults to female_avatar_1", $student->photo_url === '/images/avatars/female_avatar_1.jpg');

    // Simulate ProfileController updating avatar preset
    $presetChosen = '/images/avatars/female_avatar_2.jpg';
    $student->update(['photo_url' => $presetChosen]);

    $student->refresh();
    assertCondition("Student photo_url persisted to chosen avatar (/images/avatars/female_avatar_2.jpg)", $student->photo_url === $presetChosen);

    // Test Male student avatar
    $maleStudent = Student::create([
        'name' => 'আব্দুর রহমান',
        'email' => 'abdur_' . uniqid() . '@example.com',
        'phone' => '01800' . rand(100000, 999999),
        'gender' => 'Male',
        'status' => 'ACTIVE',
    ]);

    assertCondition("Male student defaults to male_avatar_1.png", $maleStudent->photo_url === '/images/avatars/male_avatar_1.png');

    $malePreset = '/images/avatars/male_avatar_2.jpg';
    $maleStudent->update(['photo_url' => $malePreset]);
    $maleStudent->refresh();
    assertCondition("Male student photo_url persisted to chosen avatar (/images/avatars/male_avatar_2.jpg)", $maleStudent->photo_url === $malePreset);

    DB::rollBack();
    echo "  [PASS] DB transaction rolled back cleanly without pollution" . PHP_EOL;
    $passed++;
} catch (\Throwable $e) {
    DB::rollBack();
    echo "  [FAIL] DB test threw exception: " . $e->getMessage() . PHP_EOL;
    $failed++;
}

echo PHP_EOL . "Results: $passed Passed, $failed Failed." . PHP_EOL;
if ($failed > 0) {
    exit(1);
}
echo "All assertions PASSED with Exit Code 0!" . PHP_EOL;
exit(0);
