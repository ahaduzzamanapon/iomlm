<?php

require 'e:/My Soft/iom/learning-plus/vendor/autoload.php';
$app = require_once 'e:/My Soft/iom/learning-plus/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use App\Models\GatewayTransaction;
use App\Models\AdmissionForm;
use App\Http\Controllers\Public\PaymentCallbackController;

$passed = 0;
$total = 0;

function assertCondition($desc, $cond) {
    global $passed, $total;
    $total++;
    if ($cond) {
        $passed++;
        echo " [PASS] $desc\n";
    } else {
        echo " [FAIL] $desc\n";
    }
}

echo "=== Verifying bKash Processing View & Cancel Routing ===\n\n";

// 1. Verify Routes
assertCondition("Route 'payment.bkash.page' exists", Route::has('payment.bkash.page'));
assertCondition("Route 'payment.bkash.cancel' exists", Route::has('payment.bkash.cancel'));
assertCondition("Route 'payment.bkash.create.ajax' exists", Route::has('payment.bkash.create.ajax'));
assertCondition("Route 'payment.bkash.execute.ajax' exists", Route::has('payment.bkash.execute.ajax'));

// 2. Inspect Blade Content
$bladePath = 'e:/My Soft/iom/learning-plus/resources/views/public/payment/bkash_checkout.blade.php';
assertCondition("File bkash_checkout.blade.php exists", file_exists($bladePath));
$content = file_get_contents($bladePath);

assertCondition("Contains processing title 'Processing...'", str_contains($content, 'Processing...'));
assertCondition("Does NOT contain missing font url fonts/kalpurush.ttf (fixes 404)", !str_contains($content, 'fonts/kalpurush.ttf'));
assertCondition("Contains hidden #bKash_button for SDK binding", str_contains($content, 'id="bKash_button"'));
assertCondition("Contains cancel link", str_contains($content, 'class="cancel-link"'));
assertCondition("Contains overflow hidden to prevent scrollbars", str_contains($content, 'overflow: hidden !important;'));
assertCondition("Handles onClose by redirecting to cancelUrl", str_contains($content, 'window.location.href = cancelUrl;'));
assertCondition("Auto-triggers triggerBkashOpen()", str_contains($content, 'triggerBkashOpen();'));

// 3. Test Controller Rendering
$tx = GatewayTransaction::where('gateway', 'bkash')->latest('id')->first();
if (!$tx) {
    $af = AdmissionForm::first();
    $tx = GatewayTransaction::create([
        'gateway' => 'bkash',
        'gateway_mode' => 'sandbox',
        'tran_id' => 'TEST-' . time(),
        'amount' => 500,
        'currency' => 'BDT',
        'status' => 'PENDING',
        'admission_form_id' => $af?->id,
        'customer_name' => 'Test Applicant',
        'customer_phone' => '01700000000',
    ]);
}

$controller = new PaymentCallbackController();
$viewRes = $controller->bkashCheckoutPage($tx->tran_id);
assertCondition("Controller bkashCheckoutPage returns View instance", $viewRes instanceof \Illuminate\View\View);

$data = $viewRes->getData();
assertCondition("View data has 'cancelUrl'", !empty($data['cancelUrl']));
assertCondition("View data has 'scriptUrl'", !empty($data['scriptUrl']));

$rendered = $viewRes->render();
assertCondition("Rendered HTML contains processing title", str_contains($rendered, 'Processing...'));
assertCondition("Rendered HTML does not have broken font 404 url", !str_contains($rendered, 'fonts/kalpurush.ttf'));

// 4. Test bkashCancelAjax endpoint
$req = Request::create(route('payment.bkash.cancel', $tx->tran_id), 'POST');
$req->headers->set('Accept', 'application/json');
$cancelRes = $controller->bkashCancelAjax($req, $tx->tran_id);

assertCondition("bkashCancelAjax returns JSON response", $cancelRes instanceof \Illuminate\Http\JsonResponse);
$cancelData = $cancelRes->getData(true);
assertCondition("bkashCancelAjax returns success=true", ($cancelData['success'] ?? false) === true);
assertCondition("bkashCancelAjax returns redirect_url", !empty($cancelData['redirect_url']));

$freshTx = $tx->fresh();
assertCondition("Transaction status is set to CANCELLED", $freshTx->status === 'CANCELLED');

echo "\nSummary: $passed / $total assertions passed.\n";

if ($passed === $total) {
    echo "ALL TESTS PASSED SUCCESSFULLY! (Exit Code 0)\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED! (Exit Code 1)\n";
    exit(1);
}
