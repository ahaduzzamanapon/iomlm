<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;

$students = Student::with(['admissionForms', 'enrollments'])->get();
echo "Total students: " . $students->count() . "\n";
$issues = 0;
foreach ($students as $st) {
    $approvedCount = $st->admissionForms->where('status', 'APPROVED')->count();
    $pendingCount = $st->admissionForms->where('status', 'PENDING')->count();
    $trashCount = $st->admissionForms->whereIn('status', ['TRASH', 'REJECTED'])->count();
    $activeEnrCount = $st->enrollments->where('status', 'ACTIVE')->count();

    if ($st->student_code && $approvedCount === 0 && $activeEnrCount === 0) {
        echo "[ISSUE] Student ID {$st->id} ({$st->name}) has student_code '{$st->student_code}' but 0 approved forms and 0 active enrollments! (Pending: {$pendingCount}, Trash: {$trashCount})\n";
        $issues++;
    }
}
if ($issues === 0) {
    echo "No students with orphan student_code found!\n";
}
