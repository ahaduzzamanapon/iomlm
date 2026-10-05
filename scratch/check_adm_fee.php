<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$batch = \App\Models\Batch::find(22);
$course = \App\Models\Course::find(15);
echo "Batch 22 admission_fee: " . var_export($batch->admission_fee, true) . "\n";
echo "Course 15 admission_fee: " . var_export($course->admission_fee, true) . "\n";
