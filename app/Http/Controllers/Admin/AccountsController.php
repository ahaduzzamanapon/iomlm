<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Course;
use App\Services\AccountingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    /**
     * Accounts Dashboard & Counter Fee Collection
     */
    public function dashboard(Request $request)
    {
        $today = Carbon::today();

        $stats = [
            'today_collected' => Payment::whereDate('paid_at', $today)->sum('amount'),
            'month_collected' => Payment::whereMonth('paid_at', $today->month)->whereYear('paid_at', $today->year)->sum('amount'),
            'total_collected' => Payment::sum('amount'),
            'total_due'       => Invoice::where('status', '!=', 'CANCELLED')->sum('due_amount'),
        ];

        // Recent Payments
        $recentPayments = Payment::with(['student', 'invoice'])->latest()->take(10)->get();

        // Pending Student Online Payments awaiting verification
        $pendingPayments = Payment::with(['student', 'invoice'])
            ->where('status', 'PENDING')
            ->latest()
            ->get();

        // Search invoices for counter collection
        $search = $request->query('search');
        $counterInvoices = collect();

        if ($search) {
            $counterInvoices = Invoice::with('student')
                ->where('due_amount', '>', 0)
                ->where(function ($q) use ($search) {
                    $q->where('invoice_no', 'like', "%{$search}%")
                      ->orWhereHas('student', function ($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('student_code', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                      });
                })
                ->take(10)
                ->get();
        }

        return view('admin.accounts.dashboard', compact('stats', 'recentPayments', 'pendingPayments', 'counterInvoices', 'search'));
    }

    /**
     * Approve Pending Student Online Payment
     */
    public function approvePayment(Payment $payment)
    {
        if ($payment->status === 'APPROVED') {
            return back()->with('info', 'পেমেন্টটি ইতিমধ্যে অনুমোদিত হয়েছে।');
        }

        AccountingService::approvePayment($payment);

        return back()->with('success', "✓ পেমেন্ট (ID: {$payment->payment_no}) সফলভাবে অনুমোদিত হয়েছে! ইনভয়েস বকেয়া আপডেট করা হয়েছে।");
    }

    /**
     * Reject Pending Student Online Payment
     */
    public function rejectPayment(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        AccountingService::rejectPayment($payment, $validated['reason'] ?? 'Invalid Transaction Details');

        return back()->with('success', "❌ পেমেন্টটি (ID: {$payment->payment_no}) বাতিল করা হয়েছে।");
    }

    /**
     * All Invoices & Dues List
     */
    public function invoices(Request $request)
    {
        $category = $request->query('category');
        $status   = $request->query('status');
        $search   = $request->query('search');

        $query = Invoice::with(['student', 'enrollment.course'])->latest();

        if ($category) {
            $query->where('category', $category);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('student_code', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate(20)->withQueryString();
        $students = Student::where('status', 'ACTIVE')->orderBy('name')->get();

        return view('admin.accounts.invoices', compact('invoices', 'students', 'category', 'status', 'search'));
    }

    /**
     * Create Manual Custom Invoice (New Fee or Extra Fee)
     */
    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'student_id'     => 'required|exists:students,id',
            'category'       => 'required|in:ADMISSION,SEMESTER,RETAKE,EXAM,DOCUMENT,FINE,MANUAL,EXTRA,COURSE_TRANSFER',
            'title'          => 'required|string|max:200',
            'notes'          => 'nullable|string|max:500',
            'amount'         => 'required|numeric|min:1',
            'discount'       => 'nullable|numeric|min:0',
            'due_date'       => 'nullable|date',
            'record_payment' => 'nullable|boolean',
            'paid_amount'    => 'nullable|numeric|min:1',
            'payment_method' => 'nullable|in:CASH,BKASH,NAGAD,ROCKET,BANK_TRANSFER,CARD,ONLINE',
            'sender_number'  => 'nullable|string|max:30',
            'transaction_id' => 'nullable|string|max:100',
            'remarks'        => 'nullable|string|max:255',
        ]);

        $student  = Student::findOrFail($validated['student_id']);
        $amount   = (float) $validated['amount'];
        $discount = (float) ($validated['discount'] ?? 0);
        $payable  = max(0, $amount - $discount);
        $prefix   = ($validated['category'] === 'EXTRA') ? 'INV-EXT-' : 'INV-MAN-';
        $invNo    = $prefix . date('Ymd') . '-' . rand(1000, 9999);

        $enrollment = $student->enrollments()->where('status', 'ACTIVE')->first();

        $invoice = Invoice::create([
            'invoice_no'     => $invNo,
            'student_id'     => $student->id,
            'enrollment_id'  => $enrollment?->id,
            'category'       => $validated['category'],
            'title'          => $validated['title'],
            'notes'          => $validated['notes'] ?? null,
            'amount'         => $amount,
            'discount'       => $discount,
            'payable_amount' => $payable,
            'paid_amount'    => 0.00,
            'due_amount'     => $payable,
            'status'         => 'UNPAID',
            'due_date'       => $validated['due_date'] ?? Carbon::now()->addDays(7),
            'created_by'     => auth()->id(),
        ]);

        // Optional immediate payment collection
        if ($request->boolean('record_payment') && !empty($validated['paid_amount'])) {
            $paymentAmt = min($payable, (float) $validated['paid_amount']);
            if ($paymentAmt > 0) {
                AccountingService::receivePayment(
                    $invoice,
                    $paymentAmt,
                    $validated['payment_method'] ?? 'CASH',
                    $validated['transaction_id'] ?? null,
                    $validated['remarks'] ?? ($validated['notes'] ?? 'Immediate fee collection'),
                    $validated['sender_number'] ?? null
                );
            }
        }

        return back()->with('success', "ইনভয়েস {$invNo} সফলভাবে তৈরি করা হয়েছে!");
    }

    /**
     * Receive Payment for an Invoice
     */
    public function collectPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:1|max:' . $invoice->due_amount,
            'payment_method' => 'required|in:CASH,BKASH,NAGAD,ROCKET,BANK_TRANSFER,CARD,ONLINE',
            'sender_number'  => 'nullable|string|max:30',
            'transaction_id' => 'nullable|string|max:100',
            'remarks'        => 'nullable|string',
        ]);

        $payment = AccountingService::receivePayment(
            $invoice,
            (float) $validated['amount'],
            $validated['payment_method'],
            $validated['transaction_id'] ?? null,
            $validated['remarks'] ?? null,
            $validated['sender_number'] ?? null
        );

        return back()->with('success', "পেমেন্ট {$payment->payment_no} সফলভাবে গৃহীত হয়েছে! মানি রসিদ তৈরি সম্পন্ন।");
    }

    /**
     * Master Fee Structure & Rates Setup
     */
    public function feeStructures()
    {
        $structures = FeeStructure::with('course')->latest()->get();
        $courses    = Course::where('is_active', true)->orderBy('name')->get();
        return view('admin.accounts.fee_structures', compact('structures', 'courses'));
    }

    /**
     * Save Master Fee Structure Rate
     */
    public function storeFeeStructure(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'category'    => 'required|in:ADMISSION,SEMESTER,RETAKE,EXAM,DOCUMENT,OTHER',
            'course_id'   => 'nullable|exists:courses,id',
            'amount'      => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        FeeStructure::create([
            'name'        => $validated['name'],
            'category'    => $validated['category'],
            'course_id'   => $validated['course_id'] ?? null,
            'amount'      => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'is_active'   => true,
        ]);

        return back()->with('success', 'Fee structure rate saved successfully!');
    }

    /**
     * Financial Reports & Statements with Course, Month & Date Range Filters
     */
    public function reports(Request $request)
    {
        $courseId = $request->query('course_id');
        $month    = $request->query('month'); // format: YYYY-MM
        $fromDate = $request->query('from_date');
        $toDate   = $request->query('to_date');
        $category = $request->query('category');

        // If month is selected, compute fromDate and toDate from that month
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $carbonMonth = Carbon::createFromFormat('Y-m', $month);
            $fromDate = $carbonMonth->copy()->startOfMonth()->toDateString();
            $toDate   = $carbonMonth->copy()->endOfMonth()->toDateString();
        } else {
            if (!$fromDate) {
                $fromDate = Carbon::today()->startOfMonth()->toDateString();
            }
            if (!$toDate) {
                $toDate = Carbon::today()->toDateString();
            }
        }

        $query = Payment::with(['student.enrollments.batch.course', 'invoice.enrollment.course'])
            ->where('status', 'APPROVED')
            ->whereDate('paid_at', '>=', $fromDate)
            ->whereDate('paid_at', '<=', $toDate);

        if ($category) {
            $query->whereHas('invoice', function ($q) use ($category) {
                $q->where('category', $category);
            });
        }

        if ($courseId) {
            $query->where(function ($q) use ($courseId) {
                $q->whereHas('invoice.enrollment', function ($sq) use ($courseId) {
                    $sq->where('course_id', $courseId);
                })->orWhereHas('student.enrollments', function ($sq) use ($courseId) {
                    $sq->where('course_id', $courseId);
                });
            });
        }

        $payments = $query->latest('paid_at')->get();

        // Calculate Totals
        $totalCollected = $payments->sum('amount');

        // Category Breakdown
        $categorySummary = [];
        foreach ($payments as $pay) {
            $cat = $pay->invoice->category ?? 'OTHER';
            $categorySummary[$cat] = ($categorySummary[$cat] ?? 0) + $pay->amount;
        }

        // Course Breakdown
        $courseSummary = [];
        foreach ($payments as $pay) {
            $courseName = $pay->invoice?->enrollment?->course?->name
                ?? $pay->student?->enrollments?->first()?->course?->name
                ?? 'সাধারণ / অন্যান্য';
            $courseSummary[$courseName] = ($courseSummary[$courseName] ?? 0) + $pay->amount;
        }

        $courses = Course::where('is_active', true)->orderBy('name')->get();

        return view('admin.accounts.reports', compact(
            'payments',
            'categorySummary',
            'courseSummary',
            'totalCollected',
            'courses',
            'courseId',
            'month',
            'fromDate',
            'toDate',
            'category'
        ));
    }

    /**
     * Printable Money Receipt
     */
    public function printReceipt(Payment $payment)
    {
        $payment->load(['invoice', 'student', 'receivedBy']);
        return view('admin.accounts.print_receipt', compact('payment'));
    }

    /**
     * Dedicated Student Accounts Ledger (Full CRUD + Month-wise Fee Breakdown)
     */
    public function studentLedger(Request $request, Student $student)
    {
        $feeService = app(\App\Services\StudentFeeService::class);
        $feeData = $feeService->getStudentFeeBreakdown(
            $student,
            $request->query('semester_id'),
            $request->query('course_id') ? (int) $request->query('course_id') : null
        );

        return view('admin.accounts.student_ledger', $feeData);
    }

    /**
     * Admin adjusts/edits a fee particular amount
     */
    public function updateParticular(Request $request)
    {
        $validated = $request->validate([
            'invoice_id'      => 'required|exists:invoices,id',
            'particular_name' => 'required|string',
            'new_amount'      => 'required|numeric|min:0',
            'current_due'     => 'nullable|numeric|min:0',
            'remarks'         => 'nullable|string|max:255',
        ]);

        $res = app(\App\Services\StudentFeeService::class)->updateParticular(
            (int) $validated['invoice_id'],
            $validated['particular_name'],
            (float) $validated['new_amount'],
            $validated['remarks'] ?? null,
            auth()->id(),
            isset($validated['current_due']) ? (float)$validated['current_due'] : null
        );

        return response()->json($res);
    }

    /**
     * Admin adds a new custom fee particular
     */
    public function storeParticular(Request $request)
    {
        $validated = $request->validate([
            'invoice_id'      => 'nullable|exists:invoices,id',
            'student_id'      => 'nullable|exists:students,id',
            'semester_id'     => 'nullable',
            'particular_name' => 'required|string|max:150',
            'amount'          => 'required|numeric|min:1',
            'remarks'         => 'nullable|string|max:255',
        ]);

        $res = app(\App\Services\StudentFeeService::class)->storeParticular(
            !empty($validated['invoice_id']) ? (int) $validated['invoice_id'] : null,
            !empty($validated['student_id']) ? (int) $validated['student_id'] : null,
            $validated['semester_id'] ?? null,
            $validated['particular_name'],
            (float) $validated['amount'],
            $validated['remarks'] ?? null,
            auth()->id()
        );

        return response()->json($res);
    }

    /**
     * Admin deletes a custom fee particular
     */
    public function deleteParticular(Request $request)
    {
        $validated = $request->validate([
            'invoice_id'      => 'required|exists:invoices,id',
            'particular_name' => 'required|string',
        ]);

        $res = app(\App\Services\StudentFeeService::class)->deleteParticular(
            (int) $validated['invoice_id'],
            $validated['particular_name'],
            auth()->id()
        );

        return response()->json($res);
    }

    /**
     * Admin collects payment for one or multiple particulars
     */
    public function collectParticularPayment(Request $request)
    {
        $validated = $request->validate([
            'invoice_id'         => 'nullable|exists:invoices,id',
            'student_id'         => 'nullable|exists:students,id',
            'semester_id'        => 'nullable',
            'particular_names'   => 'required|array|min:1',
            'particular_names.*' => 'required|string',
            'particular_dues'    => 'nullable|array',
            'particular_amounts' => 'nullable|array',
            'amount'             => 'required|numeric|min:1',
            'payment_method'     => 'required|in:CASH,BKASH,NAGAD,ROCKET,BANK_TRANSFER,CARD,ONLINE',
            'transaction_id'     => 'nullable|string|max:100',
            'sender_number'      => 'nullable|string|max:30',
            'remarks'            => 'nullable|string|max:255',
        ]);

        $invoice = null;
        if (!empty($validated['invoice_id'])) {
            $invoice = Invoice::find($validated['invoice_id']);
        }

        if (!$invoice && !empty($validated['student_id'])) {
            $student = Student::findOrFail($validated['student_id']);
            $enrollment = $student->enrollments()->where('status', 'ACTIVE')->first() ?? $student->enrollments()->first();
            $isNum = !empty($validated['semester_id']) && is_numeric($validated['semester_id']) && (int)$validated['semester_id'] > 0;

            $invoice = Invoice::create([
                'invoice_no'         => 'INV-TUI-' . date('Ymd') . '-' . rand(1000, 9999),
                'student_id'         => $student->id,
                'enrollment_id'      => $enrollment?->id,
                'category'           => $isNum ? 'SEMESTER' : 'MANUAL',
                'title'              => implode(', ', $validated['particular_names']),
                'amount'             => (float)$validated['amount'],
                'discount'           => 0,
                'payable_amount'     => (float)$validated['amount'],
                'paid_amount'        => 0,
                'due_amount'         => (float)$validated['amount'],
                'status'             => 'UNPAID',
                'source_type'        => $isNum ? \App\Models\Semester::class : null,
                'source_id'          => $isNum ? (int)$validated['semester_id'] : null,
                'created_by'         => auth()->id(),
                'custom_particulars' => [],
            ]);
        }

        if (!$invoice) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'ইনভয়েস সনাক্ত করা সম্ভব হয়নি।'], 422);
            }
            return back()->with('error', 'ইনভয়েস সনাক্ত করা সম্ভব হয়নি।');
        }

        $res = app(\App\Services\StudentFeeService::class)->collectParticularPayment(
            $invoice,
            $validated['particular_names'],
            (float) $validated['amount'],
            $validated['payment_method'],
            $validated['transaction_id'] ?? null,
            $validated['sender_number'] ?? null,
            $validated['remarks'] ?? null,
            auth()->id(),
            $request->input('particular_dues', []),
            $request->input('particular_amounts', [])
        );

        if ($request->wantsJson()) {
            return response()->json($res);
        }

        return back()->with('success', $res['message']);
    }

    /**
     * Update an existing Invoice (Edit Title, Amount, Discount, Due Date, Category, Notes)
     */
    public function updateInvoice(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:200',
            'category'   => 'required|in:ADMISSION,SEMESTER,RETAKE,EXAM,DOCUMENT,FINE,MANUAL,EXTRA,COURSE_TRANSFER',
            'notes'      => 'nullable|string|max:500',
            'amount'     => 'required|numeric|min:0',
            'discount'   => 'nullable|numeric|min:0',
            'due_date'   => 'nullable|date',
        ]);

        $amount   = (float) $validated['amount'];
        $discount = (float) ($validated['discount'] ?? 0);
        $payable  = max(0, $amount - $discount);

        if ($payable < $invoice->paid_amount) {
            return back()->with('error', "প্রদেয় পরিমাণ (৳{$payable}) ইতোমধ্যে পরিশোধিত পরিমাণের (৳{$invoice->paid_amount}) চেয়ে কম হতে পারে না।");
        }

        $due = max(0, $payable - $invoice->paid_amount);

        $status = 'UNPAID';
        if ($due <= 0 && $payable > 0) {
            $status = 'PAID';
        } elseif ($invoice->paid_amount > 0) {
            $status = 'PARTIAL';
        }

        $invoice->update([
            'title'          => $validated['title'],
            'category'       => $validated['category'],
            'notes'          => $validated['notes'] ?? $invoice->notes,
            'amount'         => $amount,
            'discount'       => $discount,
            'payable_amount' => $payable,
            'due_amount'     => $due,
            'status'         => $status,
            'due_date'       => $validated['due_date'] ?? $invoice->due_date,
        ]);

        return back()->with('success', "ইনভয়েস {$invoice->invoice_no} সফলভাবে আপডেট করা হয়েছে।");
    }

    /**
     * Update Payment Status of a Specific Invoice (PAID, UNPAID, PARTIAL, CANCELLED)
     */
    public function updateInvoiceStatus(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status'         => 'required|in:PAID,UNPAID,PARTIAL,CANCELLED',
            'remarks'        => 'nullable|string|max:255',
            'payment_method' => 'nullable|in:CASH,BKASH,NAGAD,ROCKET,BANK_TRANSFER,CARD,ONLINE',
            'sender_number'  => 'nullable|string|max:30',
            'transaction_id' => 'nullable|string|max:100',
        ]);

        $newStatus = $validated['status'];
        $remarks   = $validated['remarks'] ?? 'Status updated by Admin';

        if ($newStatus === 'CANCELLED') {
            // Cancel / Waive invoice: set due to 0, mark CANCELLED
            $invoice->update([
                'status'     => 'CANCELLED',
                'due_amount' => 0.00,
                'notes'      => ($invoice->notes ? $invoice->notes . "\n" : '') . "[বাতিল/মওকুফ: {$remarks}]",
            ]);

            return back()->with('success', "ইনভয়েস {$invoice->invoice_no} সফলভাবে বাতিল/মওকুফ (CANCELLED) করা হয়েছে।");
        }

        if ($newStatus === 'PAID') {
            if ($invoice->due_amount > 0) {
                // Settle remaining due by recording payment
                $method       = $validated['payment_method'] ?? 'CASH';
                $trxId        = $validated['transaction_id'] ?? null;
                $senderNumber = $validated['sender_number'] ?? null;

                AccountingService::receivePayment(
                    $invoice,
                    $invoice->due_amount,
                    $method,
                    $trxId,
                    $remarks,
                    $senderNumber
                );

                return back()->with('success', "ইনভয়েস {$invoice->invoice_no}-এর বকেয়া পরিশোধ সম্পন্ন হয়েছে এবং স্ট্যাটাস PAID করা হয়েছে।");
            } else {
                $invoice->update(['status' => 'PAID']);
                return back()->with('success', "ইনভয়েস {$invoice->invoice_no}-এর স্ট্যাটাস PAID করা হয়েছে।");
            }
        }

        if ($newStatus === 'UNPAID') {
            $due = max(0, $invoice->payable_amount - $invoice->paid_amount);
            $invoice->update([
                'status'     => ($invoice->paid_amount > 0) ? 'PARTIAL' : 'UNPAID',
                'due_amount' => $due,
            ]);

            return back()->with('success', "ইনভয়েস {$invoice->invoice_no}-এর স্ট্যাটাস আপডেট করা হয়েছে।");
        }

        if ($newStatus === 'PARTIAL') {
            $invoice->update(['status' => 'PARTIAL']);
            return back()->with('success', "ইনভয়েস {$invoice->invoice_no}-এর স্ট্যাটাস PARTIAL করা হয়েছে।");
        }

        return back();
    }

    /**
     * Delete an Invoice (Safe Deletion with Payment Audit Check or Void)
     */
    public function destroyInvoice(Request $request, Invoice $invoice)
    {
        // If payments already exist, check if user explicitly requested void/cancel
        if ($invoice->paid_amount > 0 || $invoice->payments()->exists()) {
            if ($request->boolean('force_cancel')) {
                $invoice->update([
                    'status'     => 'CANCELLED',
                    'due_amount' => 0.00,
                    'notes'      => ($invoice->notes ? $invoice->notes . "\n" : '') . '[ভুল বা অতিরিক্ত ফি হিসেবে বাতিল করা হয়েছে]',
                ]);
                return back()->with('success', "ইনভয়েসে জমা টাকা থাকায় এটি স্থায়ীভাবে ডিলিটের পরিবর্তে বাতিল (CANCELLED) হিসেবে চিহ্নিত করা হয়েছে এবং বকেয়া শূন্য করা হয়েছে।");
            }

            return back()->with('error', "এই ইনভয়েসে ইতোমধ্যে ৳{$invoice->paid_amount} টাকা পেমেন্ট জমা রয়েছে। সরাসরি ডিলিট করার পরিবর্তে আপনি স্ট্যাটাস থেকে 'CANCELLED' করতে পারেন বা ইনভয়েস এডিট করতে পারেন।");
        }

        $invNo = $invoice->invoice_no;
        $invoice->delete();

        return back()->with('success', "ভুল/অতিরিক্ত ইনভয়েস {$invNo} সফলভাবে মুছে ফেলা হয়েছে।");
    }

    /**
     * Manually Trigger ৳100 Monthly Course Activation Fee
     */
    public function applyActivationFees(Request $request)
    {
        $force = $request->boolean('force', true);
        $result = AccountingService::applyCourseActivationFees(now(), $force);

        return back()->with($result['status'] === 'success' ? 'success' : 'info', $result['message']);
    }
}
