<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$batch = \App\Models\Batch::with('course.subjects')->find(6);
echo "Batch 6: " . ($batch ? $batch->name . ' (Course ID: ' . $batch->course_id . ')' : 'Not found') . "\n";
echo "Active Enrollments in Batch 6: " . \App\Models\Enrollment::where('batch_id', 6)->where('status', 'ACTIVE')->count() . "\n";

$courseSubjects = $batch && $batch->course ? $batch->course->subjects->pluck('name', 'id')->toArray() : [];
echo "Course Subjects for Batch 6:\n";
print_r($courseSubjects);

$sub4 = \App\Models\Subject::find(4);
echo "Subject 4: " . ($sub4 ? $sub4->name : 'Not found') . "\n";

$exams = \App\Models\Exam::where('subject_id', 4)->get(['id', 'title', 'type', 'status'])->toArray();
echo "Exams for Subject 4:\n";
print_r($exams);

$sessions = \App\Models\ClassSession::where('batch_id', 6)->where('subject_id', 4)->count();
echo "Class Sessions for Batch 6 & Subject 4: " . $sessions . "\n";

$finalMarks = \App\Models\FinalMark::where('batch_id', 6)->where('subject_id', 4)->count();
echo "Final Marks in DB for Batch 6 & Subject 4: " . $finalMarks . "\n";
