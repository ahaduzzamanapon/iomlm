<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$invoices = App\Models\Invoice::where('student_id', 12)->get();
foreach ($invoices as $i) {
    echo "ID: {$i->id} | No: {$i->invoice_no} | Category: {$i->category} | Status: {$i->status}\n";
    if (!empty($i->custom_particulars)) {
        echo "  Custom keys: " . implode(', ', array_keys($i->custom_particulars)) . "\n";
        echo "  Raw JSON: " . json_encode($i->custom_particulars, JSON_UNESCAPED_UNICODE) . "\n";
    }
}
