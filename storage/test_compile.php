<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $compiled = app('blade.compiler')->compileString(file_get_contents(__DIR__ . '/../resources/views/student/fees/index.blade.php'));
    file_put_contents(__DIR__ . '/test_compiled.php', $compiled);
    exec('php -l ' . escapeshellarg(__DIR__ . '/test_compiled.php'), $out, $ret);
    echo implode(PHP_EOL, $out) . PHP_EOL;
    echo "Exit code: " . $ret . PHP_EOL;
} catch (Throwable $e) {
    echo "Exception: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
}
