<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$course = \App\Models\Course::find(15);
echo "Course 15 Semesters:\n";
foreach ($course->semesters as $s) {
    echo "ID: {$s->id}, Name: '{$s->name}', Seq: {$s->sequence_no}\n";
}
