<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Searching for amount 300 ===" . PHP_EOL;
echo "fee_structures:" . PHP_EOL;
foreach (DB::table('fee_structures')->get() as $fs) {
    echo json_encode($fs) . PHP_EOL;
}

$cols = \Illuminate\Support\Facades\Schema::getColumnListing('course_fee_package_items');
echo "Cols: " . implode(', ', $cols) . PHP_EOL;

foreach (DB::table('course_fee_package_items')->get() as $pi) {
    if ($pi->amount_per_unit == 300 || $pi->total_amount == 300 || $pi->amount_per_unit == 500 || $pi->total_amount == 500) {
        echo "Package ID: {$pi->package_id}, Label: {$pi->label}, Unit: {$pi->amount_per_unit}, Total: {$pi->total_amount}" . PHP_EOL;
    }
}

echo PHP_EOL . "Check Course 15 packages:" . PHP_EOL;
foreach (DB::table('course_fee_packages')->where('course_id', 15)->get() as $pkg) {
    echo "ID: {$pkg->id}, Name: {$pkg->name}, Default: {$pkg->is_default}" . PHP_EOL;
    foreach (DB::table('course_fee_package_items')->where('package_id', $pkg->id)->get() as $it) {
        echo "   Item: {$it->label}, Unit: {$it->amount_per_unit}, Total: {$it->total_amount}, Head: {$it->fee_head_id}" . PHP_EOL;
    }
}
