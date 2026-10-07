<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdmissionForm;
use App\Models\Student;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\AcademicSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdmissionController extends Controller
{
    public function index(Request $request)
    {
        $tab       = $request->query('tab', 'all');
        $status    = $request->query('status', '');
        $search    = $request->query('search', '');
        $courseId  = $request->query('course_id', '');
        $sessionId = $request->query('session_id', '');
        $gender    = $request->query('gender', '');

        $base = AdmissionForm::with(['student', 'interestedCourse', 'session', 'session.academicYear', 'reviewer']);

        // Apply status filter
        if ($status) {
            if (in_array(strtoupper($status), ['TRASH', 'REJECTED'])) {
                $base->whereIn('status', ['TRASH', 'REJECTED']);
            } else {
                $base->where('status', $status);
            }
        }

        // Apply course filter (Requirement 1)
        if ($courseId) {
            $base->where('interested_course_id', $courseId);
        }

        // Apply session filter (Requirement 2)
        if ($sessionId) {
            $base->where('academic_session_id', $sessionId);
        }

        // Apply gender filter (Requirement 3)
        if ($gender) {
            $base->where(function ($q) use ($gender) {
                $q->where('admission_forms.gender', $gender)
                  ->orWhereHas('student', function ($sq) use ($gender) {
                      $sq->where('gender', $gender);
                  });
            });
        }

        // Apply search
        if ($search) {
            $base->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('student_code', 'like', "%{$search}%");
                })
                ->orWhere('application_no', 'like', "%{$search}%")
                ->orWhere('manual_trx_id', 'like', "%{$search}%");
            });
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

        // Dropdown Data for Filters
        $courses  = Course::where('is_active', true)->orderBy('name')->get();
        $sessions = \App\Models\AcademicSession::with('academicYear')->where('is_active', true)->orderByDesc('id')->get();

        // Course & Session-wise Admission Statistics Report
        $admissionReport = AdmissionForm::query()
            ->leftJoin('students', 'admission_forms.student_id', '=', 'students.id')
            ->select('admission_forms.interested_course_id', 'admission_forms.academic_session_id')
            ->selectRaw('count(admission_forms.id) as total_apps')
            ->selectRaw("count(case when admission_forms.status = 'APPROVED' then 1 end) as approved_count")
            ->selectRaw("count(case when admission_forms.status = 'PENDING' then 1 end) as pending_count")
            ->selectRaw("count(case when COALESCE(admission_forms.gender, students.gender) = 'Male' then 1 end) as male_count")
            ->selectRaw("count(case when COALESCE(admission_forms.gender, students.gender) = 'Female' then 1 end) as female_count")
            ->with(['interestedCourse', 'session', 'session.academicYear'])
            ->groupBy('admission_forms.interested_course_id', 'admission_forms.academic_session_id')
            ->orderByDesc('total_apps')
            ->get();

        $reportTotalApps    = $admissionReport->sum('total_apps');
        $reportApprovedApps = $admissionReport->sum('approved_count');
        $reportPendingApps  = $admissionReport->sum('pending_count');
        $reportMaleApps     = $admissionReport->sum('male_count');
        $reportFemaleApps   = $admissionReport->sum('female_count');

        return view('admin.admissions.index', compact(
            'adminAdmissions', 'publicApplications', 'unpaidApplications', 'allPaidFormIds',
            'totalCount', 'adminCount', 'publicCount', 'publicPending', 'unpaidCount',
            'tab', 'status', 'search', 'courseId', 'sessionId', 'gender',
            'courses', 'sessions', 'admissionReport',
            'reportTotalApps', 'reportApprovedApps', 'reportPendingApps', 'reportMaleApps', 'reportFemaleApps'
        ));
    }

    public function create()
    {
        $courses       = Course::where('is_active', true)->orderBy('name')->get();
        $activeBatches = \App\Models\Batch::where('status', 'ACTIVE')->get();
        $sessions      = \App\Models\AcademicSession::with('academicYear')->where('is_active', true)->orderByDesc('id')->get();
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

            // Find existing student or create new for this specific course
            $targetCourseId = (int) $validated['interested_course_id'];
            $student = Student::where(function ($q) use ($validated) {
                if (!empty($validated['phone'])) {
                    $q->where('phone', $validated['phone']);
                }
                if (!empty($validated['email'])) {
                    $q->orWhere('email', $validated['email']);
                }
            })->where(function ($q) use ($targetCourseId) {
                $q->whereHas('admissionForms', fn($af) => $af->where('interested_course_id', $targetCourseId))
                  ->orWhereHas('enrollments', fn($en) => $en->where('course_id', $targetCourseId));
            })->first();

            if ($student) {
                $student->update(array_filter([
                    'name'             => $validated['applicant_name'] ?? $student->name,
                    'phone'            => $validated['phone'] ?? $student->phone,
                    'date_of_birth'    => $validated['date_of_birth'] ?? $student->date_of_birth,
                    'gender'           => $validated['gender'] ?? $student->gender,
                    'blood_group'      => $bloodGroupName ?? $student->blood_group,
                    'national_id'      => $validated['national_id'] ?? $student->national_id,
                    'address'          => $validated['present_house'] ?? $student->address,
                    'guardian_name'    => $validated['guardian_name'] ?? $student->guardian_name,
                    'guardian_phone'   => $validated['guardian_phone'] ?? $student->guardian_phone,
                    'ssc_gpa'          => $validated['ssc_gpa'] ?? $student->ssc_gpa,
                    'hsc_gpa'          => $validated['hsc_gpa'] ?? $student->hsc_gpa,
                ]));
            } else {
                $prevStudent = Student::where(function ($q) use ($validated) {
                    if (!empty($validated['phone'])) {
                        $q->where('phone', $validated['phone']);
                    }
                    if (!empty($validated['email'])) {
                        $q->orWhere('email', $validated['email']);
                    }
                })->latest('id')->first();

                $studentData = [
                    'name'             => $validated['applicant_name'],
                    'email'            => !empty($validated['email']) ? $validated['email'] : null,
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
                ];

                if ($prevStudent) {
                    foreach (['father_name', 'mother_name', 'guardian_relation', 'permanent_address', 'nationality', 'religion'] as $fld) {
                        if (empty($studentData[$fld]) && !empty($prevStudent->{$fld})) {
                            $studentData[$fld] = $prevStudent->{$fld};
                        }
                    }
                }

                $student = Student::create($studentData);
            }

            $student->calculateProfileCompletion();

            // Create Admission Form with source=ADMIN
            $form = AdmissionForm::create([
                'source'                  => 'ADMIN',
                'application_no'          => AdmissionForm::generateApplicationNo(),
                'student_id'              => $student->id,
                'interested_course_id'    => (int) $validated['interested_course_id'],
                'batch_id'                => !empty($validated['batch_id']) ? (int) $validated['batch_id'] : null,
                'academic_session_id'     => !empty($validated['academic_session_id'])
                    ? (int) $validated['academic_session_id']
                    : (\App\Models\AcademicSession::getActiveSession()?->id ?? \App\Models\AcademicSession::where('is_active', true)->latest('id')->value('id')),
                'attempt_no'              => 1,
                'lead_source'             => $validated['lead_source'] ?? 'Direct',
                'discount_percent'        => $validated['discount_percent'] ?? 0,
                'waiver_code'             => $waiverCode,
                'status'                  => 'PENDING',
                'notes'                   => $validated['notes'] ?? null,
                'gender'                  => $validated['gender'] ?? $student->gender,

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
                'blood_group_id'          => !empty($validated['blood_group_id']) ? (int) $validated['blood_group_id'] : null,
                'passport_no'             => $validated['passport_no'] ?? null,
                'birth_certificate_no'    => $validated['birth_certificate_no'] ?? null,
                'nationality'             => $validated['nationality'] ?? 'Bangladeshi',
                'religion_id'             => !empty($validated['religion_id']) ? (int) $validated['religion_id'] : null,

                // Present Address
                'present_house'           => $validated['present_house'] ?? null,
                'present_post_office'     => $validated['present_post_office'] ?? null,
                'present_police_station'  => $validated['present_police_station'] ?? null,
                'present_district_id'     => !empty($validated['present_district_id']) ? (int) $validated['present_district_id'] : null,
                'present_division_id'     => !empty($validated['present_division_id']) ? (int) $validated['present_division_id'] : null,

                // Permanent Address
                'same_as_present'         => $sameAsPresent,
                'permanent_house'         => $sameAsPresent ? ($validated['present_house'] ?? null)          : ($validated['permanent_house'] ?? null),
                'permanent_post_office'   => $sameAsPresent ? ($validated['present_post_office'] ?? null)    : ($validated['permanent_post_office'] ?? null),
                'permanent_police_station'=> $sameAsPresent ? ($validated['present_police_station'] ?? null) : ($validated['permanent_police_station'] ?? null),
                'permanent_district_id'   => $sameAsPresent
                    ? (!empty($validated['present_district_id']) ? (int) $validated['present_district_id'] : null)
                    : (!empty($validated['permanent_district_id']) ? (int) $validated['permanent_district_id'] : null),
                'permanent_division_id'   => $sameAsPresent
                    ? (!empty($validated['present_division_id']) ? (int) $validated['present_division_id'] : null)
                    : (!empty($validated['permanent_division_id']) ? (int) $validated['permanent_division_id'] : null),
            ]);

            // Mark waiver application as USED if applicable
            if ($waiverApp) {
                $waiverApp->update([
                    'is_used'           => true,
                    'admission_form_id' => $form->id,
                ]);
            }

            return redirect()->route('admin.admissions.show', $form)
                ->with('success', 'ভর্তি আবেদন সফলভাবে সংরক্ষিত হয়েছে। আবেদন পর্যালোচনা করে ভর্তি অনুমোদন নিশ্চিত করুন।');
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
        }])->orderByRaw('CAST(code AS UNSIGNED) ASC, code ASC')->get();

        $batchTemplates = [];
        foreach ($allCourses as $c) {
            foreach ($c->batches as $b) {
                $batchTemplates[$b->id] = [
                    'id'             => $b->id,
                    'name'           => $b->name,
                    'batch_code'     => $b->batch_code,
                    'course_id'      => $c->id,
                    'course_name'    => $c->name,
                    'email_template' => $b->getEffectiveEmailTemplate(),
                    'sms_template'   => $b->getEffectiveSmsTemplate(),
                ];
            }
        }

        foreach ($activeBatches as $b) {
            if (!isset($batchTemplates[$b->id])) {
                $batchTemplates[$b->id] = [
                    'id'             => $b->id,
                    'name'           => $b->name,
                    'batch_code'     => $b->batch_code,
                    'course_id'      => $b->course_id,
                    'course_name'    => $b->course?->name ?? 'Course',
                    'email_template' => $b->getEffectiveEmailTemplate(),
                    'sms_template'   => $b->getEffectiveSmsTemplate(),
                ];
            }
        }

        if ($admission->batch && !isset($batchTemplates[$admission->batch->id])) {
            $batchTemplates[$admission->batch->id] = [
                'id'             => $admission->batch->id,
                'name'           => $admission->batch->name,
                'batch_code'     => $admission->batch->batch_code,
                'course_id'      => $admission->batch->course_id,
                'course_name'    => $admission->batch->course?->name ?? 'Course',
                'email_template' => $admission->batch->getEffectiveEmailTemplate(),
                'sms_template'   => $admission->batch->getEffectiveSmsTemplate(),
            ];
        }

        $admissionTemplates = \App\Models\EmailTemplate::where('is_active', true)
            ->where(function ($q) {
                $q->where('category', 'ADMISSION')->orWhereNull('category');
            })
            ->with(['course', 'batch'])
            ->orderBy('name')
            ->get();

        $suggestedTemplate = \App\Models\EmailTemplate::resolveAdmissionTemplate(
            $admission->course_id,
            $admission->batch_id,
            $admission->student?->gender ?? $admission->gender
        );

        return view('admin.admissions.show', compact('admission', 'activeBatches', 'allCourses', 'batchTemplates', 'admissionTemplates', 'suggestedTemplate'));
    }

    public function approve(Request $request, AdmissionForm $admission)
    {
        $request->validate([
            'batch_id'              => 'required|exists:batches,id',
            'course_id'             => 'nullable|exists:courses,id',
            'is_fee_paid'           => 'nullable|boolean',
            'admission_paid_amount' => 'nullable|numeric|min:0',
            'payment_method'        => 'nullable|string|max:50',
            'transaction_id'        => 'nullable|string|max:100',
            'sender_number'         => 'nullable|string|max:50',
            'email_subject'         => 'nullable|string|max:255',
            'email_body'            => 'nullable|string',
            'sms_body'              => 'nullable|string|max:1000',
            'send_email'            => 'nullable|boolean',
            'send_sms'              => 'nullable|boolean',
            'save_template'         => 'nullable|boolean',
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
            $admission->rejection_reason = null;

            // ── GENERATE CUSTOM STUDENT ID (YY-BB-CC-G-RRRR) ─────────────
            $course = $batch->course ?: Course::find($batch->course_id);

            $effectiveGender = $admission->gender ?: ($student->gender ?? 'Male');
            $expectedCourseCode = Student::resolveCourseCode($course, $course?->id);
            $expectedGenderCode = Student::resolveGenderCode($effectiveGender);

            $year = Student::resolveAcademicYearCode($batch);
            $batchNum = Student::resolveBatchNumberCode($batch);
            $expectedPrefix = "{$year}{$batchNum}{$expectedCourseCode}{$expectedGenderCode}";

            $cleanCode = preg_replace('/\D/', '', (string)($student->student_code ?? ''));

            // Check if student already belongs to a different course
            $isDifferentCourse = false;
            if (!empty($cleanCode)) {
                $existingCourseCode = strlen($cleanCode) >= 6 ? substr($cleanCode, 4, 2) : '';
                if ($existingCourseCode !== $expectedCourseCode) {
                    $isDifferentCourse = true;
                }
            }
            if ($student->enrollments()->where('course_id', '!=', $course?->id)->exists()) {
                $isDifferentCourse = true;
            }
            if ($student->admissionForms()->where('id', '!=', $admission->id)->where('interested_course_id', '!=', $course?->id)->exists()) {
                $isDifferentCourse = true;
            }

            $hasPrefixMismatch = empty($cleanCode) || strlen($cleanCode) < 7 || substr($cleanCode, 0, 7) !== $expectedPrefix;

            if ($isDifferentCourse) {
                $newStudent = $student->replicate(['id', 'student_code', 'user_id', 'created_at', 'updated_at']);
                $newStudent->status = 'ACTIVE';
                $newStudent->gender = $effectiveGender;
                $newStudent->student_code = Student::generateStudentCode($batch, $course, $effectiveGender);
                $newStudent->save();

                $rawPassword = $student->getOrGenerateNumericPassword();
                $loginEmail = $newStudent->student_code . '@iom.student';
                $user = User::where('email', $loginEmail)->first();
                if (!$user) {
                    $user = User::create([
                        'name'     => $newStudent->name,
                        'email'    => $loginEmail,
                        'password' => Hash::make($rawPassword),
                        'role'     => 'student',
                    ]);
                }
                $newStudent->user_id = $user->id;
                $newStudent->temporary_password = $rawPassword;
                $newStudent->save();

                $admission->update(['student_id' => $newStudent->id, 'gender' => $effectiveGender]);
                $admission->setRelation('student', $newStudent);
                $student = $newStudent;
            } else {
                if ($hasPrefixMismatch) {
                    $student->student_code = Student::generateStudentCode($batch, $course, $effectiveGender);
                }
                $student->status = 'ACTIVE';
                $student->gender = $effectiveGender;
                $student->save();
                if (empty($admission->gender)) {
                    $admission->update(['gender' => $effectiveGender]);
                }
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
            $student->gender         = $effectiveGender;
            $student->date_of_birth  = $student->date_of_birth ?: $admission->date_of_birth;

            $student->status = 'ACTIVE';
            $student->save();
            $student->calculateProfileCompletion();

            // ── AUTO-CREATE OR RETRIEVE USER ACCOUNT ──────────────────────
            $rawPassword = $request->filled('custom_password')
                ? trim($request->input('custom_password'))
                : $student->getOrGenerateNumericPassword();

            $student->temporary_password = $rawPassword;

            if (empty($student->user_id)) {
                $loginEmail = $student->student_code ? ($student->student_code . '@iom.student') : ($student->email ?: uniqid() . '@iom.student');
                $user = User::where('email', $loginEmail)->first();

                if (!$user) {
                    $user = User::create([
                        'name'     => $student->name,
                        'email'    => $loginEmail,
                        'password' => Hash::make($rawPassword),
                        'role'     => 'student',
                    ]);
                } else {
                    $user->password = Hash::make($rawPassword);
                    $user->save();
                }

                $student->user_id = $user->id;
                $student->save();
            } else {
                $user = $student->user;
                if ($user) {
                    $user->password = Hash::make($rawPassword);
                    $user->save();
                }
                $student->save();
            }

            // Update Admission Form with Reviewer ID (Admin Audit)
            $admission->update([
                'status'      => 'APPROVED',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Auto-determine Initial Semester
            $initialSemester = $batch->semesterPosition?->currentSemester
                ?? $batch->course?->semesters()->orderBy('sequence_no')->first();

            // Create Enrollment
            $enrollment = Enrollment::create([
                'student_id'        => $student->id,
                'batch_id'          => $batch->id,
                'course_id'         => $batch->course_id,
                'semester_id'       => $batch->semesterPosition?->current_semester_id ?? $initialSemester?->id,
                'admission_form_id' => $admission->id,
                'enrolled_at'       => now()->toDateString(),
                'status'            => 'ACTIVE',
            ]);

            // Settle Admission Fee with Admin-specified Paid Amount
            $isFeePaid  = $request->boolean('is_fee_paid');
            $paidAmount = $isFeePaid ? (float) $request->input('admission_paid_amount', 0) : 0.00;

            $paymentDetails = [
                'method'        => $request->input('payment_method', $admission->manual_payment_method ?: 'bKash'),
                'trx_id'        => $request->input('transaction_id', $admission->manual_trx_id),
                'sender_number' => $request->input('sender_number', $admission->manual_sender_phone),
                'notes'         => 'Admission fee payment approved by Admin during admission review',
            ];

            // Auto-generate Admission & Initial Semester Fee Invoices
            \App\Services\AccountingService::createAdmissionInvoice($student, $admission, $enrollment, $paidAmount, $paymentDetails);
            \App\Services\AccountingService::createSemesterInvoice($student, $enrollment, $initialSemester);

            // ── DISPATCH BATCH-SPECIFIC ADMISSION APPROVAL EMAIL & SMS ───
            $course = $batch->course ?: Course::find($batch->course_id);
            $courseName = $course->name ?? 'Islamic Online Madrasah';
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

            $selectedTemplate = $request->filled('email_template_id')
                ? \App\Models\EmailTemplate::find($request->input('email_template_id'))
                : null;

            // Use customized content from request or selected template or fallback to batch defaults
            $rawEmailBody = $request->filled('email_body')
                ? $request->input('email_body')
                : ($selectedTemplate ? $selectedTemplate->content : $batch->getEffectiveEmailTemplate());

            $rawSmsBody = $request->filled('sms_body')
                ? $request->input('sms_body')
                : $batch->getEffectiveSmsTemplate();

            $defaultSubject = $selectedTemplate?->subject
                ?: "🎉 ভর্তি নিশ্চিতকরণ ও অফিসিয়াল রোল নম্বর — {$student->name} ({$courseName})";
            $rawSubject = $request->filled('email_subject')
                ? $request->input('email_subject')
                : $defaultSubject;

            $compiledEmailBody = str_replace(array_keys($replaceVars), array_values($replaceVars), $rawEmailBody);
            $compiledSmsBody   = str_replace(array_keys($replaceVars), array_values($replaceVars), $rawSmsBody);
            $compiledSubject   = str_replace(array_keys($replaceVars), array_values($replaceVars), $rawSubject);

            // Save template for future use in this batch if requested
            if ($request->boolean('save_template', true) && $request->filled('email_body')) {
                $generalizeVars = [
                    $student->name             => '{name}',
                    $admission->applicant_name => '{name}',
                    $student->student_code     => '{roll}',
                    $rawPassword               => '{password}',
                    $courseName                => '{course}',
                    $batch->name               => '{batch}',
                    $loginUrl                  => '{login_url}',
                ];

                $generalizeVars = array_filter($generalizeVars, fn($k) => !empty($k), ARRAY_FILTER_USE_KEY);

                $templateEmail = str_replace(array_keys($generalizeVars), array_values($generalizeVars), $request->input('email_body'));
                $templateSms   = str_replace(array_keys($generalizeVars), array_values($generalizeVars), $request->input('sms_body'));

                $batch->email_template = $templateEmail;
                if ($request->filled('sms_body')) {
                    $batch->sms_template = $templateSms;
                }
                $batch->save();
            }

            // Log / Send SMS dispatch
            if ($request->boolean('send_sms', true)) {
                \Illuminate\Support\Facades\Log::info("ADMISSION_CONFIRMATION_SMS to {$student->phone}: {$compiledSmsBody}");
            }

            // Dispatch Email
            if ($request->boolean('send_email', true)) {
                $targetEmail = $student->email ?: ($user ? $user->email : null);
                if (!empty($targetEmail) && filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
                    try {
                        $mailService = app(\App\Services\DynamicMailService::class);
                        $mailService->sendHtmlNotification(
                            $targetEmail,
                            $compiledSubject,
                            $compiledEmailBody,
                            null,
                            $loginUrl
                        );
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Admission Approval Email Exception: ' . $e->getMessage());
                    }
                }
            }

            $loginInfo = "Student ID: {$student->student_code} | Login Email: {$user->email} | Password: {$rawPassword}";
            $templateSavedNotice = $request->boolean('save_template', true) ? ' 💾 পরবর্তী ব্যবহারের জন্য ব্যাচ টেমপ্লেট সংরক্ষিত হয়েছে।' : '';

            return back()->with('success', "ভর্তি সফলভাবে অনুমোদিত হয়েছে! স্টুডেন্ট আইডি: {$student->student_code}, ব্যাচ: {$batch->name}। 🔑 {$loginInfo} 📧 কনফার্মেশন নোটিফিকেশন প্রক্রিয়াকৃত হয়েছে।{$templateSavedNotice}");
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

    public function trash(Request $request, AdmissionForm $admission)
    {
        $reason = $request->input('trash_reason')
            ?? $request->input('rejection_reason')
            ?? 'Moved to trash by admin';

        $admission->update([
            'status'           => 'TRASH',
            'rejection_reason' => $reason,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        // Cancel any active enrollment linked to this admission form
        Enrollment::where('admission_form_id', $admission->id)->update(['status' => 'CANCELLED']);

        // Check if student has any other APPROVED admission forms or other ACTIVE enrollments
        $student = $admission->student;
        if ($student) {
            $hasOtherApprovedForm = $student->admissionForms()
                ->where('id', '!=', $admission->id)
                ->where('status', 'APPROVED')
                ->exists();
            $hasOtherActiveEnrollment = $student->enrollments()
                ->where('admission_form_id', '!=', $admission->id)
                ->where('status', 'ACTIVE')
                ->exists();

            if (!$hasOtherApprovedForm && !$hasOtherActiveEnrollment) {
                // Free the reserved student_code so the serial sequence is released back to the sequence pool
                $student->student_code = null;
                $student->status = 'LEAD';
                $student->temporary_password = null;

                // Deactivate or remove unapproved student user portal account if created
                if ($student->user_id) {
                    $user = $student->user;
                    $student->user_id = null;
                    $student->save();
                    if ($user && $user->role === 'student') {
                        $user->delete();
                    }
                } else {
                    $student->save();
                }
            }
        }

        return back()->with('success', 'ভর্তি আবেদনটি সফলভাবে ট্র্যাশে (Trash) সরানো হয়েছে এবং সংরক্ষিত স্টুডেন্ট আইডি মুক্ত করা হয়েছে।');
    }

    public function untrash(AdmissionForm $admission)
    {
        if (!in_array($admission->status, ['TRASH', 'REJECTED'])) {
            return back()->with('info', 'আবেদনটি ট্র্যাশে নেই।');
        }

        $oldReason = $admission->rejection_reason;
        $untrashNote = 'রিস্টোর করা হয়েছে (' . now()->format('d M Y, h:i A') . ' - ' . (auth()->user()?->name ?? 'এডমিন') . ')';

        $admission->update([
            'status'           => 'PENDING',
            'notes'            => trim(($admission->notes ? $admission->notes . "\n" : '') . "Untrashed: " . $untrashNote . ($oldReason ? " (পূর্বের কারণ: {$oldReason})" : "")),
            'rejection_reason' => null,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        return back()->with('success', 'আবেদনটি ট্র্যাশ থেকে সফলভাবে পুনরুদ্ধার (Untrash) করা হয়েছে! এখন আপনি এটি পর্যালোচনা করে ভর্তি অনুমোদন করতে পারেন।');
    }

    public function reject(Request $request, AdmissionForm $admission)
    {
        return $this->trash($request, $admission);
    }
}
