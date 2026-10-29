<?php

$lines = file("C:/Users/Ahad/.gemini/antigravity-ide/brain/ebcfa6aa-feea-421d-8128-cb2e37589db7/.system_generated/logs/transcript.jsonl");
echo "Total transcript lines: " . count($lines) . "\n";

$userInputs = [];
foreach ($lines as $idx => $line) {
    $data = json_decode($line, true);
    if (($data['type'] ?? '') === 'USER_INPUT') {
        $userInputs[] = [
            'index' => $idx,
            'content' => $data['content'] ?? '',
        ];
    }
}

// Show the last 25 user inputs
$recent = array_slice($userInputs, -25);
foreach ($recent as $item) {
    echo "=== Step {$item['index']} ===\n";
    echo mb_substr($item['content'], 0, 300) . "\n\n";
}
