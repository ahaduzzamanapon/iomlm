<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\GatewayTransaction;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Services\AccountingService;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MultiInvoiceCombinedPaymentTest extends TestCase
{
    use DatabaseTransactions;

    protected Student $student;
    protected User $user;
    protected Invoice $tuitionInvoice;
    protected Invoice $activationInvoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name'              => 'Student ' . rand(100, 999),
            'email'             => 'student_' . uniqid() . '@example.com',
            'password'          => bcrypt('secret'),
            'role'              => 'student',
            'is_common_account' => true,
        ]);

        $this->student = Student::create([
            'user_id'           => $this->user->id,
            'name'              => $this->user->name,
            'phone'             => '0171' . rand(1000000, 9999999),
            'has_course_access' => false, // Initially locked
            'is_common_account' => true,
        ]);

        // 1. Tuition Fee Invoice
        $this->tuitionInvoice = Invoice::create([
            'invoice_no'     => 'INV-SEM-' . rand(1000, 9999),
            'student_id'     => $this->student->id,
            'category'       => 'SEMESTER',
            'title'          => 'Semester Tuition Fee',
            'amount'         => 500.00,
            'payable_amount' => 500.00,
            'paid_amount'    => 0.00,
            'due_amount'     => 500.00,
            'status'         => 'UNPAID',
        ]);

        // 2. Course Activation Fee Invoice
        $this->activationInvoice = Invoice::create([
            'invoice_no'     => 'INV-ACT-' . rand(1000, 9999),
            'student_id'     => $this->student->id,
            'category'       => 'FINE',
            'title'          => 'কোর্স এক্টিভেশন ফি - জানুয়ারি 2027',
            'amount'         => 200.00,
            'payable_amount' => 200.00,
            'paid_amount'    => 0.00,
            'due_amount'     => 200.00,
            'status'         => 'UNPAID',
        ]);
    }

    /**
     * Test settling online gateway transaction distributes payment across both invoices
     * and automatically unlocks student course access.
     */
    public function test_gateway_settlement_distributes_payment_to_both_invoices_and_unlocks_access(): void
    {
        $allocations = [
            $this->tuitionInvoice->id    => 500.00,
            $this->activationInvoice->id => 200.00,
        ];

        $transaction = GatewayTransaction::create([
            'tran_id'        => 'TRAN_TEST_' . uniqid(),
            'gateway'        => 'sslcommerz',
            'gateway_mode'   => 'sandbox',
            'invoice_id'     => $this->tuitionInvoice->id,
            'student_id'     => $this->student->id,
            'amount'         => 700.00,
            'currency'       => 'BDT',
            'customer_name'  => $this->student->name,
            'customer_phone' => $this->student->phone,
            'customer_email' => $this->user->email,
            'status'         => 'INITIATED',
            'raw_response'   => [
                'invoice_allocations' => $allocations,
            ],
        ]);

        $settled = PaymentGatewayService::settleSuccessfulPayment($transaction, [
            'status'       => 'VALID',
            'bank_tran_id' => 'BANK_TRX_' . rand(1000, 9999),
        ]);

        $this->assertTrue($settled);

        // Verify both invoices are now fully PAID
        $this->tuitionInvoice->refresh();
        $this->activationInvoice->refresh();
        $this->student->refresh();

        $this->assertSame('PAID', $this->tuitionInvoice->status);
        $this->assertEquals(0, $this->tuitionInvoice->due_amount);
        $this->assertEquals(500, $this->tuitionInvoice->paid_amount);

        $this->assertSame('PAID', $this->activationInvoice->status);
        $this->assertEquals(0, $this->activationInvoice->due_amount);
        $this->assertEquals(200, $this->activationInvoice->paid_amount);

        // Verify student course & portal access has been automatically unlocked
        $this->assertTrue((bool) $this->student->has_course_access);
    }

    /**
     * Test submitting manual payment with allocations creates payments for each invoice
     * and approving them unlocks student access.
     */
    public function test_manual_payment_with_allocations_splits_invoices_and_approves_cleanly(): void
    {
        $allocations = [
            $this->tuitionInvoice->id    => 500.00,
            $this->activationInvoice->id => 200.00,
        ];

        $trxId = 'TX' . strtoupper(substr(uniqid(), -8));

        $response = $this->actingAs($this->user)->post(
            route('student.fees.pay', $this->tuitionInvoice),
            [
                'payment_method'      => 'BKASH_MANUAL',
                'transaction_id'      => $trxId,
                'sender_number'       => '01712345678',
                'invoice_allocations' => json_encode($allocations),
            ]
        );

        $response->assertSessionHas('success');

        // Verify payments created for each invoice
        $tuitionPayment = Payment::where('invoice_id', $this->tuitionInvoice->id)->where('transaction_id', $trxId)->first();
        $activationPayment = Payment::where('invoice_id', $this->activationInvoice->id)->where('transaction_id', $trxId)->first();

        $this->assertNotNull($tuitionPayment);
        $this->assertEquals(500, $tuitionPayment->amount);
        $this->assertSame('PENDING', $tuitionPayment->status);

        $this->assertNotNull($activationPayment);
        $this->assertEquals(200, $activationPayment->amount);
        $this->assertSame('PENDING', $activationPayment->status);

        // Approve both payments
        AccountingService::approvePayment($tuitionPayment);
        AccountingService::approvePayment($activationPayment);

        $this->tuitionInvoice->refresh();
        $this->activationInvoice->refresh();
        $this->student->refresh();

        $this->assertSame('PAID', $this->tuitionInvoice->status);
        $this->assertSame('PAID', $this->activationInvoice->status);
        $this->assertTrue((bool) $this->student->has_course_access);
    }

    /**
     * Test blade views do not contain multi-invoice restrictions,
     * use 'এক্টিভেশন ফি' instead of 'জরিমানা', and links route to monthly bill.
     */
    public function test_views_have_no_multi_invoice_restriction_and_link_to_monthly_bill(): void
    {
        $feesBlade = file_get_contents(resource_path('views/student/fees/index.blade.php'));
        $dashboardBlade = file_get_contents(resource_path('views/student/dashboard.blade.php'));

        // 1. Restriction alert must not exist
        $this->assertStringNotContainsString('অনলাইন গেটওয়ের মাধ্যমে একসাথে একাধিক ভিন্ন ইনভয়েস পরিশোধ করা যায় না', $feesBlade);

        // 2. Student fee list must not display 'জরিমানা' badge for activation/fine
        $this->assertStringNotContainsString(">{'জরিমানা'}<", $feesBlade);
        $this->assertStringContainsString('এক্টিভেশন ফি', $feesBlade);

        // 3. Due items on dashboard must direct to monthly bill
        $this->assertStringContainsString("route('student.fees.index', ['tab' => 'monthly'])", $dashboardBlade);

        // 4. In student portal history log and subtitles, admin personal names are masked with 'এডমিন'
        $this->assertStringContainsString("isAdminSession", $feesBlade);
        $this->assertStringContainsString("'এডমিন'", $feesBlade);
    }
}
