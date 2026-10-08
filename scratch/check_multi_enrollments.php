<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== STUDENTS WITH MULTIPLE ENROLLMENTS ===\n";
foreach (\App\Models\Student::has('enrollments', '>', 1)->with(['enrollments.course', 'enrollments.batch'])->get() as $st) {
    echo "Student ID: {$st->id}, Name: {$st->name}, FeePkg: {$st->fee_package_id}\n";
    foreach ($st->enrollments as $en) {
        echo "  - Enrollment ID: {$en->id}, Course: {$en->course?->name} (ID: {$en->course_id}), Batch: {$en->batch?->name} (ID: {$en->batch_id}), Status: {$en->status}, Date: {$en->enrolled_at}\n";
    }
}
