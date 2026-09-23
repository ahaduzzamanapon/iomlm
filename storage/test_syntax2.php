<?php
$code = file_get_contents(__DIR__ . '/test_compiled.php');
$tokens = token_get_all($code);

$stack = [];
foreach ($tokens as $idx => $t) {
    if (is_string($t)) {
        if ($t === '{') {
            $stack[] = ['{', $line];
        } elseif ($t === '}') {
            $top = array_pop($stack);
            if ($top[0] !== '{') {
                echo "Mismatched } at line $line, expected {$top[0]}\n";
            }
        }
    } else {
        $line = $t[2];
        if (in_array($t[0], [T_IF, T_FOR, T_FOREACH, T_WHILE, T_SWITCH])) {
            // check if followed by ':'
            // find next non-whitespace non-comment
            for ($k = $idx + 1; $k < count($tokens); $k++) {
                $next = $tokens[$k];
                if (is_array($next) && in_array($next[0], [T_WHITESPACE, T_COMMENT])) continue;
                if ($next === ':') {
                    $stack[] = [token_name($t[0]), $line];
                    break;
                } elseif ($next === '{' || (is_string($next) && $next === ';')) {
                    break;
                }
            }
        } elseif (in_array($t[0], [T_ENDIF, T_ENDFOR, T_ENDFOREACH, T_ENDWHILE, T_ENDSWITCH])) {
            $top = array_pop($stack);
            $expected = 'T_' . substr(token_name($t[0]), 5);
            if ($top[0] !== $expected) {
                echo "Mismatched " . token_name($t[0]) . " at line $line! Stack had: " . ($top ? "{$top[0]} from line {$top[1]}" : 'EMPTY') . "\n";
            }
        }
    }
}

echo "Done tracking! Unclosed in stack:\n";
foreach ($stack as $s) {
    echo "- {$s[0]} from line {$s[1]}\n";
}
