<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WaiverApplication;
use App\Models\AdmissionForm;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\WaiverApplicationController;

echo "=== STARTING VERIFICATION: POOR FUND CODE NAME CHANGE TO PF ===\n\n";

// 1. Check Model Generation
echo "Step 1: Testing WaiverApplication::generateApplicationNo()...\n";
$newAppNo = WaiverApplication::generateApplicationNo();
echo "  Generated App No: {$newAppNo}\n";
if (!str_starts_with($newAppNo, 'PF-')) {
    echo "❌ FAIL: Expected prefix 'PF-', got: {$newAppNo}\n";
    exit(1);
}
echo "  ✔ generateApplicationNo() correctly generates 'PF-' prefix.\n";

// 2. Check Database Records in waiver_applications
echo "\nStep 2: Checking database records in waiver_applications...\n";
$poorCount = WaiverApplication::where('application_no', 'like', 'POOR-%')->count();
$pfCount   = WaiverApplication::where('application_no', 'like', 'PF-%')->count();
echo "  Records with 'POOR-%': {$poorCount}\n";
echo "  Records with 'PF-%': {$pfCount}\n";
if ($poorCount > 0) {
    echo "❌ FAIL: Still found {$poorCount} records with 'POOR-' prefix in waiver_applications.\n";
    exit(1);
}
if ($pfCount === 0) {
    echo "❌ FAIL: No records found with 'PF-' prefix in waiver_applications.\n";
    exit(1);
}
echo "  ✔ All waiver applications now use 'PF-' prefix.\n";

// 3. Check Database Records in admission_forms
echo "\nStep 3: Checking database records in admission_forms...\n";
$poorAdmCount = AdmissionForm::where('waiver_code', 'like', 'POOR-%')->count();
$pfAdmCount   = AdmissionForm::where('waiver_code', 'like', 'PF-%')->count();
echo "  Admission forms with 'POOR-%': {$poorAdmCount}\n";
echo "  Admission forms with 'PF-%': {$pfAdmCount}\n";
if ($poorAdmCount > 0) {
    echo "❌ FAIL: Still found {$poorAdmCount} admission forms with 'POOR-' waiver_code.\n";
    exit(1);
}
echo "  ✔ All admission forms now use 'PF-' prefix.\n";

// 4. Test Admin View Rendering
echo "\nStep 4: Testing Admin Waiver Applications index view rendering...\n";
$adminUser = User::where('role', 'ADMIN')->first() ?? User::first();
auth()->login($adminUser);

$controller = new WaiverApplicationController();
$request = Request::create('/admin/waiver-applications', 'GET');
$response = $controller->index($request);
$html = $response->render();

if (strpos($html, 'PF-2026-0013') === false) {
    echo "❌ FAIL: 'PF-2026-0013' not found in rendered table.\n";
    exit(1);
}
if (strpos($html, 'POOR-2026-0013') !== false) {
    echo "❌ FAIL: 'POOR-2026-0013' still found in rendered table.\n";
    exit(1);
}
echo "  ✔ Admin table displays 'PF-2026-0013' and no 'POOR-' prefix.\n";

// 5. Test Search Filter
echo "\nStep 5: Testing search filter with 'PF-2026-0013' and legacy 'POOR-2026-0013'...\n";
$reqSearchPf = Request::create('/admin/waiver-applications', 'GET', ['search' => 'PF-2026-0013']);
$respSearchPf = $controller->index($reqSearchPf);
$dataPf = $respSearchPf->getData();
if ($dataPf['applications']->isEmpty()) {
    echo "❌ FAIL: Search with 'PF-2026-0013' returned 0 results.\n";
    exit(1);
}
echo "  ✔ Search with 'PF-2026-0013' succeeded (Found: " . $dataPf['applications']->first()->application_no . ").\n";

$reqSearchPoor = Request::create('/admin/waiver-applications', 'GET', ['search' => 'POOR-2026-0013']);
$respSearchPoor = $controller->index($reqSearchPoor);
$dataPoor = $respSearchPoor->getData();
if ($dataPoor['applications']->isEmpty()) {
    echo "❌ FAIL: Legacy search with 'POOR-2026-0013' returned 0 results.\n";
    exit(1);
}
echo "  ✔ Legacy search with 'POOR-2026-0013' also resolved to: " . $dataPoor['applications']->first()->application_no . ".\n";

echo "\n=======================================================\n";
echo "🎉 ALL VERIFICATION TESTS PASSED SUCCESSFULLY! (EXIT 0)\n";
echo "=======================================================\n";
exit(0);
