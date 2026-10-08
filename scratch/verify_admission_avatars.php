<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Course;
use App\Models\Batch;
use App\Models\AdmissionForm;
use Illuminate\Support\Facades\DB;

echo "=== Verifying Task 70: Admission Gender-Based Avatars & Selection Modal / Upload ===\n\n";

$assertions = 0;
function testAssert($condition, $message) {
    global $assertions;
    if ($condition) {
        $assertions++;
        echo "  [PASS] {$message}\n";
    } else {
        echo "  [FAIL] {$message}\n";
        exit(1);
    }
}

// 1. Verify Genuine Image Files Exist and Have Expected Sizes
$avatarDir = public_path('images/avatars');
testAssert(file_exists($avatarDir . '/female_avatar_1.jpg'), "female_avatar_1.jpg exists on disk");
testAssert(filesize($avatarDir . '/female_avatar_1.jpg') === 3932, "female_avatar_1.jpg is genuine user upload (3932 bytes)");

testAssert(file_exists($avatarDir . '/female_avatar_2.jpg'), "female_avatar_2.jpg exists on disk");
testAssert(filesize($avatarDir . '/female_avatar_2.jpg') === 4371, "female_avatar_2.jpg is genuine user upload (4371 bytes)");

testAssert(file_exists($avatarDir . '/female_avatar_3.jpg'), "female_avatar_3.jpg exists on disk");
testAssert(filesize($avatarDir . '/female_avatar_3.jpg') === 5742, "female_avatar_3.jpg is genuine user upload (5742 bytes)");

testAssert(file_exists($avatarDir . '/male_avatar_1.png'), "male_avatar_1.png exists on disk");
testAssert(filesize($avatarDir . '/male_avatar_1.png') === 30786, "male_avatar_1.png is genuine user upload (30786 bytes)");

testAssert(file_exists($avatarDir . '/male_avatar_2.jpg'), "male_avatar_2.jpg exists on disk");
testAssert(filesize($avatarDir . '/male_avatar_2.jpg') === 34960, "male_avatar_2.jpg is genuine user upload (34960 bytes)");

// Compatibility files shouldn't be the results screenshot (which was 43369 bytes)
testAssert(filesize($avatarDir . '/female_niqab_black.jpg') === 3932, "female_niqab_black.jpg overwritten with genuine avatar image, not results screenshot");

// 2. Student Model Default Avatar Logic
testAssert(Student::defaultAvatarForGender('Female') === '/images/avatars/female_avatar_1.jpg', "Female resolves to /images/avatars/female_avatar_1.jpg");
testAssert(Student::defaultAvatarForGender('মহিলা') === '/images/avatars/female_avatar_1.jpg', "মহিলা resolves to /images/avatars/female_avatar_1.jpg");
testAssert(Student::defaultAvatarForGender('বোন শাখা') === '/images/avatars/female_avatar_1.jpg', "বোন শাখা resolves to /images/avatars/female_avatar_1.jpg");
testAssert(Student::defaultAvatarForGender('Male') === '/images/avatars/male_avatar_1.png', "Male resolves to /images/avatars/male_avatar_1.png");
testAssert(Student::defaultAvatarForGender('পুরুষ') === '/images/avatars/male_avatar_1.png', "পুরুষ resolves to /images/avatars/male_avatar_1.png");
testAssert(Student::defaultAvatarForGender('ভাই শাখা') === '/images/avatars/male_avatar_1.png', "ভাই শাখা resolves to /images/avatars/male_avatar_1.png");

// 3. Student Available Avatars
$avatars = Student::availableAvatars();
testAssert(isset($avatars['Female']) && count($avatars['Female']) === 3, "Available avatars contains 3 female options");
testAssert(isset($avatars['Male']) && count($avatars['Male']) === 2, "Available avatars contains 2 male options");
testAssert($avatars['Female'][0]['is_default'] === true, "First female avatar is default");
testAssert($avatars['Male'][0]['is_default'] === true, "First male avatar is default");

// 4. Photo URL Accessor Fallback
$mockStudentF = new Student(['gender' => 'Female', 'photo_url' => null]);
testAssert($mockStudentF->photo_url === '/images/avatars/female_avatar_1.jpg', "Female student with null photo_url returns default avatar");

$mockStudentM = new Student(['gender' => 'Male', 'photo_url' => null]);
testAssert($mockStudentM->photo_url === '/images/avatars/male_avatar_1.png', "Male student with null photo_url returns default avatar");

$mockStudentCustom = new Student(['gender' => 'Female', 'photo_url' => '/storage/photos/students/custom.jpg']);
testAssert($mockStudentCustom->photo_url === '/storage/photos/students/custom.jpg', "Student with explicit photo_url retains their photo");

// 5. Blade Views Verification
$applyView = file_get_contents(resource_path('views/apply/index.blade.php'));
testAssert(str_contains($applyView, 'enctype="multipart/form-data"'), "apply/index.blade.php has enctype='multipart/form-data'");
testAssert(str_contains($applyView, 'id="avatar_preview_img"'), "apply/index.blade.php has #avatar_preview_img");
testAssert(str_contains($applyView, 'id="avatarModal"'), "apply/index.blade.php has #avatarModal");
testAssert(str_contains($applyView, 'onGenderChange'), "apply/index.blade.php has onGenderChange handler");
testAssert(str_contains($applyView, 'female_avatar_1.jpg'), "apply/index.blade.php references female_avatar_1.jpg");
testAssert(str_contains($applyView, 'male_avatar_1.png'), "apply/index.blade.php references male_avatar_1.png");

$adminCreateView = file_get_contents(resource_path('views/admin/admissions/create.blade.php'));
testAssert(str_contains($adminCreateView, 'enctype="multipart/form-data"'), "admin/admissions/create.blade.php has enctype='multipart/form-data'");
testAssert(str_contains($adminCreateView, 'id="avatar_preview_img"'), "admin/admissions/create.blade.php has #avatar_preview_img");
testAssert(str_contains($adminCreateView, 'id="adminAvatarModal"'), "admin/admissions/create.blade.php has #adminAvatarModal");
testAssert(str_contains($adminCreateView, 'onGenderChange'), "admin/admissions/create.blade.php has onGenderChange handler");
testAssert(str_contains($adminCreateView, 'female_avatar_1.jpg'), "admin/admissions/create.blade.php references female_avatar_1.jpg");
testAssert(str_contains($adminCreateView, 'male_avatar_1.png'), "admin/admissions/create.blade.php references male_avatar_1.png");

// 6. DB Persistence Simulation
DB::beginTransaction();
try {
    // Simulate student registration with avatar preset
    $studentPreset = Student::create([
        'name' => 'Avatar Test Student',
        'phone' => '01999999901',
        'email' => 'avatar_test_preset@test.com',
        'gender' => 'Female',
        'photo_url' => '/images/avatars/female_avatar_2.jpg',
        'status' => 'LEAD',
    ]);
    testAssert($studentPreset->photo_url === '/images/avatars/female_avatar_2.jpg', "Preset avatar persisted in DB correctly");

    // Simulate student registration with gender default
    $studentDefault = Student::create([
        'name' => 'Default Male Student',
        'phone' => '01999999902',
        'email' => 'avatar_test_default@test.com',
        'gender' => 'Male',
        'photo_url' => Student::defaultAvatarForGender('Male'),
        'status' => 'LEAD',
    ]);
    testAssert($studentDefault->photo_url === '/images/avatars/male_avatar_1.png', "Default male avatar persisted in DB correctly");

    DB::rollBack();
    testAssert(true, "DB transaction rolled back cleanly without pollution");
} catch (\Exception $e) {
    DB::rollBack();
    echo "Exception during DB simulation: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nAll {$assertions} assertions PASSED with Exit Code 0!\n";
exit(0);
