<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\StudentFeeService;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ActivationFeePositionAndAutoSelectTest extends TestCase
{
    use DatabaseTransactions;

    protected StudentFeeService $feeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->feeService = app(StudentFeeService::class);
    }

    /**
     * Test that month and year are accurately parsed from English and Bangla names
     */
    public function test_month_and_year_parsing_from_particular_names()
    {
        $tuition = $this->feeService->parseMonthAndYearFromParticularName('Tuition Fee (Jan-2027)');
        $this->assertNotNull($tuition);
        $this->assertEquals(1, $tuition['month']);
        $this->assertEquals(2027, $tuition['year']);
        $this->assertEquals('2027-01', $tuition['tag']);

        $activationBn = $this->feeService->parseMonthAndYearFromParticularName('কোর্স এক্টিভেশন ফি - জানুয়ারি 2027');
        $this->assertNotNull($activationBn);
        $this->assertEquals(1, $activationBn['month']);
        $this->assertEquals(2027, $activationBn['year']);
        $this->assertEquals('2027-01', $activationBn['tag']);

        // Assert they share identical month tag
        $this->assertEquals($tuition['tag'], $activationBn['tag']);

        $febActivation = $this->feeService->parseMonthAndYearFromParticularName('কোর্স এক্টিভেশন ফি - ফেব্রুয়ারি 2027');
        $this->assertEquals('2027-02', $febActivation['tag']);
    }

    /**
     * Test that an activation fee for a specific month is positioned directly ABOVE that month's tuition fee
     */
    public function test_activation_fee_is_positioned_directly_above_corresponding_month_tuition_fee()
    {
        $user = User::create([
            'name'     => 'Test Student',
            'email'    => 'test_student_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role'     => 'student',
        ]);
        $course = Course::create([
            'name'           => 'Test Course ' . uniqid(),
            'code'           => 'TC-' . rand(100, 999),
            'type'           => 'SEMESTER_BASED',
            'admission_fee'  => 1500,
        ]);
        $batch = Batch::create([
            'name'          => 'Batch 2027',
            'batch_code'    => 'B27-' . rand(100, 999),
            'course_id'     => $course->id,
            'admission_fee' => 1500,
            'start_date'    => '2027-01-01',
        ]);
        $student = Student::create([
            'user_id'           => $user->id,
            'name'              => 'Student ' . uniqid(),
            'email'             => $user->email,
            'student_code'      => 'STD-' . rand(1000, 9999),
            'gender'            => 'Male',
            'status'            => 'ACTIVE',
            'has_course_access' => true,
        ]);
        $enrollment = Enrollment::create([
            'student_id'  => $student->id,
            'course_id'   => $course->id,
            'batch_id'    => $batch->id,
            'status'      => 'ACTIVE',
            'enrolled_at' => now(),
        ]);

        // Create semester tuition invoice
        $semesterInvoice = Invoice::create([
            'invoice_no'     => 'INV-SEM-' . uniqid(),
            'student_id'     => $student->id,
            'enrollment_id'  => $enrollment->id,
            'category'       => 'SEMESTER',
            'title'          => 'Semester 1 Tuition & Fees',
            'amount'         => 3000,
            'payable_amount' => 3000,
            'paid_amount'    => 0,
            'due_amount'     => 3000,
            'status'         => 'UNPAID',
            'due_date'       => '2027-01-15',
        ]);

        // Create course activation fee invoice for January 2027
        $activationInvoice = Invoice::create([
            'invoice_no'     => 'INV-ACT-' . uniqid(),
            'student_id'     => $student->id,
            'enrollment_id'  => $enrollment->id,
            'category'       => 'FINE',
            'title'          => 'কোর্স এক্টিভেশন ফি - জানুয়ারি 2027 (জানুয়ারি মাসের বেতন বকেয়া থাকায় বিলম্ব এক্টিভেশন চার্জ)',
            'amount'         => 100,
            'payable_amount' => 100,
            'paid_amount'    => 0,
            'due_amount'     => 100,
            'status'         => 'UNPAID',
            'due_date'       => '2027-01-31',
        ]);

        $breakdown = $this->feeService->getStudentFeeBreakdown($student);
        $particulars = $breakdown['step1Particulars'];

        $activationIndex = null;
        $tuitionJanIndex = null;

        foreach ($particulars as $index => $item) {
            if (str_contains($item['name'], 'এক্টিভেশন ফি') || str_contains($item['name'], 'এক্টিভিশন ফি')) {
                $activationIndex = $index;
            }
            if ($item['name'] === 'Tuition Fee (Jan-2027)') {
                $tuitionJanIndex = $index;
            }
        }

        $this->assertNotNull($activationIndex, 'Activation fee item must be present in particulars');
        $this->assertNotNull($tuitionJanIndex, 'Tuition Fee (Jan-2027) must be present in particulars');

        // The activation fee must appear directly BEFORE Tuition Fee (Jan-2027)
        $this->assertEquals($tuitionJanIndex - 1, $activationIndex, 'Activation fee must be positioned immediately above Tuition Fee (Jan-2027)');
        $this->assertEquals('2027-01', $particulars[$activationIndex]['month_tag']);
        $this->assertTrue($particulars[$activationIndex]['is_activation']);

        // Verify that rendering the view does NOT output 'জরিমানা' and displays 'এক্টিভেশন ফি'
        $viewHtml = view('student.fees.index', array_merge($breakdown, [
            'sslActive'    => false,
            'bkashActive'  => false,
            'courseType'   => 'SEMESTER_BASED',
        ]))->render();

        $this->assertStringContainsString('এক্টিভেশন ফি', $viewHtml);
        $this->assertStringNotContainsString('জরিমানা', $viewHtml);
        $this->assertStringContainsString('data-is-activation="1"', $viewHtml);
        $this->assertStringContainsString('data-month-tag="2027-01"', $viewHtml);
    }

    /**
     * Test that paying the activation fee automatically restores course access
     */
    public function test_paying_activation_fee_restores_course_access()
    {
        $user = User::create([
            'name'      => 'Suspended Student',
            'email'     => 'suspended_' . uniqid() . '@example.com',
            'password'  => bcrypt('password'),
            'role'      => 'student',
            'is_active' => false,
        ]);
        $student = Student::create([
            'user_id'           => $user->id,
            'name'              => 'Suspended Student',
            'email'             => $user->email,
            'student_code'      => 'STD-' . rand(1000, 9999),
            'gender'            => 'Female',
            'status'            => 'ACTIVE',
            'has_course_access' => false, // Access was blocked due to overdue fee
        ]);

        $activationInvoice = Invoice::create([
            'invoice_no'     => 'INV-ACT-' . uniqid(),
            'student_id'     => $student->id,
            'category'       => 'FINE',
            'title'          => 'কোর্স এক্টিভেশন ফি - জানুয়ারি 2027',
            'amount'         => 100,
            'payable_amount' => 100,
            'paid_amount'    => 0,
            'due_amount'     => 100,
            'status'         => 'UNPAID',
        ]);

        $this->assertFalse($student->fresh()->has_course_access);

        // Receive payment for activation fee
        AccountingService::receivePayment(
            $activationInvoice,
            100.00,
            'BKASH',
            'TRX-ACT-999',
            'এক্টিভেশন ফি পরিশোধ'
        );

        $student->refresh();
        $this->assertTrue($student->has_course_access, 'has_course_access must be restored to true after activation fee payment');
        $this->assertTrue((bool)$student->user->fresh()->is_active, 'user is_active must be restored to true after activation fee payment');
    }
}
