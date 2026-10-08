<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== COURSE FEE PACKAGES FLAGS ===\n";
foreach (\App\Models\CourseFeePackage::all() as $pkg) {
    echo "ID: {$pkg->id}, CourseID: {$pkg->course_id}, Name: {$pkg->name}, is_default: " . ($pkg->is_default ? 'YES' : 'NO') . ", is_active: " . ($pkg->is_active ? 'YES' : 'NO') . "\n";
}

echo "\n=== RECENT COURSE TRANSFERS ===\n";
foreach (\App\Models\CourseTransfer::latest()->take(5)->get() as $ct) {
    echo "Transfer ID: {$ct->id}, StudentID: {$ct->student_id}, From: {$ct->from_course_id}, To: {$ct->to_course_id}, Status: {$ct->status}\n";
    $student = \App\Models\Student::find($ct->student_id);
    if ($student) {
        echo "  Student Name: {$student->name}, FeePkgID: {$student->fee_package_id}\n";
        $enrollments = \App\Models\Enrollment::where('student_id', $student->id)->get();
        foreach ($enrollments as $en) {
            echo "    Enrollment ID: {$en->id}, CourseID: {$en->course_id}, BatchID: {$en->batch_id}, Status: {$en->status}, EnrolledAt: {$en->enrolled_at}\n";
        }
    }
}
