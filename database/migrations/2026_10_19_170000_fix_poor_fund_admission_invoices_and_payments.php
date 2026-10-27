<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\AdmissionForm;
use App\Models\WaiverApplication;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fixes historical admission invoices and payments where a poor fund/waiver student
     * was approved with a discounted admission fee (e.g. 700 Tk), but the invoice recorded
     * the full nominal fee (e.g. 1500 Tk) as paid or payable.
     */
    public function up(): void
    {
        $admissionInvoices = Invoice::where('category', 'ADMISSION')->get();

        foreach ($admissionInvoices as $inv) {
            $student = $inv->student;
            if (!$student) {
                continue;
            }

            // Find linked admission form
            $form = null;
            if ($inv->source_type === AdmissionForm::class && $inv->source_id) {
                $form = AdmissionForm::find($inv->source_id);
            }
            if (!$form && $inv->enrollment?->admission_form_id) {
                $form = AdmissionForm::find($inv->enrollment->admission_form_id);
            }
            if (!$form) {
                $form = AdmissionForm::where('student_id', $student->id)->latest()->first();
            }

            if (!$form) {
                continue;
            }

            // Determine approved waiver fee
            $approvedFee = null;
            if ($form->approved_admission_fee !== null && (float)$form->approved_admission_fee >= 0) {
                $approvedFee = (float) $form->approved_admission_fee;
            } elseif ($form->waiver_code) {
                $wCode = $form->waiver_code;
                $altWCode = str_starts_with($wCode, 'PF-')
                    ? str_replace('PF-', 'POOR-', $wCode)
                    : (str_starts_with($wCode, 'POOR-') ? str_replace('POOR-', 'PF-', $wCode) : $wCode);

                $wApp = WaiverApplication::where(function ($q) use ($wCode, $altWCode) {
                        $q->where('application_no', $wCode)->orWhere('application_no', $altWCode);
                    })
                    ->whereIn('apply_for', ['ADMISSION_FEE', 'BOTH'])
                    ->whereNotNull('approved_admission_fee')
                    ->first();

                if ($wApp) {
                    $approvedFee = (float) $wApp->approved_admission_fee;
                }
            }

            // If manual_paid_amount is set and less than nominal, it can also inform approved fee if waiver exists
            if ($approvedFee === null && $form->waiver_code && (float)($form->manual_paid_amount ?? 0) > 0) {
                $approvedFee = (float) $form->manual_paid_amount;
            }

            if ($approvedFee === null) {
                continue;
            }

            $course = $inv->enrollment?->course ?? $form->interestedCourse;
            $nominalCourseFee = (float) ($course?->admission_fee ?: 1500.00);
            $baseNominal = max((float)$inv->amount, $nominalCourseFee);
            if ($baseNominal <= 0) {
                $baseNominal = 1500.00;
            }

            // Check if this invoice is affected (i.e. payable_amount or paid_amount > approvedFee, or discount is 0)
            $needsCorrection = false;
            if ((float)$inv->payable_amount > $approvedFee) {
                $needsCorrection = true;
            }
            if ((float)$inv->paid_amount > $approvedFee && (float)$inv->paid_amount >= $baseNominal) {
                $needsCorrection = true;
            }
            if ((float)$inv->discount <= 0 && $baseNominal > $approvedFee) {
                $needsCorrection = true;
            }

            if (!$needsCorrection) {
                continue;
            }

            $targetPayable = $approvedFee;
            $discount = max(0, $baseNominal - $targetPayable);

            // Determine if the invoice was paid
            $isPaid = ($inv->status === 'PAID') || ((float)$inv->paid_amount >= $targetPayable);

            $targetPaid = $isPaid ? $targetPayable : min((float)$inv->paid_amount, $targetPayable);
            $targetDue = max(0, $targetPayable - $targetPaid);
            $status = $targetDue <= 0 ? 'PAID' : ($targetPaid > 0 ? 'PARTIAL' : 'UNPAID');

            // 1. Update Payments: If a payment was recorded for full 1500, adjust down to targetPaid (700)
            $payments = Payment::where('invoice_id', $inv->id)->get();
            $totalPmtSum = (float) $payments->sum('amount');
            if ($totalPmtSum > $targetPayable && $payments->isNotEmpty()) {
                if ($payments->count() === 1) {
                    $payments->first()->update(['amount' => $targetPaid]);
                } else {
                    $running = 0.0;
                    foreach ($payments as $pmt) {
                        if ($running + (float)$pmt->amount <= $targetPaid) {
                            $running += (float)$pmt->amount;
                        } else {
                            $adjusted = max(0, $targetPaid - $running);
                            $pmt->update(['amount' => $adjusted]);
                            $running += $adjusted;
                        }
                    }
                }
            }

            // 2. Update Custom Particulars if present
            $custom = $inv->custom_particulars;
            if (is_array($custom)) {
                foreach ($custom as $k => $item) {
                    if (str_contains(mb_strtolower($k), 'admission') || str_contains(mb_strtolower($k), 'ভর্তি')) {
                        $custom[$k]['amount'] = $baseNominal;
                        $custom[$k]['paid_amt'] = $targetPaid;
                        $custom[$k]['due'] = $targetDue;
                        $custom[$k]['is_paid'] = ($targetDue <= 0);
                    }
                }
            }

            // 3. Update Invoice
            $inv->update([
                'amount'             => $baseNominal,
                'discount'           => $discount,
                'payable_amount'     => $targetPayable,
                'paid_amount'        => $targetPaid,
                'due_amount'         => $targetDue,
                'status'             => $status,
                'custom_particulars' => $custom,
            ]);

            // 4. Update AdmissionForm discount fields for reporting & admin review accuracy
            $effectivePercent = $baseNominal > 0 ? round(($discount / $baseNominal) * 100, 2) : 0.0;
            $form->update([
                'discount_percent'       => $effectivePercent,
                'discount_amount'        => $discount,
                'approved_admission_fee' => $approvedFee,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data correction migration, no reversal needed
    }
};
