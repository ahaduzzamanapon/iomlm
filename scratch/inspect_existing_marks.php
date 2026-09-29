<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Result;
use App\Models\Exam;
use App\Models\FinalMark;

$results = Result::with('exam')->get();
echo "Total Results: " . $results->count() . "\n";
foreach ($results->take(10) as $r) {
    echo "ID: {$r->id}, Exam: {$r->exam_id} ({$r->exam?->title}, Type: {$r->exam?->type}), Student: {$r->student_id}, Marks: {$r->marks}, MCQ: {$r->mcq_marks}, Written: {$r->written_marks}, Tamrin: {$r->tamrin_marks}, Viva: {$r->viva_marks}\n";
}

echo "\n--- FinalMarks count: " . FinalMark::count() . " ---\n";
$fms = FinalMark::take(5)->get();
foreach ($fms as $f) {
    echo "ID: {$f->id}, Batch: {$f->batch_id}, Sem: {$f->semester_id}, Sub: {$f->subject_id}, Student: {$f->student_id}, CT: {$f->class_test_obtained} ({$f->class_test_converted}), Mid: {$f->midterm_obtained} ({$f->midterm_converted}), Fin: {$f->final_obtained} ({$f->final_converted}), Att: {$f->attendance_converted}, Tamrin: {$f->tamrin_mark}, Total: {$f->total_mark}\n";
}
