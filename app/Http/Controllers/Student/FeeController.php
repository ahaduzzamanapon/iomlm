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

        $method = strtolower($validated['payment_method']);

        // 1. Direct Online Payment Gateways (bKash & SSLCommerz)
        if (in_array($method, ['bkash', 'sslcommerz'])) {
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

        // 2. Manual / Offline Payment (bKash Manual, Cash, Bank Transfer, Offline TrxID)
        $payment = \App\Services\AccountingService::submitStudentPayment(
            $invoice,
            (float) $validated['amount'],
            strtoupper($validated['payment_method']),
            $validated['transaction_id'] ?? null,
            $validated['remarks'] ?? null,
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

