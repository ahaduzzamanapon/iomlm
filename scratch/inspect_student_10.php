<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = App\Models\Student::find(10);
echo "Student: " . ($student ? $student->name : 'null') . "\n";

foreach (App\Models\Invoice::where('student_id', 10)->get() as $inv) {
    echo "INV {$inv->id} | {$inv->invoice_no} | Title: {$inv->title} | Cat: {$inv->category} | Due: {$inv->due_amount}\n";
    echo "Custom particulars: " . json_encode($inv->custom_particulars, JSON_UNESCAPED_UNICODE) . "\n";
}
