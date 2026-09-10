<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AdmissionForm;
use App\Models\GatewayTransaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════
    // SSLCOMMERZ CALLBACKS
    // ══════════════════════════════════════════════════════════════════════

    /**
     * SSLCommerz Success Callback (Dual-Verification)
     */
    public function sslcommerzSuccess(Request $request)
    {
        $tranId = $request->input('tran_id');
        $valId = $request->input('val_id');

        Log::info("SSLCommerz Success Callback received for tran_id: {$tranId}, val_id: {$valId}");

        if (empty($tranId) || empty($valId)) {
            return redirect()->route('apply.show')->with('error', 'অবৈধ পেমেন্ট প্রতিক্রিয়া পাওয়া গেছে।');
        }

        $transaction = GatewayTransaction::where('tran_id', $tranId)->first();
        if (!$transaction) {
            return redirect()->route('apply.show')->with('error', 'পেমেন্ট ট্রানজেকশন খুঁজে পাওয়া যায়নি।');
        }

        // Check if already completed
        if ($transaction->status === 'SUCCESS') {
            $appNo = $transaction->admissionForm?->application_no;
            return redirect()->route('apply.success', $appNo ?: 0)->with('success', 'পেমেন্ট সফলভাবে যাচাই ও নিশ্চিত হয়েছে!');
        }

        // ── SERVER-TO-SERVER DUAL VERIFICATION ────────────────────────────
        $validation = PaymentGatewayService::validateSslcommerz($valId);
        $status = $validation['status'] ?? '';
        $valAmount = (float) ($validation['amount'] ?? 0);
        $valCur = $validation['currency'] ?? 'BDT';
        $valTranId = $validation['tran_id'] ?? '';

        $isValid = in_array($status, ['VALID', 'VALIDATED'])
            && abs($valAmount - $transaction->amount) < 0.01
            && $valTranId === $transaction->tran_id;

        if ($isValid) {
            PaymentGatewayService::settleSuccessfulPayment($transaction, $validation);

            $appNo = $transaction->admissionForm?->application_no;
            return redirect()->route('apply.success', $appNo ?: 0)
                ->with('success', 'আলহামদুলিল্লাহ! আপনার ভর্তি ফি ৳' . number_format($transaction->amount, 2) . ' সফলভাবে পরিশোধিত হয়েছে।');
        }

        // Verification failed
        $transaction->update([
            'status' => 'FAILED',
            'error_message' => 'সার্ভার ভেরিফিকেশন ব্যর্থ হয়েছে: স্ট্যাটাস ' . $status,
            'raw_response' => array_merge((array) ($transaction->raw_response ?? []), $validation),
        ]);

        return redirect()->route('payment.status', $tranId)->with('error', 'পেমেন্ট গেটওয়ে যাচাইকরণ ব্যর্থ হয়েছে। অনুগ্রহ করে পুনরায় চেষ্টা করুন।');
    }

    /**
     * SSLCommerz Fail Callback
     */
    public function sslcommerzFail(Request $request)
    {
        $tranId = $request->input('tran_id');
        $error = $request->input('error') ?? $request->input('failedreason') ?? 'পেমেন্ট সম্পন্ন হতে ব্যর্থ হয়েছে।';

        Log::warning("SSLCommerz Failed for tran_id: {$tranId}, reason: {$error}");

        if ($tranId) {
            $transaction = GatewayTransaction::where('tran_id', $tranId)->first();
            if ($transaction && $transaction->status !== 'SUCCESS') {
                $transaction->update([
                    'status' => 'FAILED',
                    'error_message' => $error,
                    'raw_response' => array_merge((array) ($transaction->raw_response ?? []), $request->all()),
                ]);
            }
            return redirect()->route('payment.status', $tranId)->with('error', $error);
        }

        return redirect()->route('apply.show')->with('error', $error);
    }

    /**
     * SSLCommerz Cancel Callback
     */
    public function sslcommerzCancel(Request $request)
    {
        $tranId = $request->input('tran_id');

        Log::info("SSLCommerz Cancelled by user for tran_id: {$tranId}");

        if ($tranId) {
            $transaction = GatewayTransaction::where('tran_id', $tranId)->first();
            if ($transaction && $transaction->status !== 'SUCCESS') {
                $transaction->update([
                    'status' => 'CANCELLED',
                    'error_message' => 'গ্রাহক দ্বারা পেমেন্ট বাতিল করা হয়েছে।',
                    'raw_response' => array_merge((array) ($transaction->raw_response ?? []), $request->all()),
                ]);
            }
            return redirect()->route('payment.status', $tranId)->with('error', 'পেমেন্ট প্রক্রিয়াটি বাতিল করা হয়েছে।');
        }

        return redirect()->route('apply.show')->with('error', 'পেমেন্ট বাতিল করা হয়েছে।');
    }

    /**
     * SSLCommerz IPN (Instant Payment Notification) Webhook
     */
    public function sslcommerzIpn(Request $request)
    {
        $tranId = $request->input('tran_id');
        $valId = $request->input('val_id');

        Log::info("SSLCommerz IPN Received for tran_id: {$tranId}, val_id: {$valId}");

        if (!$tranId || !$valId) {
            return response()->json(['status' => 'INVALID_PAYLOAD'], 400);
        }

        $transaction = GatewayTransaction::where('tran_id', $tranId)->first();
        if (!$transaction) {
            return response()->json(['status' => 'TRANSACTION_NOT_FOUND'], 404);
        }

        if ($transaction->status === 'SUCCESS') {
            return response()->json(['status' => 'ALREADY_SETTLED'], 200);
        }

        $validation = PaymentGatewayService::validateSslcommerz($valId);
        $status = $validation['status'] ?? '';
        $valAmount = (float) ($validation['amount'] ?? 0);

        if (in_array($status, ['VALID', 'VALIDATED']) && abs($valAmount - $transaction->amount) < 0.01) {
            PaymentGatewayService::settleSuccessfulPayment($transaction, $validation);
            return response()->json(['status' => 'SETTLED_SUCCESSFULLY'], 200);
        }

        return response()->json(['status' => 'VALIDATION_FAILED'], 400);
    }

    // ══════════════════════════════════════════════════════════════════════
    // bKash CALLBACK
    // ══════════════════════════════════════════════════════════════════════

    /**
     * bKash Callback (Dual-Verification via Execute)
     */
    public function bkashCallback(Request $request)
    {
        $paymentId = $request->query('paymentID');
        $status = $request->query('status');

        Log::info("bKash Callback Received: paymentID={$paymentId}, status={$status}");

        if (empty($paymentId)) {
            return redirect()->route('apply.show')->with('error', 'বিকাশ থেকে সঠিক পেমেন্ট রেফারেন্স পাওয়া যায়নি।');
        }

        $transaction = GatewayTransaction::where('payment_id', $paymentId)
            ->orWhere('gateway_trx_id', $paymentId)
            ->first();

        if (!$transaction) {
            return redirect()->route('apply.show')->with('error', 'বিকাশ লেনদেন রেকর্ড খুঁজে পাওয়া যায়নি।');
        }

        if ($transaction->status === 'SUCCESS') {
            $appNo = $transaction->admissionForm?->application_no;
            return redirect()->route('apply.success', $appNo ?: 0)->with('success', 'পেমেন্ট সফলভাবে নিশ্চিত হয়েছে!');
        }

        if ($status === 'cancel') {
            $transaction->update([
                'status' => 'CANCELLED',
                'error_message' => 'বিকাশ পেমেন্ট বাতিল করা হয়েছে।',
            ]);
            return redirect()->route('payment.status', $transaction->tran_id)->with('error', 'বিকাশ পেমেন্ট বাতিল করা হয়েছে।');
        }

        if ($status === 'failure') {
            $transaction->update([
                'status' => 'FAILED',
                'error_message' => 'বিকাশ পেমেন্ট ব্যর্থ হয়েছে।',
            ]);
            return redirect()->route('payment.status', $transaction->tran_id)->with('error', 'বিকাশ পেমেন্ট সম্পন্ন করা যায়নি।');
        }

        if ($status === 'success') {
            // ── SERVER-TO-SERVER EXECUTE ──────────────────────────────────
            $executeRes = PaymentGatewayService::executeBkash($paymentId);
            $statusCode = $executeRes['statusCode'] ?? '';
            $trxStatus = $executeRes['transactionStatus'] ?? '';

            if ($statusCode === '0000' && $trxStatus === 'Completed') {
                PaymentGatewayService::settleSuccessfulPayment($transaction, $executeRes);

                $appNo = $transaction->admissionForm?->application_no;
                return redirect()->route('apply.success', $appNo ?: 0)
                    ->with('success', 'আলহামদুলিল্লাহ! আপনার বিকাশ পেমেন্ট (TrxID: ' . ($executeRes['trxID'] ?? '') . ') সফল হয়েছে।');
            }

            // In case already executed, query status server-to-server
            $queryRes = PaymentGatewayService::queryBkash($paymentId);
            if (($queryRes['transactionStatus'] ?? '') === 'Completed') {
                PaymentGatewayService::settleSuccessfulPayment($transaction, $queryRes);

                $appNo = $transaction->admissionForm?->application_no;
                return redirect()->route('apply.success', $appNo ?: 0)
                    ->with('success', 'আলহামদুলিল্লাহ! বিকাশ পেমেন্ট সফলভাবে যাচাই হয়েছে।');
            }

            $errMsg = $executeRes['statusMessage'] ?? 'বিকাশ লেনদেন নিশ্চিত করা যায়নি।';
            $transaction->update([
                'status' => 'FAILED',
                'error_message' => $errMsg,
                'raw_response' => array_merge((array) ($transaction->raw_response ?? []), $executeRes),
            ]);

            return redirect()->route('payment.status', $transaction->tran_id)->with('error', $errMsg);
        }

        return redirect()->route('payment.status', $transaction->tran_id)->with('error', 'অপ্রত্যাশিত স্ট্যাটাস: ' . $status);
    }

    // ══════════════════════════════════════════════════════════════════════
    // PAYMENT STATUS & FAIL-SAFE LIVE QUERY
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Show Payment Status & Perform Live Auto-Reconciliation on Load
     */
    public function status(Request $request, string $tranId)
    {
        $transaction = GatewayTransaction::with(['admissionForm', 'student', 'invoice'])
            ->where('tran_id', $tranId)
            ->firstOrFail();

        // ── FAIL-SAFE: If transaction is still PENDING/INITIATED, query gateway live ──
        if ($transaction->isPending()) {
            $transaction->increment('check_attempts');
            $transaction->update(['last_checked_at' => now()]);

            if (strtolower($transaction->gateway) === 'sslcommerz') {
                $queryRes = PaymentGatewayService::querySslcommerzByTranId($transaction->tran_id);
                $status = $queryRes['status'] ?? ($queryRes['element'][0]['status'] ?? '');
                if (in_array($status, ['VALID', 'VALIDATED'])) {
                    $item = isset($queryRes['element'][0]) ? $queryRes['element'][0] : $queryRes;
                    PaymentGatewayService::settleSuccessfulPayment($transaction, $item);
                    $appNo = $transaction->admissionForm?->application_no;
                    return redirect()->route('apply.success', $appNo ?: 0)
                        ->with('success', 'পেমেন্ট গেটওয়েতে সফলভাবে নিশ্চিত হয়েছে!');
                }
            } elseif (strtolower($transaction->gateway) === 'bkash' && $transaction->payment_id) {
                $queryRes = PaymentGatewayService::queryBkash($transaction->payment_id);
                if (($queryRes['transactionStatus'] ?? '') === 'Completed') {
                    PaymentGatewayService::settleSuccessfulPayment($transaction, $queryRes);
                    $appNo = $transaction->admissionForm?->application_no;
                    return redirect()->route('apply.success', $appNo ?: 0)
                        ->with('success', 'বিকাশ পেমেন্ট সফলভাবে নিশ্চিত হয়েছে!');
                }
            }
        }

        return view('apply.payment_status', compact('transaction'));
    }

    /**
     * AJAX Live Poll Status Check Endpoint
     */
    public function checkStatusAjax(string $tranId)
    {
        $transaction = GatewayTransaction::where('tran_id', $tranId)->first();
        if (!$transaction) {
            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        }

        if ($transaction->status === 'SUCCESS') {
            return response()->json([
                'success' => true,
                'status' => 'SUCCESS',
                'application_no' => $transaction->admissionForm?->application_no,
            ]);
        }

        // Live check if pending
        if ($transaction->isPending()) {
            if (strtolower($transaction->gateway) === 'sslcommerz') {
                $queryRes = PaymentGatewayService::querySslcommerzByTranId($transaction->tran_id);
                $status = $queryRes['status'] ?? ($queryRes['element'][0]['status'] ?? '');
                if (in_array($status, ['VALID', 'VALIDATED'])) {
                    $item = isset($queryRes['element'][0]) ? $queryRes['element'][0] : $queryRes;
                    PaymentGatewayService::settleSuccessfulPayment($transaction, $item);
                    return response()->json([
                        'success' => true,
                        'status' => 'SUCCESS',
                        'application_no' => $transaction->admissionForm?->application_no,
                    ]);
                }
            } elseif (strtolower($transaction->gateway) === 'bkash' && $transaction->payment_id) {
                $queryRes = PaymentGatewayService::queryBkash($transaction->payment_id);
                if (($queryRes['transactionStatus'] ?? '') === 'Completed') {
                    PaymentGatewayService::settleSuccessfulPayment($transaction, $queryRes);
                    return response()->json([
                        'success' => true,
                        'status' => 'SUCCESS',
                        'application_no' => $transaction->admissionForm?->application_no,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'status' => $transaction->status,
        ]);
    }
}
