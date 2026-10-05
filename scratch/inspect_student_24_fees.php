<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Batch;
use App\Models\Course;
use App\Models\CourseFeePackage;
use App\Models\CourseFeeItem;
use App\Models\Invoice;
use App\Models\AdmissionForm;

echo "=== INSPECTING STUDENT 24 (Mazharul Islam Hridoy) ===\n";
$st = Student::find(24);
if ($st) {
    echo "Student: {$st->name} (Code: {$st->student_code}, ID: {$st->id}, User ID: {$st->user_id})\n";
    echo "Enrollments:\n";
    foreach ($st->enrollments as $en) {
        $b = $en->batch;
        $c = $en->course;
        echo "  - Enrollment ID: {$en->id}, Status: {$en->status}\n";
        echo "    Course: " . ($c ? "{$c->name} (ID: {$c->id}, Fee Start: {$c->fee_start_month}, Fee End: {$c->fee_end_month})" : 'None') . "\n";
        echo "    Batch: " . ($b ? "{$b->name} (ID: {$b->id}, Start Date: {$b->start_date}, Fee Start: {$b->fee_start_month}, Fee End: {$b->fee_end_month}, Monthly Fee: {$b->monthly_fee}, Admission Fee: {$b->admission_fee})" : 'None') . "\n";
    }

    echo "\nInvoices for Student 24:\n";
    foreach (Invoice::where('student_id', $st->id)->get() as $inv) {
        echo "  - Invoice ID: {$inv->id}, Inv No: {$inv->invoice_no}, Title: '{$inv->title}', Category: {$inv->category}, Total: {$inv->total_amount}, Discount: {$inv->discount_amount}, Payable: {$inv->payable_amount}, Paid: {$inv->paid_amount}, Due: {$inv->due_amount}, Status: {$inv->status}, Particulars: " . json_encode($inv->particulars) . "\n";
    }

echo "\n=== PACKAGE 19 DETAILS ===\n";
$pkg19 = CourseFeePackage::find(19);
if ($pkg19) {
    echo "Name: '{$pkg19->package_name}' | Total: {$pkg19->total_amount} | Months: {$pkg19->duration_months}\n";
    foreach ($pkg19->items as $it) {
        echo "  - Head: " . ($it->feeHead?->name ?? 'None') . " | Head Slug: " . ($it->feeHead?->slug ?? 'None') . " | Mode: {$it->amount_mode} | Amt: {$it->amount} | Months: {$it->months_count} | Total: {$it->total_amount}\n";
    }
}

echo "\n=== INVOICE 64 DETAILS ===\n";
$inv64 = Invoice::find(64);
if ($inv64) {
    echo json_encode($inv64->toArray(), JSON_PRETTY_PRINT) . "\n";
}

    echo "\nAdmission Form:\n";
    $af = AdmissionForm::where('student_id', $st->id)->first();
    if ($af) {
        echo "  - Form ID: {$af->id}, App No: {$af->application_no}, Status: {$af->status}, Waiver: {$af->waiver_code}, Discount: {$af->discount_amount}\n";
    }
} else {
    echo "Student 24 not found\n";
}
