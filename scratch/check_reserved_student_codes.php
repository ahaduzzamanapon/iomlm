<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdmissionForm;
use App\Models\Student;

$admissions = AdmissionForm::with('student', 'batch', 'interestedCourse')->get();
echo "Total admissions: " . $admissions->count() . "\n";
foreach ($admissions as $a) {
    $sc = $a->student?->student_code ?? 'NONE';
    $stStatus = $a->student?->status ?? 'NONE';
    $userId = $a->student?->user_id ?? 'NONE';
    $hasEnrollment = $a->student ? $a->student->enrollments()->count() : 0;
    echo sprintf(
        "ID: %d | AppNo: %s | FormStatus: %-8s | StudentCode: %-15s | StStatus: %-8s | UserID: %-5s | Enrs: %d\n",
        $a->id,
        $a->application_no,
        $a->status,
        $sc,
        $stStatus,
        $userId,
        $hasEnrollment
    );
}

echo "\n--- Students with student_code who have NO active enrollment or only PENDING/TRASH admissions ---\n";
$students = Student::whereNotNull('student_code')->with('admissionForms', 'enrollments')->get();
foreach ($students as $st) {
    $activeEnr = $st->enrollments()->where('status', 'ACTIVE')->count();
    $approvedForms = $st->admissionForms()->where('status', 'APPROVED')->count();
    $pendingOrTrashForms = $st->admissionForms()->whereIn('status', ['PENDING', 'TRASH', 'REJECTED'])->count();

    if ($activeEnr === 0 && $approvedForms === 0) {
        echo sprintf(
            "Student ID: %d | Code: %-15s | Name: %s | Status: %s | Forms: %d (Approved: %d, Pend/Trash: %d) | Enrollments: %d\n",
            $st->id,
            $st->student_code,
            $st->name,
            $st->status,
            $st->admissionForms->count(),
            $approvedForms,
            $pendingOrTrashForms,
            $st->enrollments->count()
        );
    }
}
