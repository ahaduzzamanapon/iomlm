<?php

echo "=== VERIFYING DYNAMIC GOOGLE OAUTH REDIRECT URI ===\n\n";

$baseDir = dirname(__DIR__);

$files = [
    $baseDir . '/app/Http/Controllers/Admin/GoogleAuthSettingController.php',
    $baseDir . '/app/Http/Controllers/Auth/GoogleAuthController.php',
    $baseDir . '/resources/views/admin/settings/google_auth.blade.php',
];

// 1. PHP Syntax Check
foreach ($files as $file) {
    $output = [];
    $returnCode = 0;
    exec("php -l \"$file\"", $output, $returnCode);
    if ($returnCode !== 0) {
        echo "❌ Syntax error in " . basename($file) . ": " . implode("\n", $output) . "\n";
        exit(1);
    }
    echo "✔ PASS: Syntax valid for " . basename($file) . "\n";
}

// 2. Check GoogleAuthSettingController
$settingCtrl = file_get_contents($baseDir . '/app/Http/Controllers/Admin/GoogleAuthSettingController.php');
if (strpos($settingCtrl, "'google_redirect_uri' => url('/auth/google/callback')") === false) {
    echo "❌ FAIL: GoogleAuthSettingController does not use dynamic url() in index.\n";
    exit(1);
}
echo "✔ PASS: GoogleAuthSettingController sets dynamic url() in index.\n";

// 3. Check GoogleAuthController
$authCtrl = file_get_contents($baseDir . '/app/Http/Controllers/Auth/GoogleAuthController.php');
if (strpos($authCtrl, "Setting::get('google_redirect_uri'") !== false) {
    echo "❌ FAIL: GoogleAuthController still references static Setting::get('google_redirect_uri').\n";
    exit(1);
}
if (substr_count($authCtrl, '$redirectUri = url(\'/auth/google/callback\');') !== 2) {
    echo "❌ FAIL: GoogleAuthController does not use url('/auth/google/callback') for both redirect and callback.\n";
    exit(1);
}
echo "✔ PASS: GoogleAuthController strictly uses dynamic url('/auth/google/callback') in both redirect and callback.\n";

// 4. Check View
$viewContent = file_get_contents($baseDir . '/resources/views/admin/settings/google_auth.blade.php');
if (strpos($viewContent, "{{ url('/auth/google/callback') }}") === false) {
    echo "❌ FAIL: google_auth.blade.php does not output dynamic url('/auth/google/callback').\n";
    exit(1);
}
if (strpos($viewContent, 'Auto-Generated (Dynamic)') === false) {
    echo "❌ FAIL: google_auth.blade.php missing dynamic badge indicator.\n";
    exit(1);
}
echo "✔ PASS: google_auth.blade.php renders dynamic url('/auth/google/callback') with dynamic indicator.\n";

echo "\n🎉 ALL TESTS PASSED SUCCESSFULLY (Exit Code 0)!\n";
exit(0);
