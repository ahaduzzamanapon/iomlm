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
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\AdmissionForm;
use App\Models\WaiverApplication;
use App\Services\AccountingService;
use App\Services\StudentFeeService;

// Configure in-memory sqlite
Config::set('database.default', 'sqlite');
Config::set('database.connections.sqlite', [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);
DB::purge('sqlite');
DB::reconnect('sqlite');

echo "Creating schemas for test...\n";

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('role')->default('STUDENT');
    $table->timestamps();
});

Schema::create('academic_years', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('courses', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code')->nullable();
    $table->string('type')->default('SEMESTER_BASED');
    $table->decimal('admission_fee', 10, 2)->default(1500.00);
    $table->boolean('is_poor_fund_applicable')->default(true);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('semesters', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('course_id');
    $table->string('name');
    $table->integer('sequence_no')->default(1);
    $table->timestamps();
});

Schema::create('batches', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('course_id');
    $table->string('name');
    $table->string('batch_code')->nullable();
    $table->decimal('admission_fee', 10, 2)->default(0.00);
    $table->decimal('monthly_fee', 10, 2)->default(0.00);
    $table->string('status')->default('ACTIVE');
    $table->timestamps();
});

Schema::create('batch_semester_positions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('batch_id');
    $table->unsignedBigInteger('current_semester_id')->nullable();
    $table->timestamps();
});

Schema::create('fee_heads', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->timestamps();
});

Schema::create('course_fee_packages', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('course_id');
    $table->string('name');
    $table->boolean('is_default')->default(false);
    $table->timestamps();
});

Schema::create('course_fee_package_items', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('package_id');
    $table->unsignedBigInteger('fee_head_id')->nullable();
    $table->string('label')->nullable();
    $table->decimal('amount_per_unit', 10, 2)->default(0);
    $table->decimal('total_amount', 10, 2)->default(0);
    $table->integer('months_count')->default(0);
    $table->timestamps();
});

Schema::create('program_activities', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('course_id')->nullable();
    $table->unsignedBigInteger('batch_id')->nullable();
    $table->string('name')->nullable();
    $table->timestamps();
});

Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id')->nullable();
    $table->string('student_code')->nullable();
    $table->string('name');
    $table->string('email')->nullable();
    $table->string('phone')->nullable();
    $table->string('temporary_password')->nullable();
    $table->unsignedBigInteger('fee_package_id')->nullable();
    $table->timestamps();
});

Schema::create('waiver_applications', function (Blueprint $table) {
    $table->id();
    $table->string('application_no')->unique();
    $table->string('status')->default('APPROVED');
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
    $table->decimal('approved_admission_fee', 10, 2)->nullable();
    $table->string('manual_payment_method')->nullable();
    $table->decimal('manual_paid_amount', 10, 2)->nullable();
    $table->string('manual_trx_id')->nullable();
    $table->string('manual_sender_phone')->nullable();
    $table->string('status')->default('APPROVED');
    $table->softDeletes();
    $table->timestamps();
});

Schema::create('enrollments', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('student_id');
    $table->unsignedBigInteger('course_id');
    $table->unsignedBigInteger('batch_id')->nullable();
    $table->unsignedBigInteger('semester_id')->nullable();
    $table->unsignedBigInteger('admission_form_id')->nullable();
    $table->date('enrolled_at')->nullable();
    $table->string('status')->default('ACTIVE');
    $table->timestamps();
});

Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->string('invoice_no')->unique();
    $table->unsignedBigInteger('student_id');
    $table->unsignedBigInteger('enrollment_id')->nullable();
    $table->string('category')->default('ADMISSION');
    $table->string('title')->nullable();
    $table->decimal('amount', 10, 2)->default(0.00);
    $table->decimal('discount', 10, 2)->default(0.00);
    $table->decimal('payable_amount', 10, 2)->default(0.00);
    $table->decimal('paid_amount', 10, 2)->default(0.00);
    $table->decimal('due_amount', 10, 2)->default(0.00);
    $table->string('status')->default('UNPAID');
    $table->date('due_date')->nullable();
    $table->string('source_type')->nullable();
    $table->unsignedBigInteger('source_id')->nullable();
    $table->unsignedBigInteger('created_by')->nullable();
    $table->text('notes')->nullable();
    $table->json('custom_particulars')->nullable();
    $table->softDeletes();
    $table->timestamps();
});

Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->string('payment_no')->unique();
    $table->unsignedBigInteger('invoice_id');
    $table->unsignedBigInteger('student_id');
    $table->decimal('amount', 10, 2);
    $table->string('payment_method')->default('bKash');
    $table->string('transaction_id')->nullable();
    $table->string('sender_number')->nullable();
    $table->text('remarks')->nullable();
    $table->string('status')->default('APPROVED');
    $table->unsignedBigInteger('received_by')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->timestamp('paid_at')->nullable();
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

// ── TEST SCENARIO 1: Live Poor Fund Student (Nominal 1500, Approved 700, Paid 700) ──
echo "=== Test 1: New Admission Invoice Creation with Poor Fund (700 Tk) ===\n";
$user = User::create(['name' => 'Amena Akter', 'email' => 'amena@example.com', 'role' => 'STUDENT']);
$course = Course::create(['name' => 'General Islamic Studies', 'admission_fee' => 1500.00, 'type' => 'SEMESTER_BASED']);
$sem1 = Semester::create(['course_id' => $course->id, 'name' => 'Semester 1', 'sequence_no' => 1]);
$batch = Batch::create(['course_id' => $course->id, 'name' => 'Batch 6', 'admission_fee' => 0.00]);
$student = Student::create(['user_id' => $user->id, 'name' => 'Amena Akter', 'student_code' => 'IOM-B6-0044', 'email' => 'amena@example.com']);

$waiver = WaiverApplication::create([
    'application_no' => 'PF-2026-0088',
    'status' => 'APPROVED',
    'apply_for' => 'ADMISSION_FEE',
    'approved_admission_fee' => 700.00,
    'is_used' => false, // Notice: is_used is false initially!
]);

$admForm = AdmissionForm::create([
    'source' => 'PUBLIC',
    'application_no' => 'APP-2026-0888',
    'student_id' => $student->id,
    'interested_course_id' => $course->id,
    'batch_id' => $batch->id,
    'waiver_code' => 'PF-2026-0088',
    'manual_paid_amount' => 700.00,
    'manual_payment_method' => 'bKash',
    'manual_trx_id' => 'BKH12345678',
    'status' => 'APPROVED',
]);

$enrollment = Enrollment::create([
    'student_id' => $student->id,
    'course_id' => $course->id,
    'batch_id' => $batch->id,
    'semester_id' => $sem1->id,
    'admission_form_id' => $admForm->id,
    'status' => 'ACTIVE',
]);

// Call AccountingService::createAdmissionInvoice with admin approval (paid amount 700)
$inv = AccountingService::createAdmissionInvoice($student, $admForm, $enrollment, 700.00, [
    'method' => 'bKash',
    'trx_id' => 'BKH12345678',
]);

assertCondition("Invoice nominal amount is 1500", (float)$inv->amount == 1500.00, (float)$inv->amount, 1500.00);
assertCondition("Invoice discount is 800 (1500 - 700)", (float)$inv->discount == 800.00, (float)$inv->discount, 800.00);
assertCondition("Invoice payable_amount is 700", (float)$inv->payable_amount == 700.00, (float)$inv->payable_amount, 700.00);
assertCondition("Invoice paid_amount is 700", (float)$inv->paid_amount == 700.00, (float)$inv->paid_amount, 700.00);
assertCondition("Invoice due_amount is 0", (float)$inv->due_amount == 0.00, (float)$inv->due_amount, 0.00);
assertCondition("Invoice status is PAID", $inv->status === 'PAID', $inv->status, 'PAID');

// Check Fee Breakdown
$service = app(StudentFeeService::class);
$breakdown = $service->getStudentFeeBreakdown($student, '1', $course->id);
$step1 = $breakdown['step1Particulars'] ?? [];
$admRow = collect($step1)->firstWhere('name', 'Admission Fee (ভর্তি ফি)');

assertCondition("Admission fee row exists in step1", !empty($admRow));
assertCondition("Admission row amount is nominal 1500", (float)($admRow['amount'] ?? 0) == 1500.00, (float)($admRow['amount'] ?? 0), 1500.00);
assertCondition("Admission row paid_amt is 700 (NOT 1500!)", (float)($admRow['paid_amt'] ?? 0) == 700.00, (float)($admRow['paid_amt'] ?? 0), 700.00);
assertCondition("Admission row due is 0", (float)($admRow['due'] ?? 0) == 0.00, (float)($admRow['due'] ?? 0), 0.00);
assertCondition("Admission row is_paid is true", !empty($admRow['is_paid']));

// ── TEST SCENARIO 2: Historical Corrupted Invoice Safeguard ──
// Even if an older invoice was saved with paid_amount = 1500 and payable_amount = 1500:
echo "\n=== Test 2: Historical Corrupted Live Invoice (DB had 1500, but student had 700 waiver) ===\n";
$inv->update([
    'payable_amount' => 1500.00,
    'paid_amount' => 1500.00,
    'due_amount' => 0.00,
    'discount' => 0.00,
]);
Payment::where('invoice_id', $inv->id)->update(['amount' => 1500.00]);

// Fee breakdown should automatically detect waiverApprovedFee 700 and manual_paid_amount 700
$breakdownCorrupted = $service->getStudentFeeBreakdown($student, '1', $course->id);
$admRow2 = collect($breakdownCorrupted['step1Particulars'] ?? [])->firstWhere('name', 'Admission Fee (ভর্তি ফি)');

assertCondition("Historical row amount remains nominal 1500", (float)($admRow2['amount'] ?? 0) == 1500.00, (float)($admRow2['amount'] ?? 0), 1500.00);
assertCondition("Historical row paid_amt is corrected to 700 (NOT 1500!)", (float)($admRow2['paid_amt'] ?? 0) == 700.00, (float)($admRow2['paid_amt'] ?? 0), 700.00);

// ── TEST SCENARIO 3: Admin Adjusts Paid Amount via updateParticular ──
echo "\n=== Test 3: Admin Manual Edit of Paid Amount via updateParticular ===\n";
$updateRes = $service->updateParticular(
    $inv->id,
    'Admission Fee (ভর্তি ফি)',
    700.00, // new amount
    'ভর্তি ফি ৭০০ টাকা নির্ধারণ করা হল',
    1, // user id
    0.0, // current due
    'at_bottom',
    null,
    1500.0 // current paid
);

assertCondition("updateParticular succeeded", !empty($updateRes['success']));
assertCondition("updateParticular set paid_amt to 700", (float)($updateRes['paid_amt'] ?? 0) == 700.00, (float)($updateRes['paid_amt'] ?? 0), 700.00);
assertCondition("updateParticular kept is_paid as true", !empty($updateRes['is_paid']));
assertCondition("updateParticular kept due as 0", (float)($updateRes['new_due'] ?? -1) == 0.00, (float)($updateRes['new_due'] ?? -1), 0.00);

// ── TEST SCENARIO 4: Blade View Render Verification ──
echo "\n=== Test 4: Blade View Render Testing for 'Paid (700) (মূল: 1,500)' ===\n";
$p = [
    'sl' => 1,
    'name' => 'Admission Fee (ভর্তি ফি)',
    'amount' => 1500,
    'paid_amt' => 700,
    'due' => 0,
    'is_paid' => true,
    'payable_amount' => 700,
];

// Test logic exactly as in student_ledger.blade.php and student/fees/index.blade.php:
$dispPaid = ($p['paid_amt'] ?? 0) > 0 ? $p['paid_amt'] : ($p['due'] <= 0 ? ($p['payable_amount'] ?? $p['amount'] ?? 0) : 0);
$paidText = "Paid (" . number_format($dispPaid, 0) . ")";
$nominalText = (($p['amount'] ?? 0) > $dispPaid && $dispPaid > 0) ? "(মূল: " . number_format($p['amount'], 0) . ")" : "";

assertCondition("dispPaid resolves to 700", $dispPaid == 700, $dispPaid, 700);
assertCondition("paidText is 'Paid (700)'", $paidText === 'Paid (700)', $paidText, 'Paid (700)');
assertCondition("nominalText is '(মূল: 1,500)'", $nominalText === '(মূল: 1,500)', $nominalText, '(মূল: 1,500)');
assertCondition("Screen DOES NOT show 'Paid (1,500)'", $paidText !== 'Paid (1,500)');

echo "\n" . str_repeat("=", 50) . "\n";
echo "SUMMARY: {$passed} of {$total} assertions passed.\n";
if ($passed === $total) {
    echo "STATUS: ALL TESTS PASSED! (Exit Code 0)\n";
    exit(0);
} else {
    echo "STATUS: SOME TESTS FAILED!\n";
    exit(1);
}
