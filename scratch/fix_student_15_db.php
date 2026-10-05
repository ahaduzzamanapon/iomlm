<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Invoice;

// Fix Student 15
Student::where('id', 15)->update(['fee_package_id' => 19]);
Enrollment::where('id', 19)->update(['batch_id' => 17]);
Invoice::where('id', 35)->update([
    'amount' => 627,
    'payable_amount' => 627,
    'due_amount' => 627,
    'source_type' => \App\Models\Semester::class,
    'source_id' => 53
]);
Invoice::where('id', 34)->update([
    'amount' => 1500,
    'discount' => 1000,
    'payable_amount' => 500,
    'due_amount' => 500,
    'title' => 'Admission Fee — Alim 2717'
]);

echo "Student 15 and related records updated successfully." . PHP_EOL;
