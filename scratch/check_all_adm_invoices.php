<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$invoices = \App\Models\Invoice::where('category', 'ADMISSION')->get();
echo "Admission Invoices count: " . $invoices->count() . "\n";
foreach ($invoices as $inv) {
    echo "ID: {$inv->id}, Student: {$inv->student_id} ({$inv->student?->name}), Amount: {$inv->amount}, Payable: {$inv->payable_amount}, Paid: {$inv->paid_amount}, Due: {$inv->due_amount}, Status: {$inv->status}\n";
}
