<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseTransfer;
use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class CourseTransferController extends Controller
{
    /**
     * View Course Transfer dashboard for the logged-in student
     */
    public function index()
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        // Find active enrollment
        $activeEnrollment = Enrollment::with(['course.semesters', 'batch.semesterPosition.currentSemester'])
            ->where('student_id', $student->id)
            ->where('status', 'ACTIVE')
            ->latest()
            ->first();

        // Get transfer history
        $transfers = CourseTransfer::with([
            'fromCourse',
            'fromBatch',
            'toCourse',
            'toBatch',
            'invoice.payments',
            'newEnrollment.batch',
        ])
        ->where('student_id', $student->id)
        ->latest()
        ->get();

        $activeRequest = $transfers->whereIn('status', ['PENDING', 'APPROVED_PENDING_PAYMENT'])->first();

        // Available other courses
        $currentCourseId = $activeEnrollment?->course_id;
        $availableCourses = Course::where('is_active', true)
            ->when($currentCourseId, fn($q) => $q->where('id', '!=', $currentCourseId))
            ->orderBy('name')
            ->get();

        return view('student.course-transfers.index', compact(
            'student',
            'activeEnrollment',
            'transfers',
            'activeRequest',
            'availableCourses'
        ));
    }

    /**
     * Submit application for course transfer
     */
    public function store(Request $request)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        $activeEnrollment = Enrollment::where('student_id', $student->id)
            ->where('status', 'ACTIVE')
            ->latest()
            ->first();

        if (!$activeEnrollment) {
            return back()->with('error', 'আপনার কোনো সক্রিয় কোর্স পাওয়া যায়নি। কোর্স পরিবর্তনের জন্য একটি সক্রিয় কোর্স প্রয়োজন।');
        }

        // Prevent duplicate pending requests
        $existing = CourseTransfer::where('student_id', $student->id)
            ->whereIn('status', ['PENDING', 'APPROVED_PENDING_PAYMENT'])
            ->first();

        if ($existing) {
            return back()->with('error', 'আপনার একটি কোর্স পরিবর্তনের আবেদন ইতোমধ্যে প্রক্রিয়াধীন রয়েছে। নতুন আবেদন জমা দেওয়ার পূর্বে বর্তমানটির প্রক্রিয়া শেষ হওয়া প্রয়োজন।');
        }

        $validated = $request->validate([
            'to_course_id' => [
                'required',
                'exists:courses,id',
                function ($attribute, $value, $fail) use ($activeEnrollment) {
                    if ($value == $activeEnrollment->course_id) {
                        $fail('আপনি ইতোমধ্যে এই কোর্সে যুক্ত আছেন। ভিন্ন কোনো কোর্স নির্বাচন করুন।');
                    }
                },
            ],
            'reason' => 'required|string|min:10|max:1000',
        ], [
            'to_course_id.required' => 'স্থানান্তর হতে ইচ্ছুক কোর্স নির্বাচন করুন।',
            'reason.required'       => 'কোর্স পরিবর্তনের কারণ উল্লেখ করুন।',
            'reason.min'            => 'কোর্স পরিবর্তনের কারণ ন্যূনতম ১০ অক্ষরের হতে হবে।',
        ]);

        $transfer = CourseTransfer::create([
            'student_id'         => $student->id,
            'from_course_id'     => $activeEnrollment->course_id,
            'from_batch_id'      => $activeEnrollment->batch_id,
            'from_enrollment_id' => $activeEnrollment->id,
            'to_course_id'       => $validated['to_course_id'],
            'reason'             => $validated['reason'],
            'transfer_fee'       => 0.00,
            'status'             => 'PENDING',
        ]);

        return back()->with('success', '✅ কোর্স পরিবর্তনের আবেদনটি সফলভাবে জমা হয়েছে! অ্যাডমিন পর্যালোচনা করে প্রযোজ্য ফি ও নতুন ব্যাচ নির্ধারণ করবেন।');
    }

    /**
     * Cancel a pending transfer request
     */
    public function cancel(CourseTransfer $courseTransfer)
    {
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        if ($courseTransfer->student_id !== $student->id) {
            abort(403);
        }

        if ($courseTransfer->status !== 'PENDING') {
            return back()->with('error', 'অনুমোদিত বা প্রক্রিয়াকৃত আবেদন সরাসরি বাতিল করা সম্ভব নয়। প্রয়োজনে কর্তৃপক্ষের সাথে যোগাযোগ করুন।');
        }

        $courseTransfer->update(['status' => 'CANCELLED']);

        return back()->with('success', 'আপনার কোর্স পরিবর্তনের আবেদনটি বাতিল করা হয়েছে।');
    }
}
