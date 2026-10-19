<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$failed = 0;
$passed = 0;

function assertCondition($cond, $desc) {
    global $failed, $passed;
    if ($cond) {
        echo "  [PASS] {$desc}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$desc}\n";
        $failed++;
    }
}

echo "============================================================\n";
echo "VERIFYING EMAIL TEMPLATES SIDEBAR NAVIGATION POSITION\n";
echo "============================================================\n";

$appContent = file_get_contents(resource_path('views/admin/layouts/app.blade.php'));

// 1. Blade compiler check
try {
    $compiled = app('blade.compiler')->compileString($appContent);
    assertCondition(!empty($compiled), "admin/layouts/app.blade.php compiles without syntax errors.");
} catch (\Throwable $e) {
    assertCondition(false, "admin/layouts/app.blade.php syntax error: " . $e->getMessage());
}

// 2. Check Communication section does NOT contain email-templates
$commSectionStart = strpos($appContent, '{{-- ── 6. Communication ── --}}');
$commSectionEnd = strpos($appContent, '{{-- ── 7. Accounts & Financials ── --}}');
assertCondition($commSectionStart !== false && $commSectionEnd !== false, "Communication section boundaries located.");

$commSubstr = substr($appContent, $commSectionStart, $commSectionEnd - $commSectionStart);

assertCondition(strpos($commSubstr, 'admin.email-templates') === false, "Communication section does NOT contain 'admin.email-templates' link or route.");
assertCondition(strpos($commSubstr, 'Email Templates') === false, "Communication section does NOT contain 'Email Templates' text.");

// 3. Check People section DOES contain email-templates
$peopleSectionStart = strpos($appContent, '{{-- ── 3. People ── --}}');
$peopleSectionEnd = strpos($appContent, '{{-- ── 4. Classes & Batches ── --}}');
assertCondition($peopleSectionStart !== false && $peopleSectionEnd !== false, "People section boundaries located.");

$peopleSubstr = substr($appContent, $peopleSectionStart, $peopleSectionEnd - $peopleSectionStart);

assertCondition(strpos($peopleSubstr, 'admin.email-templates.index') !== false, "People section contains 'admin.email-templates.index' route.");
assertCondition(strpos($peopleSubstr, 'Email Templates (ইমেইল টেমপ্লেট)') !== false, "People section contains 'Email Templates (ইমেইল টেমপ্লেট)'.");

echo "\n============================================================\n";
echo "VERIFICATION SUMMARY\n";
echo "============================================================\n";
echo "Passed: {$passed} | Failed: {$failed}\n";

if ($failed > 0) {
    echo "❌ VERIFICATION FAILED!\n";
    exit(1);
} else {
    echo "✅ ALL VERIFICATION TESTS PASSED SUCCESSFULLY! (Exit Code 0)\n";
    exit(0);
}
