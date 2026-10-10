<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "SEARCH FOR 'mazhar2' OR '27011610006':\n";
$tables = ['students', 'users', 'admission_forms', 'course_transfers', 'enrollments'];
foreach ($tables as $t) {
    $rows = DB::table($t)->whereRaw("CONCAT_WS(' ', " . implode(', ', DB::getSchemaBuilder()->getColumnListing($t)) . ") LIKE '%mazhar2%'")->get();
    echo "Table {$t}: " . $rows->count() . " rows\n";
    foreach ($rows as $r) {
        print_r($r);
    }
}
