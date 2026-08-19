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

        $totalCount   = $adminAdmissions->count() + $publicApplications->count();
        $adminCount   = $adminAdmissions->count();
        $publicCount  = $publicApplications->count();
        $publicPending = AdmissionForm::where('source', 'PUBLIC')->where('status', 'PENDING')->count();

        return view('admin.admissions.index', compact(
            'adminAdmissions', 'publicApplications',
            'totalCount', 'adminCount', 'publicCount', 'publicPending',
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
            'gender'                  => 'nullable|in:Male,Female,Other',
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
                $waiverApp = \App\Models\WaiverApplication::where('application_no', $code)->first();
                if ($waiverApp && $waiverApp->status === 'APPROVED' && !$waiverApp->is_used) {
                    $waiverCode = $code;
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
        $admission->load(['student', 'interestedCourse', 'reviewer']);
        $activeBatches = Batch::where('course_id', $admission->interested_course_id)
            ->where('status', 'ACTIVE')
            ->get();

        return view('admin.admissions.show', compact('admission', 'activeBatches'));
    }

    public function approve(Request $request, AdmissionForm $admission)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
        ]);

        return DB::transaction(function () use ($admission, $request) {
            $student = $admission->student;
            $batch   = Batch::findOrFail($request->input('batch_id'));

            // Generate Student Code (e.g. STD-2026-005)
            if (empty($student->student_code)) {
                $nextId = Student::whereNotNull('student_code')->max('id') + 1;
                $student->student_code = 'STD-' . date('Y') . '-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
            }

            $student->status = 'ACTIVE';
            $student->save();

            // ── AUTO-CREATE USER ACCOUNT ──────────────────────────────────
            // Only create if not already linked to a user account
            if (empty($student->user_id)) {
                $loginEmail    = $student->email ?: ($student->student_code . '@iom.student');
                $tempPassword  = $student->phone ?: 'iom@1234';

                // If email already taken by another user, use student_code based email
                if (User::where('email', $loginEmail)->exists()) {
                    $loginEmail = strtolower(str_replace([' ', '-'], '.', $student->student_code)) . '@iom.student';
                }

                $user = User::create([
                    'name'     => $student->name,
                    'email'    => $loginEmail,
                    'password' => Hash::make($tempPassword),
                    'role'     => 'student',
                ]);

                // Link User to Student
                $student->user_id = $user->id;
                $student->save();
            }
            // ─────────────────────────────────────────────────────────────

            // Update Admission Form
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
            \App\Services\AccountingService::createAdmissionInvoice($student, $admission, $enrollment);
            \App\Services\AccountingService::createSemesterInvoice($student, $enrollment);

            $loginInfo = empty($student->email)
                ? "Login: {$student->user?->email} | Password: {$student->phone}"
                : "Login: {$student->email} | Password: {$student->phone}";

            return back()->with('success', "Admission APPROVED! Student Code: {$student->student_code}, Batch: {$batch->name}. 🔑 {$loginInfo}");
        });
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
