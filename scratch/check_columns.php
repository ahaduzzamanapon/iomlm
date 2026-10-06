<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

dump(collect(Illuminate\Support\Facades\Schema::getColumnListing('admission_forms'))->filter(fn($c) => str_contains($c, 'amount') || str_contains($c, 'fee') || str_contains($c, 'manual') || str_contains($c, 'pay'))->values()->all());
