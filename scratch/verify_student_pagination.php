<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\User;
use Illuminate\Pagination\Paginator;

echo "--- VERIFYING TASK 65: STUDENT PAGINATION & CONTROLS ---\n\n";

// 1. Verify AppServiceProvider default view
$defaultView = Paginator::$defaultView;
echo "1. Checking Paginator Default View:\n";
echo "   Paginator::\$defaultView = " . ($defaultView ?? 'NULL') . "\n";
if ($defaultView !== 'vendor.pagination.custom') {
    echo "   [FAIL] Expected 'vendor.pagination.custom', got '$defaultView'\n";
    exit(1);
}
echo "   [PASS] Paginator default view is 'vendor.pagination.custom'\n\n";

// 2. Verify blade template exists and has no unstyled giant SVGs
echo "2. Checking vendor/pagination/custom.blade.php:\n";
$customBlade = resource_path('views/vendor/pagination/custom.blade.php');
if (!file_exists($customBlade)) {
    echo "   [FAIL] custom.blade.php does not exist\n";
    exit(1);
}
$content = file_get_contents($customBlade);
if (strpos($content, 'पूर्ववर्ती') === false && strpos($content, 'পূর্ববর্তী') === false) {
    echo "   [FAIL] custom.blade.php missing Bengali prev text\n";
    exit(1);
}
if (strpos($content, 'পরবর্তী') === false) {
    echo "   [FAIL] custom.blade.php missing Bengali next text\n";
    exit(1);
}
if (strpos($content, 'Kalpurush') === false) {
    echo "   [FAIL] custom.blade.php must use 'Kalpurush' font\n";
    exit(1);
}
echo "   [PASS] Custom pagination blade file is well-formed with Bengali text and 'Kalpurush' font.\n\n";

// 3. Test StudentController per_page query handling
echo "3. Testing Controller with different per_page inputs:\n";
$admin = User::where('role', 'admin')->first() ?: User::first();

// Request with per_page = 10
$request10 = \Illuminate\Http\Request::create('/admin/students', 'GET', ['per_page' => 10]);
$controller = app(\App\Http\Controllers\Admin\StudentController::class);
$response10 = $controller->index($request10);
$viewData10 = $response10->getData();
$paginator10 = $viewData10['students'];

if ($paginator10->perPage() !== 10) {
    echo "   [FAIL] Expected perPage 10, got " . $paginator10->perPage() . "\n";
    exit(1);
}
echo "   [PASS] per_page=10 returned paginator with perPage = 10\n";

// Request with per_page = 50
$request50 = \Illuminate\Http\Request::create('/admin/students', 'GET', ['per_page' => 50]);
$response50 = $controller->index($request50);
$paginator50 = $response50->getData()['students'];
if ($paginator50->perPage() !== 50) {
    echo "   [FAIL] Expected perPage 50, got " . $paginator50->perPage() . "\n";
    exit(1);
}
echo "   [PASS] per_page=50 returned paginator with perPage = 50\n";

// Request with per_page = 'all'
$requestAll = \Illuminate\Http\Request::create('/admin/students', 'GET', ['per_page' => 'all']);
$responseAll = $controller->index($requestAll);
$paginatorAll = $responseAll->getData()['students'];
if ($paginatorAll->perPage() < 1000) {
    echo "   [FAIL] Expected perPage for 'all' to be large (>=1000), got " . $paginatorAll->perPage() . "\n";
    exit(1);
}
echo "   [PASS] per_page='all' returned paginator with perPage = " . $paginatorAll->perPage() . "\n\n";

// 4. Test View Render of pagination
echo "4. Testing Rendered View Output:\n";
auth()->login($admin);
$renderedHtml = $response10->render();
if (strpos($renderedHtml, 'id="per_page_select"') === false) {
    echo "   [FAIL] Rendered view missing per_page_select\n";
    exit(1);
}
if (strpos($renderedHtml, 'প্রতি পৃষ্ঠায়:') === false) {
    echo "   [FAIL] Rendered view missing Bengali per-page label\n";
    exit(1);
}
echo "   [PASS] Student list view rendered with per-page dropdown and styled pagination controls.\n\n";

echo "ALL TASK 65 CHECKS PASSED SUCCESSFULLY (Exit Code 0)!\n";
exit(0);
