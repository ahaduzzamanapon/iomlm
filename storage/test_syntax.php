<?php
$lines = file(__DIR__ . '/test_compiled.php');
// test line by line or binary search to see where syntax error begins
for ($i = 10; $i <= count($lines); $i += 50) {
    $slice = array_slice($lines, 0, $i);
    // if $slice has unclosed if, add endif;
    // Let's test with php -l on valid blocks
}
// Or let's print tokens from line 1320 to 1350
$tokens = token_get_all(file_get_contents(__DIR__ . '/test_compiled.php'));
foreach ($tokens as $t) {
    if (is_array($t)) {
        if ($t[2] >= 1340 && $t[2] <= 1350) {
            echo token_name($t[0]) . ": " . trim($t[1]) . " (line {$t[2]})\n";
        }
    } else {
        // char
    }
}
