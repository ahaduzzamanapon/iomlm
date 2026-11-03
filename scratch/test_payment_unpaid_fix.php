<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Enrollment;
use App\Models\AdmissionForm;
use App\Services\StudentFeeService;
use App\Services\AccountingService;

echo "--- Testing Admission Fee Unpaid / Revert Logic ---" . PHP_EOL;

// 1. Setup a dummy student with course & batch
$course = Course::firstOrCreate(
    ['code' => 'TEST-ADM-99'],
    ['name' => 'Test Admission Course', 'admission_fee' => 1500, 'type' => 'SEMESTER_BASED']
);

$batch = Batch::firstOrCreate(
    ['code' => 'TEST-B99'],
    ['name' => 'Test Batch 99', 'course_id' => $course->id, 'admission_fee' => 1500]
);

$student = Student::firstOrCreate(
    ['email' => 'test_unpaid_student@example.com'],
    ['name' => 'Test Unpaid Student', 'gender' => 'Male', 'student_code' => '99010110001']
);

$enrollment = Enrollment::firstOrCreate(
    ['student_id' => $student->id, 'course_id' => $course->id, 'batch_id' => $batch->id],
    ['status' => 'ACTIVE', 'enrolled_at' => now()]
);

$admForm = AdmissionForm::firstOrCreate(
    ['student_id' => $student->id],
    ['applicant_name' => $student->name, 'status' => 'APPROVED']
);

// Create Admission Invoice simulating mistaken 1000 paid / 500 due
$inv = Invoice::updateOrCreate(
    ['student_id' => $student->id, 'category' => 'ADMISSION'],
    [
        'enrollment_id'  => $enrollment->id,
        'title'          => 'Admission Fee — ' . $batch->name,
        'amount'         => 1500,
        'discount'       => 0,
        'payable_amount' => 1500,
        'paid_amount'    => 1000,
        'due_amount'     => 500,
        'status'         => 'PARTIAL',
        'invoice_no'     => 'INV-ADM-TEST-01',
    ]
);

// Payment record of 1000
Payment::updateOrCreate(
    ['invoice_id' => $inv->id],
    ['amount' => 1000, 'status' => 'APPROVED', 'payment_method' => 'CASH']
);

$feeSvc = app(StudentFeeService::class);

echo "Initial State: Paid={$inv->paid_amount}, Due={$inv->due_amount}, Status={$inv->status}" . PHP_EOL;

// Revert Payment using StudentFeeService
$res = $feeSvc->revertParticularPayment($inv->id, 'Admission Fee (ভর্তি ফি)', 'এডমিন ভুল সংশোধনের জন্য সম্পূর্ণ আনপেইড করেছেন', null, 1000);

$inv->refresh();
echo "After Revert State: Paid={$inv->paid_amount}, Due={$inv->due_amount}, Status={$inv->status}" . PHP_EOL;

$part = $feeSvc->buildStudentStep1Particulars($student, 'admission');
print_r($part['step1Particulars']);
