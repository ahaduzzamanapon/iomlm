<?php

namespace App\Services;

use App\Models\CourseTransfer;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;

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
                ->where('course_id', $transfer->from_course_id)
                ->where('status', 'ACTIVE')
                ->get();

            foreach ($oldEnrollments as $old) {
                $old->update(['status' => 'TRANSFERRED']);
            }

            // Fallback: If from_enrollment_id is set, ensure it is also marked TRANSFERRED
            if ($transfer->from_enrollment_id) {
                Enrollment::where('id', $transfer->from_enrollment_id)
                    ->where('status', 'ACTIVE')
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

            // 4. Update CourseTransfer record to COMPLETED
            $transfer->update([
                'status'            => 'COMPLETED',
                'new_enrollment_id' => $newEnrollment->id,
                'completed_at'      => now(),
            ]);

            return $transfer;
        });
    }
}
