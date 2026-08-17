<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Setting;
use App\Models\Invoice;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Attendance;

class EnforcementService
{
    /**
     * Check if student is blocked from joining live class due to unpaid fees.
     */
    public static function canJoinClass(Student $student): array
    {
        $dueLevel = Setting::where('key', 'due_enforcement_level')->value('value') ?? 'NONE';

        if ($dueLevel === 'BLOCK_CLASS') {
            $totalDue = Invoice::where('student_id', $student->id)
                ->where('status', '!=', 'CANCELLED')
                ->sum('due_amount');

            if ($totalDue > 0) {
                return [
                    'allowed' => false,
                    'reason'  => "Access to Live Classes is currently blocked due to unpaid balance of ৳" . number_format($totalDue, 2) . ". Please clear your dues from 'My Fees & Invoices' menu.",
                ];
            }
        }

        return ['allowed' => true, 'reason' => ''];
    }

    /**
     * Check if student is blocked from taking exam due to fee dues or attendance threshold.
     */
    public static function canTakeExam(Student $student): array
    {
        $dueLevel = Setting::where('key', 'due_enforcement_level')->value('value') ?? 'NONE';

        // 1. Fee Guard Check
        if (in_array($dueLevel, ['BLOCK_CLASS', 'BLOCK_EXAM'])) {
            $totalDue = Invoice::where('student_id', $student->id)
                ->where('status', '!=', 'CANCELLED')
                ->sum('due_amount');

            if ($totalDue > 0) {
                return [
                    'allowed' => false,
                    'reason'  => "Access to Online Examinations is blocked due to outstanding dues of ৳" . number_format($totalDue, 2) . ". Please clear your dues first.",
                ];
            }
        }

        // 2. Attendance Threshold Guard Check
        $reqAttendance = Setting::where('key', 'min_attendance_required')->value('value') ?? '0';
        $minPercent    = (float) (Setting::where('key', 'min_attendance_percent')->value('value') ?? 75);

        if ($reqAttendance === '1') {
            $batchIds = Enrollment::where('student_id', $student->id)->where('status', 'ACTIVE')->pluck('batch_id');
            $totalSessions = ClassSession::whereIn('batch_id', $batchIds)->where('status', 'COMPLETED')->count();

            if ($totalSessions > 0) {
                $presentCount = Attendance::where('student_id', $student->id)->where('status', 'PRESENT')->count();
                $actualPercent = ($presentCount / $totalSessions) * 100;

                if ($actualPercent < $minPercent) {
                    return [
                        'allowed' => false,
                        'reason'  => "Exam eligibility blocked! Your attendance rate is " . round($actualPercent, 1) . "%, which is below the required minimum threshold of {$minPercent}%.",
                    ];
                }
            }
        }

        return ['allowed' => true, 'reason' => ''];
    }
}
