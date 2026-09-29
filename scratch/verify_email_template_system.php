<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

echo "=== START VERIFICATION: EMAIL TEMPLATE BROADCAST SYSTEM ===\n\n";

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

// 1. Verify Model & Seeding
echo "1. Verifying EmailTemplate Model & Standard Templates...\n";
EmailTemplate::seedDefaultTemplates();
$templateCount = EmailTemplate::count();
assertTest($templateCount >= 8, "EmailTemplate seeded at least 8 default templates (Found: {$templateCount})");

$examTpl = EmailTemplate::where('category', 'EXAM')->first();
assertTest($examTpl !== null, "Found EXAM category template");
assertTest($examTpl->category_label === 'পরীক্ষা সংক্রান্ত', "Category label Bengali mapping correct: '{$examTpl->category_label}'");

$feesTpl = EmailTemplate::where('category', 'FEES')->first();
assertTest($feesTpl !== null && $feesTpl->category_label === 'ফি ও একাউন্টস', "Found FEES category template with label '{$feesTpl->category_label}'");

// 2. Verify Routes Registered
echo "\n2. Verifying Admin Routes Registration...\n";
assertTest(Route::has('admin.email-templates.store'), "Route 'admin.email-templates.store' is registered");
assertTest(Route::has('admin.email-templates.destroy'), "Route 'admin.email-templates.destroy' is registered");
assertTest(Route::has('admin.email-templates.list-json'), "Route 'admin.email-templates.list-json' is registered");
assertTest(Route::has('admin.notifications.create'), "Route 'admin.notifications.create' is registered");

// 3. Verify Admin Authentication & View Rendering
echo "\n3. Verifying View Rendering at admin/notifications/create...\n";
$adminUser = User::where('role', 'admin')->first();
if (!$adminUser) {
    $adminUser = User::first();
}
Auth::login($adminUser);

$controller = app()->make(\App\Http\Controllers\Admin\BroadcastNotificationController::class);
$view = $controller->create();
$html = $view->render();

assertTest(str_contains($html, 'quick_template_select'), "Rendered HTML contains quick_template_select dropdown");
assertTest(str_contains($html, 'openEmailTemplateModal()'), "Rendered HTML contains openEmailTemplateModal() button");
assertTest(str_contains($html, 'openSaveAsTemplateModal()'), "Rendered HTML contains openSaveAsTemplateModal() button");
assertTest(str_contains($html, 'insertVariableTag'), "Rendered HTML contains dynamic variable tags insertion buttons");
assertTest(str_contains($html, 'emailTemplateModal'), "Rendered HTML contains emailTemplateModal overlay");
assertTest(str_contains($html, 'saveAsTemplateModal'), "Rendered HTML contains saveAsTemplateModal overlay");
assertTest(str_contains($html, 'templateToastNotification'), "Rendered HTML contains live toast notification");

// 4. Test Storing New Custom Template via Controller
echo "\n4. Testing EmailTemplateController@store (AJAX)...\n";
$templateController = app()->make(\App\Http\Controllers\Admin\EmailTemplateController::class);
$request = \Illuminate\Http\Request::create(route('admin.email-templates.store'), 'POST', [
    'name'     => 'পরীক্ষামূলক বিশেষ নোটিশ',
    'category' => 'GENERAL',
    'subject'  => 'জরুরি নোটিশ: টেস্ট সাবজেক্ট',
    'content'  => "আসসালামু আলাইকুম {name},\nআপনার রোল {roll}। এটি একটি পরীক্ষামূলক টেমপ্লেট।",
]);
$request->headers->set('Accept', 'application/json');

$response = $templateController->store($request);
assertTest($response->getStatusCode() === 200, "Store custom template returned HTTP 200");
$responseData = json_decode($response->getContent(), true);
assertTest($responseData['success'] === true, "Store response JSON contains success=true");
$newTplId = $responseData['template']['id'];
assertTest($newTplId > 0, "New template saved with ID {$newTplId}");

// 5. Test Deleting Custom Template vs System Template Protection
echo "\n5. Testing EmailTemplateController@destroy Protection...\n";
// Attempt deleting system template - should fail
$sysTpl = EmailTemplate::where('is_system', true)->first();
$delSysReq = \Illuminate\Http\Request::create(route('admin.email-templates.destroy', $sysTpl->id), 'DELETE');
$delSysReq->headers->set('Accept', 'application/json');
$delSysRes = $templateController->destroy($sysTpl, $delSysReq);
assertTest($delSysRes->getStatusCode() === 422, "Deleting system template is blocked with HTTP 422");

// Delete the newly created custom template
$customTpl = EmailTemplate::find($newTplId);
$delCustReq = \Illuminate\Http\Request::create(route('admin.email-templates.destroy', $customTpl->id), 'DELETE');
$delCustReq->headers->set('Accept', 'application/json');
$delCustRes = $templateController->destroy($customTpl, $delCustReq);
assertTest($delCustRes->getStatusCode() === 200, "Deleting custom template succeeded with HTTP 200");
assertTest(EmailTemplate::find($newTplId) === null, "Custom template successfully deleted from database");

echo "\n=== ALL {$passCount} EMAIL TEMPLATE TESTS PASSED (Exit Code 0) ===\n";
