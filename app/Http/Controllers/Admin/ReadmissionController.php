<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Readmission;
use App\Models\Student;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\FinalMark;
use App\Models\SubjectRetake;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReadmissionController extends Controller
{
    /**
     * Display list of re-admissions and candidate management
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status');
        $courseFilter = $request->query('course_id');
        $search       = $request->query('search');

        $query = Readmission::with([
            'student',
            'course',
            'semester',
            'fromBatch',
            'toBatch',
            'invoice',
            'decidedBy',
        ])->latest();

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($courseFilter) {
            $query->where('course_id', $courseFilter);
        }

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $readmissions = $query->paginate(20)->withQueryString();

        // Summary Statistics
        $totalCount    = Readmission::count();
        $pendingCount  = Readmission::where('status', 'PENDING')->count();
        $approvedCount = Readmission::where('status', 'APPROVED')->count();
        $retakeCount   = Readmission::where('status', 'CONTINUED_WITH_RETAKE')->count();

        // Reference Data for Modals & Filters
        $courses   = Course::where('is_active', true)->with(['semesters', 'batches'])->orderBy('name')->get();
        $batches   = Batch::where('status', 'ACTIVE')->orderBy('name')->get();
        $semesters = Semester::orderBy('course_id')->orderBy('sequence_no')->get();
        $subjects  = Subject::where('is_active', true)->orderBy('name')->get();
        $students  = Student::where('status', 'ACTIVE')->orderBy('name')->get();

        return view('admin.readmissions.index', compact(
            'readmissions',
            'totalCount',
            'pendingCount',
            'approvedCount',
            'retakeCount',
            'courses',
            'batches',
            'semesters',
            'subjects',
            'students',
            'statusFilter',
            'courseFilter',
            'search'
        ));
    }

    /**
     * Manually register a student for re-admission
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'            => 'required|exists:students,id',
            'course_id'             => 'required|exists:courses,id',
            'semester_id'           => 'nullable|exists:semesters,id',
            'from_batch_id'         => 'nullable|exists:batches,id',
            'to_batch_id'           => 'nullable|exists:batches,id',
            'failed_subject_ids'    => 'nullable|array',
            'failed_subject_ids.*'  => 'exists:subjects,id',
            'readmission_fee'       => 'nullable|numeric|min:0',
            'notes'                 => 'nullable|string',
        ]);

        $student    = Student::findOrFail($validated['student_id']);
        $enrollment = $student->enrollments()->where('status', 'ACTIVE')->first();
        $course     = Course::find($validated['course_id']);

        $failedSubjectIds = $validated['failed_subject_ids'] ?? [];
        $failedCount      = count($failedSubjectIds);

        // Fallback fee from course configuration if not provided
        $fee = isset($validated['readmission_fee']) && $validated['readmission_fee'] !== null
            ? (float)$validated['readmission_fee']
            : (float)($course->readmission_fee ?: ($course->admission_fee ?: 0.00));

        $readmission = Readmission::create([
            'student_id'            => $student->id,
            'enrollment_id'         => $enrollment?->id,
            'course_id'             => $course->id,
            'semester_id'           => $validated['semester_id'] ?? $enrollment?->semester_id,
            'from_batch_id'         => $validated['from_batch_id'] ?? $enrollment?->batch_id,
            'to_batch_id'           => $validated['to_batch_id'] ?? null,
            'failed_subjects_count' => $failedCount,
            'failed_subject_ids'    => $failedSubjectIds,
            'readmission_fee'       => $fee,
            'status'                => 'PENDING',
            'notes'                 => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "{$student->name}-এর রি-এডমিশন এন্ট্রি সফলভাবে যুক্ত হয়েছে। অ্যাডমিন রিভিউ করে সিদ্ধান্ত নিতে পারবেন।");
    }

    /**
     * Approve Re-admission: Move student to new batch and repeat semester
     */
    public function approve(Request $request, Readmission $readmission)
    {
        $validated = $request->validate([
            'to_batch_id'     => 'required|exists:batches,id',
            'readmission_fee' => 'required|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $readmission) {
            $student     = $readmission->student;
            $targetBatch = Batch::findOrFail($validated['to_batch_id']);

            // 1. Update Readmission Record
            $readmission->update([
                'to_batch_id'     => $targetBatch->id,
                'readmission_fee' => (float)$validated['readmission_fee'],
                'status'          => 'APPROVED',
                'admin_decision'  => 'READMISSION',
                'notes'           => $validated['notes'] ?? $readmission->notes,
                'decided_by'      => auth()->id(),
                'decided_at'      => now(),
            ]);

            // 2. Transfer / Create Enrollment in New Batch for repeating this semester
            $oldEnrollment = $student->enrollments()
                ->where('status', 'ACTIVE')
                ->where('course_id', $readmission->course_id)
                ->first();

            if ($oldEnrollment) {
                $oldEnrollment->update([
                    'status' => 'TRANSFERRED',
                ]);
            }

            $newEnrollment = Enrollment::create([
                'student_id'   => $student->id,
                'course_id'    => $readmission->course_id,
                'batch_id'     => $targetBatch->id,
                'semester_id'  => $readmission->semester_id,
                'enrolled_at'  => now(),
                'status'       => 'ACTIVE',
            ]);

            $readmission->update(['enrollment_id' => $newEnrollment->id]);

            // 3. Generate Re-admission Fee Invoice
            $invoice = AccountingService::createReadmissionInvoice(
                $student,
                $readmission,
                (float)$validated['readmission_fee']
            );

            return back()->with(
                'success',
                "✅ রি-এডমিশন সফলভাবে অনুমোদিত হয়েছে! শিক্ষার্থীকে নতুন ব্যাচ '{$targetBatch->name}'-এ স্থানান্তর করা হয়েছে এবং ৳" . number_format($validated['readmission_fee'], 0) . " টাকার ইনভয়েস ({$invoice->invoice_no}) তৈরি হয়েছে।"
            );
        });
    }

    /**
     * Allow student to continue with retakes instead of repeating the batch
     */
    public function continueWithRetake(Request $request, Readmission $readmission)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $readmission) {
            $student = $readmission->student;

            // 1. Update Readmission record to CONTINUED_WITH_RETAKE
            $readmission->update([
                'status'         => 'CONTINUED_WITH_RETAKE',
                'admin_decision' => 'CONTINUE_WITH_RETAKE',
                'notes'          => $validated['notes'] ?? $readmission->notes,
                'decided_by'     => auth()->id(),
                'decided_at'     => now(),
            ]);

            // 2. Auto-create SubjectRetake records for all failed subjects if not already existing
            $createdRetakes = 0;
            if (!empty($readmission->failed_subject_ids)) {
                foreach ($readmission->failed_subject_ids as $subId) {
                    $existing = SubjectRetake::where('student_id', $student->id)
                        ->where('subject_id', $subId)
                        ->whereIn('status', ['PENDING', 'IN_PROGRESS'])
                        ->first();

                    if (!$existing) {
                        SubjectRetake::create([
                            'student_id'    => $student->id,
                            'subject_id'    => $subId,
                            'enrollment_id' => $readmission->enrollment_id,
                            'retake_type'   => 'EXAM_ONLY',
                            'status'        => 'PENDING',
                            'reason'        => 'রি-এডমিশন বিবেচনায় রিটেক দিয়ে কন্টিনিউ করার অনুমতি দেওয়া হয়েছে (Re-admission #' . $readmission->id . ')',
                        ]);
                        $createdRetakes++;
                    }
                }
            }

            return back()->with(
                'success',
                "✅ শিক্ষার্থী '{$student->name}'-কে রিটেক নিয়ে কন্টিনিউ করার অনুমতি দেওয়া হয়েছে। {$createdRetakes}টি বিষয়ের রিটেক এন্ট্রি তৈরি হয়েছে।"
            );
        });
    }

    /**
     * Auto-detect students who failed in more than 2 subjects in a semester
     */
    public function autoDetect(Request $request)
    {
        $newDetected = 0;

        // 1. Scan from FinalMark where status = 'FAIL'
        try {
            $failGroups = FinalMark::where('status', 'FAIL')
                ->select(
                    'student_id',
                    'semester_id',
                    'batch_id',
                    DB::raw('count(DISTINCT subject_id) as fail_count'),
                    DB::raw('GROUP_CONCAT(DISTINCT subject_id) as subject_ids')
                )
                ->groupBy('student_id', 'semester_id', 'batch_id')
                ->having('fail_count', '>', 2)
                ->get();

            foreach ($failGroups as $group) {
                $student = Student::find($group->student_id);
                $batch   = Batch::find($group->batch_id);
                $course  = $batch?->course;

                if (!$student || !$course) {
                    continue;
                }

                // Check if already in readmission table for this semester
                $alreadyExists = Readmission::where('student_id', $student->id)
                    ->where('semester_id', $group->semester_id)
                    ->whereIn('status', ['PENDING', 'APPROVED', 'CONTINUED_WITH_RETAKE'])
                    ->exists();

                if (!$alreadyExists) {
                    $subjectIdArray = array_map('intval', explode(',', $group->subject_ids));
                    $fee = (float)($course->readmission_fee ?: ($course->admission_fee ?: 0.00));

                    Readmission::create([
                        'student_id'            => $student->id,
                        'enrollment_id'         => $student->enrollments()->where('batch_id', $batch->id)->value('id'),
                        'course_id'             => $course->id,
                        'semester_id'           => $group->semester_id,
                        'from_batch_id'         => $batch->id,
                        'failed_subjects_count' => (int)$group->fail_count,
                        'failed_subject_ids'    => $subjectIdArray,
                        'readmission_fee'       => $fee,
                        'status'                => 'PENDING',
                        'notes'                 => "অটো-ডিটেকশন: সেমিস্টারে {$group->fail_count}টি বিষয়ে অনুত্তীর্ণ (Failed > 2 subjects)",
                    ]);
                    $newDetected++;
                }
            }
        } catch (\Throwable $e) {
            // Log if table is empty or error
        }

        // 2. Scan from SubjectRetake where a student has > 2 retakes pending for the same course/enrollment
        try {
            $retakeGroups = SubjectRetake::whereIn('status', ['PENDING', 'IN_PROGRESS'])
                ->select(
                    'student_id',
                    'enrollment_id',
                    DB::raw('count(DISTINCT subject_id) as retake_count'),
                    DB::raw('GROUP_CONCAT(DISTINCT subject_id) as subject_ids')
                )
                ->groupBy('student_id', 'enrollment_id')
                ->having('retake_count', '>', 2)
                ->get();

            foreach ($retakeGroups as $rg) {
                $enrollment = Enrollment::find($rg->enrollment_id);
                $student    = Student::find($rg->student_id);
                if (!$enrollment || !$student) {
                    continue;
                }

                $course = $enrollment->course ?? $enrollment->batch?->course;
                if (!$course) {
                    continue;
                }

                $alreadyExists = Readmission::where('student_id', $student->id)
                    ->where('semester_id', $enrollment->semester_id)
                    ->whereIn('status', ['PENDING', 'APPROVED', 'CONTINUED_WITH_RETAKE'])
                    ->exists();

                if (!$alreadyExists) {
                    $subjectIdArray = array_map('intval', explode(',', $rg->subject_ids));
                    $fee = (float)($course->readmission_fee ?: ($course->admission_fee ?: 0.00));

                    Readmission::create([
                        'student_id'            => $student->id,
                        'enrollment_id'         => $enrollment->id,
                        'course_id'             => $course->id,
                        'semester_id'           => $enrollment->semester_id,
                        'from_batch_id'         => $enrollment->batch_id,
                        'failed_subjects_count' => (int)$rg->retake_count,
                        'failed_subject_ids'    => $subjectIdArray,
                        'readmission_fee'       => $fee,
                        'status'                => 'PENDING',
                        'notes'                 => "অটো-ডিটেকশন: শিক্ষার্থীর {$rg->retake_count}টি বিষয়ে রিটেক আবেদন রয়েছে (> ২ বিষয়)",
                    ]);
                    $newDetected++;
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        if ($newDetected > 0) {
            return back()->with('success', "✅ নতুন {$newDetected} জন শিক্ষার্থীকে রি-এডমিশন তালিকায় সনাক্ত করা হয়েছে (২-এর বেশি বিষয়ে অনুত্তীর্ণ)!");
        }

        return back()->with('info', "নতুন কোনো ২-এর বেশি বিষয়ে অনুত্তীর্ণ শিক্ষার্থী পাওয়া যায়নি বা সকলেই ইতোমধ্যে তালিকায় রয়েছে।");
    }

    /**
     * Delete/Cancel a re-admission entry
     */
    public function destroy(Readmission $readmission)
    {
        $readmission->delete();
        return back()->with('success', 'রি-এডমিশন রেকর্ডটি বাতিল/মুছে ফেলা হয়েছে।');
    }
}
