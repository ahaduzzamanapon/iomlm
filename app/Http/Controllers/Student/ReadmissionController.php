<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Batch;
use App\Models\Readmission;
use Illuminate\Http\Request;

class ReadmissionController extends Controller
{
    /**
     * View Readmission dashboard for logged-in student
     */
    public function index()
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        $activeEnrollment = Enrollment::with(['course.semesters', 'batch'])
            ->where('student_id', $student->id)
            ->where('status', 'ACTIVE')
            ->latest()
            ->first();

        $readmissions = Readmission::with([
            'course',
            'semester',
            'fromBatch',
            'toBatch',
            'invoice.payments',
            'decidedBy'
        ])
        ->where('student_id', $student->id)
        ->latest()
        ->get();

        $activeRequest = $readmissions->whereIn('status', ['PENDING', 'APPROVED'])->first();

        // Available batches for readmission (active batches in the student's current course)
        $availableBatches = collect();
        if ($activeEnrollment) {
            $availableBatches = Batch::where('course_id', $activeEnrollment->course_id)
                ->where('id', '!=', $activeEnrollment->batch_id)
                ->where('status', 'ACTIVE')
                ->orderBy('name')
                ->get();
        }

        return view('student.readmissions.index', compact(
            'student',
            'activeEnrollment',
            'readmissions',
            'activeRequest',
            'availableBatches'
        ));
    }

    /**
     * Submit an application for readmission
     */
    public function store(Request $request)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        if ($student->is_common_account || (auth()->user() && auth()->user()->is_common_account)) {
            return back()->with('error', 'কমন শেয়ার্ড অ্যাকাউন্টের জন্য রি-এডমিশন আবেদন করার অনুমতি নেই।');
        }

        $activeEnrollment = Enrollment::where('student_id', $student->id)
            ->where('status', 'ACTIVE')
            ->latest()
            ->first();

        if (!$activeEnrollment) {
            return back()->with('error', 'আপনার কোনো সক্রিয় কোর্স পাওয়া যায়নি। রি-এডমিশনের জন্য একটি সক্রিয় কোর্স প্রয়োজন।');
        }

        // Prevent duplicate pending requests
        $existing = Readmission::where('student_id', $student->id)
            ->whereIn('status', ['PENDING'])
            ->first();

        if ($existing) {
            return back()->with('error', 'আপনার একটি রি-এডমিশন আবেদন ইতোমধ্যে প্রক্রিয়াধীন রয়েছে। নতুন আবেদন জমা দেওয়ার পূর্বে বর্তমানটির প্রক্রিয়া শেষ হওয়া প্রয়োজন।');
        }

        $validated = $request->validate([
            'to_batch_id' => 'nullable|exists:batches,id',
            'reason'      => 'required|string|min:10|max:1000',
        ], [
            'reason.required' => 'রি-এডমিশনের কারণ বা বিস্তারিত বিবরণ উল্লেখ করুন।',
            'reason.min'      => 'কারণ ন্যূনতম ১০ অক্ষরের হতে হবে।',
        ]);

        Readmission::create([
            'student_id'            => $student->id,
            'enrollment_id'         => $activeEnrollment->id,
            'course_id'             => $activeEnrollment->course_id,
            'semester_id'           => $activeEnrollment->semester_id,
            'from_batch_id'         => $activeEnrollment->batch_id,
            'to_batch_id'           => $validated['to_batch_id'] ?: null,
            'failed_subjects_count' => 0,
            'failed_subject_ids'    => [],
            'readmission_fee'       => 0.00,
            'status'                => 'PENDING',
            'notes'                 => $validated['reason'],
        ]);

        return back()->with('success', '✅ রি-এডমিশনের আবেদনটি সফলভাবে জমা হয়েছে! অ্যাডমিন কর্তৃপক্ষ পর্যালোচনা করে ফি ও নতুন ব্যাচ নির্ধারণ করবেন।');
    }

    /**
     * Cancel a pending readmission request
     */
    public function cancel(Readmission $readmission)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        if ($readmission->student_id !== $student->id) {
            abort(403);
        }

        if ($readmission->status !== 'PENDING') {
            return back()->with('error', 'অনুমোদিত বা প্রক্রিয়াকৃত আবেদন সরাসরি বাতিল করা সম্ভব নয়।');
        }

        $readmission->update(['status' => 'CANCELLED']);

        return back()->with('success', 'আপনার রি-এডমিশন আবেদনটি বাতিল করা হয়েছে।');
    }
}
