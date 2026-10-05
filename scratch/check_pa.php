<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "ProgramActivity count: " . \App\Models\ProgramActivity::count() . "\n";
foreach (\App\Models\ProgramActivity::all() as $pa) {
    echo "ID: {$pa->id}, course_id: {$pa->course_id}, batch_id: {$pa->batch_id}, starting_month: {$pa->starting_month}, start_date: {$pa->start_date}\n";
}

$batch = \App\Models\Batch::find(22);
echo "\nBatch 22:\n";
echo "fee_start_month: {$batch->fee_start_month}\n";
echo "fee_end_month: {$batch->fee_end_month}\n";
echo "start_month: " . ($batch->start_month ?? 'null') . "\n";
echo "end_month: " . ($batch->end_month ?? 'null') . "\n";
echo "start_date: {$batch->start_date}\n";
echo "end_date: {$batch->end_date}\n";
echo "session_year: {$batch->session_year}\n";
echo "year: " . ($batch->year ?? 'null') . "\n";
