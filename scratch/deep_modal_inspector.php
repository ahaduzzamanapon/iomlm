<?php

$viewsPath = realpath(__DIR__ . '/../resources/views');
$report = file_get_contents(__DIR__ . '/modal_audit_report.txt');
preg_match_all('/=== (.*?) \(/', $report, $matches);
$files = $matches[1];

$results = [];

foreach ($files as $file) {
    $fullPath = $viewsPath . DIRECTORY_SEPARATOR . $file;
    if (!file_exists($fullPath)) continue;
    $content = file_get_contents($fullPath);
    
    // Find all overlay divs
    preg_match_all('/<div\s+([^>]*?(?:class=["\'][^"\']*(?:modal-overlay|modal-backdrop|admin-modal-overlay)[^"\']*["\']|id=["\'][^"\']*(?:Modal|modal)[^"\']*["\'])[^>]*)>/is', $content, $divs, PREG_SET_ORDER);
    
    foreach ($divs as $d) {
        $attrs = $d[1];
        // filter out non-overlay elements
        if (preg_match('/class=["\'][^"\']*(?:modal-body|modal-footer|modal-header|modal-title|modal-close|modal-content|form-group)[^"\']*/i', $attrs) && !preg_match('/(?:modal-overlay|modal-backdrop|admin-modal-overlay)/i', $attrs)) {
            continue;
        }
        if (preg_match('/id=["\'][^"\']*(?:title|btn|body|text|desc|input|select|header|footer|close|inv|gateway|due)[^"\']*/i', $attrs)) {
            continue;
        }
        
        preg_match('/id=["\']([^"\']+)["\']/i', $attrs, $idM);
        preg_match('/class=["\']([^"\']+)["\']/i', $attrs, $classM);
        preg_match('/style=["\']([^"\']+)["\']/i', $attrs, $styleM);
        preg_match('/onclick=["\']([^"\']+)["\']/i', $attrs, $onclickM);
        
        $modalId = $idM[1] ?? 'NO_ID';
        $modalClass = $classM[1] ?? 'NO_CLASS';
        $modalStyle = $styleM[1] ?? 'NO_STYLE';
        $modalOnclick = $onclickM[1] ?? 'NO_ONCLICK';
        
        // Find JS openers for this id
        $openers = [];
        if ($modalId !== 'NO_ID') {
            if (preg_match_all('/(?:openModal\([\'"]' . preg_quote($modalId, '/') . '[\'"]\)|document\.getElementById\([\'"]' . preg_quote($modalId, '/') . '[\'"]\)\.(?:classList\.add\([^\)]+\)|style\.display\s*=\s*[\'"][^\'"]+[\'"]))/i', $content, $opM)) {
                $openers = $opM[0];
            }
        }
        
        $results[] = [
            'file' => $file,
            'id' => $modalId,
            'class' => $modalClass,
            'style' => $modalStyle,
            'onclick' => $modalOnclick,
            'openers' => array_unique($openers),
        ];
    }
}

echo "Total Modal Overlays Identified: " . count($results) . "\n\n";

$issues = [];
foreach ($results as $r) {
    $hasIssues = false;
    $issueDesc = [];
    
    // Check 1: Missing onclick AND not having modal-overlay / modal-backdrop / admin-modal-overlay class
    if ($r['onclick'] === 'NO_ONCLICK' && 
        !str_contains($r['class'], 'modal-overlay') && 
        !str_contains($r['class'], 'modal-backdrop') && 
        !str_contains($r['class'], 'admin-modal-overlay')) {
        $issueDesc[] = "No standard backdrop class and no onclick";
        $hasIssues = true;
    }
    
    // Check 2: Inline style has display:none without standard class or opened via classList.add
    if (str_contains($r['style'], 'display:none') || str_contains($r['style'], 'display: none')) {
        foreach ($r['openers'] as $op) {
            if (str_contains($op, 'classList.add') && !str_contains($op, 'style.display')) {
                $issueDesc[] = "Has inline style display:none but opened via classList.add! Conflict!";
                $hasIssues = true;
            }
        }
    }
    
    // Check 3: Opened via style.display = 'flex' but no standard backdrop onclick
    foreach ($r['openers'] as $op) {
        if (str_contains($op, "style.display = 'flex'") || str_contains($op, 'style.display="flex"')) {
            if ($r['onclick'] === 'NO_ONCLICK') {
                $issueDesc[] = "Opened via style.display = flex, but has no inline onclick backdrop closer";
                $hasIssues = true;
            }
        }
    }
    
    // Check 4: Modal has align-items: center in inline style
    if (str_contains($r['style'], 'align-items:center') || str_contains($r['style'], 'align-items: center')) {
        $issueDesc[] = "Has inline align-items: center (risks negative overflow clipping)";
        $hasIssues = true;
    }

    if ($hasIssues) {
        $issues[] = [
            'file' => $r['file'],
            'id' => $r['id'],
            'issues' => $issueDesc,
            'class' => $r['class'],
            'style' => $r['style'],
            'onclick' => $r['onclick'],
            'openers' => $r['openers'],
        ];
    }
}

echo "Found " . count($issues) . " modal elements with potential problems:\n\n";
foreach ($issues as $idx => $iss) {
    echo ($idx + 1) . ". [{$iss['file']}] ID: {$iss['id']}\n";
    echo "   Class: {$iss['class']}\n";
    echo "   Style: {$iss['style']}\n";
    echo "   Onclick: {$iss['onclick']}\n";
    echo "   Openers: " . implode(', ', $iss['openers']) . "\n";
    echo "   ISSUES:\n";
    foreach ($iss['issues'] as $i) {
        echo "     * {$i}\n";
    }
    echo "\n";
}
