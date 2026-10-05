<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$student = \App\Models\Student::find(24);
echo "Student 24 columns:\n";
print_r($student->toArray());
