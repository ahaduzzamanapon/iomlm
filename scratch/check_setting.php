<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

echo "DB Value: " . Setting::get('google_redirect_uri', 'NULL') . "\n";
echo "Dynamic url(): " . url('/auth/google/callback') . "\n";
