<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tx = App\Models\GatewayTransaction::where('tran_id', 'ADM-20261014-CB2A1714')->first();
if ($tx) {
    echo "ID: " . $tx->id . "\n";
    echo "Tran ID: " . $tx->tran_id . "\n";
    echo "Status: " . $tx->status . "\n";
    echo "Amount: " . $tx->amount . "\n";
    echo "Response Payload: " . json_encode($tx->response_payload, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "Transaction not found.\n";
}
