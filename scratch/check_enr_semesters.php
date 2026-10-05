<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Enrollment;

echo "=== Enrollments and semester_id ===" . PHP_EOL;
foreach (Enrollment::with('student', 'batch.semesterPosition')->get() as $e) {
    $posSem = $e->batch?->semesterPosition?->current_semester_id ?? $e->batch?->semesterPosition?->semester_id;
    echo "Enr ID: {$e->id}, Student: {$e->student_id} ({$e->student?->student_code}), Batch: {$e->batch_id}, Course: {$e->course_id}, Enr sem_id: " . ($e->semester_id ?? 'NULL') . ", Batch Pos sem_id: " . ($posSem ?? 'NULL') . PHP_EOL;
}
