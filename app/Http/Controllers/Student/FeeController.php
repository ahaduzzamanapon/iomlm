<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        $student = Student::with([
            'enrollments.course.semesters',
            'enrollments.batch.semesterPosition.currentSemester',
            'enrollments.semester'
        ])->where('user_id', auth()->id())->first();

        if (!$student) {
            abort(403, 'শিক্ষার্থীর প্রোফাইল পাওয়া যায়নি।');
        }

        $selectedCourseId   = request('course_id') ? (int) request('course_id') : null;
        $selectedSemesterId = request('semester_id');

        $feeService = app(\App\Services\StudentFeeService::class);
        $feeData = $feeService->getStudentFeeBreakdown($student, $selectedSemesterId, $selectedCourseId);

        $sslActive   = \App\Services\PaymentGatewayService::isSslcommerzActive();
        $bkashActive = \App\Services\PaymentGatewayService::isBkashActive();

        return view('student.fees.index', array_merge($feeData, compact('sslActive', 'bkashActive')));
    }

    /**
     * Submit payment for an invoice from Student Portal (Online Gateway or Offline).
     */
    public function payInvoice(Request $request, Invoice $invoice)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        if ($invoice->student_id !== $student->id) {
            abort(403, 'Unauthorized access to invoice.');
        }

        if ($invoice->status === 'PAID' || $invoice->due_amount <= 0) {
            return back()->with('error', 'এই ইনভয়েসটির সকল বকেয়া ইতিমধ্যে পরিশোধিত হয়েছে।');
        }

        $validated = $request->validate([
            'amount'         => 'required|numeric|min:1|max:' . $invoice->due_amount,
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string|max:100',
            'sender_number'  => 'nullable|string|max:30',
            'remarks'        => 'nullable|string|max:255',
        ]);

        $rawMethod = strtoupper(trim($validated['payment_method']));
        $method = strtolower($rawMethod);

        // 1. Direct Online Payment Gateways (Automated PGW - only exact 'bkash' or 'sslcommerz')
        if (in_array($method, ['bkash', 'sslcommerz']) && !str_contains($rawMethod, 'MANUAL')) {
            $user = auth()->user();
            $sslActive = \App\Services\PaymentGatewayService::isSslcommerzActive();
            $bkashActive = \App\Services\PaymentGatewayService::isBkashActive();

            if ($method === 'bkash' && !$bkashActive) {
                return back()->with('error', 'বিকাশ গেটওয়ে বর্তমানে নিষ্ক্রিয় রয়েছে। অনুগ্রহ করে অন্য মাধ্যম ব্যবহার করুন।');
            }
            if ($method === 'sslcommerz' && !$sslActive) {
                return back()->with('error', 'SSLCommerz গেটওয়ে বর্তমানে নিষ্ক্রিয় রয়েছে। অনুগ্রহ করে অন্য মাধ্যম ব্যবহার করুন।');
            }

            $gatewayMode = ($method === 'bkash')
                ? \App\Services\PaymentGatewayService::getBkashConfig()['mode']
                : \App\Services\PaymentGatewayService::getSslcommerzConfig()['mode'];

            $transaction = \App\Models\GatewayTransaction::create([
                'tran_id'           => \App\Models\GatewayTransaction::generateTranId('FEE'),
                'gateway'           => $method,
                'gateway_mode'      => $gatewayMode,
                'invoice_id'        => $invoice->id,
                'student_id'        => $student->id,
                'amount'            => (float) $validated['amount'],
                'currency'          => 'BDT',
                'customer_name'     => $student->name,
                'customer_phone'    => $student->phone,
                'customer_email'    => $student->email ?: $user?->email,
                'status'            => 'INITIATED',
                'ip_address'        => $request->ip(),
            ]);

            if ($method === 'sslcommerz') {
                $initRes = \App\Services\PaymentGatewayService::initiateSslcommerz(
                    $transaction,
                    null,
                    $student->phone,
                    $student->email ?: $user?->email,
                    $student->name,
                    "Student Fee Payment - " . ($invoice->title ?: $invoice->invoice_no)
                );
            } else {
                $initRes = \App\Services\PaymentGatewayService::initiateBkash(
                    $transaction,
                    null,
                    $student->phone
                );
            }

            if (!empty($initRes['success']) && !empty($initRes['redirect_url'])) {
                return redirect()->away($initRes['redirect_url']);
            }

            return back()->with('error', $initRes['message'] ?? 'পেমেন্ট গেটওয়েতে সংযোগ করতে সমস্যা হয়েছে।');
        }

        // 2. Manual / Offline Payment (BKASH_MANUAL, NAGAD_MANUAL, ROCKET_MANUAL, BANK_TRANSFER, CASH)
        $isMobileManual = in_array($rawMethod, ['BKASH_MANUAL', 'NAGAD_MANUAL', 'ROCKET_MANUAL', 'BKASH', 'NAGAD', 'ROCKET']);

        if ($isMobileManual) {
            $request->validate([
                'transaction_id' => [
                    'required',
                    'string',
                    'size:10',
                    'regex:/^[A-Za-z0-9]{10}$/',
                ],
                'sender_number' => 'required|string|regex:/^01[3-9]\d{8}$/',
            ], [
                'transaction_id.required' => 'ট্রানজেকশন আইডি (TrxID) প্রদান করা বাধ্যতামূলক।',
                'transaction_id.size'     => 'ট্রানজেকশন আইডি অবশ্যই ১০ অক্ষরের হতে হবে।',
                'transaction_id.regex'    => 'ট্রানজেকশন আইডি কেবল বর্ণ ও সংখ্যা (A-Z, 0-9) সমন্বয়ে ১০ ডিজিটের হতে হবে।',
                'sender_number.required'  => 'প্রেরক মোবাইল নম্বর প্রদান করা আবশ্যক।',
                'sender_number.regex'     => 'সঠিক ১১ ডিজিটের বাংলাদেশী মোবাইল নম্বর প্রদান করুন (যেমন: 01712345678)।',
            ]);
        }

        $cleanTrxId = !empty($validated['transaction_id']) ? strtoupper(trim($validated['transaction_id'])) : null;

        // Duplicate Transaction ID prevention check
        if ($cleanTrxId) {
            $isTrxUsed = \App\Models\Payment::where('transaction_id', $cleanTrxId)
                ->where('status', '!=', 'REJECTED')
                ->exists()
                || \App\Models\GatewayTransaction::where('gateway_trx_id', $cleanTrxId)
                ->where('status', 'SUCCESS')
                ->exists()
                || \App\Models\AdmissionForm::where('manual_trx_id', $cleanTrxId)
                ->where('status', '!=', 'REJECTED')
                ->exists();

            if ($isTrxUsed) {
                return back()->withInput()->with('error', 'এই ট্রানজেকশন আইডি (' . $cleanTrxId . ') ইতিপূর্বে ব্যবহার করা হয়েছে। এক ট্রাঞ্জেকশন আইডি একাধিকবার ব্যবহার করা যাবে না। অনুগ্রহ করে নতুন ট্রানজেকশন আইডি দিন।');
            }
        }

        $dbPaymentMethod = match ($rawMethod) {
            'BKASH_MANUAL'  => 'BKASH',
            'NAGAD_MANUAL'  => 'NAGAD',
            'ROCKET_MANUAL' => 'ROCKET',
            'BANK_TRANSFER' => 'BANK_TRANSFER',
            'CASH'          => 'CASH',
            default         => in_array($rawMethod, ['BKASH', 'NAGAD', 'ROCKET', 'BANK_TRANSFER', 'CASH', 'CARD', 'ONLINE']) ? $rawMethod : 'BKASH'
        };

        $defaultRemarks = match ($rawMethod) {
            'BKASH_MANUAL'  => 'বিকাশ ম্যানুয়াল ট্রানজেকশন (Pending Approval)',
            'NAGAD_MANUAL'  => 'নগদ ম্যানুয়াল ট্রানজেকশন (Pending Approval)',
            'ROCKET_MANUAL' => 'রকেট ম্যানুয়াল ট্রানজেকশন (Pending Approval)',
            'BANK_TRANSFER' => 'ব্যাংক ডিপোজিট / স্লিপ (Pending Approval)',
            default         => 'Student Portal Manual Payment (Pending Approval)'
        };

        $payment = \App\Services\AccountingService::submitStudentPayment(
            $invoice,
            (float) $validated['amount'],
            $dbPaymentMethod,
            $cleanTrxId,
            $validated['remarks'] ?: $defaultRemarks,
            $validated['sender_number'] ?? null
        );

        return back()->with('success', '⏳ পেমেন্ট ট্রানজেকশন সফলভাবে জমা দেওয়া হয়েছে! অ্যাডমিন অনুমোদন করার সাথে সাথে ফি রসিদ ও বকেয়া আপডেট হয়ে যাবে।');
    }

    public function printReceipt(Payment $payment)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        if ($payment->student_id !== $student->id) {
            abort(403, 'Unauthorized access to receipt.');
        }
        $payment->load(['invoice', 'student', 'receivedBy']);
        return view('admin.accounts.print_receipt', compact('payment'));
    }

    /**
     * Admin manual fee particular override (Taka increase / decrease & save).
     */
    public function updateParticular(Request $request)
    {
        $isAdmin = session()->has('admin_impersonator_id') 
            || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'ADMIN'));

        if (!$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'অননুমোদিত অনুরোধ। শুধুমাত্র অ্যাডমিন ফি পরিবর্তন করতে পারবেন।'
            ], 403);
        }

        $validated = $request->validate([
            'invoice_id'      => 'required|exists:invoices,id',
            'particular_name' => 'required|string',
            'new_amount'      => 'required|numeric|min:0',
            'current_due'     => 'nullable|numeric|min:0',
            'remarks'         => 'nullable|string|max:255',
            'position'        => 'nullable|string|in:at_bottom,at_top,after,before',
            'relative_to'     => 'nullable|string|max:150',
        ]);

        $res = app(\App\Services\StudentFeeService::class)->updateParticular(
            (int) $validated['invoice_id'],
            $validated['particular_name'],
            (float) $validated['new_amount'],
            $validated['remarks'] ?? null,
            session('admin_impersonator_id') ?? auth()->id(),
            isset($validated['current_due']) ? (float)$validated['current_due'] : null,
            $validated['position'] ?? null,
            $validated['relative_to'] ?? null
        );

        return response()->json($res);
    }

    /**
     * Admin adds a new fee particular to an invoice.
     */
    public function storeParticular(Request $request)
    {
        $isAdmin = session()->has('admin_impersonator_id') 
            || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'ADMIN'));

        if (!$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'অননুমোদিত অনুরোধ। শুধুমাত্র অ্যাডমিন নতুন ফি যোগ করতে পারবেন।'
            ], 403);
        }

        $validated = $request->validate([
            'invoice_id'      => 'nullable|exists:invoices,id',
            'student_id'      => 'nullable|exists:students,id',
            'semester_id'     => 'nullable',
            'particular_name' => 'required|string|max:150',
            'amount'          => 'required|numeric|min:1',
            'remarks'         => 'nullable|string|max:255',
            'position'        => 'nullable|string|in:at_bottom,at_top,after,before',
            'relative_to'     => 'nullable|string|max:150',
        ]);

        $res = app(\App\Services\StudentFeeService::class)->storeParticular(
            !empty($validated['invoice_id']) ? (int) $validated['invoice_id'] : null,
            !empty($validated['student_id']) ? (int) $validated['student_id'] : null,
            $validated['semester_id'] ?? null,
            $validated['particular_name'],
            (float) $validated['amount'],
            $validated['remarks'] ?? null,
            session('admin_impersonator_id') ?? auth()->id(),
            $validated['position'] ?? 'at_bottom',
            $validated['relative_to'] ?? null
        );

        return response()->json($res);
    }

    /**
     * Admin deletes a custom fee particular from an invoice.
     */
    public function deleteParticular(Request $request)
    {
        $isAdmin = session()->has('admin_impersonator_id') 
            || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'ADMIN'));

        if (!$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'অননুমোদিত অনুরোধ। শুধুমাত্র অ্যাডমিন ফি ডিলিট করতে পারবেন।'
            ], 403);
        }

        $validated = $request->validate([
            'invoice_id'      => 'required|exists:invoices,id',
            'particular_name' => 'required|string',
        ]);

        $res = app(\App\Services\StudentFeeService::class)->deleteParticular(
            (int) $validated['invoice_id'],
            $validated['particular_name'],
            session('admin_impersonator_id') ?? auth()->id()
        );

        return response()->json($res);
    }

    /**
     * Admin reverts/deletes the payment of a specific fee particular, restoring it to UNPAID.
     */
    public function revertParticularPayment(Request $request)
    {
        $isAdmin = session()->has('admin_impersonator_id') 
            || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'ADMIN'));

        if (!$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'অননুমোদিত অনুরোধ। শুধুমাত্র অ্যাডমিন পেমেন্ট বাতিল করতে পারবেন।'
            ], 403);
        }

        $validated = $request->validate([
            'invoice_id'      => 'required|exists:invoices,id',
            'particular_name' => 'required|string',
            'paid_amount'     => 'nullable|numeric|min:0',
            'reason'          => 'nullable|string|max:255',
        ]);

        $res = app(\App\Services\StudentFeeService::class)->revertParticularPayment(
            (int) $validated['invoice_id'],
            $validated['particular_name'],
            $validated['reason'] ?? null,
            session('admin_impersonator_id') ?? auth()->id(),
            isset($validated['paid_amount']) ? (float)$validated['paid_amount'] : null
        );

        return response()->json($res);
    }
}

