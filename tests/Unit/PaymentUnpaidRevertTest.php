<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\StudentFeeService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PaymentUnpaidRevertTest extends TestCase
{
    use DatabaseTransactions;

    public function test_revert_particular_payment_cancels_payments_and_zeros_out_paid()
    {
        $course = Course::create([
            'name' => 'Alim Course',
            'code' => 'ALIM-01',
            'type' => 'SEMESTER_BASED',
            'admission_fee' => 1500,
        ]);

        $batch = Batch::create([
            'name' => 'Batch 01',
            'batch_code' => 'B01',
            'course_id' => $course->id,
            'admission_fee' => 1500,
            'start_date' => now(),
        ]);

        $student = Student::create([
            'name' => 'Amina Khatun',
            'email' => 'amina@example.com',
            'student_code' => '202601001',
            'gender' => 'Female',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'batch_id' => $batch->id,
            'status' => 'ACTIVE',
            'enrolled_at' => now(),
        ]);

        $invoice = Invoice::create([
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'category' => 'ADMISSION',
            'title' => 'Admission Fee',
            'amount' => 1500,
            'payable_amount' => 1500,
            'paid_amount' => 1000,
            'due_amount' => 500,
            'status' => 'PARTIAL',
            'invoice_no' => 'INV-TEST-001',
        ]);

        $payment = Payment::create([
            'student_id' => $student->id,
            'invoice_id' => $invoice->id,
            'amount' => 1000,
            'status' => 'APPROVED',
            'payment_method' => 'CASH',
            'payment_no' => 'PAY-TEST-001',
            'paid_at' => now(),
        ]);

        $feeService = app(StudentFeeService::class);

        // Revert payment
        $result = $feeService->revertParticularPayment($invoice->id, 'Admission Fee (ভর্তি ফি)', 'Mistaken payment', null, 1000);

        $this->assertTrue($result['success']);
        
        $invoice->refresh();
        $this->assertEquals(0, $invoice->paid_amount);
        $this->assertEquals(1500, $invoice->due_amount);
        $this->assertEquals('UNPAID', $invoice->status);

        $payment->refresh();
        $this->assertEquals('CANCELLED', $payment->status);

        $data = $feeService->getStudentFeeBreakdown($student, 'admission');
        $admRow = collect($data['step1Particulars'])->firstWhere('name', 'Admission Fee (ভর্তি ফি)');

        $this->assertNotNull($admRow);
        $this->assertEquals(0, $admRow['paid_amt']);
        $this->assertEquals(1500, $admRow['due']);
        $this->assertFalse((bool)$admRow['is_paid']);
    }

    public function test_update_particular_with_reset_paid_clears_previous_paid_amount()
    {
        $course = Course::create([
            'name' => 'Alim Course 2',
            'code' => 'ALIM-02',
            'type' => 'SEMESTER_BASED',
            'admission_fee' => 1500,
        ]);

        $batch = Batch::create([
            'name' => 'Batch 02',
            'batch_code' => 'B02',
            'course_id' => $course->id,
            'admission_fee' => 1500,
            'start_date' => now(),
        ]);

        $student = Student::create([
            'name' => 'Fatima Begum',
            'email' => 'fatima@example.com',
            'student_code' => '202601002',
            'gender' => 'Female',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'batch_id' => $batch->id,
            'status' => 'ACTIVE',
            'enrolled_at' => now(),
        ]);

        $invoice = Invoice::create([
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'category' => 'ADMISSION',
            'title' => 'Admission Fee',
            'amount' => 1500,
            'payable_amount' => 1500,
            'paid_amount' => 1000,
            'due_amount' => 500,
            'status' => 'PARTIAL',
            'invoice_no' => 'INV-TEST-002',
            'custom_particulars' => [
                'Admission Fee (ভর্তি ফি)' => [
                    'amount' => 1500,
                    'paid_amt' => 1000,
                    'due' => 500,
                    'is_paid' => false,
                ]
            ],
        ]);

        $feeService = app(StudentFeeService::class);

        // Admin updates fee to 1500 with resetPaid = true
        $res = $feeService->updateParticular(
            $invoice->id,
            'Admission Fee (ভর্তি ফি)',
            1500,
            'Resetting paid mistake to full due',
            null,
            500,
            null,
            null,
            1000,
            true // resetPaid = true
        );

        $this->assertTrue($res['success']);
        $this->assertEquals(0, $res['paid_amt']);
        $this->assertEquals(1500, $res['new_due']);

        $data = $feeService->getStudentFeeBreakdown($student, 'admission');
        $admRow = collect($data['step1Particulars'])->firstWhere('name', 'Admission Fee (ভর্তি ফি)');

        $this->assertNotNull($admRow);
        $this->assertEquals(0, $admRow['paid_amt']);
        $this->assertEquals(1500, $admRow['due']);
    }

    public function test_admission_fee_with_inflated_custom_amount_and_full_due_normalizes_to_zero_paid()
    {
        $course = Course::create([
            'name' => 'Alim Course 3',
            'code' => 'ALIM-03',
            'type' => 'SEMESTER_BASED',
            'admission_fee' => 1500,
        ]);

        $batch = Batch::create([
            'name' => 'Batch 03',
            'batch_code' => 'B03',
            'course_id' => $course->id,
            'admission_fee' => 1500,
            'start_date' => now(),
        ]);

        $student = Student::create([
            'name' => 'Khadija Khatun',
            'email' => 'khadija@example.com',
            'student_code' => '202601003',
            'gender' => 'Female',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'batch_id' => $batch->id,
            'status' => 'ACTIVE',
            'enrolled_at' => now(),
        ]);

        // Simulating the exact state from user's screenshot: amount = 2500, due = 1500, paid_amt = 1000
        $invoice = Invoice::create([
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'category' => 'ADMISSION',
            'title' => 'Admission Fee',
            'amount' => 2500,
            'payable_amount' => 2500,
            'paid_amount' => 1000,
            'due_amount' => 1500,
            'status' => 'PARTIAL',
            'invoice_no' => 'INV-TEST-003',
            'custom_particulars' => [
                'Admission Fee (ভর্তি ফি)' => [
                    'amount' => 2500,
                    'paid_amt' => 1000,
                    'due' => 1500,
                    'is_paid' => false,
                ]
            ],
        ]);

        $feeService = app(StudentFeeService::class);

        // Safeguard in resolveAdmissionFeeItemData should normalize it immediately
        $data = $feeService->getStudentFeeBreakdown($student, 'admission');
        $admRow = collect($data['step1Particulars'])->firstWhere('name', 'Admission Fee (ভর্তি ফি)');

        $this->assertNotNull($admRow);
        $this->assertEquals(0, $admRow['paid_amt'], 'Paid amount must be normalized to 0');
        $this->assertEquals(1500, $admRow['due'], 'Due must be 1500');
        $this->assertEquals(1500, $admRow['amount'], 'Nominal amount must be normalized to 1500');
        $this->assertFalse((bool)$admRow['is_paid']);

        // Even updating without explicit resetPaid flag should auto-reset when due >= 1500
        $res = $feeService->updateParticular(
            $invoice->id,
            'Admission Fee (ভর্তি ফি)',
            1500,
            'Updating to 1500',
            null,
            1500,
            null,
            null,
            1000,
            false
        );

        $this->assertTrue($res['success']);
        $this->assertEquals(0, $res['paid_amt']);
        $this->assertEquals(1500, $res['new_due']);
        $this->assertEquals(1500, $res['new_amount']);
    }
}
