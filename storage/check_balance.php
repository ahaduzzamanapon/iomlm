<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$content = file_get_contents(__DIR__ . '/../resources/views/student/fees/index.blade.php');

$compiled = app('blade.compiler')->compileString($content);
$tokens = token_get_all($compiled);

$stack = [];
foreach ($tokens as $token) {
    if (is_array($token)) {
        $name = token_name($token[0]);
        $text = $token[1];
        $line = $token[2];
        if ($token[0] === T_IF) {
            $stack[] = ['type' => 'if', 'line' => $line];
        } elseif ($token[0] === T_ENDIF) {
            if (empty($stack)) {
                echo "Unexpected ENDIF at compiled line $line\n";
            } else {
                array_pop($stack);
            }
        }
    }
}

if (!empty($stack)) {
    echo "UNCLOSED IF statements in compiled PHP:\n";
    foreach ($stack as $s) {
        echo " - Line {$s['line']}\n";
    }
} else {
    echo "All IFs in compiled PHP are balanced!\n";
}
