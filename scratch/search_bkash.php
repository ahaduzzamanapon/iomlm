<?php
$content = file_get_contents(__DIR__ . '/bkash_script_utf8.js');
echo "File size: " . strlen($content) . "\n";
echo "First 200 chars:\n" . substr($content, 0, 200) . "\n\n";

preg_match('/var (_0x[a-f0-9]+)\s*=\s*\[(.*?)\];/s', $content, $m);
if (isset($m[2])) {
    echo "Found array matches!\n";
    eval('$arr = [' . $m[2] . '];');
    echo "Total strings: " . count($arr) . "\n";
    foreach ($arr as $str) {
        if (preg_match('/iframe|modal|scroll|overflow|style|height|width|fixed|bKash|px/i', $str)) {
            echo "MATCH: $str\n";
        }
    }
} else {
    echo "No array match\n";
}
