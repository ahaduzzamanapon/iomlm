<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\Batch;
use App\Models\AdmissionForm;
use App\Models\WaiverApplication;

// Configure in-memory sqlite
Config::set('database.default', 'sqlite');
Config::set('database.connections.sqlite', [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);
DB::purge('sqlite');
DB::reconnect('sqlite');

echo "Creating test schemas...\n";

Schema::create('courses', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->decimal('admission_fee', 10, 2)->default(0);
    $table->boolean('is_poor_fund_applicable')->default(true);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('batches', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('course_id');
    $table->string('name');
    $table->string('batch_code')->nullable();
    $table->decimal('admission_fee', 10, 2)->default(0.00);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('waiver_applications', function (Blueprint $table) {
    $table->id();
    $table->string('application_no')->unique();
    $table->string('status')->default('PENDING');
    $table->string('apply_for')->default('BOTH');
    $table->string('full_name')->nullable();
    $table->string('phone')->nullable();
    $table->string('discount_type')->default('PERCENTAGE');
    $table->decimal('approved_discount_value', 10, 2)->default(0);
    $table->decimal('approved_discount_percent', 5, 2)->default(0);
    $table->decimal('approved_admission_fee', 10, 2)->nullable();
    $table->unsignedBigInteger('approved_package_id')->nullable();
    $table->unsignedBigInteger('course_id')->nullable();
    $table->boolean('is_used')->default(false);
    $table->softDeletes();
    $table->timestamps();
});

Schema::create('admission_forms', function (Blueprint $table) {
    $table->id();
    $table->string('source')->default('PUBLIC');
    $table->string('application_no')->unique();
    $table->unsignedBigInteger('student_id')->nullable();
    $table->unsignedBigInteger('interested_course_id');
    $table->unsignedBigInteger('batch_id')->nullable();
    $table->string('waiver_code')->nullable();
    $table->decimal('discount_percent', 5, 2)->default(0.00);
    $table->string('discount_type')->default('PERCENTAGE');
    $table->decimal('discount_amount', 10, 2)->default(0.00);
    $table->string('status')->default('PENDING');
    $table->timestamps();
});

echo "Schemas created successfully.\n\n";

$passed = 0;
$total = 0;

function assertCondition($name, $condition, $actual = null, $expected = null) {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$name}\n";
    } else {
        echo "  [FAIL] {$name} (Expected: " . var_export($expected, true) . ", Got: " . var_export($actual, true) . ")\n";
    }
}

// Case 1: Alim Preparatory Course (Course fee 1500, Batch fee 0.00, Approved Poor Fund fee 500)
echo "Testing Case 1: Akhi akther mou scenario (1500 course fee, approved 500, stored discount_percent 0)...\n";
$course1 = Course::create(['name' => 'Alim Preparatory Course', 'admission_fee' => 1500.00]);
$batch1 = Batch::create(['course_id' => $course1->id, 'name' => 'Spring 2026', 'admission_fee' => 0.00]);
$waiver1 = WaiverApplication::create([
    'application_no' => 'PF-2026-0025',
    'status' => 'APPROVED',
    'apply_for' => 'BOTH',
    'approved_admission_fee' => 500.00,
    'approved_discount_percent' => 0.00,
    'is_used' => true,
]);

$adm1 = AdmissionForm::create([
    'application_no' => 'APP-2026-0101',
    'interested_course_id' => $course1->id,
    'batch_id' => $batch1->id,
    'waiver_code' => 'PF-2026-0025',
    'discount_percent' => 0.00,
    'discount_amount' => 0.00,
]);

assertCondition("Effective discount percent is 66.67%", $adm1->effective_discount_percent == 66.67, $adm1->effective_discount_percent, 66.67);
assertCondition("Effective discount amount is 1000", $adm1->effective_discount_amount == 1000.00, $adm1->effective_discount_amount, 1000.00);

// Fee calculation test mirroring show.blade.php
$courseFee = (float)($adm1->interestedCourse->admission_fee ?? 0);
$batchFee = ($adm1->batch && (float)$adm1->batch->admission_fee > 0) ? (float)$adm1->batch->admission_fee : $courseFee;
$wApp = $adm1->waiver_code ? WaiverApplication::where('application_no', $adm1->waiver_code)->first() : null;
$effectivePercent = $adm1->effective_discount_percent;
$effectiveDiscAmount = $adm1->effective_discount_amount;

if ($wApp && $wApp->approved_admission_fee !== null && in_array($wApp->apply_for, ['ADMISSION_FEE', 'BOTH'])) {
    $approvedAdmFee = (float) $wApp->approved_admission_fee;
    if ($batchFee > 0) {
        $netPayable = min($batchFee, $approvedAdmFee);
        $discVal = max(0, $batchFee - $netPayable);
    } else {
        $netPayable = $approvedAdmFee;
        $discVal = $effectiveDiscAmount;
        $batchFee = $netPayable + $discVal;
    }
} else {
    $discVal = $effectiveDiscAmount;
    $netPayable = max(0, $batchFee - $discVal);
}

assertCondition("show.blade.php fee calculation resolves Course/Batch fee to 1500 (not 0)", $batchFee == 1500.00, $batchFee, 1500.00);
assertCondition("show.blade.php fee calculation resolves Applied Waiver / Discount to 1000", $discVal == 1000.00, $discVal, 1000.00);
assertCondition("show.blade.php fee calculation resolves Net Payable to 500", $netPayable == 500.00, $netPayable, 500.00);

// Case 2: Regular coupon code scenario (32% discount on 1500)
echo "\nTesting Case 2: Regular coupon code scenario (32% stored discount)...\n";
$adm2 = AdmissionForm::create([
    'application_no' => 'APP-2026-0098',
    'interested_course_id' => $course1->id,
    'batch_id' => $batch1->id,
    'waiver_code' => 'IOMREAD17',
    'discount_percent' => 32.00,
    'discount_amount' => 480.00,
]);
assertCondition("Coupon code effective discount percent is 32%", $adm2->effective_discount_percent == 32.00, $adm2->effective_discount_percent, 32.00);
assertCondition("Coupon code effective discount amount is 480", $adm2->effective_discount_amount == 480.00, $adm2->effective_discount_amount, 480.00);

// Case 3: Poor Fund waiver with approved percentage (e.g. 50%)
echo "\nTesting Case 3: Poor Fund with approved percentage (50%)...\n";
$waiver3 = WaiverApplication::create([
    'application_no' => 'PF-2026-0027',
    'status' => 'APPROVED',
    'apply_for' => 'ADMISSION_FEE',
    'approved_discount_percent' => 50.00,
    'is_used' => true,
]);
$adm3 = AdmissionForm::create([
    'application_no' => 'APP-2026-0103',
    'interested_course_id' => $course1->id,
    'batch_id' => $batch1->id,
    'waiver_code' => 'PF-2026-0027',
    'discount_percent' => 0.00,
]);
assertCondition("Percentage waiver effective discount percent is 50%", $adm3->effective_discount_percent == 50.00, $adm3->effective_discount_percent, 50.00);
assertCondition("Percentage waiver effective discount amount is 750", $adm3->effective_discount_amount == 750.00, $adm3->effective_discount_amount, 750.00);

// Case 4: Verify Blade View compilation
echo "\nTesting Blade view compilation...\n";
$viewFiles = [
    'resources/views/admin/admissions/show.blade.php',
    'resources/views/admin/admissions/index.blade.php',
    'resources/views/apply/payment.blade.php',
];

$compiler = $app->make(\Illuminate\View\Compilers\BladeCompiler::class);
foreach ($viewFiles as $vf) {
    $fullPath = realpath(__DIR__ . '/../' . $vf);
    $content = file_get_contents($fullPath);
    $compiled = $compiler->compileString($content);
    assertCondition("Blade compiles without error: {$vf}", !empty($compiled));
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "SUMMARY: {$passed} of {$total} assertions passed.\n";
if ($passed === $total) {
    echo "STATUS: ALL TESTS PASSED! (Exit Code 0)\n";
    exit(0);
} else {
    echo "STATUS: SOME TESTS FAILED!\n";
    exit(1);
}
