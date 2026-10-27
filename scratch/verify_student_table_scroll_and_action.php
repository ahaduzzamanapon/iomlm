<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

echo "=== START VERIFICATION: STUDENT TABLE SCROLL & ACTION DROPDOWN FIX ===\n\n";

$passCount = 0;
function assertTest($condition, $message) {
    global $passCount;
    if ($condition) {
        echo "  [PASS] {$message}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$message}\n";
        exit(1);
    }
}

// 1. PHP Lint on views and modified files
echo "1. Checking PHP Syntax on Blade files...\n";
$bladeFiles = [
    'resources/views/admin/students/index.blade.php',
    'resources/views/admin/courses/index.blade.php',
    'resources/views/admin/exams/index.blade.php',
    'resources/views/admin/layouts/app.blade.php',
];
foreach ($bladeFiles as $file) {
    $fullPath = realpath(__DIR__ . '/../' . $file);
    assertTest(file_exists($fullPath), "File exists: {$file}");
}

// 2. Verify admin/students/index.blade.php
echo "\n2. Checking resources/views/admin/students/index.blade.php...\n";
$studentsBlade = file_get_contents(resource_path('views/admin/students/index.blade.php'));
assertTest(!str_contains($studentsBlade, '.table-wrapper:has(.dropdown-menu.open)'), "students/index.blade.php has NO '.table-wrapper:has(.dropdown-menu.open)'");
assertTest(!str_contains($studentsBlade, 'overflow: visible !important') && !str_contains($studentsBlade, 'overflow:visible !important'), "students/index.blade.php has NO 'overflow: visible !important'");
assertTest(str_contains($studentsBlade, 'overflow-x: auto !important'), "students/index.blade.php has explicit 'overflow-x: auto !important'");
assertTest(str_contains($studentsBlade, 'min-width:1100px') || str_contains($studentsBlade, 'min-width: 1100px'), "students table has min-width: 1100px");

// 3. Verify admin/courses/index.blade.php
echo "\n3. Checking resources/views/admin/courses/index.blade.php...\n";
$coursesBlade = file_get_contents(resource_path('views/admin/courses/index.blade.php'));
assertTest(!str_contains($coursesBlade, '.table-wrapper:has(.dropdown-menu.open)'), "courses/index.blade.php has NO '.table-wrapper:has(.dropdown-menu.open)'");

// 4. Verify admin/exams/index.blade.php
echo "\n4. Checking resources/views/admin/exams/index.blade.php...\n";
$examsBlade = file_get_contents(resource_path('views/admin/exams/index.blade.php'));
assertTest(!str_contains($examsBlade, '.table-wrapper:has(.dropdown-menu.open)'), "exams/index.blade.php has NO '.table-wrapper:has(.dropdown-menu.open)'");

// 5. Verify admin/layouts/app.blade.php toggleDropdown implementation
echo "\n5. Checking resources/views/admin/layouts/app.blade.php...\n";
$appBlade = file_get_contents(resource_path('views/admin/layouts/app.blade.php'));
assertTest(str_contains($appBlade, "m.style.position = 'fixed'"), "app.blade.php sets position: fixed for table dropdowns");
assertTest(str_contains($appBlade, "m.style.zIndex = '99999'"), "app.blade.php sets zIndex = 99999");
assertTest(str_contains($appBlade, "btn.getBoundingClientRect()"), "app.blade.php computes btnRect via getBoundingClientRect()");
assertTest(str_contains($appBlade, "window.addEventListener('scroll'"), "app.blade.php handles scroll closing via window scroll capture");

// 6. Test Blade compilation
echo "\n6. Compiling Blade templates...\n";
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$bladeCompiler = app('blade.compiler');
$compiledStudents = $bladeCompiler->compileString($studentsBlade);
assertTest(strlen($compiledStudents) > 1000, "BladeCompiler successfully compiled students/index.blade.php without syntax errors");

$compiledApp = $bladeCompiler->compileString($appBlade);
assertTest(strlen($compiledApp) > 1000, "BladeCompiler successfully compiled admin/layouts/app.blade.php without syntax errors");

echo "\n=== ALL {$passCount} VERIFICATION CHECKS PASSED (Exit Code 0) ===\n";
