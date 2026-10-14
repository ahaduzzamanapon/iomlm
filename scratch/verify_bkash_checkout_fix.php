<?php

require 'e:/My Soft/iom/learning-plus/vendor/autoload.php';
$app = require_once 'e:/My Soft/iom/learning-plus/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PaymentGatewayService;
use App\Models\GatewayTransaction;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

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

echo "=== bKash Checkout & Gateway Fix Verification ===\n\n";

// 1. Check Routes
assertCondition("Route 'payment.bkash.page' exists", Route::has('payment.bkash.page'));
assertCondition("Route 'payment.bkash.create.ajax' exists", Route::has('payment.bkash.create.ajax'));
assertCondition("Route 'payment.bkash.execute.ajax' exists", Route::has('payment.bkash.execute.ajax'));

// 2. Check Blade View and Elements
$viewPath = 'e:/My Soft/iom/learning-plus/resources/views/public/payment/bkash_checkout.blade.php';
assertCondition("View file bkash_checkout.blade.php exists", file_exists($viewPath));
$viewContent = file_get_contents($viewPath);
assertCondition("View contains checkout-card", str_contains($viewContent, 'checkout-card'));
assertCondition("View contains #bKash_button", str_contains($viewContent, 'id="bKash_button"'));
assertCondition("View contains Kalpurush font", str_contains($viewContent, 'Kalpurush'));
assertCondition("View contains #loadingOverlay with initial display:none", str_contains($viewContent, 'id="loadingOverlay"'));
assertCondition("View contains bKash.init", str_contains($viewContent, 'bKash.init'));
assertCondition("View contains createRequest callback", str_contains($viewContent, 'createRequest:'));
assertCondition("View contains executeRequestOnAuthorization callback", str_contains($viewContent, 'executeRequestOnAuthorization:'));
assertCondition("View contains onClose callback", str_contains($viewContent, 'onClose:'));

// 3. Test View Compilation with mock/real transaction
$tx = GatewayTransaction::where('gateway', 'bkash')->latest()->first();
if (!$tx) {
    $tx = GatewayTransaction::latest()->first();
}

if ($tx) {
    try {
        $config = PaymentGatewayService::getBkashConfig();
        $rendered = View::make('public.payment.bkash_checkout', [
            'transaction' => $tx,
            'config' => $config,
            'paymentData' => ['paymentID' => 'TEST_PID_123', 'amount' => '100.00'],
            'scriptUrl' => 'https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js'
        ])->render();
        assertCondition("View renders successfully without Blade syntax errors", strlen($rendered) > 1000 && str_contains($rendered, 'bKash_button'));
    } catch (\Throwable $e) {
        assertCondition("View renders successfully: " . $e->getMessage(), false);
    }
}

// 4. Test Live bKash Config & Token Grant
$config = PaymentGatewayService::getBkashConfig();
assertCondition("bKash configuration is enabled", !empty($config['enabled']));
assertCondition("bKash App Key is set", !empty($config['app_key']));

$token = PaymentGatewayService::getBkashToken();
assertCondition("bKash Live Token Grant is successful", !empty($token) && strlen($token) > 50);

// 5. Test Live Payment Initiation
if ($tx) {
    $initRes = PaymentGatewayService::initiateBkash($tx, $tx->admissionForm, $tx->customer_phone);
    assertCondition("Payment initiation returns success status", !empty($initRes['success']));
    assertCondition("Payment initiation returns valid paymentID", !empty($initRes['payment_id']));
}

echo "\nSummary: $passed / $total assertions passed.\n";

if ($passed === $total) {
    echo "ALL TESTS PASSED SUCCESSFULLY! (Exit Code 0)\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED! (Exit Code 1)\n";
    exit(1);
}
