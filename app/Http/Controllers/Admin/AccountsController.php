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
     * Create Manual Custom Invoice
     */
    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'category'   => 'required|in:ADMISSION,SEMESTER,RETAKE,EXAM,DOCUMENT,FINE,MANUAL',
            'title'      => 'required|string|max:200',
            'amount'     => 'required|numeric|min:1',
            'discount'   => 'nullable|numeric|min:0',
            'due_date'   => 'nullable|date',
        ]);

        $student  = Student::findOrFail($validated['student_id']);
        $amount   = (float) $validated['amount'];
        $discount = (float) ($validated['discount'] ?? 0);
        $payable  = max(0, $amount - $discount);
        $invNo    = 'INV-MAN-' . date('Ymd') . '-' . rand(1000, 9999);

        $enrollment = $student->enrollments()->where('status', 'ACTIVE')->first();

        Invoice::create([
            'invoice_no'     => $invNo,
            'student_id'     => $student->id,
            'enrollment_id'  => $enrollment?->id,
            'category'       => $validated['category'],
            'title'          => $validated['title'],
            'amount'         => $amount,
            'discount'       => $discount,
            'payable_amount' => $payable,
            'paid_amount'    => 0.00,
            'due_amount'     => $payable,
            'status'         => 'UNPAID',
            'due_date'       => $validated['due_date'] ?? Carbon::now()->addDays(7),
            'created_by'     => auth()->id(),
        ]);

        return back()->with('success', "Invoice {$invNo} created successfully!");
    }

    /**
     * Receive Payment for an Invoice
     */
    public function collectPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:1|max:' . $invoice->due_amount,
            'payment_method' => 'required|in:CASH,BKASH,NAGAD,ROCKET,BANK_TRANSFER,CARD,ONLINE',
            'transaction_id' => 'nullable|string|max:100',
            'remarks'        => 'nullable|string',
        ]);

        $payment = AccountingService::receivePayment(
            $invoice,
            (float) $validated['amount'],
            $validated['payment_method'],
            $validated['transaction_id'] ?? null,
            $validated['remarks'] ?? null
        );

        return back()->with('success', "Payment {$payment->payment_no} received successfully! Money receipt generated.");
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
     * Financial Reports & Statements
     */
    public function reports(Request $request)
    {
        $fromDate = $request->query('from_date', Carbon::today()->startOfMonth()->toDateString());
        $toDate   = $request->query('to_date', Carbon::today()->toDateString());

        $payments = Payment::with(['student', 'invoice'])
            ->whereDate('paid_at', '>=', $fromDate)
            ->whereDate('paid_at', '<=', $toDate)
            ->latest('paid_at')
            ->get();

        $categorySummary = Payment::join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->whereDate('payments.paid_at', '>=', $fromDate)
            ->whereDate('payments.paid_at', '<=', $toDate)
            ->selectRaw('invoices.category, SUM(payments.amount) as total_amount')
            ->groupBy('invoices.category')
            ->pluck('total_amount', 'category');

        return view('admin.accounts.reports', compact('payments', 'categorySummary', 'fromDate', 'toDate'));
    }

    /**
     * Printable Money Receipt
     */
    public function printReceipt(Payment $payment)
    {
        $payment->load(['invoice', 'student', 'receivedBy']);
        return view('admin.accounts.print_receipt', compact('payment'));
    }
}
