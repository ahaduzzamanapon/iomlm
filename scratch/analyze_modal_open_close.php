<?php

$viewsPath = realpath(__DIR__ . '/../resources/views');
$report = file_get_contents(__DIR__ . '/modal_audit_report.txt');

// Parse files from report
preg_match_all('/=== (.*?) \(/', $report, $matches);
$files = $matches[1];

$analysis = [];

foreach ($files as $file) {
    $fullPath = $viewsPath . DIRECTORY_SEPARATOR . $file;
    if (!file_exists($fullPath)) continue;
    $content = file_get_contents($fullPath);
    
    // Check how modals are opened/closed in scripts
    $openCalls = [];
    $closeCalls = [];
    
    if (preg_match_all('/(?:document\.getElementById\([\'"]([^\'"]+)[\'"]\)\.(?:classList\.add\([\'"]([^\'"]+)[\'"]\)|style\.display\s*=\s*[\'"]([^\'"]+)[\'"])|openModal\([\'"]([^\'"]+)[\'"]\))/i', $content, $mOpen)) {
        for ($i = 0; $i < count($mOpen[0]); $i++) {
            $openCalls[] = trim($mOpen[0][$i]);
        }
    }
    
    // Check if file has local style with modal
    $hasLocalStyle = (bool)preg_match('/<style>[\s\S]*?(?:modal|\.modal-overlay)[\s\S]*?<\/style>/i', $content);
    
    // Check if modal has inline onclick="if(event.target===this)..."
    $hasBackdropOnclick = (bool)preg_match('/onclick=["\'][^"\']*event\.target\s*===\s*this[^"\']*["\']/i', $content);
    
    $analysis[$file] = [
        'hasLocalStyle' => $hasLocalStyle,
        'hasBackdropOnclick' => $hasBackdropOnclick,
        'openCalls' => array_slice(array_unique($openCalls), 0, 5),
    ];
}

foreach ($analysis as $f => $data) {
    echo "FILE: {$f}\n";
    echo "  Local modal style: " . ($data['hasLocalStyle'] ? 'YES' : 'NO') . "\n";
    echo "  Inline backdrop onclick: " . ($data['hasBackdropOnclick'] ? 'YES' : 'NO') . "\n";
    echo "  Open/Display calls: " . implode(' | ', $data['openCalls']) . "\n\n";
}
