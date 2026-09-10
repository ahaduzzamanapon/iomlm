<?php

namespace App\Console\Commands;

use App\Models\GatewayTransaction;
use App\Services\PaymentGatewayService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcilePendingPayments extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'payment:reconcile-pending {--minutes=3 : Find pending transactions older than this many minutes}';

    /**
     * The console command description.
     */
    protected $description = 'Reconcile incomplete or dropped payment transactions directly with gateway APIs (3-minute fail-safe)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = (int) $this->option('minutes') ?: 3;
        $this->info("Scanning for pending transactions older than {$minutes} minutes...");

        $pending = GatewayTransaction::needsReconciliation($minutes)->get();

        if ($pending->isEmpty()) {
            $this->info("No pending transactions requiring reconciliation.");
            return 0;
        }

        $reconciledCount = 0;
        $failedCount = 0;

        foreach ($pending as $trx) {
            $this->line("Checking Tran ID: {$trx->tran_id} (Gateway: {$trx->gateway}, Amount: {$trx->amount})...");
            $trx->increment('check_attempts');
            $trx->update(['last_checked_at' => now()]);

            $gateway = strtolower($trx->gateway);

            // ── 1. SSLCOMMERZ RECONCILIATION ──────────────────────────────
            if ($gateway === 'sslcommerz') {
                $queryRes = PaymentGatewayService::querySslcommerzByTranId($trx->tran_id);
                $status = $queryRes['status'] ?? ($queryRes['element'][0]['status'] ?? null);

                if (in_array($status, ['VALID', 'VALIDATED'])) {
                    $item = isset($queryRes['element'][0]) ? $queryRes['element'][0] : $queryRes;
                    PaymentGatewayService::settleSuccessfulPayment($trx, $item);
                    $this->info(" -> SUCCESS: Tran {$trx->tran_id} reconciled and marked as PAID!");
                    Log::info("Payment Reconciler: SSLCommerz Tran {$trx->tran_id} successfully reconciled as PAID.");
                    $reconciledCount++;
                    continue;
                } elseif (in_array($status, ['FAILED', 'CANCELLED'])) {
                    $trx->update(['status' => 'FAILED', 'error_message' => 'Gateway confirmed status: ' . $status]);
                    $this->warn(" -> FAILED: Gateway reported status: {$status}");
                    $failedCount++;
                    continue;
                }
            }

            // ── 2. bKash RECONCILIATION ────────────────────────────
            if ($gateway === 'bkash' && !empty($trx->payment_id)) {
                $queryRes = PaymentGatewayService::queryBkash($trx->payment_id);
                $trxStatus = $queryRes['transactionStatus'] ?? '';

                if ($trxStatus === 'Completed') {
                    PaymentGatewayService::settleSuccessfulPayment($trx, $queryRes);
                    $this->info(" -> SUCCESS: bKash payment {$trx->payment_id} reconciled and marked as PAID!");
                    Log::info("Payment Reconciler: bKash Tran {$trx->tran_id} successfully reconciled as PAID.");
                    $reconciledCount++;
                    continue;
                } elseif (in_array($trxStatus, ['Failed', 'Cancelled'])) {
                    $trx->update(['status' => 'FAILED', 'error_message' => 'bKash confirmed status: ' . $trxStatus]);
                    $this->warn(" -> FAILED: bKash reported status: {$trxStatus}");
                    $failedCount++;
                    continue;
                }
            }

            // ── 3. EXPIRE STALE TRANSACTIONS (> 2 Hours) ──────────────────
            if ($trx->created_at <= now()->subHours(2)) {
                $trx->update([
                    'status' => 'EXPIRED',
                    'error_message' => 'Transaction timed out after 2 hours without gateway confirmation',
                ]);
                $this->comment(" -> EXPIRED: Transaction {$trx->tran_id} expired.");
            }
        }

        $this->info("Reconciliation complete. Reconciled: {$reconciledCount}, Failed: {$failedCount}");
        return 0;
    }
}
