<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Exam;
use App\Models\Result;
use App\Models\FinalMark;
use App\Models\Batch;

echo "=== Columns in final_marks ===\n";
print_r(Schema::getColumnListing('final_marks'));

echo "\n=== Columns in results ===\n";
print_r(Schema::getColumnListing('results'));

echo "\n=== Columns in exams ===\n";
print_r(Schema::getColumnListing('exams'));

echo "\n=== Sample Exam Record ===\n";
$exam = Exam::latest('id')->first();
if ($exam) {
    echo "Exam ID: {$exam->id}, title: {$exam->title}, type: {$exam->type}, subject_id: {$exam->subject_id}, semester_id: {$exam->semester_id}, has_tamrin: {$exam->has_tamrin}, tamrin_marks: {$exam->tamrin_marks}\n";
}

echo "\n=== Sample Result Record ===\n";
$res = Result::latest('id')->first();
if ($res) {
    echo "Result ID: {$res->id}, exam_id: {$res->exam_id}, student_id: {$res->student_id}, marks: {$res->marks}, mcq: {$res->mcq_marks}, written: {$res->written_marks}, tamrin: {$res->tamrin_marks}\n";
}

echo "\n=== Sample FinalMark Record ===\n";
$fm = FinalMark::latest('id')->first();
if ($fm) {
    echo "FinalMark ID: {$fm->id}, student_id: {$fm->student_id}, subject_id: {$fm->subject_id}, semester_id: {$fm->semester_id}, CT: {$fm->class_test_obtained}, Mid: {$fm->midterm_obtained}, Final: {$fm->final_obtained}, Att: {$fm->attendance_converted}, Tamrin: {$fm->tamrin_mark}, Total: {$fm->total_mark}\n";
}
