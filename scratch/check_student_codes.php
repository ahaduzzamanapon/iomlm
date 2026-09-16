<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$codes = \App\Models\Student::whereNotNull('student_code')->pluck('student_code')->take(20);
echo "Existing student codes:\n";
foreach ($codes as $c) {
    echo " - " . $c . "\n";
}
