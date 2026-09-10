<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseTransfer;
use App\Models\Course;
use App\Models\Batch;
use App\Services\AccountingService;
use App\Services\CourseTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseTransferController extends Controller
{
    /**
     * Display course transfer applications and statistics
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status');
        $courseFilter = $request->query('to_course_id');
        $search       = $request->query('search');

        $query = CourseTransfer::with([
            'student',
            'fromCourse',
            'fromBatch',
            'toCourse.semesters',
            'toBatch',
            'toSemester',
            'invoice',
            'approvedBy',
            'newEnrollment',
        ])->latest();

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($courseFilter) {
            $query->where('to_course_id', $courseFilter);
        }

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $transfers = $query->paginate(20)->withQueryString();

        // Statistics
        $totalCount          = CourseTransfer::count();
        $pendingCount        = CourseTransfer::where('status', 'PENDING')->count();
        $waitingPaymentCount = CourseTransfer::where('status', 'APPROVED_PENDING_PAYMENT')->count();
        $completedCount      = CourseTransfer::where('status', 'COMPLETED')->count();
        $rejectedCount       = CourseTransfer::where('status', 'REJECTED')->count();

        // Reference Data
        $courses = Course::where('is_active', true)
            ->with(['batches' => fn($q) => $q->where('status', 'ACTIVE'), 'semesters'])
            ->orderBy('name')
            ->get();

        $batches = Batch::where('status', 'ACTIVE')->orderBy('name')->get();

        return view('admin.course-transfers.index', compact(
            'transfers',
            'totalCount',
            'pendingCount',
            'waitingPaymentCount',
            'completedCount',
            'rejectedCount',
            'courses',
            'batches',
            'statusFilter',
            'courseFilter',
            'search'
        ));
    }

    /**
     * Approve Course Transfer: set target batch, semester, and transfer fee.
     */
    public function approve(Request $request, CourseTransfer $courseTransfer)
    {
        if (!in_array($courseTransfer->status, ['PENDING', 'APPROVED_PENDING_PAYMENT'])) {
            return back()->with('error', 'এই আবেদনটি ইতোমধ্যে প্রক্রিয়া সম্পন্ন বা বাতিল করা হয়েছে।');
        }

        $validated = $request->validate([
            'to_batch_id'    => 'required|exists:batches,id',
            'to_semester_id' => 'nullable|exists:semesters,id',
            'transfer_fee'   => 'required|numeric|min:0',
            'admin_notes'    => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $validated, $courseTransfer) {
            $feeRate     = (float)$validated['transfer_fee'];
            $targetBatch = Batch::findOrFail($validated['to_batch_id']);

            $courseTransfer->update([
                'to_batch_id'    => $targetBatch->id,
                'to_semester_id' => $validated['to_semester_id'] ?? null,
                'transfer_fee'   => $feeRate,
                'admin_notes'    => $validated['admin_notes'] ?? $courseTransfer->admin_notes,
                'approved_by'    => auth()->id(),
                'approved_at'    => now(),
            ]);

            if ($feeRate > 0) {
                // Generate Invoice and await payment
                $courseTransfer->update(['status' => 'APPROVED_PENDING_PAYMENT']);

                if (!$courseTransfer->invoice) {
                    $invoice = AccountingService::createCourseTransferInvoice(
                        $courseTransfer->student,
                        $courseTransfer,
                        $feeRate
                    );
                } else {
                    $courseTransfer->invoice->update([
                        'amount'         => $feeRate,
                        'payable_amount' => $feeRate,
                        'due_amount'     => max(0, $feeRate - $courseTransfer->invoice->paid_amount),
                    ]);
                    $invoice = $courseTransfer->invoice;
                }

                return back()->with(
                    'success',
                    "✅ কোর্স পরিবর্তন অনুমোদন করা হয়েছে! শিক্ষার্থী '{$courseTransfer->student->name}'-এর জন্য ৳" . number_format($feeRate, 0) . " টাকার ইনভয়েস ({$invoice->invoice_no}) তৈরি হয়েছে। ফি পরিশোধ হলেই স্থানান্তর স্বয়ংক্রিয়ভাবে কার্যকর হবে।"
                );
            }

            // If fee is 0.00 (Free), complete immediately!
            CourseTransferService::executeTransfer($courseTransfer);

            return back()->with(
                'success',
                "✅ কোর্স পরিবর্তন সফলভাবে কার্যকর হয়েছে (বিনামূল্যে)! শিক্ষার্থী '{$courseTransfer->student->name}' নতুন কোর্স '{$courseTransfer->toCourse->name}'-এর ব্যাচ '{$targetBatch->name}'-এ যুক্ত হয়েছেন।"
            );
        });
    }

    /**
     * Mark fee as paid directly by Admin and complete transfer immediately
     */
    public function markPaid(Request $request, CourseTransfer $courseTransfer)
    {
        if ($courseTransfer->status === 'COMPLETED') {
            return back()->with('info', 'কোর্স স্থানান্তর ইতিপূর্বেই সম্পন্ন হয়েছে।');
        }

        return DB::transaction(function () use ($courseTransfer) {
            if ($courseTransfer->invoice && $courseTransfer->invoice->due_amount > 0) {
                AccountingService::receivePayment(
                    $courseTransfer->invoice,
                    $courseTransfer->invoice->due_amount,
                    'CASH',
                    null,
                    'Admin counter manual clearance for course transfer'
                );
            } else {
                CourseTransferService::executeTransfer($courseTransfer);
            }

            return back()->with('success', "✅ ফি পরিশোধিত চিহ্নিত করা হয়েছে এবং শিক্ষার্থীকে নতুন কোর্সে সফলভাবে স্থানান্তরিত করা হয়েছে!");
        });
    }

    /**
     * Reject Course Transfer Application
     */
    public function reject(Request $request, CourseTransfer $courseTransfer)
    {
        if ($courseTransfer->status === 'COMPLETED') {
            return back()->with('error', 'ইতিমধ্যে সম্পন্ন হওয়া স্থানান্তর বাতিল করা সম্ভব নয়।');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        return DB::transaction(function () use ($validated, $courseTransfer) {
            $courseTransfer->update([
                'status'           => 'REJECTED',
                'rejection_reason' => $validated['rejection_reason'],
                'approved_by'      => auth()->id(),
            ]);

            if ($courseTransfer->invoice && $courseTransfer->invoice->status === 'UNPAID') {
                $courseTransfer->invoice->update(['status' => 'CANCELLED']);
            }

            return back()->with('success', 'কোর্স পরিবর্তনের আবেদনটি বাতিল করা হয়েছে।');
        });
    }
}
