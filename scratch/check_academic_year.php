<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== Tables with academic_year ===" . PHP_EOL;
$tables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$col = 'Tables_in_' . $dbName;
foreach ($tables as $t) {
    $tbl = $t->$col;
    $hasId = Schema::hasColumn($tbl, 'academic_year_id');
    $hasCol = Schema::hasColumn($tbl, 'academic_year');
    if ($hasId || $hasCol) {
        echo "Table: $tbl (academic_year_id: " . ($hasId ? 'yes' : 'no') . ", academic_year: " . ($hasCol ? 'yes' : 'no') . ")" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Academic Years in DB ===" . PHP_EOL;
$cols = Schema::getColumnListing('academic_years');
echo "Cols: " . implode(', ', $cols) . PHP_EOL;
$academicYears = DB::table('academic_years')->get();
foreach ($academicYears as $ay) {
    echo json_encode($ay) . PHP_EOL;
}

echo PHP_EOL . "=== Batches and their Academic Years ===" . PHP_EOL;
$bCols = Schema::getColumnListing('batches');
echo "Batch Cols: " . implode(', ', $bCols) . PHP_EOL;
$batches = DB::table('batches')->get();
foreach ($batches as $b) {
    $bname = $b->batch_name ?? ($b->name ?? 'N/A');
    $fmonth = $b->fee_start_month ?? 'N/A';
    $sdate = $b->start_date ?? 'N/A';
    echo "Batch ID: {$b->id}, Name: {$bname}, AY_ID: {$b->academic_year_id}, fee_start: {$fmonth}, start_date: {$sdate}" . PHP_EOL;
}
