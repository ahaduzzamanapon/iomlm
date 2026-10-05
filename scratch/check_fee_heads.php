<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FeeHead;
echo "=== Fee Heads ===" . PHP_EOL;
foreach (FeeHead::all() as $fh) {
    echo "ID: {$fh->id}, Name: {$fh->name}, Slug: {$fh->slug}" . PHP_EOL;
}

$annualInvs = \App\Models\Invoice::where('title', 'like', '%Annual%')->orWhere('title', 'like', '%বার্ষিক%')->get();
echo "Annual Invoices count: " . $annualInvs->count() . PHP_EOL;
foreach ($annualInvs as $ai) {
    echo "ID: {$ai->id}, Title: {$ai->title}, Cat: {$ai->category}, Amt: {$ai->amount}" . PHP_EOL;
}
