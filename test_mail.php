<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== INVOICE source_type values ===\n";
$rows = DB::select('SELECT id, category, source_type, source_id, title FROM invoices ORDER BY id DESC LIMIT 10');
foreach ($rows as $r) {
    echo "ID:{$r->id} | CAT:{$r->category} | source_type:{$r->source_type} | source_id:{$r->source_id} | title:" . substr($r->title, 0, 50) . "\n";
}

echo "\n=== Semester model class name ===\n";
echo App\Models\Semester::class . "\n";
