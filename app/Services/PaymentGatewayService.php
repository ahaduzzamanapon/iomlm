<?php

namespace App\Services;

use App\Models\AdmissionForm;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\GatewayTransaction;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    /**
     * Get SSLCommerz active configuration based on sandbox/live toggle.
     */
    public static function getSslcommerzConfig(): array
    {
        $enabled = Setting::where('key', 'sslcommerz_enabled')->value('value') === '1';
        $mode = Setting::where('key', 'sslcommerz_mode')->value('value') ?: 'sandbox';
        $isSandbox = ($mode === 'sandbox');

        $storeId = $isSandbox
            ? (Setting::where('key', 'sslcommerz_sandbox_store_id')->value('value') ?: '')
            : (Setting::where('key', 'sslcommerz_live_store_id')->value('value') ?: '');

        $storePasswd = $isSandbox
            ? (Setting::where('key', 'sslcommerz_sandbox_store_passwd')->value('value') ?: '')
            : (Setting::where('key', 'sslcommerz_live_store_passwd')->value('value') ?: '');

        $baseUrl = $isSandbox
            ? (Setting::where('key', 'sslcommerz_sandbox_url')->value('value') ?: 'https://sandbox.sslcommerz.com')
            : (Setting::where('key', 'sslcommerz_live_url')->value('value') ?: 'https://securepay.sslcommerz.com');

        $currency = Setting::where('key', 'sslcommerz_currency')->value('value') ?: 'BDT';

        return [
            'enabled'      => $enabled,
            'mode'         => $mode,
            'is_sandbox'   => $isSandbox,
            'store_id'     => trim($storeId),
            'store_passwd' => trim($storePasswd),
            'base_url'     => rtrim($baseUrl, '/'),
            'currency'     => $currency,
        ];
    }

    /**
     * Get Direct bKash active configuration based on sandbox/live toggle.
     */
    public static function getBkashConfig(): array
    {
        $enabled = Setting::where('key', 'bkash_enabled')->value('value') === '1';
        $mode = Setting::where('key', 'bkash_mode')->value('value') ?: 'sandbox';
        $isSandbox = ($mode === 'sandbox');

        $appKey = $isSandbox
            ? (Setting::where('key', 'bkash_sandbox_app_key')->value('value') ?: '')
            : (Setting::where('key', 'bkash_live_app_key')->value('value') ?: '');

        $appSecret = $isSandbox
            ? (Setting::where('key', 'bkash_sandbox_app_secret')->value('value') ?: '')
            : (Setting::where('key', 'bkash_live_app_secret')->value('value') ?: '');

        $username = $isSandbox
            ? (Setting::where('key', 'bkash_sandbox_username')->value('value') ?: '')
            : (Setting::where('key', 'bkash_live_username')->value('value') ?: '');

        $password = $isSandbox
            ? (Setting::where('key', 'bkash_sandbox_password')->value('value') ?: '')
            : (Setting::where('key', 'bkash_live_password')->value('value') ?: '');

        $baseUrl = $isSandbox
            ? (Setting::where('key', 'bkash_sandbox_base_url')->value('value') ?: 'https://tokenized.sandbox.bka.sh/v1.2.0-beta')
            : (Setting::where('key', 'bkash_live_base_url')->value('value') ?: 'https://tokenized.pay.bka.sh/v1.2.0-beta');

        $currency = Setting::where('key', 'bkash_currency')->value('value') ?: 'BDT';

        return [
            'enabled'    => $enabled,
            'mode'       => $mode,
            'is_sandbox' => $isSandbox,
            'app_key'    => trim($appKey),
            'app_secret' => trim($appSecret),
            'username'   => trim($username),
            'password'   => trim($password),
            'base_url'   => rtrim($baseUrl, '/'),
            'currency'   => $currency,
        ];
    }

    /**
     * Check if SSLCommerz is enabled.
     */
    public static function isSslcommerzActive(): bool
    {
        $config = self::getSslcommerzConfig();
        return $config['enabled'] && !empty($config['store_id']) && !empty($config['store_passwd']);
    }

    /**
     * Check if Direct bKash is enabled.
     */
    public static function isBkashActive(): bool
    {
        $config = self::getBkashConfig();
        return $config['enabled'] && !empty($config['app_key']) && !empty($config['app_secret']);
    }

    // ══════════════════════════════════════════════════════════════════════
    // SSLCOMMERZ API INTEGRATION
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Initiate payment session with SSLCommerz.
     */
    public static function initiateSslcommerz(
        GatewayTransaction $transaction,
        AdmissionForm $form,
        ?string $customerPhone = null,
        ?string $customerEmail = null,
        ?string $customerName = null
    ): array {
        $config = self::getSslcommerzConfig();
        if (!$config['enabled'] || empty($config['store_id']) || empty($config['store_passwd'])) {
            return ['success' => false, 'message' => 'SSLCommerz গেটওয়ে সঠিকভাবে কনফিগার করা নেই।'];
        }

        $postData = [
            'store_id'         => $config['store_id'],
            'store_passwd'     => $config['store_passwd'],
            'total_amount'     => number_format($transaction->amount, 2, '.', ''),
            'currency'         => $transaction->currency ?: 'BDT',
            'tran_id'          => $transaction->tran_id,
            'success_url'      => route('payment.callback.sslcommerz.success'),
            'fail_url'         => route('payment.callback.sslcommerz.fail'),
            'cancel_url'       => route('payment.callback.sslcommerz.cancel'),
            'ipn_url'          => route('payment.callback.sslcommerz.ipn'),
            'cus_name'         => $customerName ?: ($form->student->name ?? 'Student'),
            'cus_email'        => $customerEmail ?: ($form->student->email ?? 'applicant@iom.edu.bd'),
            'cus_add1'         => $form->present_house ?: 'Dhaka',
            'cus_city'         => 'Dhaka',
            'cus_country'      => 'Bangladesh',
            'cus_phone'        => $customerPhone ?: ($form->student->phone ?? '01700000000'),
            'shipping_method'  => 'NO',
            'product_name'     => 'Admission Fee - ' . ($form->interestedCourse->name ?? 'Course'),
            'product_category' => 'Education',
            'product_profile'  => 'general',
            'value_a'          => (string) $form->id,
            'value_b'          => (string) $transaction->id,
        ];

        try {
            $endpoint = $config['base_url'] . '/gwprocess/v4/api.php';
            $response = Http::asForm()->timeout(15)->post($endpoint, $postData);

            if (!$response->successful()) {
                Log::error('SSLCommerz initiation failed HTTP status: ' . $response->status(), ['body' => $response->body()]);
                return ['success' => false, 'message' => 'SSLCommerz সার্ভারের সাথে সংযোগ স্থাপন করা যায়নি।'];
            }

            $data = $response->json();
            if (isset($data['status']) && $data['status'] === 'SUCCESS' && !empty($data['GatewayPageURL'])) {
                $transaction->update([
                    'status'       => 'PENDING',
                    'raw_response' => $data,
                ]);

                return [
                    'success'      => true,
                    'redirect_url' => $data['GatewayPageURL'],
                ];
            }

            $error = $data['failedreason'] ?? 'SSLCommerz পেমেন্ট সেশন তৈরি ব্যর্থ হয়েছে।';
            $transaction->update([
                'status'        => 'FAILED',
                'error_message' => $error,
                'raw_response'  => $data,
            ]);

            return ['success' => false, 'message' => $error];
        } catch (\Exception $e) {
            Log::error('SSLCommerz Initiation Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'পেমেন্ট গেটওয়ে এর সাথে যোগাযোগে ত্রুটি ঘটেছে: ' . $e->getMessage()];
        }
    }

    /**
     * Server-to-Server validation for SSLCommerz via validationserverAPI.
     */
    public static function validateSslcommerz(string $valId): array
    {
        $config = self::getSslcommerzConfig();
        $endpoint = $config['base_url'] . '/validator/api/validationserverAPI.php';

        try {
            $response = Http::timeout(15)->get($endpoint, [
                'val_id'       => $valId,
                'store_id'     => $config['store_id'],
                'store_passwd' => $config['store_passwd'],
                'format'       => 'json',
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('SSLCommerz Server Validation Exception: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Query SSLCommerz transaction by merchant tran_id (for 3-minute fail-safe reconciliation).
     */
    public static function querySslcommerzByTranId(string $tranId): array
    {
        $config = self::getSslcommerzConfig();
        $endpoint = $config['base_url'] . '/validator/api/merchantTransIDvalidationAPI.php';

        try {
            $response = Http::timeout(15)->get($endpoint, [
                'tran_id'      => $tranId,
                'store_id'     => $config['store_id'],
                'store_passwd' => $config['store_passwd'],
                'format'       => 'json',
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('SSLCommerz Transaction Query Exception: ' . $e->getMessage());
        }

        return [];
    }

    // ══════════════════════════════════════════════════════════════════════
    // DIRECT BKASH API INTEGRATION
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Grant or retrieve cached bKash id_token.
     */
    public static function getBkashToken(): ?string
    {
        $config = self::getBkashConfig();
        if (empty($config['app_key']) || empty($config['app_secret'])) {
            return null;
        }

        $cacheKey = 'bkash_token_' . md5($config['app_key'] . $config['mode']);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $endpoint = $config['base_url'] . '/tokenized/checkout/token/grant';
            $response = Http::withHeaders([
                'username' => $config['username'],
                'password' => $config['password'],
            ])->timeout(15)->post($endpoint, [
                'app_key'    => $config['app_key'],
                'app_secret' => $config['app_secret'],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['id_token'])) {
                    $token = $data['id_token'];
                    $expiresIn = max(60, ((int) ($data['expires_in'] ?? 3600)) - 300);
                    Cache::put($cacheKey, $token, $expiresIn);
                    return $token;
                }
            }
            Log::error('bKash Token Grant Failed: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('bKash Token Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Initiate payment session with Direct bKash.
     */
    public static function initiateBkash(
        GatewayTransaction $transaction,
        AdmissionForm $form,
        ?string $customerPhone = null
    ): array {
        $config = self::getBkashConfig();
        if (!$config['enabled'] || empty($config['app_key']) || empty($config['app_secret'])) {
            return ['success' => false, 'message' => 'বিকাশ গেটওয়ে সঠিকভাবে কনফিগার করা নেই।'];
        }

        $token = self::getBkashToken();
        if (!$token) {
            return ['success' => false, 'message' => 'বিকাশ সিকিউরিটি টোকেন সংগ্রহ ব্যর্থ হয়েছে।'];
        }

        $endpoint = $config['base_url'] . '/tokenized/checkout/create';
        $postData = [
            'mode'                  => '0011',
            'payerReference'        => $customerPhone ?: ($form->student->phone ?? '01700000000'),
            'callbackURL'           => route('payment.callback.bkash'),
            'amount'                => number_format($transaction->amount, 2, '.', ''),
            'currency'              => 'BDT',
            'intent'                => 'sale',
            'merchantInvoiceNumber' => $transaction->tran_id,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
                'X-APP-Key'     => $config['app_key'],
            ])->timeout(15)->post($endpoint, $postData);

            if (!$response->successful()) {
                Log::error('bKash create payment failed HTTP: ' . $response->status(), ['body' => $response->body()]);
                return ['success' => false, 'message' => 'বিকাশ সার্ভারে পেমেন্ট শুরু করা যায়নি।'];
            }

            $data = $response->json();
            if (isset($data['statusCode']) && $data['statusCode'] === '0000' && !empty($data['bkashURL'])) {
                $transaction->update([
                    'status'       => 'PENDING',
                    'payment_id'   => $data['paymentID'] ?? null,
                    'raw_response' => $data,
                ]);

                return [
                    'success'      => true,
                    'redirect_url' => $data['bkashURL'],
                    'payment_id'   => $data['paymentID'] ?? null,
                ];
            }

            $error = $data['statusMessage'] ?? 'বিকাশ পেমেন্ট শুরু করতে ব্যর্থ হয়েছে।';
            $transaction->update([
                'status'        => 'FAILED',
                'error_message' => $error,
                'raw_response'  => $data,
            ]);

            return ['success' => false, 'message' => $error];
        } catch (\Exception $e) {
            Log::error('bKash Initiate Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'বিকাশ গেটওয়ের সাথে যোগাযোগে ত্রুটি: ' . $e->getMessage()];
        }
    }

    /**
     * Execute bKash payment server-to-server.
     */
    public static function executeBkash(string $paymentId): array
    {
        $config = self::getBkashConfig();
        $token = self::getBkashToken();
        if (!$token) {
            return [];
        }

        $endpoint = $config['base_url'] . '/tokenized/checkout/execute';

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
                'X-APP-Key'     => $config['app_key'],
            ])->timeout(20)->post($endpoint, [
                'paymentID' => $paymentId,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            Log::error('bKash Execute Failed HTTP: ' . $response->status(), ['body' => $response->body()]);
        } catch (\Exception $e) {
            Log::error('bKash Execute Exception: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Query bKash payment server-to-server (for 3-minute fail-safe reconciliation).
     */
    public static function queryBkash(string $paymentId): array
    {
        $config = self::getBkashConfig();
        $token = self::getBkashToken();
        if (!$token) {
            return [];
        }

        $endpoint = $config['base_url'] . '/tokenized/checkout/payment/query';

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
                'X-APP-Key'     => $config['app_key'],
            ])->timeout(15)->post($endpoint, [
                'paymentID' => $paymentId,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('bKash Query Exception: ' . $e->getMessage());
        }

        return [];
    }

    // ══════════════════════════════════════════════════════════════════════
    // PAYMENT SETTLEMENT & RECONCILIATION
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Settle verified successful payment:
     * - Mark GatewayTransaction as SUCCESS
     * - Record payment in accounting (marks invoice as PAID)
     * - Automatically approve admission form & generate student credentials
     */
    public static function settleSuccessfulPayment(GatewayTransaction $transaction, array $gatewayData): bool
    {
        if ($transaction->status === 'SUCCESS') {
            return true; // Already settled
        }

        return DB::transaction(function () use ($transaction, $gatewayData) {
            $isBkash = (strtolower($transaction->gateway) === 'bkash');

            $gatewayTrxId = $gatewayData['bank_tran_id']
                ?? $gatewayData['trxID']
                ?? $gatewayData['transaction_id']
                ?? $transaction->gateway_trx_id;

            $valId = $gatewayData['val_id'] ?? $transaction->val_id;
            $paymentId = $gatewayData['paymentID'] ?? $transaction->payment_id;

            $cardType   = $gatewayData['card_type'] ?? ($isBkash ? 'BKASH' : null);
            $cardBrand  = $gatewayData['card_brand'] ?? ($isBkash ? 'BKASH' : null);
            $cardIssuer = $gatewayData['card_issuer'] ?? ($isBkash ? 'bKash Limited' : null);
            $bankStatus = $gatewayData['status'] ?? $gatewayData['transactionStatus'] ?? 'SUCCESS';

            $transaction->update([
                'status'         => 'SUCCESS',
                'gateway_trx_id' => $gatewayTrxId,
                'val_id'         => $valId,
                'payment_id'     => $paymentId,
                'card_type'      => $cardType,
                'card_brand'     => $cardBrand,
                'card_issuer'    => $cardIssuer,
                'bank_status'    => $bankStatus,
                'verified_at'    => now(),
                'raw_response'   => array_merge((array) ($transaction->raw_response ?? []), $gatewayData),
            ]);

            $form = $transaction->admissionForm;
            if (!$form) {
                return true;
            }

            // Execute auto-approval and invoice settlement for this admission
            self::approvePaidAdmission($form, $transaction);

            return true;
        });
    }

    /**
     * Auto-approve student admission upon verified payment and mark invoice as PAID.
     */
    public static function approvePaidAdmission(AdmissionForm $form, ?GatewayTransaction $transaction = null): void
    {
        $student = $form->student;
        $batch = $form->batch ?: Batch::where('course_id', $form->interested_course_id)->where('status', 'ACTIVE')->first();

        // 1. Generate Custom Student ID (YY-BB-CC-G-RRRR)
        if (empty($student->student_code) && $batch) {
            $yearCode = date('y');
            $batchNum = 1;
            if (!empty($batch->batch_code) && preg_match('/\d+/', $batch->batch_code, $m)) {
                $batchNum = (int) $m[0];
            } elseif (!empty($batch->name) && preg_match('/\d+/', $batch->name, $m)) {
                $batchNum = (int) $m[0];
            } else {
                $batchNum = $batch->id;
            }
            $batchCode = str_pad($batchNum % 100, 2, '0', STR_PAD_LEFT);
            $courseId = $batch->course_id ?: 1;
            $courseCode = str_pad($courseId % 100, 2, '0', STR_PAD_LEFT);

            $genderCode = '1';
            if (!empty($student->gender)) {
                $g = strtolower(trim($student->gender));
                if (in_array($g, ['female', '2', 'f', 'নারি', 'মহিলা'])) {
                    $genderCode = '2';
                }
            }

            $filterPrefix = "{$yearCode}-{$batchCode}-{$courseCode}-";
            $existingCount = Student::where('student_code', 'like', "{$filterPrefix}%")->count();
            $seqNo = str_pad($existingCount + 1, 4, '0', STR_PAD_LEFT);

            $student->student_code = "{$yearCode}-{$batchCode}-{$courseCode}-{$genderCode}-{$seqNo}";
        }

        // Sync details to Student
        $student->status = 'ACTIVE';
        $student->save();
        $student->calculateProfileCompletion();

        // 2. Create Student User Account if not exists
        $rawPassword = null;
        if (empty($student->user_id)) {
            $loginEmail = $student->email ?: ($student->student_code . '@iom.student');
            $tempPassword = $student->phone ?: 'iom@1234';
            $rawPassword = $tempPassword;

            if (User::where('email', $loginEmail)->exists()) {
                $loginEmail = strtolower(str_replace([' ', '-'], '.', $student->student_code ?: uniqid())) . '@iom.student';
            }

            $user = User::create([
                'name'     => $student->name,
                'email'    => $loginEmail,
                'password' => Hash::make($tempPassword),
                'role'     => 'student',
            ]);

            $student->user_id = $user->id;
            $student->save();
        } else {
            $user = $student->user;
        }

        // 3. Mark Admission Form as APPROVED
        $form->update([
            'status'      => 'APPROVED',
            'reviewed_at' => now(),
        ]);

        // 4. Create Student Enrollment
        $enrollment = null;
        if ($batch) {
            $enrollment = Enrollment::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'batch_id'   => $batch->id,
                ],
                [
                    'course_id'         => $batch->course_id,
                    'semester_id'       => $batch->semesterPosition?->current_semester_id,
                    'admission_form_id' => $form->id,
                    'enrolled_at'       => now()->toDateString(),
                    'status'            => 'ACTIVE',
                ]
            );

            // Generate invoices
            $admissionInv = AccountingService::createAdmissionInvoice($student, $form, $enrollment);
            $initialSemester = $batch->semesterPosition?->currentSemester
                ?? $batch->course?->semesters()->orderBy('sequence_no')->first();
            if ($initialSemester) {
                AccountingService::createSemesterInvoice($student, $enrollment, $initialSemester);
            }

            // Settle Admission Invoice with this verified payment
            if ($transaction) {
                $trxId = $transaction->gateway_trx_id ?: $transaction->tran_id;
                $method = strtoupper($transaction->gateway) === 'BKASH' ? 'BKASH' : 'ONLINE';
                AccountingService::receivePayment(
                    $admissionInv,
                    (float) $transaction->amount,
                    $method,
                    $trxId,
                    "Online Payment via " . strtoupper($transaction->gateway) . " (Tran ID: {$transaction->tran_id})"
                );

                $transaction->update(['invoice_id' => $admissionInv->id]);
            } elseif ($admissionInv && $admissionInv->payable_amount == 0) {
                $admissionInv->update(['status' => 'PAID']);
            }
        }

        // 5. Send celebratory credentials email
        $targetEmail = $student->email ?: ($user ? $user->email : null);
        if (!empty($targetEmail) && filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                $mailService = app(\App\Services\DynamicMailService::class);
                $courseName = $form->interestedCourse->name ?? 'Course';
                $batchName = $batch ? $batch->name : 'Target Batch';
                $subject = "🎉 Admission Approved & Payment Received! Welcome to IOM";
                $displayPassword = $rawPassword ?: ($student->phone ?: 'Your registered phone number');

                $body = "Assalamu Alaikum, {$student->name}!\n\n"
                    . "Alhamdulillah! Your online admission and payment of ৳" . number_format($transaction->amount, 2) . " for \"{$courseName}\" has been successfully confirmed.\n\n"
                    . "Official Student Credentials:\n"
                    . "• Student ID: {$student->student_code}\n"
                    . "• Batch: {$batchName}\n"
                    . "• Login Email/ID: {$user->email} OR {$student->student_code}\n"
                    . "• Password: {$displayPassword}\n\n"
                    . "Please login to your Student Portal to access class schedules and complete your profile.";

                $mailService->sendHtmlNotification(
                    $targetEmail,
                    $subject,
                    $body,
                    null,
                    url('/login')
                );
            } catch (\Exception $e) {
                Log::error('Admission Approval Email Error: ' . $e->getMessage());
            }
        }
    }
}
