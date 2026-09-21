<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Services\AccountingService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

echo "=== TASK 22: VERIFY PAYMENT SYSTEM OVERHAUL ===\n\n";

$assertions = 0;

function assertCondition($condition, $message) {
    global $assertions;
    if ($condition) {
        echo "  [PASS] {$message}\n";
        $assertions++;
    } else {
        echo "  [FAIL] {$message}\n";
        exit(1);
    }
}

// ── 1. Database Schema Checks ──
echo "1. Database Schema Validation:\n";
assertCondition(Schema::hasColumn('payments', 'sender_number'), "payments table has 'sender_number' column");
assertCondition(Schema::hasColumn('invoices', 'notes'), "invoices table has 'notes' column");

// ── 2. Setup Test Student and Enrollment ──
echo "\n2. Test Student Setup:\n";
$student = Student::where('status', 'ACTIVE')->first();
if (!$student) {
    $user = User::firstOrCreate(
        ['email' => 'test_payment_student@iom.edu.bd'],
        ['name' => 'Test Payment Student', 'password' => bcrypt('password')]
    );
    $student = Student::create([
        'user_id' => $user->id,
        'student_code' => '26261519999',
        'name' => 'Test Payment Student',
        'phone' => '01700000001',
        'email' => $user->email,
        'gender' => 'MALE',
        'status' => 'ACTIVE',
    ]);
}
assertCondition($student && $student->id > 0, "Student resolved: ID #{$student->id} ({$student->name})");

// Ensure active enrollment
$enrollment = $student->enrollments()->first();
if (!$enrollment) {
    $batch = Batch::first();
    $enrollment = Enrollment::create([
        'student_id' => $student->id,
        'course_id' => $batch?->course_id ?? 1,
        'batch_id' => $batch?->id ?? 1,
        'status' => 'ACTIVE',
    ]);
}
assertCondition($enrollment && $enrollment->id > 0, "Active enrollment linked: ID #{$enrollment->id}");

// ── 3. Fee Generation (New Fee & Extra Fee with Reason) ──
echo "\n3. Fee Generation (New Fee, Extra Fee & Purpose / Notes):\n";
$invNo = 'INV-TEST-' . rand(10000, 99999);
$newInvoice = Invoice::create([
    'invoice_no'     => $invNo,
    'student_id'     => $student->id,
    'enrollment_id'  => $enrollment->id,
    'category'       => 'EXTRA',
    'title'          => 'বিলম্ব জরিমানা (Late Fine)',
    'notes'          => 'নির্ধারিত সময়ের পর ফি পরিশোধের জন্য ধার্যকৃত বিলম্ব জরিমানা',
    'amount'         => 300.00,
    'discount'       => 50.00,
    'payable_amount' => 250.00,
    'paid_amount'    => 0.00,
    'due_amount'     => 250.00,
    'status'         => 'UNPAID',
    'due_date'       => now()->addDays(5),
]);

assertCondition($newInvoice->id > 0, "Invoice created with category EXTRA");
assertCondition($newInvoice->notes === 'নির্ধারিত সময়ের পর ফি পরিশোধের জন্য ধার্যকৃত বিলম্ব জরিমানা', "Invoice has reason/notes stored properly: '{$newInvoice->notes}'");
assertCondition($newInvoice->payable_amount == 250.00 && $newInvoice->due_amount == 250.00, "Amounts calculated correctly (300 - 50 = 250)");

// ── 4. Adjust Fee Amount (Increase / Decrease) ──
echo "\n4. Adjusting Fee Amount (Increase / Decrease):\n";
$accountsController = new \App\Http\Controllers\Admin\AccountsController();

// Modify amount to 400 with discount 100 -> payable 300
$reqUpdate = Request::create("/admin/accounts/invoices/{$newInvoice->id}", 'PUT', [
    'title'    => 'বিলম্ব জরিমানা সংশোধিত (Adjusted Late Fine)',
    'category' => 'EXTRA',
    'notes'    => 'কর্তৃপক্ষের অনুমোদনক্রমে ফি পরিমাণ সমন্বয় করা হলো',
    'amount'   => 400.00,
    'discount' => 100.00,
    'due_date' => now()->addDays(7)->toDateString(),
]);

$accountsController->updateInvoice($reqUpdate, $newInvoice);
$newInvoice->refresh();

assertCondition($newInvoice->title === 'বিলম্ব জরিমানা সংশোধিত (Adjusted Late Fine)', "Title updated properly");
assertCondition($newInvoice->notes === 'কর্তৃপক্ষের অনুমোদনক্রমে ফি পরিমাণ সমন্বয় করা হলো', "Notes updated properly");
assertCondition($newInvoice->amount == 400.00 && $newInvoice->discount == 100.00 && $newInvoice->payable_amount == 300.00, "Amount increased/adjusted properly (400 - 100 = 300)");
assertCondition($newInvoice->due_amount == 300.00, "Due amount recalculated to 300.00");

// ── 5. Manual Payment with bKash Sender Number & TrxID ──
echo "\n5. Manual bKash Payment with Sender Number & TrxID:\n";
$payment = AccountingService::receivePayment(
    $newInvoice,
    150.00,
    'BKASH',
    'TRX-BKASH-998877',
    'Manual bKash counter collection',
    '01711223344'
);

$newInvoice->refresh();
assertCondition($payment->id > 0, "Payment record created: ID #{$payment->id}");
assertCondition($payment->payment_method === 'BKASH', "Payment method is BKASH");
assertCondition($payment->sender_number === '01711223344', "bKash sender number stored in Payment: {$payment->sender_number}");
assertCondition($payment->transaction_id === 'TRX-BKASH-998877', "bKash TrxID stored in Payment: {$payment->transaction_id}");
assertCondition($newInvoice->paid_amount == 150.00 && $newInvoice->due_amount == 150.00, "Invoice paid_amount updated to 150 and due_amount to 150");
assertCondition($newInvoice->status === 'PARTIAL', "Invoice status updated to PARTIAL");

// ── 6. Update Payment Status of Specific Fee ──
echo "\n6. Update Payment Status of Specific Fee:\n";
// Settle remaining 150 with bKash and set status to PAID
$reqStatusPaid = Request::create("/admin/accounts/invoices/{$newInvoice->id}/status", 'PATCH', [
    'status'         => 'PAID',
    'payment_method' => 'BKASH',
    'sender_number'  => '01899887766',
    'transaction_id' => 'TRX-BKASH-SETTLE123',
    'remarks'        => 'Settled full balance via bKash',
]);

$accountsController->updateInvoiceStatus($reqStatusPaid, $newInvoice);
$newInvoice->refresh();

assertCondition($newInvoice->status === 'PAID', "Invoice status successfully updated to PAID");
assertCondition($newInvoice->due_amount == 0.00, "Invoice due_amount is now 0.00");
assertCondition($newInvoice->paid_amount == 300.00, "Invoice paid_amount is now 300.00");

$lastPayment = $newInvoice->payments()->latest('id')->first();
assertCondition($lastPayment && $lastPayment->sender_number === '01899887766', "Settlement payment recorded with bKash sender number {$lastPayment?->sender_number}");

// Test CANCELLED status on an invoice
$cancelInv = Invoice::create([
    'invoice_no'     => 'INV-CANCEL-TEST-' . rand(1000, 9999),
    'student_id'     => $student->id,
    'enrollment_id'  => $enrollment->id,
    'category'       => 'EXTRA',
    'title'          => 'ভুল ফি টেস্ট (Mistaken Fee Test)',
    'notes'          => 'ভুলবশত এন্ট্রি করা হয়েছিল',
    'amount'         => 500.00,
    'discount'       => 0.00,
    'payable_amount' => 500.00,
    'paid_amount'    => 0.00,
    'due_amount'     => 500.00,
    'status'         => 'UNPAID',
]);

$reqStatusCancel = Request::create("/admin/accounts/invoices/{$cancelInv->id}/status", 'PATCH', [
    'status'  => 'CANCELLED',
    'remarks' => 'অফিস ভুল সংশোধনে ফি মওকুফ',
]);

$accountsController->updateInvoiceStatus($reqStatusCancel, $cancelInv);
$cancelInv->refresh();

assertCondition($cancelInv->status === 'CANCELLED', "Invoice status changed to CANCELLED");
assertCondition($cancelInv->due_amount == 0.00, "Cancelled invoice due_amount set to 0.00");
assertCondition(str_contains($cancelInv->notes, 'বাতিল/মওকুফ'), "Audit remark appended to invoice notes");

// ── 7. Safe Fee Removal (Delete Unpaid Fee) ──
echo "\n7. Safe Fee Removal (Delete Mistaken Unpaid Fee):\n";
$delInv = Invoice::create([
    'invoice_no'     => 'INV-DEL-' . rand(1000, 9999),
    'student_id'     => $student->id,
    'enrollment_id'  => $enrollment->id,
    'category'       => 'EXTRA',
    'title'          => 'ভুল অতিরিক্ত ফি (Wrong Extra Fee)',
    'amount'         => 200.00,
    'discount'       => 0.00,
    'payable_amount' => 200.00,
    'paid_amount'    => 0.00,
    'due_amount'     => 200.00,
    'status'         => 'UNPAID',
]);

$delInvId = $delInv->id;
$reqDel = Request::create("/admin/accounts/invoices/{$delInvId}", 'DELETE');
$accountsController->destroyInvoice($reqDel, $delInv);

assertCondition(!Invoice::find($delInvId), "Unpaid mistaken invoice successfully deleted from active database");

// ── 8. Student Portal Fee Submission with bKash Sender Number ──
echo "\n8. Student Portal Fee Submission with bKash Sender Number:\n";
$stuInv = Invoice::create([
    'invoice_no'     => 'INV-STU-' . rand(1000, 9999),
    'student_id'     => $student->id,
    'enrollment_id'  => $enrollment->id,
    'category'       => 'SEMESTER',
    'title'          => 'মাসিক বেতন (Monthly Installment)',
    'amount'         => 1000.00,
    'discount'       => 0.00,
    'payable_amount' => 1000.00,
    'paid_amount'    => 0.00,
    'due_amount'     => 1000.00,
    'status'         => 'UNPAID',
]);

$stuPay = AccountingService::submitStudentPayment(
    $stuInv,
    500.00,
    'BKASH',
    'TRX-ONLINE-BKASH-776655',
    '1st Month Installment',
    '01911998877'
);

assertCondition($stuPay->id > 0, "Student payment submitted: ID #{$stuPay->id}");
assertCondition($stuPay->status === 'PENDING', "Student manual payment status is PENDING");
assertCondition($stuPay->payment_method === 'BKASH', "Student payment method is BKASH");
assertCondition($stuPay->sender_number === '01911998877', "bKash sender number recorded: {$stuPay->sender_number}");
assertCondition($stuPay->transaction_id === 'TRX-ONLINE-BKASH-776655', "bKash TrxID recorded: {$stuPay->transaction_id}");

// Admin approves student payment
AccountingService::approvePayment($stuPay);
$stuInv->refresh();
$stuPay->refresh();
assertCondition($stuPay->status === 'APPROVED', "Payment approved by admin");
assertCondition($stuInv->paid_amount == 500.00 && $stuInv->due_amount == 500.00, "Invoice updated upon payment approval");

// ── 9. View Render Validations ──
echo "\n9. View Rendering Validation:\n";

// Login as admin for layout checks
$adminUser = User::where('role', 'super_admin')->first() ?? User::first();
auth()->login($adminUser);

// 9a. Admin Student Ledger
$ledgerHtml = view('admin.accounts.student_ledger', [
    'student'     => $student,
    'invoices'    => Invoice::where('student_id', $student->id)->with(['payments', 'enrollment.course'])->latest()->get(),
    'payments'    => Payment::where('student_id', $student->id)->with(['invoice', 'receivedBy'])->latest('paid_at')->get(),
    'totalBilled' => 1000.00,
    'totalPaid'   => 500.00,
    'totalDue'    => 500.00,
])->render();

assertCondition(str_contains($ledgerHtml, 'অতিরিক্ত ফি (Extra Fee)'), "Ledger contains 'অতিরিক্ত ফি (Extra Fee)' action button");
assertCondition(str_contains($ledgerHtml, 'নতুন ফি / পেমেন্ট ধার্য'), "Ledger contains 'নতুন ফি / পেমেন্ট ধার্য' action button");
assertCondition(str_contains($ledgerHtml, 'বিকাশ / প্রেরক মোবাইল নম্বর'), "Ledger collect modal contains 'বিকাশ / প্রেরক মোবাইল নম্বর'");
assertCondition(str_contains($ledgerHtml, 'পেমেন্ট স্ট্যাটাস পরিবর্তন করুন'), "Ledger contains status update modal");
assertCondition(str_contains($ledgerHtml, 'বিবরণ ও ফি ধার্যের কারণ'), "Ledger table has column for reason / why generated");

// 9b. Official Money Receipt / Voucher
$receiptHtml = view('admin.accounts.print_receipt', [
    'payment' => $payment->load(['student', 'invoice', 'receivedBy']),
])->render();

assertCondition(str_contains($receiptHtml, 'Sender / bKash No:'), "Money Receipt displays 'Sender / bKash No:'");
assertCondition(str_contains($receiptHtml, '01711223344'), "Money Receipt displays actual sender number 01711223344");
assertCondition(str_contains($receiptHtml, 'TRX-BKASH-998877'), "Money Receipt displays Trx ID TRX-BKASH-998877");

// 9c. Student Dashboard (Separated Monthly & Due Sections)
auth()->login($student->user ?? $adminUser);

$dashHtml = view('student.dashboard', [
    'student'               => $student,
    'stats'                 => [
        'enrolled_courses'   => 1,
        'upcoming_classes'   => 0,
        'attendance_percent' => 100,
        'upcoming_exams'     => 0,
    ],
    'currentModules'        => collect(),
    'upcomingClasses'       => collect(),
    'recentResults'         => collect(),
    'upcomingExamsList'     => collect(),
    'notices'               => collect(),
    'dashboardMonthly'      => [
        ['month_no' => 1, 'name' => '১ম মাস', 'rate' => 500, 'status' => 'PAID'],
        ['month_no' => 2, 'name' => '২য় মাস', 'rate' => 500, 'status' => 'UNPAID'],
    ],
    'runningSemesterName'   => 'সেমিস্টার ১',
    'runningSemDue'         => 500.00,
    'runningSemPaid'        => 500.00,
    'dueInvoices'           => Invoice::where('student_id', $student->id)->where('due_amount', '>', 0)->get(),
    'paidInvoices'          => Invoice::where('student_id', $student->id)->where('due_amount', '<=', 0)->get(),
    'totalOverallDue'       => 500.00,
    'totalOverallPaid'      => 500.00,
    'recentVoucherPayments' => Payment::where('student_id', $student->id)->latest('paid_at')->take(5)->get(),
])->render();

assertCondition(str_contains($dashHtml, 'মান্থলি পেমেন্ট (Monthly Fees Breakdown)'), "Student Dashboard has separated Monthly Fees section");
assertCondition(str_contains($dashHtml, 'ডিউ পেমেন্ট, পরিশোধিত ফি ও ভাউচার (Dues & Payment Vouchers)'), "Student Dashboard has separated Due & Vouchers section");
assertCondition(str_contains($dashHtml, 'বর্তমান বকেয়া ফি সমূহ'), "Dashboard Dues section lists outstanding dues");
assertCondition(str_contains($dashHtml, 'পেমেন্ট হিস্ট্রি ও ভাউচার'), "Dashboard Dues section lists payment history & vouchers");

// 9d. Student Fees Portal (Separated Tabs for Monthly and Dues)
$course = Course::first();
$feesHtml = view('student.fees.index', [
    'student'                => $student,
    'course'                 => $course,
    'courseType'             => 'SEMESTER_BASED',
    'runningSemester'        => null,
    'runningSemesterName'    => 'সেমিস্টার ১',
    'invoices'               => Invoice::where('student_id', $student->id)->latest()->get(),
    'payments'               => Payment::where('student_id', $student->id)->latest('paid_at')->get(),
    'totalDue'               => 500.00,
    'totalPaid'              => 500.00,
    'runningSemesterDue'     => 500.00,
    'semesterBreakdown'      => collect([
        [
            'label'        => 'সেমিস্টার ১ 🔵',
            'category'     => 'SEMESTER',
            'isRunning'    => true,
            'payable'      => 3000,
            'paid'         => 1500,
            'due'          => 1500,
            'hasInvoice'   => true,
            'invoice'      => $stuInv,
            'monthlyRate'  => 500,
            'totalMonths'  => 6,
            'monthlyItems' => [
                ['month_no' => 1, 'label' => '১ম মাস (Month 1)', 'payable' => 500, 'paid' => 500, 'due' => 0, 'status' => 'PAID'],
                ['month_no' => 2, 'label' => '২য় মাস (Month 2)', 'payable' => 500, 'paid' => 0, 'due' => 500, 'status' => 'UNPAID'],
            ],
        ],
    ]),
    'studentCourses'         => collect([$course]),
    'packageItemsBreakdown'  => collect(),
    'sslActive'              => true,
    'bkashActive'            => true,
])->render();

assertCondition(str_contains($feesHtml, 'id="portalSection_monthly"'), "Student Fees portal has dedicated Monthly Payments section container");
assertCondition(str_contains($feesHtml, 'id="portalSection_dues"'), "Student Fees portal has dedicated Dues & Vouchers section container");
assertCondition(str_contains($feesHtml, 'ম্যানুয়াল বিকাশ ট্রানজেকশন'), "Payment modal has manual bKash transaction option");
assertCondition(str_contains($feesHtml, 'বিকাশ / প্রেরক মোবাইল নম্বর'), "Payment modal has bKash / Sender Mobile Number field");
assertCondition(str_contains($feesHtml, 'বিকাশ / প্রেরক নম্বর'), "Payment history table has bKash / Sender number column header");

// Clean up temporary test invoices
$newInvoice->payments()->delete();
$newInvoice->delete();
$cancelInv->delete();
$stuInv->payments()->delete();
$stuInv->delete();

echo "\n============================================\n";
echo "SUCCESS! All {$assertions} assertions passed with Exit Code 0.\n";
echo "Task 22: Payment System Overhaul is fully verified!\n";
echo "============================================\n";

