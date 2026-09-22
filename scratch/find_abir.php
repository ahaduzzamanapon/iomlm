<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;

$abir = Student::where('name', 'like', '%আবির%')->get();
foreach ($abir as $s) {
    echo "ID: {$s->id} | Code: {$s->student_code} | Name: {$s->name} | Phone: {$s->phone}" . PHP_EOL;
    foreach ($s->invoices as $inv) {
        echo " - Inv #{$inv->id} | Cat: {$inv->category} | Title: {$inv->title} | Source: {$inv->source_type} #{$inv->source_id} | Payable: {$inv->payable_amount} | Paid: {$inv->paid_amount} | Due: {$inv->due_amount}" . PHP_EOL;
    }
}
