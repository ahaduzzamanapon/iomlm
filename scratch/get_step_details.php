<?php

$lines = file("C:/Users/Ahad/.gemini/antigravity-ide/brain/ebcfa6aa-feea-421d-8128-cb2e37589db7/.system_generated/logs/transcript.jsonl");

$stepsToInspect = [4012, 4162, 4540, 4711, 5027, 5152, 5262, 5427, 5627, 5703, 6076];

foreach ($stepsToInspect as $targetStep) {
    if (isset($lines[$targetStep])) {
        $data = json_decode($lines[$targetStep], true);
        echo "=== STEP $targetStep ===\n";
        echo "User text: " . ($data['content'] ?? '') . "\n";
        // Also look at planner response right after it to see what we did
        for ($j = $targetStep + 1; $j < $targetStep + 15 && $j < count($lines); $j++) {
            $p = json_decode($lines[$j], true);
            if (($p['type'] ?? '') === 'PLANNER_RESPONSE' && !empty($p['content'])) {
                echo "Response snippet: " . mb_substr($p['content'], 0, 300) . "\n---\n";
                break;
            }
        }
        echo "\n";
    }
}
