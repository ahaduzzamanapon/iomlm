<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== FEE STRUCTURE TABLE ===\n";
foreach (\App\Models\FeeStructure::all() as $fs) {
    echo "ID: {$fs->id}, Cat: {$fs->category}, Amount: {$fs->amount}, CourseID: " . ($fs->course_id ?? 'NULL') . "\n";
}
