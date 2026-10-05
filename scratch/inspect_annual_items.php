<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== All Annual Fee Items across all packages ===" . PHP_EOL;
$items = DB::table('course_fee_package_items')
    ->where('fee_head_id', 9)
    ->orWhere('fee_head_id', 10)
    ->orWhere('label', 'like', '%annual%')
    ->orWhere('label', 'like', '%বার্ষিক%')
    ->get();

foreach ($items as $it) {
    $pkg = DB::table('course_fee_packages')->find($it->package_id);
    $course = $pkg ? DB::table('courses')->find($pkg->course_id) : null;
    echo "Pkg ID: {$it->package_id} ({$pkg?->name}) | Course: " . ($course?->name ?? 'none') . " | Item: {$it->label} | Head: {$it->fee_head_id} | Unit: {$it->amount_per_unit} | Total: {$it->total_amount}" . PHP_EOL;
}
