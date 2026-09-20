<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$batch = \App\Models\Batch::with(['course.semesters', 'enrollments.student'])->find(21);
$course = $batch->course;
$isSemesterBased = ($course && $course->type === 'SEMESTER_BASED' && $course->semesters->isNotEmpty());
$runningSemesterId = $batch->semesterPosition?->current_semester_id 
    ?? $batch->enrollments()->whereNotNull('semester_id')->latest()->value('semester_id')
    ?? $course?->semesters->first()?->id;

echo "Batch: {$batch->name} | Course: {$course->name} | IsSemBased: " . ($isSemesterBased ? 'YES' : 'NO') . " | RunningSem: {$runningSemesterId}\n";

$semesterSubjects = $course->subjects()->wherePivot('semester_id', $runningSemesterId)->get();
if ($semesterSubjects->isEmpty()) {
    $semesterSubjects = $course->subjects;
}
echo "Semester Subjects: " . $semesterSubjects->count() . "\n";
foreach ($semesterSubjects as $subj) {
    $exams = \App\Models\Exam::where('subject_id', $subj->id)->get();
    $marksCount = \App\Models\FinalMark::where('batch_id', $batch->id)->where('subject_id', $subj->id)->count();
    echo "  -> Subj: {$subj->name} | Exams: " . $exams->count() . " | FinalMarks: {$marksCount}\n";
}

echo "\nStudents Evaluation:\n";
foreach ($batch->enrollments as $enr) {
    $student = $enr->student;
    if (!$student) continue;

    $marks = \App\Models\FinalMark::where('batch_id', $batch->id)->where('student_id', $student->id)->get();
    $fails = $marks->where('status', 'FAIL')->count();
    $passes = $marks->where('status', 'PASS')->count();
    $total = $marks->sum('total_mark');

    $status = 'PENDING';
    if ($marks->count() > 0) {
        if ($fails === 0) {
            $status = 'ELIGIBLE_PROMOTION (প্রমোশন যোগ্য)';
        } elseif ($fails <= 2) {
            $status = 'ELIGIBLE_RETAKE (রিটেকসহ প্রমোশন)';
        } else {
            $status = 'NEEDS_READMISSION (রি-এডমিশন লাগবে)';
        }
    }
    echo "  Student: {$student->name} | Evaluated: {$marks->count()} | Pass: {$passes} | Fail: {$fails} | Status: {$status}\n";
}
