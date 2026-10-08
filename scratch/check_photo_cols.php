<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== STUDENTS COLUMNS ===\n";
print_r(\Illuminate\Support\Facades\Schema::getColumnListing('students'));

echo "\n=== ADMISSION FORMS COLUMNS ===\n";
print_r(\Illuminate\Support\Facades\Schema::getColumnListing('admission_forms'));
