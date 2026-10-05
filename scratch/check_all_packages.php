<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (\App\Models\CourseFeePackage::all() as $p) {
    echo "Package ID: {$p->id}, Name: {$p->name}, Course: {$p->course_id}, Default: {$p->is_default}\n";
    foreach ($p->items as $it) {
        echo "   - {$it->label} ({$it->feeHead?->slug}): unit={$it->amount_per_unit}, total={$it->total_amount}\n";
    }
}
