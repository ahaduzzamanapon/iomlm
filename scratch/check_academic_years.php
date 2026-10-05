<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Academic Years:\n";
foreach (\App\Models\AcademicYear::all() as $ay) {
    echo "ID: {$ay->id}, Name: '{$ay->name}', start_date: '{$ay->start_date}', is_active: {$ay->is_active}\n";
}

$batch22 = \App\Models\Batch::find(22);
echo "\nBatch 22:\n";
echo "academic_year_id: " . ($batch22->academic_year_id ?? 'null') . "\n";
echo "academicYear relation: " . ($batch22->academicYear ? $batch22->academicYear->name : 'null') . "\n";
echo "Batch start_date: {$batch22->start_date}\n";

$student24 = \App\Models\Student::find(24);
echo "\nStudent 24 student_id: {$student24->student_id}\n";
echo "resolveAcademicYearCode(Batch 22): " . \App\Models\Student::resolveAcademicYearCode($batch22) . "\n";
