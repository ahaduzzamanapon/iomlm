<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\Batch;
use App\Models\Course;
use App\Models\AdmissionForm;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\DB;

echo "=== DEFINITIVE CORRECTION OF ALL EXISTING STUDENT CODES ===\n";

DB::beginTransaction();

try {
    // 1. Temporarily store original codes and assign temp codes to bypass unique constraint
    $students = Student::orderBy('id')->get();
    $origCodes = [];
    foreach ($students as $student) {
        if (!empty($student->student_code)) {
            $origCodes[$student->id] = $student->student_code;
            DB::table('students')->where('id', $student->id)->update([
                'student_code' => 'TMP_' . $student->id . '_' . uniqid(),
            ]);
        }
    }

    $assignedCodes = [];
    $updatedCount = 0;

    foreach ($students as $student) {
        if (!isset($origCodes[$student->id])) {
            continue;
        }

        $origCode = $origCodes[$student->id];

        // Find approved form first, then latest form, then active enrollment
        $form = AdmissionForm::where('student_id', $student->id)->where('status', 'APPROVED')->whereNotNull('batch_id')->latest()->first()
             ?: AdmissionForm::where('student_id', $student->id)->whereNotNull('batch_id')->latest()->first();

        $enrollment = $student->enrollments()->where('status', 'ACTIVE')->latest()->first()
                   ?: $student->enrollments()->latest()->first();

        $batch = $form?->batch ?: $enrollment?->batch;
        $course = $form?->interestedCourse ?: ($enrollment?->course ?: $batch?->course);

        $year = Student::resolveAcademicYearCode($batch);
        $batchNum = Student::resolveBatchNumberCode($batch);
        $courseCode = Student::resolveCourseCode($course, $batch?->course_id);
        $gender = Student::resolveGenderCode($student->gender);

        $prefix = "{$year}{$batchNum}{$courseCode}{$gender}";

        $cleanCode = preg_replace('/\D/', '', (string)$origCode);
        $preferredSeq = strlen($cleanCode) >= 11 ? (int)substr($cleanCode, 7, 4) : ($student->id % 10000);
        if ($preferredSeq <= 0) $preferredSeq = 1;

        $targetSeq = $preferredSeq;
        $candidate = $prefix . str_pad($targetSeq, 4, '0', STR_PAD_LEFT);
        while (in_array($candidate, $assignedCodes)) {
            $targetSeq++;
            $candidate = $prefix . str_pad($targetSeq, 4, '0', STR_PAD_LEFT);
        }
        $assignedCodes[] = $candidate;

        echo "Student [ID {$student->id}] '{$student->name}': '{$origCode}' -> '{$candidate}' (Year: {$year}, Batch: {$batchNum}, Course: {$courseCode}, Gender: {$gender})\n";
        DB::table('students')->where('id', $student->id)->update([
            'student_code' => $candidate,
            'updated_at'   => now(),
        ]);

        // Update user email if linked to old student_code
        $user = $student->user;
        if ($user) {
            if (str_contains($user->email, '@iom.student')) {
                $oldEmail = $user->email;
                $user->email = "{$candidate}@iom.student";
                $user->save();
                echo "  -> Updated User [ID {$user->id}] Email: '{$oldEmail}' -> '{$user->email}'\n";
            }
        }

        // Update support tickets
        $ticketsUpdated = SupportTicket::where('student_id', $origCode)->update(['student_id' => $candidate]);
        if ($ticketsUpdated > 0) {
            echo "  -> Updated {$ticketsUpdated} support ticket(s) with new code\n";
        }

        $updatedCount++;
    }

    DB::commit();
    echo "\n✅ Successfully corrected all {$updatedCount} student IDs in the database!\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error correcting student IDs: " . $e->getMessage() . "\n";
    exit(1);
}
