<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$col = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM support_tickets LIKE 'status'");
print_r($col);

$statuses = \App\Models\SupportTicket::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as cnt'))->groupBy('status')->get();
print_r($statuses->toArray());
