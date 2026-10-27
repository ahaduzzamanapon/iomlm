<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "DB_CONNECTED\n";
    
    // Search for payment PAY-20261014-6278
    $pmt = \App\Models\Payment::where('payment_no', 'like', '%PAY-20261014-6278%')->first();
    if ($pmt) {
        echo "Payment found:\n";
        echo "ID: {$pmt->id}, Invoice ID: {$pmt->invoice_id}, Amount: {$pmt->amount}, Method: {$pmt->payment_method}\n";
        $inv = $pmt->invoice;
        if ($inv) {
            echo "Invoice found:\n";
            echo "ID: {$inv->id}, InvNo: {$inv->invoice_no}, Amount: {$inv->amount}, Payable: {$inv->payable_amount}, Paid: {$inv->paid_amount}, Due: {$inv->due_amount}, Status: {$inv->status}\n";
            echo "Custom Particulars: " . json_encode($inv->custom_particulars) . "\n";
            $student = $inv->student;
            if ($student) {
                echo "Student found: ID: {$student->id}, Name: {$student->name}\n";
                $service = app(\App\Services\StudentFeeService::class);
                $breakdown = $service->getStudentFeeBreakdown($student, null, null);
                echo "Step 1 Particulars count: " . count($breakdown['step1Particulars'] ?? []) . "\n";
                foreach ($breakdown['step1Particulars'] ?? [] as $part) {
                    echo " - {$part['name']}: amount={$part['amount']}, paid_amt=" . ($part['paid_amt'] ?? 'null') . ", due={$part['due']}, is_paid=" . ($part['is_paid'] ? '1' : '0') . "\n";
                }
            }
        }
    } else {
        echo "Payment PAY-20261014-6278 not found. Searching by student name Amena...\n";
        $st = \App\Models\Student::where('name', 'like', '%আমেনা%')->orWhere('name', 'like', '%Amena%')->first();
        if ($st) {
            echo "Found student: {$st->id} - {$st->name}\n";
            $invoices = \App\Models\Invoice::where('student_id', $st->id)->get();
            foreach ($invoices as $i) {
                echo "Invoice: {$i->invoice_no}, Category: {$i->category}, Amount: {$i->amount}, Payable: {$i->payable_amount}, Paid: {$i->paid_amount}, Due: {$i->due_amount}, Status: {$i->status}\n";
            }
        } else {
            echo "Student not found in DB.\n";
        }
    }
} catch (\Exception $e) {
    echo "DB_ERROR: " . $e->getMessage() . "\n";
}
