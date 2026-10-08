<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Course;
use App\Models\Student;

$courses = Course::all();
foreach ($courses as $c) {
    echo "Course ID: {$c->id}, Code: {$c->code}, Name: {$c->name}\n";
}

$all = Student::all();
echo "\nTotal Students in DB: " . $all->count() . "\n";
foreach ($all as $st) {
    echo "ID: {$st->id}, Code: {$st->student_code}, Name: {$st->name}, Phone: {$st->phone}\n";
}
