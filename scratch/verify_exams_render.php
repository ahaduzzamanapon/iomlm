<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\ExamController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

echo "=== VERIFYING EXAMS INDEX CONTROLLER & VIEW RENDERING ===\n";

View::share('errors', new \Illuminate\Support\ViewErrorBag());

$adminUser = \App\Models\User::where('role', 'ADMIN')->first() ?? \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($adminUser);

try {
    $controller = app(ExamController::class);
    $response = $controller->index(new Request());

    if ($response instanceof \Illuminate\View\View) {
        $html = $response->render();
        echo "✓ ExamController::index rendered successfully without errors!\n";
        echo "✓ HTML output size: " . strlen($html) . " bytes\n";
    } else {
        echo "✓ Controller returned: " . get_class($response) . "\n";
    }

    echo "SUCCESS: Exit Code 0\n";
    exit(0);
} catch (\Throwable $e) {
    echo "❌ Render Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
