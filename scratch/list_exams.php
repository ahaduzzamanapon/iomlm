<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exam;
use App\Models\Batch;
use App\Models\Subject;

$exams = Exam::all();
echo "Total exams: " . $exams->count() . "\n";
foreach ($exams as $e) {
    echo "ID: {$e->id}, Title: {$e->title}, Type: {$e->type}, SubjectID: {$e->subject_id}, SemID: {$e->semester_id}, has_tamrin: {$e->has_tamrin}, tamrin_marks: {$e->tamrin_marks}, full_marks: {$e->full_marks}\n";
}
