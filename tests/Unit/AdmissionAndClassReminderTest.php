<?php

namespace Tests\Unit;

use App\Models\AdmissionForm;
use App\Models\Batch;
use App\Models\Course;
use App\Models\EmailTemplate;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdmissionAndClassReminderTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test Item 20: Daily Morning Class Reminder Command executes with exit code 0.
     */
    public function test_daily_class_reminder_command_executes_successfully(): void
    {
        $exitCode = Artisan::call('classes:send-daily-reminders', [
            '--dry-run' => true,
        ]);

        $this->assertSame(0, $exitCode);
    }

    /**
     * Test Item 21 (Part 1): Admission SMS template seeding, resolution, and English content.
     */
    public function test_admission_sms_template_seeds_and_resolves_english_text(): void
    {
        EmailTemplate::seedDefaultTemplates();

        $smsTpl = EmailTemplate::where('type', 'SMS')
            ->where('category', 'ADMISSION')
            ->first();

        $this->assertNotNull($smsTpl, 'Admission SMS template must exist in email_templates table');
        $this->assertSame('SMS', $smsTpl->type);
        $this->assertSame('ADMISSION', $smsTpl->category);

        $expectedTemplate = "Salam {name}, IOM admission confirmed. ID: {student_id} Pass: {password} Login: {login_url}. Check email for details.\n-IOM";

        $this->assertSame(
            trim(str_replace("\r\n", "\n", $expectedTemplate)),
            trim(str_replace("\r\n", "\n", $smsTpl->content))
        );

        // Verify resolution
        $resolved = EmailTemplate::resolveAdmissionSmsTemplate(null, null);
        $this->assertNotNull($resolved);
        $this->assertFalse((bool) preg_match('/[\x{0980}-\x{09FF}]/u', $resolved->content), 'Admission SMS must not contain Bengali text');
        $this->assertStringContainsString('Salam {name}, IOM admission confirmed.', $resolved->content);

        // Verify Batch fallback
        $batch = new Batch();
        $batch->sms_template = 'বাংলায় ভর্তি নিশ্চিত করা হলো'; // Legacy Bengali in batch
        $effectiveSms = $batch->getEffectiveSmsTemplate();
        $this->assertFalse((bool) preg_match('/[\x{0980}-\x{09FF}]/u', $effectiveSms), 'Legacy Bengali in batch must be superseded by English default');
        $this->assertStringContainsString('Salam {name}, IOM admission confirmed.', $effectiveSms);
    }

    /**
     * Test Item 21 (Part 2): Full Admission Approval Controller Flow works cleanly without error.
     */
    public function test_admission_approval_executes_cleanly_without_error(): void
    {
        // 1. Create admin user
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 2. Resolve an active course and batch
        $course = Course::first() ?? Course::create([
            'name' => 'Alim Course',
            'code' => 'ALM',
            'department' => 'Shariah',
            'is_active' => true,
        ]);

        $batch = Batch::first() ?? Batch::create([
            'course_id'  => $course->id,
            'name'       => 'Batch 01',
            'start_date' => now()->toDateString(),
            'status'     => 'ACTIVE',
        ]);

        // 3. Create a pending admission applicant
        $student = Student::create([
            'name'   => 'Test Applicant',
            'email'  => 'applicant_' . uniqid() . '@example.com',
            'phone'  => '01700000001',
            'gender' => 'Male',
            'status' => 'LEAD',
        ]);

        $admission = AdmissionForm::create([
            'source'               => 'PUBLIC',
            'application_no'       => 'APP-' . rand(10000, 99999),
            'student_id'           => $student->id,
            'interested_course_id' => $course->id,
            'batch_id'             => null,
            'gender'               => 'Male',
            'status'               => 'PENDING',
        ]);

        // 4. Admin submits approval request
        $response = $this->actingAs($admin)->patch(route('admin.admissions.approve', $admission), [
            'batch_id'              => $batch->id,
            'course_id'             => $course->id,
            'is_fee_paid'           => 1,
            'admission_paid_amount' => 500,
            'payment_method'        => 'bKash',
            'transaction_id'        => 'TRX_TEST_' . rand(1000, 9999),
            'sender_number'         => '01700000001',
            'send_email'            => 0,
            'send_sms'              => 0,
            'save_template'         => 0,
        ]);

        // Assert redirect back with success banner (no 500 internal server error)
        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $response->assertSessionMissing('error');

        // Verify admission updated
        $admission->refresh();
        $this->assertSame('APPROVED', $admission->status);
        $this->assertEquals($admin->id, $admission->reviewed_by);

        // Verify student record updated & code assigned
        $student->refresh();
        $this->assertSame('ACTIVE', $student->status);
        $this->assertNotEmpty($student->student_code);
        $this->assertNotNull($student->user_id);

        // Verify enrollment created
        $enrollment = Enrollment::where('admission_form_id', $admission->id)->first();
        $this->assertNotNull($enrollment);
        $this->assertSame('ACTIVE', $enrollment->status);
        $this->assertEquals($batch->id, $enrollment->batch_id);

        // Verify admission invoice created
        $invoice = Invoice::where('enrollment_id', $enrollment->id)->where('category', 'ADMISSION')->first();
        $this->assertNotNull($invoice);
        $this->assertGreaterThanOrEqual(0, $invoice->amount);
    }
}
