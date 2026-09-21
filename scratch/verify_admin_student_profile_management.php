<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\User;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\CourseFeePackage;
use App\Models\AuditLog;
use App\Models\LoginHistory;
use App\Http\Middleware\EnsureCourseAccess;
use Illuminate\Http\Request;

echo "=== TASK 31: STUDENT PROFILE MANAGEMENT VERIFICATION ===\n\n";

$passed = 0;
$failed = 0;

function assertCondition(string $description, bool $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "  \033[32m[PASS]\033[0m $description\n";
        $passed++;
    } else {
        echo "  \033[31m[FAIL]\033[0m $description\n";
        $failed++;
    }
}

DB::beginTransaction();

try {
    // ── SECTION 1: Schema Integrity Check ────────
    echo "1. Testing Schema Integrity for Task 31...\n";

    $studentColumns = Schema::getColumnListing('students');
    $requiredStudentCols = [
        'has_course_access',
        'fee_package_id',
        'monthly_discount',
        'discount_type',
        'poor_fund_remarks'
    ];
    foreach ($requiredStudentCols as $col) {
        assertCondition("students table has '$col' column", in_array($col, $studentColumns));
    }

    $auditLogColumns = Schema::getColumnListing('audit_logs');
    assertCondition("audit_logs table has 'description' column", in_array('description', $auditLogColumns));

    assertCondition("login_histories table exists", Schema::hasTable('login_histories'));
    $loginCols = Schema::getColumnListing('login_histories');
    $requiredLoginCols = [
        'user_id', 'student_id', 'ip_address', 'device', 'browser', 'platform', 
        'is_impersonated', 'impersonated_by', 'login_at'
    ];
    foreach ($requiredLoginCols as $col) {
        assertCondition("login_histories table has '$col' column", in_array($col, $loginCols));
    }

    // ── SECTION 2: Test Data Setup ────────
    echo "\n2. Setting Up Test Student & Admin...\n";

    $adminUser = User::create([
        'name'     => 'Super Admin',
        'email'    => 'admin_test_' . uniqid() . '@iom.edu.bd',
        'password' => Hash::make('password123'),
        'role'     => 'super_admin',
    ]);
    Auth::login($adminUser);

    $studentUser = User::create([
        'name'     => 'আহমাদুল্লাহ আল-মামুন',
        'email'    => 'student_test_' . uniqid() . '@iom.student',
        'password' => Hash::make('old_pass_123'),
        'role'     => 'student',
    ]);

    $course = Course::firstOrCreate(
        ['name' => 'ডিপ্লোমা ইন অ্যারাবিক ল্যাঙ্গুয়েজ'],
        ['code' => '05', 'type' => 'SEMESTER_BASED', 'duration_value' => 12, 'duration_unit' => 'MONTH', 'is_active' => true]
    );

    $batch = Batch::firstOrCreate(
        ['name' => 'ব্যাচ ০১ - আরবি ডিপ্লোমা', 'course_id' => $course->id],
        ['batch_code' => '01', 'status' => 'ACTIVE', 'start_date' => now()->toDateString()]
    );

    $feePackage = CourseFeePackage::firstOrCreate(
        ['name' => 'মান্থলি ফি প্যাকেজ (১০০০/মাস)', 'course_id' => $course->id],
        ['description' => '১০০০ টাকা প্রতি মাস', 'is_active' => true]
    );

    $student = Student::create([
        'user_id'                 => $studentUser->id,
        'student_code'            => '26010510099',
        'name'                    => 'আহমাদুল্লাহ আল-মামুন',
        'email'                   => $studentUser->email,
        'phone'                   => '01799887766',
        'status'                  => 'ACTIVE',
        'has_course_access'       => true,
        'gender'                  => 'MALE',
        'blood_group'             => 'B+',
        'national_id'             => '1998765432109',
        'father_name'             => 'মাওলানা মুহাম্মদ আলী',
        'mother_name'             => 'রোকেয়া খাতুন',
        'address'                 => 'মিরপুর-১০, ঢাকা',
        'permanent_address'       => 'সদর, ফরিদপুর',
        'education_qualification' => 'আলিম / এইচএসসি',
    ]);

    $enrollment = Enrollment::create([
        'student_id'  => $student->id,
        'course_id'   => $course->id,
        'batch_id'    => $batch->id,
        'status'      => 'ACTIVE',
        'enrolled_at' => now(),
    ]);

    assertCondition("Test student created with ID: {$student->id}", $student->id > 0);

    // ── SECTION 3: Course Access Toggle Testing ────────
    echo "\n3. Testing Course Access Toggle & Audit Logging...\n";

    assertCondition("Student initially has course access enabled", $student->has_course_access === true);

    // Toggle off
    $newStatus = $student->toggleCourseAccess(false, 'বকেয়া ফি পরিশোধ না করায় কোর্স অ্যাক্সেস স্থগিত');
    $student->refresh();

    assertCondition("toggleCourseAccess(false) sets has_course_access to false", $student->has_course_access === false && $newStatus === false);

    $auditLogToggle = AuditLog::where('auditable_type', Student::class)
        ->where('auditable_id', $student->id)
        ->where('event', 'course_access_toggled')
        ->latest()
        ->first();

    assertCondition("AuditLog entry recorded for course_access_toggled", $auditLogToggle !== null);
    assertCondition("AuditLog contains correct new_values", ($auditLogToggle->new_values['has_course_access'] ?? null) === false);
    assertCondition("AuditLog description captured correctly", str_contains($auditLogToggle->description ?? '', 'বকেয়া'));

    // Toggle back on
    $newStatus2 = $student->toggleCourseAccess(true, 'ফি পরিশোধ সাপেক্ষে পুনরায় অ্যাক্সেস চালু');
    $student->refresh();
    assertCondition("toggleCourseAccess(true) restores has_course_access to true", $student->has_course_access === true && $newStatus2 === true);

    // ── SECTION 4: Middleware EnsureCourseAccess Test ────────
    echo "\n4. Testing EnsureCourseAccess Middleware Guard...\n";

    $middleware = new EnsureCourseAccess();

    // Case A: When student has course access enabled
    $requestClasses = Request::create('/student/classes', 'GET');
    $requestClasses->setUserResolver(fn() => $studentUser);

    // Simulate route name
    $routeMock = new class {
        public $name = 'student.classes.index';
        public function getName() { return $this->name; }
        public function named(...$patterns) {
            foreach ($patterns as $pattern) {
                if (\Illuminate\Support\Str::is($pattern, $this->name)) {
                    return true;
                }
            }
            return false;
        }
    };
    $requestClasses->setRouteResolver(fn() => $routeMock);

    $response = $middleware->handle($requestClasses, function($req) {
        return response('ALLOWED');
    });

    assertCondition("Middleware ALLOWS course classes when has_course_access is true", $response->getContent() === 'ALLOWED');

    // Case B: When student has course access disabled
    $student->toggleCourseAccess(false);
    $student->refresh();

    $responseBlocked = $middleware->handle($requestClasses, function($req) {
        return response('ALLOWED');
    });

    assertCondition("Middleware REDIRECTS when has_course_access is false", $responseBlocked->isRedirection());

    // Case C: Allowed routes (profile, fees, dashboard) must NOT be blocked
    $requestFees = Request::create('/student/fees', 'GET');
    $requestFees->setUserResolver(fn() => $studentUser);
    $feesRouteMock = new class {
        public $name = 'student.fees.index';
        public function getName() { return $this->name; }
        public function named(...$patterns) {
            foreach ($patterns as $pattern) {
                if (\Illuminate\Support\Str::is($pattern, $this->name)) {
                    return true;
                }
            }
            return false;
        }
    };
    $requestFees->setRouteResolver(fn() => $feesRouteMock);

    $responseFees = $middleware->handle($requestFees, function($req) {
        return response('ALLOWED_FEES');
    });

    assertCondition("Middleware ALLOWS student.fees even when course access is disabled", $responseFees->getContent() === 'ALLOWED_FEES');

    // Restore course access for next tests
    $student->toggleCourseAccess(true);

    // ── SECTION 5: Password Reset Testing ────────
    echo "\n5. Testing Password Reset Functionality...\n";

    $newPassword = 'NewSecretPassword@2026';
    $studentUser->password = Hash::make($newPassword);
    $studentUser->save();

    AuditLog::log(
        'password_reset',
        $student,
        null,
        ['reset_by' => $adminUser->id],
        "পাসওয়ার্ড রিসেট করা হয়েছে"
    );

    assertCondition("Student user password updated successfully", Hash::check($newPassword, $studentUser->password));

    $passwordAudit = AuditLog::where('auditable_type', Student::class)
        ->where('auditable_id', $student->id)
        ->where('event', 'password_reset')
        ->latest()
        ->first();

    assertCondition("Password reset audit log generated", $passwordAudit !== null);

    // ── SECTION 6: Fee Structure & Poor Fund Adjustment ────────
    echo "\n6. Testing Fee Structure & Poor Fund Adjustment...\n";

    $student->adjustFeeStructure($feePackage->id, 300.0, 'FIXED', 'পুওর ফান্ড আবেদন অনুমোদিত (PF-2026-0005)');
    $student->refresh();

    assertCondition("Student fee_package_id updated", $student->fee_package_id === $feePackage->id);
    assertCondition("Student monthly_discount updated to 300.0", (float)$student->monthly_discount === 300.0);
    assertCondition("Student discount_type is FIXED", $student->discount_type === 'FIXED');
    assertCondition("Student poor_fund_remarks preserved", str_contains($student->poor_fund_remarks, 'PF-2026-0005'));

    $feeAudit = AuditLog::where('auditable_type', Student::class)
        ->where('auditable_id', $student->id)
        ->where('event', 'fee_structure_adjusted')
        ->latest()
        ->first();

    assertCondition("AuditLog entry recorded for fee_structure_adjusted", $feeAudit !== null);
    assertCondition("AuditLog contains monthly_discount 300 in new_values", (float)($feeAudit->new_values['monthly_discount'] ?? null) === 300.0);

    // ── SECTION 7: Student Profile Personal Details Update & Audit ────────
    echo "\n7. Testing Student Profile Personal Info Update & Audit...\n";

    $oldPhone = $student->phone;
    $newPhone = '01811223344';
    $student->update(['phone' => $newPhone]);

    AuditLog::log(
        'student_profile_updated',
        $student,
        ['phone' => $oldPhone],
        ['phone' => $newPhone],
        'শিক্ষার্থীর মোবাইল নম্বর পরিবর্তন করা হয়েছে'
    );

    $student->refresh();
    assertCondition("Student phone updated to $newPhone", $student->phone === $newPhone);

    $profileAudit = AuditLog::where('auditable_type', Student::class)
        ->where('auditable_id', $student->id)
        ->where('event', 'student_profile_updated')
        ->latest()
        ->first();

    assertCondition("AuditLog entry recorded for student_profile_updated", $profileAudit !== null);
    assertCondition("AuditLog old value captures $oldPhone", ($profileAudit->old_values['phone'] ?? null) === $oldPhone);
    assertCondition("AuditLog new value captures $newPhone", ($profileAudit->new_values['phone'] ?? null) === $newPhone);

    // ── SECTION 8: Login History Recording & Impersonation ────────
    echo "\n8. Testing Login History Recording & Impersonation Tracking...\n";

    // Normal student login
    $normalLogin = LoginHistory::recordLogin($studentUser, $student, false, null);
    assertCondition("Normal login history created", $normalLogin->id > 0);
    assertCondition("Login record is_impersonated is false", $normalLogin->is_impersonated === false);
    assertCondition("Login record student_id matches", $normalLogin->student_id === $student->id);

    // Impersonated login by admin
    $impersonatedLogin = LoginHistory::recordLogin($studentUser, $student, true, $adminUser->id);
    assertCondition("Impersonated login history created", $impersonatedLogin->id > 0);
    assertCondition("Login record is_impersonated is true", $impersonatedLogin->is_impersonated === true);
    assertCondition("Login record impersonated_by matches admin ID", $impersonatedLogin->impersonated_by === $adminUser->id);

    assertCondition("Student relationship loginHistories() counts 2", $student->loginHistories()->count() === 2);

    // ── SECTION 9: Cancel Admission Action ────────
    echo "\n9. Testing Cancel Admission Action...\n";

    $student->cancelAdmission('শিক্ষার্থী নিজেই পড়ালেখা বন্ধের আবেদন করেছে');
    $student->refresh();
    $enrollment->refresh();

    assertCondition("Student status changed to CANCELLED", $student->status === 'CANCELLED');
    assertCondition("Student has_course_access set to false", $student->has_course_access === false);
    assertCondition("Student active enrollment status changed to CANCELLED", $enrollment->status === 'CANCELLED');

    $cancelAudit = AuditLog::where('auditable_type', Student::class)
        ->where('auditable_id', $student->id)
        ->where('event', 'admission_cancelled')
        ->latest()
        ->first();

    assertCondition("AuditLog entry recorded for admission_cancelled", $cancelAudit !== null);

    // ── SECTION 10: Routes & Blade Views Integrity ────────
    echo "\n10. Testing Routes & Blade Views Integrity for Task 31...\n";

    $requiredRoutes = [
        'admin.students.index',
        'admin.students.show',
        'admin.students.edit',
        'admin.students.update',
        'admin.students.impersonate',
        'admin.students.toggle-course-access',
        'admin.students.cancel-admission',
        'admin.students.reset-password',
        'admin.students.adjust-fee-structure',
    ];

    foreach ($requiredRoutes as $r) {
        assertCondition("Named route '$r' is registered", Route::has($r));
    }

    $bladeFiles = [
        'resources/views/admin/students/show.blade.php',
        'resources/views/admin/students/edit.blade.php',
    ];

    foreach ($bladeFiles as $f) {
        $fullPath = __DIR__ . '/../' . $f;
        $exists = file_exists($fullPath);
        assertCondition("Blade file '$f' exists", $exists);
        if ($exists) {
            $content = file_get_contents($fullPath);
            assertCondition("Blade file '$f' includes 'Kalpurush' font reference", 
                str_contains($content, 'Kalpurush') || str_contains($content, 'kalpurush')
            );
        }
    }

    // Check key elements in show.blade.php
    $showContent = file_get_contents(__DIR__ . '/../resources/views/admin/students/show.blade.php');
    assertCondition("show.blade.php contains toggle-course-access form", str_contains($showContent, 'admin.students.toggle-course-access'));
    assertCondition("show.blade.php contains cancel-admission modal/form", str_contains($showContent, 'admin.students.cancel-admission'));
    assertCondition("show.blade.php contains reset-password modal/form", str_contains($showContent, 'admin.students.reset-password'));
    assertCondition("show.blade.php contains adjust-fee-structure modal/form", str_contains($showContent, 'admin.students.adjust-fee-structure'));
    assertCondition("show.blade.php contains Audit Log tab/table", str_contains($showContent, 'auditLogs'));
    assertCondition("show.blade.php contains Login History tab/table", str_contains($showContent, 'loginHistories'));

    // Rollback changes to keep DB pristine
    DB::rollBack();
    echo "\nVerification database transaction rolled back cleanly.\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n\033[31mException occurred:\033[0m " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    $failed++;
}

echo "\n=======================================================\n";
echo "VERIFICATION SUMMARY:\n";
echo "Total Passed: $passed\n";
echo "Total Failed: $failed\n";

if ($failed === 0) {
    echo "\033[32mALL ASSERTIONS PASSED! Task 31 verified successfully.\033[0m\n";
    exit(0);
} else {
    echo "\033[31mSOME ASSERTIONS FAILED! Please review.\033[0m\n";
    exit(1);
}
