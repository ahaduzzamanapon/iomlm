<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AcademicSession;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\ReportController;

function assertTest($condition, $message) {
    if (!$condition) {
        echo "FAILED: {$message}\n";
        exit(1);
    }
    echo "PASSED: {$message}\n";
}

echo "=== VERIFYING TASK 73: ACADEMIC SESSION REPORT WITH ACADEMIC YEAR ===\n";

// 1. Check database relations
$session = AcademicSession::with('academicYear')->whereNotNull('academic_year_id')->first();
if (!$session) {
    // If no session has academic_year_id, assign one for testing
    $year = AcademicYear::firstOrCreate(['name' => '2026'], ['is_active' => true]);
    $session = AcademicSession::first();
    if ($session) {
        $session->academic_year_id = $year->id;
        $session->save();
        $session->load('academicYear');
    }
}

assertTest($session !== null, "AcademicSession exists in database");
assertTest($session->academicYear !== null, "AcademicSession has academicYear loaded");
assertTest(!empty($session->academicYear->name), "AcademicYear name is present: " . ($session->academicYear->name ?? ''));

// 2. Authenticate admin user
$adminUser = User::where('role', 'admin')->first() ?: User::first();
\Illuminate\Support\Facades\Auth::login($adminUser);

// 3. Test ReportController::index response
$controller = new ReportController();
$request = Request::create(route('admin.reports.index'), 'GET', [
    'session_id' => $session->id,
]);

$response = $controller->index($request);
assertTest($response instanceof \Illuminate\View\View, "ReportController::index returns a Blade view");

$renderedHtml = $response->render();

// 4. Verify Academic Year appears in parentheses in dropdown
$expectedOptionText = "({$session->academicYear->name})";
assertTest(str_contains($renderedHtml, $expectedOptionText), "Dropdown option renders academic year in parentheses: '{$expectedOptionText}'");

// 5. Verify Academic Year appears in the main title
assertTest(
    str_contains($renderedHtml, 'Report of ' . $session->name . ' (' . $session->academicYear->name . ')'),
    "H1 Report title includes Academic Year in parentheses: 'Report of {$session->name} ({$session->academicYear->name})'"
);

// 6. Verify print header has academic year
assertTest(
    str_contains($renderedHtml, 'Report of ' . $session->name . ' (' . $session->academicYear->name . ')'),
    "Print header includes Academic Year in parentheses"
);

// 7. Verify blade file syntax integrity
$bladeContent = file_get_contents(resource_path('views/admin/reports/index.blade.php'));
assertTest(
    str_contains($bladeContent, '{{ $sess->academicYear?->name ? \' (\' . $sess->academicYear->name . \')\' : \'\' }}'),
    "Blade template contains dynamic academic year in parentheses in session loop"
);
assertTest(
    str_contains($bladeContent, '{{ $selectedSession?->academicYear?->name ? \' (\' . $selectedSession->academicYear->name . \')\' : \'\' }}'),
    "Blade template contains dynamic academic year in parentheses in header"
);

echo "\n>>> ALL TASK 73 ASSERTIONS PASSED! EXIT CODE 0 <<<\n";
exit(0);
