<?php

$js = file_get_contents("e:/My Soft/iom/learning-plus/scratch/bkash_script.js");

// Search for iframe creation and styles
preg_match_all('/(<iframe[^>]+>|<div[^>]+id=[^>]+>|iframe|style|height|width|overflow|scrolling)/i', $js, $matches);
echo "Occurrences found: " . count($matches[0]) . "\n";

// Let's find snippets with 'iframe'
$lines = explode(";", $js);
foreach ($lines as $line) {
    if (str_contains($line, 'iframe') || str_contains($line, 'bKash_modal') || str_contains($line, 'bKash_frame')) {
        echo "SNIPPET: " . trim(substr($line, 0, 300)) . "\n---\n";
    }
}
