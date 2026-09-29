<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\FinalMark;
use App\Models\Result;
use App\Models\Exam;

$student = Student::where('name', 'like', '%Bani Amin%')
    ->orWhere('student_code', 'like', '%STD2026001%')
    ->first();

if ($student) {
    echo "Found student: ID {$student->id}, Name: {$student->name}, Code: {$student->student_code}\n";
    $fms = FinalMark::where('student_id', $student->id)->get();
    foreach ($fms as $fm) {
        echo "FinalMark ID: {$fm->id}, Batch: {$fm->batch_id}, Sem: {$fm->semester_id}, Sub: {$fm->subject_id}\n";
        echo "  CT: {$fm->class_test_obtained} (conv: {$fm->class_test_converted})\n";
        echo "  Mid: {$fm->midterm_obtained} (conv: {$fm->midterm_converted})\n";
        echo "  Fin: {$fm->final_obtained} (conv: {$fm->final_converted})\n";
        echo "  Att: {$fm->attendance_converted}\n";
        echo "  Tamrin: {$fm->tamrin_mark}\n";
        echo "  Total: {$fm->total_mark}, Grade: {$fm->grade}, GPA: {$fm->gpa}\n";
    }

    $results = Result::with('exam')->where('student_id', $student->id)->get();
    echo "\nResults count for student: " . $results->count() . "\n";
    foreach ($results as $r) {
        echo "  Result ID: {$r->id}, Exam: {$r->exam_id} ({$r->exam?->title}, type: {$r->exam?->type}), Marks: {$r->marks}, MCQ: {$r->mcq_marks}, Written: {$r->written_marks}, Tamrin: {$r->tamrin_marks}, Viva: {$r->viva_marks}\n";
    }
} else {
    echo "Student Bani Amin not found.\n";
}
