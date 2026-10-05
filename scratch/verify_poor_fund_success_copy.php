<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WaiverApplication;
use Illuminate\Support\Facades\View;

echo "=== VERIFYING POOR FUND SUCCESS COPY BUTTON & PRESERVATION NOTICE ===\n\n";

$assertions = 0;

function assertCondition($cond, $msg) {
    global $assertions;
    if (!$cond) {
        echo "❌ FAILED: $msg\n";
        exit(1);
    }
    echo "✅ PASSED: $msg\n";
    $assertions++;
}

// Find or create sample waiver application
$waiverApp = WaiverApplication::where('application_no', 'PF-2026-0015')->first();
if (!$waiverApp) {
    $waiverApp = WaiverApplication::first();
}
assertCondition($waiverApp !== null, "Found sample WaiverApplication (App No: {$waiverApp->application_no})");

$renderedHtml = View::make('public.poor_fund_success', ['app' => $waiverApp])->render();

// 1. Check preservation instructions
assertCondition(str_contains($renderedHtml, 'গুরুত্বপূর্ণ তথ্য'), "Notice contains 'গুরুত্বপূর্ণ তথ্য'");
assertCondition(str_contains($renderedHtml, 'রেফারেন্স নম্বরটি যত্নসহকারে সংরক্ষণ করুন'), "Notice contains 'রেফারেন্স নম্বরটি যত্নসহকারে সংরক্ষণ করুন'");
assertCondition(str_contains($renderedHtml, 'alert-box'), "Preservation alert box class .alert-box is present");

// 2. Check Copy Button
assertCondition(str_contains($renderedHtml, 'id="copyBtn"'), "Copy button #copyBtn present in DOM");
assertCondition(str_contains($renderedHtml, 'কপি করুন'), "Button label 'কপি করুন' present in DOM");
assertCondition(str_contains($renderedHtml, 'copyRefNumber'), "JavaScript copyRefNumber function call present");
assertCondition(str_contains($renderedHtml, 'id="appRefNo"'), "Reference number container #appRefNo present");
assertCondition(str_contains($renderedHtml, $waiverApp->application_no), "Rendered HTML contains application number: {$waiverApp->application_no}");

// 3. Check Font
assertCondition(str_contains($renderedHtml, 'Kalpurush'), "Kalpurush font is included in style rules");

echo "\n======================================================\n";
echo ">>> ALL {$assertions} ASSERTIONS PASSED WITH EXIT CODE 0 <<<\n";
echo "======================================================\n";
exit(0);
