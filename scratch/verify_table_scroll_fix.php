<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== START VERIFICATION: TABLE HORIZONTAL SCROLL & OVERFLOW FIX ===\n\n";

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

// 1. Verify batches/index.blade.php has no overflow:visible and has proper scrollable table
echo "1. Checking admin/batches/index.blade.php...\n";
$batchesBlade = file_get_contents(resource_path('views/admin/batches/index.blade.php'));
assertTest(!str_contains($batchesBlade, 'style="overflow:visible"'), "batches/index.blade.php has NO 'style=\"overflow:visible\"'");
assertTest(str_contains($batchesBlade, 'overflow-x: auto !important'), "batches/index.blade.php has 'overflow-x: auto !important'");
assertTest(str_contains($batchesBlade, 'min-width: 1120px') || str_contains($batchesBlade, 'min-width:1120px'), "batches table has min-width: 1120px for clear layout spacing");
assertTest(str_contains($batchesBlade, 'tbody tr:last-child .dropdown-menu'), "batches/index.blade.php has auto-flip dropdown positioning for last rows");

// 2. Verify teachers/index.blade.php has no overflow:visible
echo "\n2. Checking admin/teachers/index.blade.php...\n";
$teachersBlade = file_get_contents(resource_path('views/admin/teachers/index.blade.php'));
assertTest(!str_contains($teachersBlade, 'style="overflow:visible"'), "teachers/index.blade.php has NO 'style=\"overflow:visible\"'");
assertTest(str_contains($teachersBlade, 'overflow-x: auto !important'), "teachers/index.blade.php has 'overflow-x: auto !important'");

// 3. Verify support/tickets.blade.php has no overflow:visible
echo "\n3. Checking admin/support/tickets.blade.php...\n";
$ticketsBlade = file_get_contents(resource_path('views/admin/support/tickets.blade.php'));
assertTest(!str_contains($ticketsBlade, 'style="overflow:visible"'), "support/tickets.blade.php has NO 'style=\"overflow:visible\"'");
assertTest(str_contains($ticketsBlade, 'overflow-x: auto !important'), "support/tickets.blade.php has 'overflow-x: auto !important'");

// 4. Verify global app.css and admin layout have universal table-wrapper scroll styles
echo "\n4. Checking app.css and admin layout.blade.php...\n";
$appCss = file_get_contents(public_path('css/app.css'));
assertTest(str_contains($appCss, '.table-wrapper {') && str_contains($appCss, 'overflow-x: auto !important;'), "app.css has universal .table-wrapper overflow-x: auto !important");

$adminLayout = file_get_contents(resource_path('views/admin/layouts/app.blade.php'));
assertTest(str_contains($adminLayout, '.table-wrapper {') && str_contains($adminLayout, 'overflow-x: auto !important;'), "admin layout has universal .table-wrapper overflow-x: auto !important");

// 5. Verify Controller & View Rendering for Admin Batches Page
echo "\n5. Testing Admin Batches Controller & Blade Rendering...\n";
$adminUser = User::where('role', 'admin')->first() ?: User::first();
Auth::login($adminUser);

$controller = app()->make(\App\Http\Controllers\Admin\BatchController::class);
$request = \Illuminate\Http\Request::create(route('admin.batches.index'), 'GET', [
    'academic_year_id' => 9,
]);

$response = $controller->index($request);
assertTest($response !== null, "BatchController@index executed successfully");

$html = $response->render();
assertTest(strlen($html) > 5000, "Rendered HTML has valid content (Length: " . strlen($html) . " bytes)");
assertTest(str_contains($html, 'class="table-wrapper"'), "Rendered HTML contains table-wrapper");
assertTest(str_contains($html, 'overflow-x:auto') || str_contains($html, 'overflow-x: auto'), "Rendered HTML contains horizontal scroll style");
assertTest(str_contains($html, 'ব্যাচ তালিকা'), "Rendered HTML contains 'ব্যাচ তালিকা' header");

echo "\n=== ALL {$passCount} TABLE SCROLL TESTS PASSED (Exit Code 0) ===\n";
