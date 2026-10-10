<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Enrollment;
use App\Models\Student;

$multiStudentIds = Enrollment::select('student_id')->groupBy('student_id')->havingRaw('count(*) > 1')->pluck('student_id');
echo "STUDENTS WITH MULTIPLE ENROLLMENTS: " . count($multiStudentIds) . "\n";
foreach ($multiStudentIds as $sid) {
    $st = Student::find($sid);
    echo "\nStudent ID: {$st?->id} | Name: {$st?->name} | Code: {$st?->student_code}\n";
    $enrs = Enrollment::with(['course', 'batch'])->where('student_id', $sid)->get();
    foreach ($enrs as $e) {
        echo "  - Enrollment ID: {$e->id} | Course: {$e->course?->name} (ID: {$e->course_id}) | Batch: {$e->batch?->name} (ID: {$e->batch_id}) | Status: {$e->status}\n";
    }
}
