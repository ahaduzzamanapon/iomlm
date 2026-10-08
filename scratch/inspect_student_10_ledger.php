<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;
use App\Models\User;
use App\Http\Controllers\Admin\AccountsController;
use Illuminate\Http\Request;

$admin = User::where('role', 'super_admin')->first() ?: User::where('role', 'admin')->first();
\Illuminate\Support\Facades\Auth::login($admin);

$student = Student::find(10);
if (!$student) {
    echo "Student 10 not found!\n";
    exit(1);
}

$controller = new AccountsController();
$request = Request::create("/admin/students/10/accounts", 'GET');
$response = $controller->studentLedger($request, $student);

if ($response instanceof \Illuminate\View\View) {
    $html = $response->render();
    
    // Check if "আনপেইড করুন" exists in the rendered HTML
    $hasRevertBtn = str_contains($html, 'আনপেইড করুন');
    echo "Has 'আনপেইড করুন': " . ($hasRevertBtn ? "YES" : "NO") . "\n";
    
    // Check if onAdminPartEditPositionChange exists
    $hasPosChange = str_contains($html, 'onAdminPartEditPositionChange');
    echo "Has 'onAdminPartEditPositionChange': " . ($hasPosChange ? "YES" : "NO") . "\n";

    // Extract table rows from HTML
    preg_match_all('/<tr style="border-bottom:1px solid #f1f5f9"[^>]*>(.*?)<\/tr>/s', $html, $matches);
    echo "Total rendered rows: " . count($matches[0]) . "\n";
    foreach ($matches[0] as $idx => $row) {
        preg_match('/<td[^>]*>(.*?)<\/td>/s', $row, $tds);
        echo "Row " . ($idx+1) . " snippet:\n";
        // print particular name and action
        preg_match('/<span>(.*?)<\/span>/', $row, $nameMatch);
        $name = $nameMatch[1] ?? 'unknown';
        $hasRevert = str_contains($row, 'confirmRevertPayment');
        $hasPaid = str_contains($row, 'পেইড');
        echo "  Name: $name | Has Paid: " . ($hasPaid ? 'YES' : 'NO') . " | Has Revert Btn: " . ($hasRevert ? 'YES' : 'NO') . "\n";
    }
}
