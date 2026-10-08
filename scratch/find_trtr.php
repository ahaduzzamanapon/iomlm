<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\Invoice::all() as $i) {
    $cJson = json_encode($i->custom_particulars, JSON_UNESCAPED_UNICODE);
    if (str_contains($i->title, 'trtr') || str_contains($cJson, 'trtr')) {
        echo "Found in INV {$i->id} (student {$i->student_id}): Title: {$i->title} | Custom: {$cJson}\n";
    }
}
