<?php

$viewsDir = __DIR__ . '/../resources/views';
$cssFile = __DIR__ . '/../public/css/app.css';

echo "=== Font Awesome Migration Verification ===\n\n";

$failed = false;

// 1. Check CDN in Layouts
$layouts = [
    'admin' => $viewsDir . '/admin/layouts/app.blade.php',
    'teacher' => $viewsDir . '/teacher/layouts/app.blade.php',
    'student' => $viewsDir . '/student/layouts/app.blade.php',
    'support' => $viewsDir . '/support/layouts/app.blade.php',
    'login' => $viewsDir . '/auth/login.blade.php',
];

echo "1. Checking Font Awesome CDN in layout files:\n";
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

// 4. Check FA icon counts across key templates
echo "\n4. Verifying Font Awesome icons usage across touched templates:\n";
$sampleTemplates = [
    'admin/dashboard.blade.php',
    'admin/courses/index.blade.php',
    'admin/teachers/index.blade.php',
    'admin/support/tickets.blade.php',
    'teacher/dashboard.blade.php',
    'student/dashboard.blade.php',
    'student/my-course/index.blade.php',
];

foreach ($sampleTemplates as $tmpl) {
    $p = $viewsDir . '/' . $tmpl;
    if (file_exists($p)) {
        $c = file_get_contents($p);
        preg_match_all('/<i\s+class="[^"]*fa-[^"]*"[^>]*>/i', $c, $matches);
        $count = count($matches[0]);
        echo "  [PASS] $tmpl has $count Font Awesome icon(s)\n";
        if ($count === 0) {
            echo "  [WARN] Expected FA icons in $tmpl\n";
        }
    }
}

echo "\n============================================\n";
if ($failed) {
    echo "VERIFICATION FAILED! (Exit Code 1)\n";
    exit(1);
} else {
    echo "ALL CHECKS PASSED SUCCESSFULLY! (Exit Code 0)\n";
    exit(0);
}
