<?php

namespace App\Services;

use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\AdmissionForm;
use App\Models\SubjectRetake;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AccountingService
{
    /**
     * Auto-generate Admission Fee Invoice when student is approved.
     * Respects waiver approved_admission_fee if a used waiver is linked.
     */
    public static function createAdmissionInvoice(Student $student, AdmissionForm $admission, Enrollment $enrollment): Invoice
    {
        $batch  = $enrollment->batch;
        $course = $enrollment->course ?? $batch?->course;

        // Priority 1: Batch admission fee, Priority 2: Course admission fee, Priority 3: FeeStructure fallback
        $feeRate = ($batch && $batch->admission_fee > 0)
            ? (float)$batch->admission_fee
            : (($course && $course->admission_fee > 0)
                ? (float)$course->admission_fee
                : (float)(FeeStructure::where('category', 'ADMISSION')
                    ->where(function ($q) use ($enrollment) {
                        $q->where('course_id', $enrollment->course_id)
                          ->orWhereNull('course_id');
                    })
                    ->where('is_active', true)
                    ->value('amount') ?? 2000.00));

        // Check if a waiver code is linked and has an approved_admission_fee set
        $waiverApp = null;
        if ($admission->waiver_code) {
            $waiverApp = \App\Models\WaiverApplication::where('application_no', $admission->waiver_code)
                ->where('status', 'APPROVED')
                ->where('is_used', true)
                ->first();
        }

        // If waiver has explicit admission fee override — use it directly
        if ($waiverApp && $waiverApp->approved_admission_fee !== null
            && in_array($waiverApp->apply_for, ['ADMISSION_FEE', 'BOTH'])) {
            $approvedFee    = (float) $waiverApp->approved_admission_fee;
            $discountAmount = max(0, $feeRate - $approvedFee);
            $payableAmount  = $approvedFee;
        } else {
            // Legacy: percentage or fixed discount from admission form
            $discountAmount = 0.00;
            if (($admission->discount_type ?? 'PERCENTAGE') === 'FIXED') {
                $val = $admission->discount_amount > 0 ? $admission->discount_amount : $admission->discount_percent;
                $discountAmount = min($feeRate, (float)$val);
            } else {
                $discountPercent = (float)($admission->discount_percent ?? 0);
                $discountAmount  = round(($feeRate * $discountPercent) / 100, 2);
            }
            $payableAmount = max(0, $feeRate - $discountAmount);
        }

        $invNo = 'INV-ADM-' . date('Ymd') . '-' . rand(1000, 9999);

        return Invoice::create([
            'invoice_no'     => $invNo,
            'student_id'     => $student->id,
            'enrollment_id'  => $enrollment->id,
            'category'       => 'ADMISSION',
            'title'          => "Admission Fee — " . ($batch ? $batch->name : $enrollment->course->name),
            'amount'         => $feeRate,
            'discount'       => $discountAmount,
            'payable_amount' => $payableAmount,
            'paid_amount'    => 0.00,
            'due_amount'     => $payableAmount,
            'status'         => $payableAmount <= 0 ? 'PAID' : 'UNPAID',
            'due_date'       => Carbon::now()->addDays(7),
            'source_type'    => AdmissionForm::class,
            'source_id'      => $admission->id,
            'created_by'     => auth()->id(),
        ]);
    }


    /**
     * Auto-generate Subject Retake Fee Invoice.
     */
    public static function createRetakeInvoice(Student $student, SubjectRetake $retake, float $feeOverride = 0.00): Invoice
    {
        // Use admin-set fee if provided, else fall back to FeeStructure, else 1500
        $feeRate = $feeOverride > 0
            ? $feeOverride
            : (float)(FeeStructure::where('category', 'RETAKE')
                ->where('is_active', true)
                ->value('amount') ?? 1500.00);

        $subjectName = $retake->subject?->name ?? $retake->subject()->value('name') ?? 'Subject';
        $invNo = 'INV-RET-' . date('Ymd') . '-' . rand(1000, 9999);

        return Invoice::create([
            'invoice_no'     => $invNo,
            'student_id'     => $student->id,
            'enrollment_id'  => $retake->enrollment_id,
            'category'       => 'RETAKE',
            'title'          => "Subject Retake Fee — {$subjectName} ({$retake->retake_type})",
            'amount'         => $feeRate,
            'discount'       => 0.00,
            'payable_amount' => $feeRate,
            'paid_amount'    => 0.00,
            'due_amount'     => $feeRate,
            'status'         => 'UNPAID',
            'due_date'       => Carbon::now()->addDays(5),
            'source_type'    => SubjectRetake::class,
            'source_id'      => $retake->id,
            'created_by'     => auth()->id(),
        ]);
    }

    /**
     * Auto-generate Semester Fee Invoice.
     * If the student has an approved waiver with a package, uses the package total.
     */
    public static function createSemesterInvoice(Student $student, Enrollment $enrollment, ?Semester $semester = null): Invoice
    {
        $course = $enrollment->course ?? $enrollment->batch?->course;
        $totalSemesters = max(1, $course?->semesters()->count() ?: 6);

        // Check if student has an active approved waiver with a tuition package
        $approvedPackage = null;
        // Find waiver code from admission form
        $waiverCode = \App\Models\AdmissionForm::where('student_id', $student->id)
            ->whereNotNull('waiver_code')
            ->value('waiver_code');

        if ($waiverCode) {
            $waiverApp = \App\Models\WaiverApplication::where('application_no', $waiverCode)
                ->where('status', 'APPROVED')
                ->where('is_used', true)
                ->whereIn('apply_for', ['TUITION_FEE', 'BOTH'])
                ->whereNotNull('approved_package_id')
                ->first();

            if ($waiverApp) {
                $approvedPackage = \App\Models\CourseFeePackage::find($waiverApp->approved_package_id);
            }
        }

        if ($approvedPackage) {
            $fullPackageTotal = (float) $approvedPackage->items()->sum('total_amount');
            $feeRate          = round($fullPackageTotal / $totalSemesters, 2);
            $feeTitle         = "Semester Tuition Fee — {$course?->name} ({$approvedPackage->name})";
            $discountAmount   = 0.00;
        } else {
            // Check if course has default fee package
            $defaultPackage = $course?->feePackages()->where('is_default', true)->first()
                ?? $course?->feePackages()->first();

            $packageTotal = $defaultPackage ? (float) $defaultPackage->items()->sum('total_amount') : 0;

            if ($defaultPackage && $packageTotal > 0) {
                $feeRate  = round($packageTotal / $totalSemesters, 2);
                $feeTitle = "Semester Tuition Fee — " . ($course ? $course->name : '') . " ({$defaultPackage->name})";
                $discountAmount = 0.00;
            } else {
                // Fallback: FeeStructure lookup
                $feeRate  = (float)(FeeStructure::where('category', 'SEMESTER')
                    ->where(function ($q) use ($enrollment) {
                        $q->where('course_id', $enrollment->course_id)
                          ->orWhereNull('course_id');
                    })
                    ->where('is_active', true)
                    ->value('amount') ?? 7000.00);
                $feeTitle       = "Semester Tuition Fee — {$course?->name}";
                $discountAmount = 0.00;
            }
        }

        $semName = $semester?->name ?? 'Current Semester';
        $invNo   = 'INV-SEM-' . date('Ymd') . '-' . rand(1000, 9999);

        return Invoice::create([
            'invoice_no'     => $invNo,
            'student_id'     => $student->id,
            'enrollment_id'  => $enrollment->id,
            'category'       => 'SEMESTER',
            'title'          => $feeTitle . " ({$semName})",
            'amount'         => $feeRate,
            'discount'       => $discountAmount,
            'payable_amount' => $feeRate,
            'paid_amount'    => 0.00,
            'due_amount'     => $feeRate,
            'status'         => 'UNPAID',
            'due_date'       => Carbon::now()->addDays(15),
            'source_type'    => Semester::class,
            'source_id'      => $semester?->id,
            'created_by'     => auth()->id(),
        ]);
    }


    /**
     * Receive payment against an invoice and update due status.
     */
    public static function receivePayment(Invoice $invoice, float $amount, string $method = 'CASH', ?string $trxId = null, ?string $remarks = null): Payment
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($invoice, $amount, $method, $trxId, $remarks) {
            $payNo = 'PAY-' . date('Ymd') . '-' . rand(1000, 9999);

            $payment = Payment::create([
                'payment_no'     => $payNo,
                'invoice_id'     => $invoice->id,
                'student_id'     => $invoice->student_id,
                'amount'         => $amount,
                'payment_method' => $method,
                'transaction_id' => $trxId,
                'remarks'        => $remarks,
                'received_by'    => auth()->id(),
                'paid_at'        => now(),
            ]);

            $newPaidAmount = $invoice->paid_amount + $amount;
            $newDueAmount  = max(0, $invoice->payable_amount - $newPaidAmount);

            $status = 'UNPAID';
            if ($newDueAmount <= 0) {
                $status = 'PAID';
            } elseif ($newPaidAmount > 0) {
                $status = 'PARTIAL';
            }

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'due_amount'  => $newDueAmount,
                'status'      => $status,
            ]);

            return $payment;
        });
    }
}
