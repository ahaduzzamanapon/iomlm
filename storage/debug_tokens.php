<?php
$tokens = token_get_all(file_get_contents(__DIR__ . '/test_compiled.php'));

$ifColonStack = [];
$curlyStack = [];

for ($i = 0; $i < count($tokens); $i++) {
    $t = $tokens[$i];
    if (is_array($t)) {
        $id = $t[0];
        $text = $t[1];
        $line = $t[2];

        if ($id === T_IF) {
            // check if followed by colon syntax or brace syntax
            // look ahead for ':' or '{'
            for ($j = $i + 1; $j < count($tokens); $j++) {
                $tj = $tokens[$j];
                if (is_string($tj)) {
                    if ($tj === ':') {
                        $ifColonStack[] = $line;
                        break;
                    } elseif ($tj === '{') {
                        // braced if
                        break;
                    } elseif ($tj === ';') {
                        break;
                    }
                }
            }
        } elseif ($id === T_ENDIF) {
            if (empty($ifColonStack)) {
                echo "EXTRA T_ENDIF at line $line !\n";
            } else {
                $matched = array_pop($ifColonStack);
                // echo "T_ENDIF at line $line matched T_IF from line $matched\n";
            }
        }
    }
}

echo "Remaining unmatched T_IF colons: " . count($ifColonStack) . "\n";
foreach ($ifColonStack as $l) {
    echo " - Unmatched T_IF from line $l\n";
}
