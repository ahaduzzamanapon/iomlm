<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== batch_semester_positions ===" . PHP_EOL;
if (Schema::hasTable('batch_semester_positions')) {
    echo "Columns: " . implode(', ', Schema::getColumnListing('batch_semester_positions')) . PHP_EOL;
    foreach (DB::table('batch_semester_positions')->get() as $p) {
        echo json_encode($p) . PHP_EOL;
    }
} else {
    echo "No table batch_semester_positions" . PHP_EOL;
}
