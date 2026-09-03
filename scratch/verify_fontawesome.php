<?php

$viewsDir = __DIR__ . '/../resources/views';
$cssFile = __DIR__ . '/../public/css/app.css';

echo "=== Font Awesome & Streamlined Icon Cleanup Verification ===\n\n";

$failed = false;

// 1. Check CDN in Layouts
$layouts = [
    'admin' => $viewsDir . '/admin/layouts/app.blade.php',
    'teacher' => $viewsDir . '/teacher/layouts/app.blade.php',
    'student' => $viewsDir . '/student/layouts/app.blade.php',
    'support' => $viewsDir . '/support/layouts/app.blade.php',
    'login' => $viewsDir . '/auth/login.blade.php',
    'welcome' => $viewsDir . '/welcome.blade.php',
];

echo "1. Checking Font Awesome CDN in layout and standalone files:\n";
foreach ($layouts as $name => $path) {
    if (!file_exists($path)) {
        echo "  [FAIL] $name layout not found at: $path\n";
        $failed = true;
        continue;
    }
    $content = file_get_contents($path);
    if (strpos($content, 'font-awesome') !== false || strpos($content, 'fontawesome') !== false) {
        echo "  [PASS] $name layout includes Font Awesome CDN\n";
    } else {
        echo "  [FAIL] $name layout MISSING Font Awesome CDN!\n";
        $failed = true;
    }
}

// 2. Check CSS styling in app.css
echo "\n2. Checking CSS rule support in public/css/app.css:\n";
if (file_exists($cssFile)) {
    $css = file_get_contents($cssFile);
    if (strpos($css, '.nav-item i') !== false && strpos($css, '.tree-toggle i') !== false) {
        echo "  [PASS] app.css contains dedicated .nav-item i and .tree-toggle i styling\n";
    } else {
        echo "  [FAIL] app.css missing Font Awesome nav-item / tree-toggle rules\n";
        $failed = true;
    }
} else {
    echo "  [FAIL] app.css not found!\n";
    $failed = true;
}

// 3. Scan all Blade files for <svg> tags
echo "\n3. Scanning all Blade files for lingering <svg> tags:\n";
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
$svgCount = 0;
$scannedFiles = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        $scannedFiles++;
        $content = file_get_contents($file->getPathname());
        if (preg_match('/<svg\b/i', $content)) {
            echo "  [FAIL] Lingering <svg> found in: " . $file->getPathname() . "\n";
            $svgCount++;
            $failed = true;
        }
    }
}

echo "  Scanned $scannedFiles blade templates.\n";
if ($svgCount === 0) {
    echo "  [PASS] 0 lingering <svg> tags found across all blade templates!\n";
} else {
    echo "  [FAIL] Found $svgCount blade templates with <svg> tags!\n";
}

// 4. Verify Sidebar Navigation Icons are preserved
echo "\n4. Verifying Sidebar Navigation Icons preserved in all layouts:\n";
$sidebarLayouts = [
    'admin' => $viewsDir . '/admin/layouts/app.blade.php',
    'teacher' => $viewsDir . '/teacher/layouts/app.blade.php',
    'student' => $viewsDir . '/student/layouts/app.blade.php',
    'support' => $viewsDir . '/support/layouts/app.blade.php',
];
foreach ($sidebarLayouts as $role => $layoutPath) {
    $c = file_get_contents($layoutPath);
    preg_match_all('/<a\b[^>]*class="[^"]*(?:nav-item|menu-item)[^"]*"[^>]*>\s*<i\s+class="[^"]*fa-[^"]*"[^>]*>/i', $c, $navMatches);
    $navCount = count($navMatches[0]);
    if ($navCount > 0) {
        echo "  [PASS] $role sidebar retains $navCount Font Awesome navigation icons\n";
    } else {
        echo "  [FAIL] $role sidebar is missing navigation icons!\n";
        $failed = true;
    }
}

// 5. Verify Functional Edit & Delete Icons are preserved
echo "\n5. Verifying Edit and Delete functional icons preserved:\n";
$sampleActionViews = [
    'admin/routine/index.blade.php',
    'admin/notices/index.blade.php',
    'teacher/exams/builder.blade.php',
];
foreach ($sampleActionViews as $sav) {
    $c = file_get_contents($viewsDir . '/' . $sav);
    $hasEditOrDelete = (strpos($c, 'fa-pen-to-square') !== false || strpos($c, 'fa-trash') !== false);
    if ($hasEditOrDelete) {
        echo "  [PASS] $sav retains Edit and/or Delete action icons\n";
    } else {
        echo "  [FAIL] $sav missing expected Edit/Delete action icons!\n";
        $failed = true;
    }
}

// 6. Verify non-essential decorative emoji icons are eliminated
echo "\n6. Checking for lingering decorative emoji icons in headings and buttons:\n";
$emojiPattern = '/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F1E6}-\x{1F1FF}\x{1F600}-\x{1F64F}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1FA70}-\x{1FAFF}]/u';
$emojiFound = 0;
foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        $lines = file($file->getPathname());
        foreach ($lines as $line) {
            // Ignore sanitization line in fees/index.blade.php
            if (strpos($line, "str_replace(' 🔵'") !== false) continue;
            if (preg_match($emojiPattern, $line)) {
                $emojiFound++;
            }
        }
    }
}
if ($emojiFound === 0) {
    echo "  [PASS] 0 decorative emoji icons remaining across all blade templates!\n";
} else {
    echo "  [FAIL] $emojiFound decorative emoji icons still detected!\n";
    $failed = true;
}

echo "\n============================================\n";
if ($failed) {
    echo "VERIFICATION FAILED! (Exit Code 1)\n";
    exit(1);
} else {
    echo "ALL CHECKS PASSED SUCCESSFULLY! (Exit Code 0)\n";
    exit(0);
}
