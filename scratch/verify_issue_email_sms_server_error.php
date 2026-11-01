<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\EmailTemplate;
use App\Http\Controllers\Admin\EmailTemplateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

echo "=== VERIFYING EMAIL TEMPLATES & SMS SERVER ERROR FIX ===\n";

// Ensure errors view bag exists for CLI view rendering
if (!View::shared('errors')) {
    View::share('errors', new ViewErrorBag());
}

// 1. Test Self-Healing Seed
echo "1. Testing EmailTemplate::seedDefaultTemplates() self-healing...\n";
EmailTemplate::seedDefaultTemplates();
echo "  [PASS] seedDefaultTemplates executed cleanly.\n";

// 2. Test Admin Controller index rendering with Authenticated Admin
echo "2. Testing EmailTemplateController index rendering...\n";
$admin = User::where('role', 'super_admin')->first();
auth()->login($admin);

$controller = new EmailTemplateController();
$request = Request::create(route('admin.email-templates.index'), 'GET');
$response = $controller->index($request);
$rendered = $response->render();

if (strpos($rendered, 'টেমপ্লেট ব্যবস্থাপনা (ইমেইল ও এসএমএস)') === false) {
    echo "  [FAIL] Expected Bengali heading not found in rendered output!\n";
    exit(1);
}
echo "  [PASS] Index view rendered successfully (HTML Length: " . strlen($rendered) . ").\n";

// 3. Test EmailTemplateController create rendering
echo "3. Testing EmailTemplateController create rendering...\n";
$createReq = Request::create(route('admin.email-templates.create'), 'GET', ['type' => 'SMS']);
$createView = $controller->create($createReq);
$createHtml = $createView->render();

if (strpos($createHtml, 'নতুন এসএমএস টেমপ্লেট তৈরি') === false) {
    echo "  [FAIL] Expected create template heading not found!\n";
    exit(1);
}
echo "  [PASS] Create view rendered successfully (HTML Length: " . strlen($createHtml) . ").\n";

// 4. Test Middleware variadic parameter handling
echo "4. Testing AdminModuleAccessMiddleware with multiple module parameters...\n";
$middleware = new \App\Http\Middleware\AdminModuleAccessMiddleware();
$testReq = Request::create('/admin/email-templates', 'GET');

$called = false;
$next = function($req) use (&$called) {
    $called = true;
    return response('OK', 200);
};

$midRes = $middleware->handle($testReq, $next, 'admissions', 'communication');
if (!$called || $midRes->getStatusCode() !== 200) {
    echo "  [FAIL] Middleware did not grant access for multiple modules!\n";
    exit(1);
}
echo "  [PASS] AdminModuleAccessMiddleware handled variadic parameters with success.\n";

echo "\n>>> ALL EMAIL TEMPLATES & SMS TESTS PASSED (Exit Code 0) <<<\n";
exit(0);
