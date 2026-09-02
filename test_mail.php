<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Columns of payments table ===\n";
$cols = DB::select('DESCRIBE payments');
foreach ($cols as $c) {
    echo $c->Field . " (" . $c->Type . ")\n";
}
