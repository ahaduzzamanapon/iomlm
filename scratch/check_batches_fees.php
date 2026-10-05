<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (\App\Models\Batch::all() as $b) {
    echo "Batch ID: {$b->id}, Name: {$b->name}, Course: {$b->course_id}, admission_fee: {$b->admission_fee}, monthly_fee: {$b->monthly_fee}\n";
}
