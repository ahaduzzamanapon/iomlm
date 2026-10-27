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

// Configure in-memory sqlite
Config::set('database.default', 'sqlite');
Config::set('database.connections.sqlite', [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);
DB::purge('sqlite');
DB::reconnect('sqlite');

echo "Setting up in-memory sqlite schema for migration test...\n";

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamps();
});

Schema::create('courses', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->decimal('admission_fee', 10, 2)->default(1500.00);
    $table->timestamps();
});

Schema::create('batches', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('course_id');
    $table->string('name');
    $table->timestamps();
});

Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id')->nullable();
    $table->string('student_code')->nullable();
    $table->string('name');
    $table->string('email')->nullable();
    $table->string('temporary_password')->nullable();
    $table->timestamps();
});

Schema::create('waiver_applications', function (Blueprint $table) {
    $table->id();
    $table->string('application_no')->unique();
    $table->string('status')->default('APPROVED');
    $table->string('apply_for')->default('ADMISSION_FEE');
    $table->decimal('approved_admission_fee', 10, 2)->nullable();
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
    $table->decimal('discount_amount', 10, 2)->default(0.00);
    $table->decimal('approved_admission_fee', 10, 2)->nullable();
    $table->decimal('manual_paid_amount', 10, 2)->nullable();
    $table->string('status')->default('APPROVED');
    $table->softDeletes();
    $table->timestamps();
});

Schema::create('enrollments', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('student_id');
    $table->unsignedBigInteger('course_id');
    $table->unsignedBigInteger('batch_id')->nullable();
    $table->unsignedBigInteger('admission_form_id')->nullable();
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
    $table->string('source_type')->nullable();
    $table->unsignedBigInteger('source_id')->nullable();
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
    $table->string('status')->default('APPROVED');
    $table->timestamps();
});

echo "Schema ready. Seeding live-corrupted scenario...\n";

// Scenario: Amena Akter with Poor Fund 700 Tk, but invoice and payment recorded 1500
$course = Course::create(['name' => 'General Islamic Studies', 'admission_fee' => 1500.00]);
$batch = Batch::create(['course_id' => $course->id, 'name' => 'Batch 6']);
$student = Student::create(['name' => 'Amena Akter', 'student_code' => 'IOM-B6-0044']);

$waiver = WaiverApplication::create([
    'application_no' => 'PF-2026-0044',
    'status' => 'APPROVED',
    'apply_for' => 'ADMISSION_FEE',
    'approved_admission_fee' => 700.00,
]);

$form = AdmissionForm::create([
    'application_no' => 'APP-2026-0044',
    'student_id' => $student->id,
    'interested_course_id' => $course->id,
    'batch_id' => $batch->id,
    'waiver_code' => 'PF-2026-0044',
    'discount_percent' => 0.00,
    'discount_amount' => 0.00,
    'manual_paid_amount' => 700.00,
    'status' => 'APPROVED',
]);

$enrollment = Enrollment::create([
    'student_id' => $student->id,
    'course_id' => $course->id,
    'batch_id' => $batch->id,
    'admission_form_id' => $form->id,
]);

// Corrupted Invoice: 1500 paid, 1500 payable, 0 discount
$inv = Invoice::create([
    'invoice_no' => 'INV-ADM-20261014-9999',
    'student_id' => $student->id,
    'enrollment_id' => $enrollment->id,
    'category' => 'ADMISSION',
    'title' => 'Admission Fee — Batch 6',
    'amount' => 1500.00,
    'discount' => 0.00,
    'payable_amount' => 1500.00,
    'paid_amount' => 1500.00,
    'due_amount' => 0.00,
    'status' => 'PAID',
    'source_type' => AdmissionForm::class,
    'source_id' => $form->id,
    'custom_particulars' => [
        'Admission Fee (ভর্তি ফি)' => [
            'amount' => 1500.00,
            'paid_amt' => 1500.00,
            'due' => 0.00,
            'is_paid' => true,
        ]
    ]
]);

$pmt = Payment::create([
    'payment_no' => 'PAY-ADM-20261014-9999',
    'invoice_id' => $inv->id,
    'student_id' => $student->id,
    'amount' => 1500.00,
    'payment_method' => 'bKash',
    'status' => 'APPROVED',
]);

echo "Initial state before migration:\n";
echo "  Invoice #{$inv->invoice_no}: amount={$inv->amount}, payable={$inv->payable_amount}, paid={$inv->paid_amount}, discount={$inv->discount}, status={$inv->status}\n";
echo "  Payment #{$pmt->payment_no}: amount={$pmt->amount}\n";

// Execute migration
echo "\nRunning Migration...\n";
$migration = require __DIR__ . '/../database/migrations/2026_10_19_170000_fix_poor_fund_admission_invoices_and_payments.php';
$migration->up();

$inv->refresh();
$pmt->refresh();
$form->refresh();

echo "\nState after migration:\n";
echo "  Invoice #{$inv->invoice_no}: amount={$inv->amount}, payable={$inv->payable_amount}, paid={$inv->paid_amount}, discount={$inv->discount}, status={$inv->status}\n";
echo "  Payment #{$pmt->payment_no}: amount={$pmt->amount}\n";
echo "  AdmissionForm: discount_percent={$form->discount_percent}%, discount_amount={$form->discount_amount}, approved_fee={$form->approved_admission_fee}\n";

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

echo "\nVerifying Assertions:\n";
assertCondition("Invoice amount remains nominal 1500", (float)$inv->amount == 1500.00, (float)$inv->amount, 1500.00);
assertCondition("Invoice discount is corrected to 800 (1500 - 700)", (float)$inv->discount == 800.00, (float)$inv->discount, 800.00);
assertCondition("Invoice payable_amount is corrected to 700", (float)$inv->payable_amount == 700.00, (float)$inv->payable_amount, 700.00);
assertCondition("Invoice paid_amount is corrected to 700 (NOT 1500!)", (float)$inv->paid_amount == 700.00, (float)$inv->paid_amount, 700.00);
assertCondition("Invoice due_amount is 0", (float)$inv->due_amount == 0.00, (float)$inv->due_amount, 0.00);
assertCondition("Invoice status remains PAID", $inv->status === 'PAID', $inv->status, 'PAID');
assertCondition("Payment amount is corrected to 700", (float)$pmt->amount == 700.00, (float)$pmt->amount, 700.00);
assertCondition("AdmissionForm discount_percent is 53.33%", (float)$form->discount_percent == 53.33, (float)$form->discount_percent, 53.33);
assertCondition("AdmissionForm discount_amount is 800", (float)$form->discount_amount == 800.00, (float)$form->discount_amount, 800.00);
assertCondition("AdmissionForm approved_admission_fee is 700", (float)$form->approved_admission_fee == 700.00, (float)$form->approved_admission_fee, 700.00);
assertCondition("Custom particulars paid_amt is corrected to 700", (float)($inv->custom_particulars['Admission Fee (ভর্তি ফি)']['paid_amt'] ?? 0) == 700.00);

echo "\n" . str_repeat("=", 50) . "\n";
echo "SUMMARY: {$passed} of {$total} assertions passed.\n";
if ($passed === $total) {
    echo "STATUS: ALL MIGRATION TESTS PASSED! (Exit Code 0)\n";
    exit(0);
} else {
    echo "STATUS: SOME TESTS FAILED!\n";
    exit(1);
}
