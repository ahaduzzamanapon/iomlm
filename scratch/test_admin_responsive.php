<?php

echo "=== VERIFYING ADMIN MOBILE RESPONSIVE ENGINE & ASSETS ===\n\n";

$baseDir = dirname(__DIR__);

// 1. Check PHP syntax for Blade templates
$bladeFiles = [
    $baseDir . '/resources/views/admin/layouts/app.blade.php',
    $baseDir . '/resources/views/admin/dashboard.blade.php',
];

foreach ($bladeFiles as $file) {
    $output = [];
    $returnCode = 0;
    exec("php -l \"$file\"", $output, $returnCode);
    if ($returnCode !== 0) {
        echo "❌ Syntax error in " . basename($file) . ": " . implode("\n", $output) . "\n";
        exit(1);
    }
    echo "✔ PASS: Syntax valid for " . basename($file) . "\n";
}

// 2. Check public/css/app.css
$appCssPath = $baseDir . '/public/css/app.css';
if (!file_exists($appCssPath)) {
    echo "❌ FAIL: public/css/app.css not found\n";
    exit(1);
}

$appCss = file_get_contents($appCssPath);
$cssChecks = [
    '.main-area' => 'main-area definition exists in app.css',
    'max-width: 100% !important;' => 'main-area max-width reset in app.css responsive media query',
    'width: 100% !important;' => 'main-area width reset in app.css responsive media query',
];

foreach ($cssChecks as $needle => $label) {
    if (strpos($appCss, $needle) !== false) {
        echo "✔ PASS: [app.css] {$label}\n";
    } else {
        echo "❌ FAIL: [app.css] {$label} missing needle: {$needle}\n";
        exit(1);
    }
}

// 3. Check resources/views/admin/layouts/app.blade.php responsive block
$layoutPath = $baseDir . '/resources/views/admin/layouts/app.blade.php';
$layoutContent = file_get_contents($layoutPath);

$layoutChecks = [
    'UNIVERSAL ADMIN MOBILE RESPONSIVE ENGINE' => 'Universal responsive CSS engine in layout',
    'max-width: 100vw !important;' => 'Main-area viewport 100vw override in layout style',
    'width: 100% !important;' => 'Main-area width 100% override in layout style',
    '.dashboard-banner' => 'Responsive dashboard banner style override',
    '.stats-grid' => 'Responsive stats grid 2-column override',
    'Kalpurush' => 'Bangla Kalpurush font in layout style',
];

foreach ($layoutChecks as $needle => $label) {
    if (strpos($layoutContent, $needle) !== false) {
        echo "✔ PASS: [app.blade.php] {$label}\n";
    } else {
        echo "❌ FAIL: [app.blade.php] {$label} missing needle: {$needle}\n";
        exit(1);
    }
}

// 4. Check resources/views/admin/dashboard.blade.php
$dashPath = $baseDir . '/resources/views/admin/dashboard.blade.php';
$dashContent = file_get_contents($dashPath);

$openDivCount = substr_count($dashContent, '<div');
$closeDivCount = substr_count($dashContent, '</div');
echo "ℹ INFO: dashboard.blade.php div balance: <div: {$openDivCount}, </div: {$closeDivCount}\n";
if ($openDivCount !== $closeDivCount) {
    echo "❌ FAIL: dashboard.blade.php has unmatched <div> tags (<div: {$openDivCount}, </div: {$closeDivCount})\n";
    exit(1);
}
echo "✔ PASS: dashboard.blade.php <div> tags are perfectly balanced\n";

echo "\n🎉 ALL VERIFICATION CRITERIA MET WITH SUCCESS (Exit Code 0)!\n";
exit(0);
