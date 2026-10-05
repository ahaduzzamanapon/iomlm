<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$w = \App\Models\WaiverApplication::where('application_no', 'PF-2026-0013')->first();
echo json_encode($w, JSON_PRETTY_PRINT) . PHP_EOL;

$inv34 = \App\Models\Invoice::find(34);
echo json_encode($inv34, JSON_PRETTY_PRINT) . PHP_EOL;
