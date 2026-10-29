<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tables = [
    'class_sessions',
    'routine_entries',
    'routine_slots',
    'sent_notifications',
    'notification_events',
    'notification_deliveries',
    'students',
];

foreach ($tables as $t) {
    if (Schema::hasTable($t)) {
        echo "=== TABLE: $t ===\n";
        $columns = Schema::getColumnListing($t);
        echo implode(', ', $columns) . "\n\n";
    }
}
