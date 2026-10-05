<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Fee Heads:\n";
foreach (\App\Models\FeeHead::all() as $fh) {
    echo "ID: {$fh->id}, Name: {$fh->name}, Slug: {$fh->slug}, Category: " . ($fh->category ?? 'null') . "\n";
}
