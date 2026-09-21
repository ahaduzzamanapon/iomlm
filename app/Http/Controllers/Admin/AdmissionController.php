<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdmissionForm;
use App\Models\Student;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdmissionController extends Controller
{
    public function index(Request $request)
    {
        $tab    = $request->query('tab', 'all');
        $status = $request->query('status', '');
        $search = $request->query('search', '');

        $base = AdmissionForm::with(['student', 'interestedCourse', 'session', 'reviewer']);

        // Apply status filter
        if ($status) {
            $base->where('status', $status);
        }

        // Apply search
        if ($search) {
            $base->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })->orWhere('application_no', 'like', "%{$search}%");
        }

        $adminAdmissions    = (clone $base)->where('source', 'ADMIN')->latest()->get();
        $publicApplications = (clone $base)->where('source', 'PUBLIC')->latest()->get();

        $paidFormIds = \App\Models\GatewayTransaction::where('status', 'SUCCESS')->whereNotNull('admission_form_id')->pluck('admission_form_id');
        $paidInvoiceFormIds = \App\Models\Invoice::where('source_type', AdmissionForm::class)->where('status', 'PAID')->pluck('source_id');
        $allPaidFormIds = $paidFormIds->merge($paidInvoiceFormIds)->unique();

        $unpaidApplications = (clone $base)->whereNotIn('id', $allPaidFormIds)->latest()->get();
        $unpaidCount = $unpaidApplications->count();

        $totalCount   = $adminAdmissions->count() + $publicApplications->count();
        $adminCount   = $adminAdmissions->count();
        $publicCount  = $publicApplications->count();
        $publicPending = AdmissionForm::where('source', 'PUBLIC')->where('status', 'PENDING')->count();

        return view('admin.admissions.index', compact(
            'adminAdmissions', 'publicApplications', 'unpaidApplications', 'allPaidFormIds',
            'totalCount', 'adminCount', 'publicCount', 'publicPending', 'unpaidCount',
            'tab', 'status', 'search'
        ));
    }

    public function create()
    {
        $courses       = Course::where('is_active', true)->orderBy('name')->get();
        $activeBatches = \App\Models\Batch::where('status', 'ACTIVE')->get();
        $sessions      = \App\Models\AcademicSession::where('is_active', true)->orderByDesc('id')->get();
        $bloodGroups   = \App\Models\BloodGroup::active()->get();
        $religions     = \App\Models\Religion::active()->get();
        $divisions     = \App\Models\Division::orderBy('name')->get();

        return view('admin.admissions.create', compact(
            'courses', 'activeBatches', 'sessions', 'bloodGroups', 'religions', 'divisions'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'interested_course_id'   => 'required|exists:courses,id',
            'batch_id'                => 'nullable|exists:batches,id',
            'academic_session_id'     => 'nullable|exists:academic_sessions,id',
            'applicant_name'          => 'required|string|max:200',
            'phone'                   => 'required|string|max:30',
            'email'                   => 'nullable|email|max:150',
            'date_of_birth'           => 'nullable|date',
            'gender'                  => 'nullable|in:Male,Female,Other,male,female,other',
            'device_type'             => 'nullable|string|max:50',
            'occupation'              => 'nullable|string|max:100',
            'education_qualification' => 'nullable|string|max:100',
            'ssc_school'              => 'nullable|string|max:200',
            'ssc_board'               => 'nullable|string|max:100',
            'ssc_year'                => 'nullable|integer|min:1990|max:' . now()->year,
            'ssc_gpa'                 => 'nullable|numeric|min:0|max:5',
            'hsc_college'             => 'nullable|string|max:200',
            'hsc_board'               => 'nullable|string|max:100',
            'hsc_year'                => 'nullable|integer|min:1990|max:' . now()->year,
            'hsc_gpa'                 => 'nullable|numeric|min:0|max:5',
            'university_name'         => 'nullable|string|max:200',
            'department_name'         => 'nullable|string|max:100',
            'blood_group_id'          => 'nullable|exists:blood_groups,id',
            'religion_id'             => 'nullable|exists:religions,id',
            'national_id'             => 'nullable|string|max:50',
            'passport_no'             => 'nullable|string|max:50',
            'birth_certificate_no'    => 'nullable|string|max:50',
            'nationality'             => 'nullable|string|max:50',
            'guardian_name'           => 'nullable|string|max:200',
            'guardian_phone'          => 'nullable|string|max:30',
            'present_house'           => 'nullable|string|max:300',
            'present_post_office'     => 'nullable|string|max:100',
            'present_police_station'  => 'nullable|string|max:100',
            'present_district_id'     => 'nullable|exists:districts,id',
            'present_division_id'     => 'nullable|exists:divisions,id',
            'same_as_present'         => 'nullable|boolean',
            'permanent_house'         => 'nullable|string|max:300',
            'permanent_post_office'   => 'nullable|string|max:100',
            'permanent_police_station'=> 'nullable|string|max:100',
            'permanent_district_id'   => 'nullable|exists:districts,id',
            'permanent_division_id'   => 'nullable|exists:divisions,id',
            'lead_source'             => 'nullable|string',
            'discount_percent'        => 'nullable|numeric|min:0|max:100',
            'waiver_code'             => 'nullable|string|max:50',
            'notes'                   => 'nullable|string',
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $request) {
            $sameAsPresent = $request->boolean('same_as_present');

            // Check Waiver Code
            $waiverCode = null;
            $waiverApp  = null;
            if (!empty($validated['waiver_code'])) {
                $code = strtoupper(trim($validated['waiver_code']));
                $altCode = str_starts_with($code, 'PF-')
                    ? str_replace('PF-', 'POOR-', $code)
                    : (str_starts_with($code, 'POOR-') ? str_replace('POOR-', 'PF-', $code) : $code);

                $waiverApp = \App\Models\WaiverApplication::where(function ($q) use ($code, $altCode) {
                    $q->where('application_no', $code)->orWhere('application_no', $altCode);
                })->first();

                if ($waiverApp && $waiverApp->status === 'APPROVED' && !$waiverApp->is_used) {
                    $waiverCode = $waiverApp->application_no;
                    if (empty($validated['discount_percent'])) {
                        $validated['discount_percent'] = $waiverApp->approved_discount_percent;
                    }
                }
            }

            // Find blood group name if ID provided
            $bloodGroupName = null;
            if (!empty($validated['blood_group_id'])) {
                $bloodGroupName = \App\Models\BloodGroup::find($validated['blood_group_id'])?->name;
            }

            // Create Student as LEAD/PENDING
            $student = Student::create([
                'name'             => $validated['applicant_name'],
                'email'            => $validated['email'] ?? null,
                'phone'            => $validated['phone'],
                'date_of_birth'    => $validated['date_of_birth'] ?? null,
                'gender'           => $validated['gender'] ?? null,
                'blood_group'      => $bloodGroupName,
                'national_id'      => $validated['national_id'] ?? null,
                'address'          => $validated['present_house'] ?? null,
                'guardian_name'    => $validated['guardian_name'] ?? null,
                'guardian_phone'   => $validated['guardian_phone'] ?? null,
                'ssc_gpa'          => $validated['ssc_gpa'] ?? null,
                'hsc_gpa'          => $validated['hsc_gpa'] ?? null,
                'status'           => 'PENDING',
            ]);

            $student->calculateProfileCompletion();

            // Create Admission Form with source=ADMIN
            $form = AdmissionForm::create([
                'source'                  => 'ADMIN',
                'application_no'          => AdmissionForm::generateApplicationNo(),
                'student_id'              => $student->id,
                'interested_course_id'    => $validated['interested_course_id'],
                'batch_id'                => $validated['batch_id'] ?? null,
                'academic_session_id'     => $validated['academic_session_id'] ?? null,
                'attempt_no'              => 1,
                'lead_source'             => $validated['lead_source'] ?? 'Direct',
                'discount_percent'        => $validated['discount_percent'] ?? 0,
                'waiver_code'             => $waiverCode,
                'status'                  => 'PENDING',
                'notes'                   => $validated['notes'] ?? null,

                // Education Info
                'occupation'              => $validated['occupation'] ?? null,
                'education_qualification' => $validated['education_qualification'] ?? null,
                'ssc_school'              => $validated['ssc_school'] ?? null,
                'ssc_board'               => $validated['ssc_board'] ?? null,
                'ssc_year'                => $validated['ssc_year'] ?? null,
                'hsc_college'             => $validated['hsc_college'] ?? null,
                'hsc_board'               => $validated['hsc_board'] ?? null,
                'hsc_year'                => $validated['hsc_year'] ?? null,
                'university_name'         => $validated['university_name'] ?? null,
                'department_name'         => $validated['department_name'] ?? null,
                'device_type'             => $validated['device_type'] ?? null,

                // Personal Info
                'blood_group_id'          => $validated['blood_group_id'] ?? null,
                'passport_no'             => $validated['passport_no'] ?? null,
                'birth_certificate_no'    => $validated['birth_certificate_no'] ?? null,
                'nationality'             => $validated['nationality'] ?? 'Bangladeshi',
                'religion_id'             => $validated['religion_id'] ?? null,

                // Present Address
                'present_house'           => $validated['present_house'] ?? null,
                'present_post_office'     => $validated['present_post_office'] ?? null,
                'present_police_station'  => $validated['present_police_station'] ?? null,
                'present_district_id'     => $validated['present_district_id'] ?? null,
                'present_division_id'     => $validated['present_division_id'] ?? null,

                // Permanent Address
                'same_as_present'         => $sameAsPresent,
                'permanent_house'         => $sameAsPresent ? ($validated['present_house'] ?? null)          : ($validated['permanent_house'] ?? null),
                'permanent_post_office'   => $sameAsPresent ? ($validated['present_post_office'] ?? null)    : ($validated['permanent_post_office'] ?? null),
                'permanent_police_station'=> $sameAsPresent ? ($validated['present_police_station'] ?? null) : ($validated['permanent_police_station'] ?? null),
                'permanent_district_id'   => $sameAsPresent ? ($validated['present_district_id'] ?? null)    : ($validated['permanent_district_id'] ?? null),
                'permanent_division_id'   => $sameAsPresent ? ($validated['present_division_id'] ?? null)    : ($validated['permanent_division_id'] ?? null),
            ]);

            // Mark waiver application as USED if applicable
            if ($waiverApp) {
                $waiverApp->update([
                    'is_used'           => true,
                    'admission_form_id' => $form->id,
                ]);
            }

            return redirect()->route('admin.admissions.show', $form)
                ->with('success', 'Admission application created successfully (Source: Admin).');
        });
    }

    public function show(AdmissionForm $admission)
    {
        $admission->load(['student', 'interestedCourse', 'reviewer', 'batch']);
        $activeBatches = Batch::where('course_id', $admission->interested_course_id)
            ->where('status', 'ACTIVE')
            ->get();
        $allCourses = Course::where('is_active', true)->with(['batches' => function($q) {
            $q->where('status', 'ACTIVE');
        }])->orderBy('name')->get();

        return view('admin.admissions.show', compact('admission', 'activeBatches', 'allCourses'));
    }

    public function approve(Request $request, AdmissionForm $admission)
    {
        $request->validate([
            'batch_id'  => 'required|exists:batches,id',
            'course_id' => 'nullable|exists:courses,id',
        ]);

        return DB::transaction(function () use ($admission, $request) {
            $student = $admission->student;
            $batch   = Batch::findOrFail($request->input('batch_id'));

            // 1. Allow Admin to change course prior to confirmation
            if ($request->filled('course_id') && $request->course_id != $admission->interested_course_id) {
                $admission->interested_course_id = $request->course_id;
            }

            // 2. Allow Admin to adjust fee structure prior to confirmation
            if ($request->filled('approved_admission_fee')) {
                $admission->approved_admission_fee = (float) $request->approved_admission_fee;
            }
            if ($request->has('discount_percent')) {
                $admission->discount_percent = (float) $request->discount_percent;
            }
            if ($request->has('discount_amount')) {
                $admission->discount_amount = (float) $request->discount_amount;
            }
            if ($request->has('waiver_notes')) {
                $admission->waiver_notes = $request->waiver_notes;
            }
            $admission->batch_id = $batch->id;

            // ── GENERATE CUSTOM STUDENT ID (YY-BB-CC-G-RRRR) ─────────────
            if (empty($student->student_code)) {
                // 1. Year Code (2 digits)
                $yearCode = date('y');

                // 2. Batch Code (2 digits)
                $batchNum = 1;
                if (!empty($batch->batch_code) && preg_match('/\d+/', $batch->batch_code, $m)) {
                    $batchNum = (int) $m[0];
                } elseif (!empty($batch->name) && preg_match('/\d+/', $batch->name, $m)) {
                    $batchNum = (int) $m[0];
                } else {
                    $batchNum = $batch->id;
                }
                $batchCode = str_pad($batchNum % 100, 2, '0', STR_PAD_LEFT);

                // 3. Course Code (2 digits - Digits 5 & 6)
                $course = $batch->course ?: Course::find($batch->course_id);
                if ($course && !empty($course->code)) {
                    $digits = preg_replace('/\D/', '', $course->code);
                    $courseCode = !empty($digits) ? str_pad(substr($digits, 0, 2), 2, '0', STR_PAD_LEFT) : str_pad(($course->id % 100), 2, '0', STR_PAD_LEFT);
                } else {
                    $courseCode = str_pad(($batch->course_id ?: 1) % 100, 2, '0', STR_PAD_LEFT);
                }

                // 4. Gender Code (1 digit: 1 = Male, 2 = Female)
                $genderCode = '1';
                if (!empty($student->gender)) {
                    $g = strtolower(trim($student->gender));
                    if (in_array($g, ['female', '2', 'f', 'নারি', 'মহিলা'])) {
                        $genderCode = '2';
                    }
                }

                // 5. Roll Sequence (4 digits)
                $filterPrefix = "{$yearCode}{$batchCode}{$courseCode}";
                $existingCount = Student::where('student_code', 'like', "{$filterPrefix}%")
                    ->orWhere('student_code', 'like', "{$yearCode}-{$batchCode}-{$courseCode}-%")
                    ->count();
                $seqNo = str_pad($existingCount + 1, 4, '0', STR_PAD_LEFT);

                $student->student_code = "{$yearCode}{$batchCode}{$courseCode}{$genderCode}{$seqNo}";
            }

            // Sync all profile details from admission form into student
            $student->blood_group    = $student->blood_group ?: ($admission->bloodGroup?->name ?? $admission->blood_group);
            $student->father_name    = $student->father_name ?: $admission->father_name;
            $student->mother_name    = $student->mother_name ?: $admission->mother_name;
            $student->guardian_name  = $student->guardian_name ?: $admission->guardian_name;
            $student->guardian_phone = $student->guardian_phone ?: $admission->guardian_phone;
            $student->national_id    = $student->national_id ?: $admission->national_id;
            $student->address        = $student->address ?: ($admission->present_house ?: $admission->permanent_house);
            $student->email          = $student->email ?: $admission->email;
            $student->phone          = $student->phone ?: $admission->phone;
            $student->gender         = $student->gender ?: $admission->gender;
            $student->date_of_birth  = $student->date_of_birth ?: $admission->date_of_birth;

            $student->status = 'ACTIVE';
            $student->save();
            $student->calculateProfileCompletion();

            // ── AUTO-CREATE OR RETRIEVE USER ACCOUNT ──────────────────────
            $rawPassword = $request->input('custom_password') ?: ($student->phone ?: 'iom@1234');
            if (empty($student->user_id)) {
                $loginEmail = $student->email ?: ($student->student_code . '@iom.student');
                if (User::where('email', $loginEmail)->exists()) {
                    $loginEmail = strtolower(str_replace([' ', '-'], '.', $student->student_code)) . '@iom.student';
                }

                $user = User::create([
                    'name'     => $student->name,
                    'email'    => $loginEmail,
                    'password' => Hash::make($rawPassword),
                    'role'     => 'student',
                ]);

                $student->user_id = $user->id;
                $student->save();
            } else {
                $user = $student->user;
                if ($request->filled('custom_password')) {
                    $user->password = Hash::make($rawPassword);
                    $user->save();
                }
            }

            // Update Admission Form with Reviewer ID (Admin Audit)
            $admission->update([
                'status'      => 'APPROVED',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Create Enrollment
            $enrollment = Enrollment::create([
                'student_id'        => $student->id,
                'batch_id'          => $batch->id,
                'course_id'         => $batch->course_id,
                'semester_id'       => $batch->semesterPosition?->current_semester_id,
                'admission_form_id' => $admission->id,
                'enrolled_at'       => now()->toDateString(),
                'status'            => 'ACTIVE',
            ]);

            // Auto-generate Admission & Initial Semester Fee Invoices
            $initialSemester = $batch->semesterPosition?->currentSemester
                ?? $batch->course?->semesters()->orderBy('sequence_no')->first();

            \App\Services\AccountingService::createAdmissionInvoice($student, $admission, $enrollment);
            \App\Services\AccountingService::createSemesterInvoice($student, $enrollment, $initialSemester);

            // ── DISPATCH BATCH-SPECIFIC ADMISSION APPROVAL EMAIL & SMS ───
            $emailTpl = $batch->getEffectiveEmailTemplate();
            $smsTpl   = $batch->getEffectiveSmsTemplate();

            $courseName = $admission->interestedCourse->name ?? 'Islamic Online Madrasah';
            $loginUrl   = url('/login');

            $replaceVars = [
                '{name}'       => $student->name,
                '{roll}'       => $student->student_code,
                '{student_id}' => $student->student_code,
                '{password}'   => $rawPassword,
                '{course}'     => $courseName,
                '{batch}'      => $batch->name,
                '{login_url}'  => $loginUrl,
            ];

            $compiledEmailBody = str_replace(array_keys($replaceVars), array_values($replaceVars), $emailTpl);
            $compiledSmsBody   = str_replace(array_keys($replaceVars), array_values($replaceVars), $smsTpl);

            // Log SMS dispatch
            \Illuminate\Support\Facades\Log::info("ADMISSION_CONFIRMATION_SMS to {$student->phone}: {$compiledSmsBody}");

            $targetEmail = $student->email ?: ($user ? $user->email : null);
            if (!empty($targetEmail) && filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
                try {
                    $mailService = app(\App\Services\DynamicMailService::class);
                    $subject = "🎉 ভর্তি নিশ্চিতকরণ ও অফিসিয়াল রোল নম্বর — {$student->name} ({$courseName})";
                    $mailService->sendHtmlNotification(
                        $targetEmail,
                        $subject,
                        $compiledEmailBody,
                        null,
                        $loginUrl
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Admission Approval Email Exception: ' . $e->getMessage());
                }
            }

            $loginInfo = "Student ID: {$student->student_code} | Login Email: {$user->email} | Password: {$rawPassword}";

            return back()->with('success', "ভর্তি সফলভাবে অনুমোদিত হয়েছে! স্টুডেন্ট আইডি: {$student->student_code}, ব্যাচ: {$batch->name}। 🔑 {$loginInfo} 📧 ব্যাচ টেমপ্লেট অনুযায়ী কনফার্মেশন মেসেজ প্রেরিত হয়েছে।");
        });
    }

    public function sendRepaymentEmail(AdmissionForm $admission)
    {
        $targetEmail = $admission->email ?: $admission->student?->email;
        if (empty($targetEmail) || !filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'আবেদনকারীর কোনো বৈধ ইমেইল ঠিকানা পাওয়া যায়নি।');
        }

        $courseName = $admission->interestedCourse->name ?? 'Course';
        $paymentUrl = route('apply.payment', $admission->application_no);
        $studentName = $admission->student?->name ?? 'সম্মানিত শিক্ষার্থী';

        $subject = "ভর্তি ফি পরিশোধের রিমাইন্ডার — ইসলামিক অনলাইন মাদ্রাসা ({$admission->application_no})";
        $body = "আসসালামু আলাইকুম {$studentName},\n\n"
            . "ইসলামিক অনলাইন মাদ্রাসায় \"{$courseName}\" কোর্সে আপনার ভর্তি আবেদনটি (আবেদন নং: {$admission->application_no}) গ্রহণ করা হয়েছে।\n"
            . "ভর্তি নিশ্চিতকরণের জন্য অনুগ্রহ করে নির্ধারিত ভর্তি ফি পরিশোধ করুন।\n\n"
            . "নিচের লিংকে ক্লিক করে আপনি সরাসরি বিকাশ অথবা অন্যান্য কার্ড/মোবাইল ব্যাংকিংয়ের মাধ্যমে নিরাপদে ফি পরিশোধ করতে পারবেন:\n"
            . "পেমেন্ট লিংক: {$paymentUrl}\n\n"
            . "ফি পরিশোধ সম্পন্ন হলেই আপনার ভর্তি প্রক্রিয়া চূড়ান্তভাবে সম্পন্ন হবে।";

        try {
            $mailService = app(\App\Services\DynamicMailService::class);
            $mailService->sendHtmlNotification($targetEmail, $subject, $body, null, $paymentUrl);
            return back()->with('success', "আবেদনকারীর ইমেইলে ({$targetEmail}) রি-পেমেন্ট লিংক সফলভাবে পাঠানো হয়েছে।");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Repayment Email Exception: ' . $e->getMessage());
            return back()->with('error', 'ইমেইল পাঠাতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, AdmissionForm $admission)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $admission->update([
            'status'           => 'REJECTED',
            'rejection_reason' => $request->input('rejection_reason'),
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        return back()->with('success', 'Admission application rejected. Reason logged for student re-apply.');
    }
}
