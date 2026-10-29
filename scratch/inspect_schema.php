<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Database: " . DB::connection()->getDatabaseName() . "\n";
$tables = Schema::getTableListing();
$relevant = array_filter($tables, function($t) {
    return str_contains($t, 'class') || str_contains($t, 'session') || str_contains($t, 'routine') || str_contains($t, 'notification') || str_contains($t, 'student') || str_contains($t, 'batch');
});
echo "Relevant tables: " . implode(', ', $relevant) . "\n";
