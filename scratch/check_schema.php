<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = Illuminate\Support\Facades\DB::select('SHOW TABLES');
foreach ($tables as $t) {
    $vals = array_values((array)$t);
    if (str_contains($vals[0], 'template') || str_contains($vals[0], 'sms') || str_contains($vals[0], 'notification') || str_contains($vals[0], 'setting')) {
        echo $vals[0] . "\n";
    }
}
