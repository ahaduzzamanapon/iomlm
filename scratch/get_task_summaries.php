<?php

$lines = file("C:/Users/Ahad/.gemini/antigravity-ide/brain/ebcfa6aa-feea-421d-8128-cb2e37589db7/.system_generated/logs/transcript.jsonl");

$steps = [4162, 4540, 4711, 5027, 5152, 5262, 5427, 5627, 5703, 6076];

foreach ($steps as $s) {
    echo "==================== STEP $s ====================\n";
    for ($i = $s; $i < $s + 400 && $i < count($lines); $i++) {
        $row = json_decode($lines[$i], true);
        if (($row['type'] ?? '') === 'PLANNER_RESPONSE' && !empty($row['content'])) {
            // Find any markdown headers or bold summaries
            $c = $row['content'];
            if (preg_match('/###\s+(.+)/u', $c, $m) || preg_match('/\*\*([^*]+)\*\*/u', $c, $m)) {
                echo "Heading: " . substr($m[0], 0, 150) . "\n";
            }
            // print first 500 chars of final response if it looks like a task summary
            if (str_contains($c, 'টাস্ক') || str_contains($c, 'Task') || str_contains($c, 'সম্পন্ন') || str_contains($c, 'ভেরিফিকেশন') || str_contains($c, 'আলহামদুলিল্লাহ')) {
                echo "Summary: " . mb_substr($c, 0, 400) . "\n---\n";
                break;
            }
        }
    }
}
