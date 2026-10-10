<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\CourseTransfer;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CourseTransferService
{
    /**
     * Execute the atomic transition of a student from from_course to to_course.
     */
    public static function executeTransfer(CourseTransfer $transfer): CourseTransfer
    {
        if ($transfer->status === 'COMPLETED') {
            return $transfer;
        }

        return DB::transaction(function () use ($transfer) {
            $student = $transfer->student;

            // 1. Deactivate old course active enrollment(s) -> set status to TRANSFERRED
            $oldEnrollments = Enrollment::where('student_id', $student->id)
                ->where(function ($q) use ($transfer) {
                    $q->where('course_id', $transfer->from_course_id)
                      ->orWhere('batch_id', $transfer->from_batch_id)
                      ->orWhereHas('batch', fn($bq) => $bq->where('course_id', $transfer->from_course_id));
                })
                ->whereIn('status', ['ACTIVE', 'PENDING'])
                ->get();

            foreach ($oldEnrollments as $old) {
                $old->update(['status' => 'TRANSFERRED']);
            }

            // Fallback: If from_enrollment_id is set, ensure it is also marked TRANSFERRED
            if ($transfer->from_enrollment_id) {
                Enrollment::where('id', $transfer->from_enrollment_id)
                    ->update(['status' => 'TRANSFERRED']);
            }

            // 2. Resolve target semester if course is semester-based
            $targetSemesterId = $transfer->to_semester_id;
            if (!$targetSemesterId && $transfer->toCourse?->type === 'SEMESTER_BASED') {
                $targetSemesterId = $transfer->toCourse->semesters()->orderBy('sequence_no')->value('id');
            }

            // 3. Create new active enrollment in to_course and to_batch
            $newEnrollment = Enrollment::create([
                'student_id'   => $student->id,
                'course_id'    => $transfer->to_course_id,
                'batch_id'     => $transfer->to_batch_id,
                'semester_id'  => $targetSemesterId,
                'enrolled_at'  => now(),
                'status'       => 'ACTIVE',
            ]);

            // 4. Update Student ID (Course code at digits 5 & 6, Batch at digits 3 & 4)
            $toCourse = $transfer->toCourse ?: Course::find($transfer->to_course_id);
            $toBatch  = $transfer->toBatch ?: Batch::find($transfer->to_batch_id);
            $student->updateCodeForTransferOrReadmission($toBatch, $toCourse);

            // 5. Update Fee Structure & Package for the target course
            $newFeePackage = $toCourse?->feePackages()->where('is_default', true)->where('is_active', true)->first()
                ?? $toCourse?->feePackages()->where('is_active', true)->first()
                ?? $toCourse?->feePackages()->first();

            $oldFeePackageId = $student->fee_package_id;
            $student->fee_package_id = $newFeePackage?->id;
            $student->save();

            \App\Models\AuditLog::log(
                'fee_structure_adjusted',
                $student,
                ['fee_package_id' => $oldFeePackageId],
                ['fee_package_id' => $newFeePackage?->id],
                "কোর্স স্থানান্তরের কারণে ফি কাঠামো নতুন কোর্স ({$toCourse?->name})-এর প্যাকেজে আপডেট করা হয়েছে"
            );

            // 6. Cancel old course unpaid semester invoices
            $oldInvoices = \App\Models\Invoice::where('student_id', $student->id)
                ->where('category', 'SEMESTER')
                ->where('status', 'UNPAID')
                ->where('paid_amount', 0)
                ->where(function ($q) use ($transfer, $oldEnrollments) {
                    $q->whereIn('enrollment_id', $oldEnrollments->pluck('id'))
                      ->orWhereHas('enrollment', fn($q2) => $q2->where('course_id', $transfer->from_course_id))
                      ->orWhere('title', 'like', "%{$transfer->fromCourse?->name}%");
                })
                ->get();

            foreach ($oldInvoices as $oldInv) {
                $oldInv->update([
                    'status' => 'CANCELLED',
                    'notes'  => trim(($oldInv->notes ?? '') . "\nকোর্স স্থানান্তরের কারণে এই ইনভয়েস বাতিল করা হয়েছে।"),
                ]);
            }

            // 7. Auto-generate semester tuition invoice for the new course & semester
            $targetSemester = $targetSemesterId ? \App\Models\Semester::find($targetSemesterId) : null;
            try {
                \App\Services\AccountingService::createSemesterInvoice($student, $newEnrollment, $targetSemester);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("CourseTransferService: Failed to generate semester invoice after transfer: " . $e->getMessage());
            }

            // 8. Update CourseTransfer record to COMPLETED
            $transfer->update([
                'status'            => 'COMPLETED',
                'new_enrollment_id' => $newEnrollment->id,
                'completed_at'      => now(),
            ]);

            return $transfer;
        });
    }
}
