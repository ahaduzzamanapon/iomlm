<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Invoice;

return new class extends Migration
{
    public function up(): void
    {
        // Fix 1: Admission invoices where batch/course explicitly set fee=0 but invoice was created with non-zero amount
        $admInvoices = Invoice::where('category', 'ADMISSION')->get();
        foreach ($admInvoices as $inv) {
            $enrollment = $inv->enrollment;
            if (!$enrollment) continue;

            $batch  = $enrollment->batch;
            $course = $enrollment->course;

            $batchFee  = $batch  ? (float)$batch->admission_fee  : null;
            $courseFee = $course ? (float)$course->admission_fee : null;

            $shouldBeFee = $batchFee ?? $courseFee;

            if ($shouldBeFee !== null && $shouldBeFee == 0 && $inv->amount > 0) {
                $inv->update([
                    'amount'         => 0,
                    'payable_amount' => 0,
                    'due_amount'     => 0,
                    'status'         => 'PAID',
                ]);
            }
        }

        // Fix 2: SEMESTER invoices for SUBJECT_BASED courses that were wrongly divided by semesters
        $semInvoices = Invoice::where('category', 'SEMESTER')->get();
        foreach ($semInvoices as $inv) {
            $enrollment = $inv->enrollment;
            if (!$enrollment) continue;
            $course = $enrollment->course;
            if (!$course || $course->type !== 'SUBJECT_BASED') continue;

            $defaultPkg = $course->feePackages()->first();
            if (!$defaultPkg) continue;

            $pkgTotal = (float) $defaultPkg->items()->sum('total_amount');
            if ($pkgTotal <= 0) continue;

            if ($inv->amount < $pkgTotal) {
                $newDue = max(0, $pkgTotal - (float)$inv->paid_amount);
                $inv->update([
                    'amount'         => $pkgTotal,
                    'payable_amount' => $pkgTotal,
                    'due_amount'     => $newDue,
                    'status'         => $newDue <= 0 ? 'PAID' : ($inv->paid_amount > 0 ? 'PARTIAL' : 'UNPAID'),
                ]);
            }
        }
    }

    public function down(): void {}
};
