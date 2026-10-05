<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$files = glob(storage_path('framework/sessions/*'));
usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
foreach (array_slice($files, 0, 10) as $f) {
    $content = file_get_contents($f);
    $data = @unserialize($content);
    echo "File: " . basename($f) . ", mtime: " . date('Y-m-d H:i:s', filemtime($f)) . PHP_EOL;
    if (is_array($data)) {
        foreach ($data as $k => $v) {
            if (str_starts_with($k, 'login_')) {
                echo "   $k => $v" . PHP_EOL;
                $u = DB::table('users')->find($v);
                echo "   User: " . ($u?->name ?? 'none') . " (id: {$v}, email: " . ($u?->email ?? '') . ")" . PHP_EOL;
                $st = DB::table('students')->where('user_id', $v)->first();
                if ($st) {
                    echo "   Student ID: {$st->id}, Code: {$st->student_code}, FeePkg: {$st->fee_package_id}" . PHP_EOL;
                }
            }
        }
    }
}
