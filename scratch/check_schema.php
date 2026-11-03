<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Enrollments columns: " . implode(', ', Schema::getColumnListing('enrollments')) . PHP_EOL;
echo "Routine entries columns: " . implode(', ', Schema::getColumnListing('routine_entries')) . PHP_EOL;
echo "Class sessions columns: " . implode(', ', Schema::getColumnListing('class_sessions')) . PHP_EOL;
echo "Semesters columns: " . implode(', ', Schema::getColumnListing('semesters')) . PHP_EOL;
echo "Course Subject Maps columns: " . implode(', ', Schema::getColumnListing('course_subject_maps')) . PHP_EOL;
